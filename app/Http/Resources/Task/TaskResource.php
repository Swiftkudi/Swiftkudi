<?php

namespace App\Http\Resources\Task;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for Task model.
 * Used for API responses in the task creation flow.
 */
class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'platform' => $this->platform,
            'task_type' => $this->task_type,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'task_type' => $this->category->task_type,
                ];
            }),
            'budget' => (float) $this->budget,
            'quantity' => (int) $this->quantity,
            'worker_reward_per_task' => (float) $this->worker_reward_per_task,
            'platform_commission' => (float) $this->platform_commission,
            'escrow_amount' => (float) $this->escrow_amount,
            'completed_count' => (int) $this->completed_count,
            'remaining_slots' => $this->remaining_slots,
            'target_url' => $this->target_url,
            'target_account' => $this->target_account,
            'hashtag' => $this->hashtag,
            'proof_type' => $this->proof_type,
            'proof_instructions' => $this->proof_instructions,
            'min_followers' => $this->min_followers,
            'min_account_age_days' => $this->min_account_age_days,
            'min_level' => $this->min_level,
            'max_submissions_per_user' => $this->max_submissions_per_user,
            'is_active' => (bool) $this->is_active,
            'is_approved' => (bool) $this->is_approved,
            'is_featured' => (bool) $this->is_featured,
            'is_sample' => (bool) $this->is_sample,
            'starts_at' => $this->starts_at ? $this->starts_at->toIso8601String() : null,
            'expires_at' => $this->expires_at ? $this->expires_at->toIso8601String() : null,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            
            // Computed attributes
            'is_active_status' => $this->isActive(),
            'has_available_slots' => $this->hasAvailableSlots(),
            'is_premium' => $this->isPremium(),
            'is_micro' => $this->isMicro(),
            'progress_percentage' => $this->progress_percentage,
            
            // Formatted values
            'formatted_budget' => $this->formatted_budget,
            'formatted_reward' => $this->formatted_reward,
            'formatted_commission' => $this->formatted_commission,
            'platform_icon' => $this->platform_icon,
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function with($request): array
    {
        return [
            'success' => true,
        ];
    }
}
