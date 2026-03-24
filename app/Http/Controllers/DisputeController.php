<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisputeController extends Controller
{
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
