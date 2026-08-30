<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractMilestone;
use App\Models\EscrowTransaction;
use App\Models\Wallet;
use App\Services\NotificationManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ContractController extends Controller
{
    public function __construct(private NotificationManager $notificationManager)
    {
    }

    public function index(Request $request)
    {
        $userId = Auth::id();
        $query = Contract::with(['client', 'freelancer', 'milestones'])
            ->where(fn ($q) => $q->where('client_id', $userId)->orWhere('freelancer_id', $userId));

        $status = $request->validate([
            'status' => 'nullable|in:active,completed,cancelled,disputed',
        ])['status'] ?? null;

        if ($status) {
            $query->where('status', $status);
        }

        $contracts = $query->latest()->paginate(15)->withQueryString();

        return view('contracts.index', compact('contracts'));
    }

    public function show(Contract $contract)
    {
        $this->authorizeParticipant($contract);
        $contract->load(['client', 'freelancer', 'job', 'application', 'milestones.escrow']);

        return view('contracts.show', compact('contract'));
    }

    public function storeMilestone(Request $request, Contract $contract)
    {
        $this->authorizeClient($contract);
        abort_unless($contract->status === Contract::STATUS_ACTIVE, 422, 'Only active contracts can receive milestones.');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:5000'],
            'amount' => ['required', 'numeric', 'min:1', 'max:1000000000'],
            'due_at' => ['nullable', 'date', 'after:today'],
        ]);

        DB::transaction(function () use ($contract, $validated) {
            ContractMilestone::create([
                'contract_id' => $contract->id,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'amount' => $validated['amount'],
                'due_at' => $validated['due_at'] ?? null,
                'status' => ContractMilestone::STATUS_PENDING_FUNDING,
            ]);
            $contract->increment('amount', (float) $validated['amount']);
        });

        return back()->with('success', 'Milestone added. Fund it when you are ready for work to begin.');
    }

    public function fund(Request $request, Contract $contract, ContractMilestone $milestone)
    {
        $this->authorizeClient($contract);
        $this->ensureMilestoneBelongsToContract($contract, $milestone);

        if ($milestone->status !== ContractMilestone::STATUS_PENDING_FUNDING) {
            return back()->with('error', 'This milestone is not waiting for funding.');
        }

        DB::transaction(function () use ($contract, $milestone) {
            $lockedMilestone = ContractMilestone::whereKey($milestone->id)->lockForUpdate()->firstOrFail();
            if ($lockedMilestone->contract_id !== $contract->id) {
                abort(404);
            }
            if ($lockedMilestone->status !== ContractMilestone::STATUS_PENDING_FUNDING) {
                throw ValidationException::withMessages(['milestone' => 'This milestone has already been funded or processed.']);
            }

            $amount = (float) $lockedMilestone->amount;
            $wallet = Wallet::where('user_id', $contract->client_id)->lockForUpdate()->first();
            if (!$wallet || !$wallet->canAffordTotal($amount)) {
                throw ValidationException::withMessages(['wallet' => 'Your wallet balance is not enough to fund this milestone.']);
            }

            if (!$wallet->addToEscrow($amount)) {
                throw ValidationException::withMessages(['wallet' => 'The milestone could not be funded. Please retry.']);
            }

            $escrow = EscrowTransaction::create([
                'transaction_no' => 'ESC-' . Str::upper(Str::random(12)),
                'order_id' => $lockedMilestone->id,
                'order_type' => 'contract_milestone',
                'payer_id' => $contract->client_id,
                'payee_id' => $contract->freelancer_id,
                'amount' => $amount,
                'platform_fee' => 0,
                'total_amount' => $amount,
                'status' => EscrowTransaction::STATUS_FUNDED,
            ]);

            $lockedMilestone->update([
                'escrow_transaction_id' => $escrow->id,
                'status' => ContractMilestone::STATUS_FUNDED,
                'revision_message' => null,
            ]);
        });

        $milestone->refresh();

        $this->notificationManager->notify(NotificationManager::EVENT_MILESTONE_FUNDED, $contract->freelancer, [
            'contract_title' => $contract->title,
            'milestone_title' => $milestone->title,
            'amount' => (float) $milestone->amount,
            'action_url' => route('contracts.show', $contract),
        ]);

        return back()->with('success', 'Milestone funded securely in escrow.');
    }

    public function start(Contract $contract, ContractMilestone $milestone)
    {
        $this->authorizeFreelancer($contract);
        $this->ensureMilestoneBelongsToContract($contract, $milestone);

        if (!in_array($milestone->status, [ContractMilestone::STATUS_FUNDED, ContractMilestone::STATUS_REVISION_REQUESTED], true)) {
            return back()->with('error', 'This milestone cannot be started right now.');
        }

        $milestone->update(['status' => ContractMilestone::STATUS_IN_PROGRESS]);

        return back()->with('success', 'Milestone marked in progress.');
    }

    public function submit(Request $request, Contract $contract, ContractMilestone $milestone)
    {
        $this->authorizeFreelancer($contract);
        $this->ensureMilestoneBelongsToContract($contract, $milestone);

        if (!in_array($milestone->status, [ContractMilestone::STATUS_FUNDED, ContractMilestone::STATUS_IN_PROGRESS, ContractMilestone::STATUS_REVISION_REQUESTED], true)) {
            return back()->with('error', 'This milestone cannot be submitted right now.');
        }

        $validated = $request->validate([
            'submission_message' => ['required', 'string', 'max:5000'],
            'files' => ['nullable', 'array', 'max:5'],
            'files.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,webp,zip,txt'],
        ]);

        $files = [];
        foreach ($request->file('files', []) as $file) {
            $path = $file->store('contract-submissions/' . $contract->id . '/' . $milestone->id);
            $files[] = ['path' => $path, 'name' => $file->getClientOriginalName(), 'size' => $file->getSize()];
        }

        $milestone->update([
            'status' => ContractMilestone::STATUS_SUBMITTED,
            'submission_message' => $validated['submission_message'],
            'submission_files' => $files,
            'revision_message' => null,
            'submitted_at' => now(),
        ]);

        $this->notificationManager->notify(NotificationManager::EVENT_MILESTONE_SUBMITTED, $contract->client, [
            'contract_title' => $contract->title,
            'milestone_title' => $milestone->title,
            'action_url' => route('contracts.show', $contract),
        ]);

        return back()->with('success', 'Work submitted for client review.');
    }

    public function requestRevision(Request $request, Contract $contract, ContractMilestone $milestone)
    {
        $this->authorizeClient($contract);
        $this->ensureMilestoneBelongsToContract($contract, $milestone);
        abort_unless($milestone->status === ContractMilestone::STATUS_SUBMITTED, 422, 'Only submitted work can be sent back for revision.');

        $validated = $request->validate(['revision_message' => ['required', 'string', 'max:5000']]);
        $milestone->update([
            'status' => ContractMilestone::STATUS_REVISION_REQUESTED,
            'revision_message' => $validated['revision_message'],
        ]);

        $this->notificationManager->notify(NotificationManager::EVENT_MILESTONE_REVISION_REQUESTED, $contract->freelancer, [
            'contract_title' => $contract->title,
            'milestone_title' => $milestone->title,
            'action_url' => route('contracts.show', $contract),
        ]);

        return back()->with('success', 'Revision request sent to the freelancer.');
    }

    public function approve(Contract $contract, ContractMilestone $milestone)
    {
        $this->authorizeClient($contract);
        $this->ensureMilestoneBelongsToContract($contract, $milestone);

        if ($milestone->status !== ContractMilestone::STATUS_SUBMITTED) {
            return back()->with('error', 'Only submitted work can be approved.');
        }

        DB::transaction(function () use ($contract, $milestone) {
            $lockedMilestone = ContractMilestone::whereKey($milestone->id)->lockForUpdate()->firstOrFail();
            if ($lockedMilestone->status !== ContractMilestone::STATUS_SUBMITTED) {
                throw ValidationException::withMessages(['milestone' => 'This milestone was already processed.']);
            }

            $clientWallet = Wallet::where('user_id', $contract->client_id)->lockForUpdate()->firstOrFail();
            $freelancerWallet = Wallet::where('user_id', $contract->freelancer_id)->lockForUpdate()->firstOrFail();

            if (!$clientWallet->releaseFromEscrow((float) $lockedMilestone->amount, $freelancerWallet)) {
                throw ValidationException::withMessages(['milestone' => 'Escrow release could not be completed. Please contact support.']);
            }

            if ($lockedMilestone->escrow) {
                $lockedMilestone->escrow->release();
            }

            $lockedMilestone->update([
                'status' => ContractMilestone::STATUS_RELEASED,
                'approved_at' => now(),
                'released_at' => now(),
            ]);

            $remaining = ContractMilestone::where('contract_id', $contract->id)
                ->whereNotIn('status', [ContractMilestone::STATUS_RELEASED, ContractMilestone::STATUS_CANCELLED])
                ->exists();

            if (!$remaining) {
                $contract->update(['status' => Contract::STATUS_COMPLETED, 'completed_at' => now()]);
            }
        });

        $this->notificationManager->notify(NotificationManager::EVENT_MILESTONE_RELEASED, $contract->freelancer, [
            'contract_title' => $contract->title,
            'milestone_title' => $milestone->title,
            'amount' => (float) $milestone->amount,
            'action_url' => route('contracts.show', $contract),
        ]);

        return back()->with('success', 'Work approved and milestone payment released.');
    }

    public function download(Contract $contract, ContractMilestone $milestone, int $fileIndex)
    {
        $this->authorizeParticipant($contract);
        $this->ensureMilestoneBelongsToContract($contract, $milestone);
        $files = $milestone->submission_files ?: [];
        abort_unless(isset($files[$fileIndex]['path']), 404);

        $file = $files[$fileIndex];
        abort_unless(Storage::exists($file['path']), 404);

        return Storage::download($file['path'], $file['name'] ?? basename($file['path']));
    }

    private function authorizeParticipant(Contract $contract): void
    {
        abort_unless($contract->involves((int) Auth::id()), 403);
    }

    private function authorizeClient(Contract $contract): void
    {
        abort_unless($contract->client_id === Auth::id(), 403);
    }

    private function authorizeFreelancer(Contract $contract): void
    {
        abort_unless($contract->freelancer_id === Auth::id(), 403);
    }

    private function ensureMilestoneBelongsToContract(Contract $contract, ContractMilestone $milestone): void
    {
        abort_unless($milestone->contract_id === $contract->id, 404);
    }
}
