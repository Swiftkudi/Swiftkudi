<?php

namespace App\Http\Resources\Task;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for TaskCategory model.
 * Used for category selection in task creation.
 */
class TaskCategoryResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'platform' => $this->platform,
            'task_type' => $this->task_type,
            'proof_type' => $this->proof_type,
            'base_price' => (float) ($this->base_price ?? 0),
            'min_price' => (float) ($this->min_price ?? $this->base_price ?? 0),
            'max_price' => (float) ($this->max_price ?? $this->base_price ?? 0),
            'platform_margin' => (float) ($this->platform_margin ?? 25),
            'min_level' => (int) ($this->min_level ?? 1),
            'is_active' => (bool) $this->is_active,
            'is_featured' => (bool) $this->is_featured,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
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
