<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_DISPUTED = 'disputed';

    protected $fillable = [
        'contract_no', 'job_id', 'job_application_id', 'client_id', 'freelancer_id',
        'title', 'description', 'contract_type', 'amount', 'hourly_rate', 'status',
        'started_at', 'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function job() { return $this->belongsTo(Job::class); }
    public function application() { return $this->belongsTo(JobApplication::class, 'job_application_id'); }
    public function client() { return $this->belongsTo(User::class, 'client_id'); }
    public function freelancer() { return $this->belongsTo(User::class, 'freelancer_id'); }
    public function milestones() { return $this->hasMany(ContractMilestone::class)->orderBy('id'); }

    public function involves(int $userId): bool
    {
        return $this->client_id === $userId || $this->freelancer_id === $userId;
    }

    public function getProgressPercentAttribute(): int
    {
        $total = $this->milestones->count();
        if ($total === 0) return 0;
        $released = $this->milestones->where('status', ContractMilestone::STATUS_RELEASED)->count();
        return (int) round(($released / $total) * 100);
    }
}
