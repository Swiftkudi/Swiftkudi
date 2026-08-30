<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisputeController extends Controller
{
    /**
     * Generic dispute creation is intentionally order-driven.
     *
     * A dispute must be attached to an eligible marketplace order/escrow so
     * users cannot manufacture disputes for unrelated records. Keep this
     * route for backwards compatibility and direct users to the order flow.
     */
    public function create(Request $request)
    {
        return redirect()->route('disputes.index')->with(
            'info',
            'Open a dispute from the related order or escrow transaction so SwiftKudi can attach the correct payment and participant records.'
        );
    }

    /**
     * Display all disputes.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Dispute::with(['user', 'order'])
            ->where('user_id', $user->id);

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $disputes = $query->latest()->paginate(15);

        return view('disputes.index', compact('disputes'));
    }

    /**
     * Show dispute details.
     */
    public function show(Dispute $dispute)
    {
        $this->authorize('view', $dispute);
        
        $dispute->load(['user', 'order', 'responses.user']);

        return view('disputes.show', compact('dispute'));
    }

    /**
     * Create a new dispute.
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'order_type' => 'required|in:task,service,growth,digital',
            'subject' => 'required|string|max:255',
            'description' => 'required|string|min:50',
            'evidence' => 'nullable|array',
        ]);

        $dispute = new Dispute([
            'user_id' => Auth::id(),
            'order_id' => $request->order_id,
            'order_type' => $request->order_type,
            'subject' => $request->subject,
            'description' => $request->description,
            'status' => 'open',
        ]);

        if ($request->has('evidence')) {
            $dispute->evidence = json_encode($request->evidence);
        }

        $dispute->save();

        return redirect()->route('disputes.show', $dispute)
            ->with('success', 'Dispute submitted. Our team will review and respond within 48 hours.');
    }

    /**
     * Add response to dispute.
     */
    public function respond(Request $request, Dispute $dispute)
    {
        $this->authorize('respond', $dispute);

        $request->validate([
            'message' => 'required|string|min:10',
        ]);

        $dispute->responses()->create([
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);

        return back()->with('success', 'Response submitted.');
    }

    /**
     * Add private evidence files to a dispute.
     */
    public function submitEvidence(Request $request, Dispute $dispute)
    {
        $userId = (int) Auth::id();
        $participantIds = array_values(array_filter([
            (int) ($dispute->raiser_id ?? 0),
            (int) ($dispute->responder_id ?? 0),
            (int) ($dispute->complainant_id ?? 0),
            (int) ($dispute->respondent_id ?? 0),
            (int) ($dispute->user_id ?? 0),
        ]));

        abort_unless(in_array($userId, $participantIds, true), 403);

        $validated = $request->validate([
            'evidence' => 'required|array|min:1|max:5',
            'evidence.*' => 'required|file|max:10240|mimes:jpg,jpeg,png,webp,pdf,doc,docx,txt,zip',
            'note' => 'nullable|string|max:1000',
        ]);

        $items = is_array($dispute->evidence) ? $dispute->evidence : [];

        foreach ($request->file('evidence', []) as $file) {
            // Evidence is deliberately stored on the private local disk, not
            // the public web disk. Access should always go through an
            // authenticated dispute participant/admin endpoint.
            $path = $file->store("disputes/{$dispute->id}/evidence", 'local');

            $items[] = [
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => $userId,
                'note' => $validated['note'] ?? null,
                'uploaded_at' => now()->toIso8601String(),
            ];
        }

        $dispute->evidence = $items;
        $dispute->save();

        return back()->with('success', 'Evidence uploaded securely.');
    }

    /**
     * Close/resolution dispute (admin only).
     */
    public function resolve(Request $request, Dispute $dispute)
    {
        $request->validate([
            'resolution' => 'required|string|min:20',
            'refund_amount' => 'nullable|numeric|min:0',
        ]);

        $dispute->status = 'resolved';
        $dispute->resolution = $request->resolution;
        $dispute->refund_amount = $request->refund_amount;
        $dispute->resolved_at = now();
        $dispute->save();

        // Process refund if applicable
        if ($request->refund_amount > 0) {
            // Refund logic here
        }

        return back()->with('success', 'Dispute resolved.');
    }
}
