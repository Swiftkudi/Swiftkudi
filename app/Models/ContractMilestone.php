<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractMilestone extends Model
{
    use HasFactory;

    public const STATUS_PENDING_FUNDING = 'pending_funding';
    public const STATUS_FUNDED = 'funded';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_REVISION_REQUESTED = 'revision_requested';
    public const STATUS_RELEASED = 'released';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'contract_id', 'escrow_transaction_id', 'title', 'description', 'amount', 'due_at',
        'status', 'submission_message', 'submission_files', 'revision_message',
        'submitted_at', 'approved_at', 'released_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_at' => 'datetime',
        'submission_files' => 'array',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function contract() { return $this->belongsTo(Contract::class); }
    public function escrow() { return $this->belongsTo(EscrowTransaction::class, 'escrow_transaction_id'); }
}
