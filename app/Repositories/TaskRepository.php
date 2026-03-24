<?php

namespace App\Repositories;

use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Repository for Task database operations.
 * Implements Repository pattern for clean data access.
 */
class TaskRepository
{
    /**
     * Create a new task in the database.
     *
     * @param array $data
     * @return Task
     */
    public function create(array $data): Task
    {
        return DB::transaction(function () use ($data) {
            // Calculate pricing if not provided
            if (!isset($data['worker_reward_per_task']) || !isset($data['platform_commission'])) {
                $category = TaskCategory::find($data['category_id'] ?? null);
                $pricing = Task::calculateRewardPerTask(
                    $data['budget'],
                    $data['quantity'],
                    $category
                );

                $data['worker_reward_per_task'] = $data['worker_reward_per_task'] 
                    ?? $pricing['worker_reward_per_task'];
                $data['platform_commission'] = $data['platform_commission'] 
                    ?? $pricing['platform_commission'];
            }

            // Calculate escrow amount
            $data['escrow_amount'] = $data['budget'];

            // Default values
            $data['is_active'] = $data['is_active'] ?? true;
            $data['is_approved'] = $data['is_approved'] ?? true;
            $data['is_featured'] = $data['is_featured'] ?? false;
            $data['is_sample'] = $data['is_sample'] ?? false;
            $data['completed_count'] = 0;
            
            // Set default values for nullable fields that have NOT NULL constraints
            $data['min_followers'] = $data['min_followers'] ?? 0;
            $data['min_account_age_days'] = $data['min_account_age_days'] ?? 0;
            $data['min_level'] = $data['min_level'] ?? 1;
            $data['max_submissions_per_user'] = $data['max_submissions_per_user'] ?? 1;
            $data['proof_instructions'] = $data['proof_instructions'] ?? '';

            return Task::create($data);
        });
    }

    /**
     * Find a task by ID.
     *
     * @param int $id
     * @return Task|null
     */
    public function findById(int $id): ?Task
    {
        return Task::with(['user', 'category'])->find($id);
    }

    /**
     * Find a task by ID with relations.
     *
     * @param int $id
     * @return Task|null
     */
    public function findByIdWithRelations(int $id): ?Task
    {
        return Task::with(['user', 'category', 'completions'])
            ->find($id);
    }

    /**
     * Get all tasks created by a user.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByUser(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return Task::where('user_id', $userId)
            ->withCount(['completions as pending_submissions_count' => function ($q) {
                $q->where('status', 'pending');
            }])
            ->withCount('completions')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get active tasks (available for workers).
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getActiveTasks(int $perPage = 20): LengthAwarePaginator
    {
        return Task::active()
            ->where('is_sample', false)
            ->orderBy('is_featured', 'desc')
            ->orderBy('worker_reward_per_task', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get active tasks excluding a specific user.
     *
     * @param User $user
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getActiveTasksExcludingUser(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return Task::active()
            ->where('user_id', '!=', $user->id)
            ->where('is_sample', false)
            ->orderBy('is_featured', 'desc')
            ->orderBy('worker_reward_per_task', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Update a task.
     *
     * @param Task $task
     * @param array $data
     * @return Task
     */
    public function update(Task $task, array $data): Task
    {
        // Recalculate pricing if budget or quantity changed
        if (isset($data['budget']) || isset($data['quantity'])) {
            $category = $task->category;
            $budget = $data['budget'] ?? $task->budget;
            $quantity = $data['quantity'] ?? $task->quantity;

            $pricing = Task::calculateRewardPerTask($budget, $quantity, $category);

            $data['worker_reward_per_task'] = $data['worker_reward_per_task'] 
                ?? $pricing['worker_reward_per_task'];
            $data['platform_commission'] = $data['platform_commission'] 
                ?? $pricing['platform_commission'];
            $data['escrow_amount'] = $budget;
        }

        $task->update($data);
        return $task->fresh();
    }

    /**
     * Delete a task.
     *
     * @param Task $task
     * @return bool
     */
    public function delete(Task $task): bool
    {
        return $task->delete();
    }

    /**
     * Increment completed count for a task.
     *
     * @param Task $task
     * @return Task
     */
    public function incrementCompletedCount(Task $task): Task
    {
        $task->increment('completed_count');
        return $task->fresh();
    }

    /**
     * Decrement completed count for a task.
     *
     * @param Task $task
     * @return Task
     */
    public function decrementCompletedCount(Task $task): Task
    {
        if ($task->completed_count > 0) {
            $task->decrement('completed_count');
        }
        return $task->fresh();
    }

    /**
     * Get tasks with filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getFilteredTasks(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Task::query();

        // Filter by platform
        if (!empty($filters['platform'])) {
            $query->where('platform', $filters['platform']);
        }

        // Filter by task type
        if (!empty($filters['task_type'])) {
            $query->where('task_type', $filters['task_type']);
        }

        // Filter by category
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Filter by task type group
        if (!empty($filters['task_group'])) {
            $categoryIds = TaskCategory::where('task_type', $filters['task_group'])
                ->pluck('id');
            $query->whereIn('category_id', $categoryIds);
        }

        // Filter by minimum reward
        if (!empty($filters['min_reward'])) {
            $query->where('worker_reward_per_task', '>=', $filters['min_reward']);
        }

        // Filter by featured
        if (!empty($filters['featured'])) {
            $query->where('is_featured', true);
        }

        // Exclude sample tasks in production
        if ($filters['hide_samples'] ?? true) {
            $query->where('is_sample', false);
        }

        return $query->orderBy('is_featured', 'desc')
            ->orderBy('worker_reward_per_task', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get all categories for task creation.
     *
     * @return Collection
     */
    public function getCategories(): Collection
    {
        return TaskCategory::getActiveCategories();
    }

    /**
     * Get categories grouped by type.
     *
     * @return array
     */
    public function getCategoriesGrouped(): array
    {
        return [
            'micro' => TaskCategory::where('task_type', 'micro')->get(),
            'ugc' => TaskCategory::where('task_type', 'ugc')->get(),
            'referral' => TaskCategory::where('task_type', 'referral')->get(),
            'premium' => TaskCategory::where('task_type', 'premium')->get(),
        ];
    }

    /**
     * Get category by ID.
     *
     * @param int $id
     * @return TaskCategory|null
     */
    public function getCategoryById(int $id): ?TaskCategory
    {
        return TaskCategory::find($id);
    }

    /**
     * Get user's recent tasks.
     *
     * @param int $userId
     * @param int $limit
     * @return Collection
     */
    public function getUserRecentTasks(int $userId, int $limit = 5): Collection
    {
        return Task::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get count of active tasks.
     *
     * @return int
     */
    public function getActiveTaskCount(): int
    {
        return Task::active()->count();
    }

    /**
     * Get count of tasks created by user today.
     *
     * @param int $userId
     * @return int
     */
    public function getUserTodayTaskCount(int $userId): int
    {
        return Task::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->count();
    }
}
