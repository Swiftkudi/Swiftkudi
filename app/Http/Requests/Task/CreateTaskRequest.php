<?php

namespace App\Http\Requests\Task;

use App\Models\Task;
use App\Models\TaskCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

/**
 * Form Request for creating a new task.
 * Validates all inputs server-side with comprehensive security checks.
 */
class CreateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // User must be authenticated and have task creation permissions
        return auth()->check() && $this->user()->canCreateTasks();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Idempotency token - optional for now
            'idempotency_token' => ['nullable', 'uuid', 'max:36'],

            // Basic task information
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['required', 'string', 'min:10', 'max:5000'],

            // Category and type
            'category_id' => ['required', 'integer', Rule::exists('task_categories', 'id')],
            'task_type' => ['required', 'string'],
            'platform' => ['nullable', 'string', 'max:100'],

            // Budget and quantity
            'budget' => ['required', 'numeric', 'min:100', 'max:1000000'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'worker_reward_per_task' => ['nullable', 'numeric', 'min:0', 'max:100000'],

            // Target details
            'target_url' => ['nullable', 'url', 'max:2048'],
            'target_account' => ['nullable', 'string', 'max:100', 'regex:/^@?[a-zA-Z0-9_.]+$/'],
            'hashtag' => ['nullable', 'string', 'max:100', 'regex:/^#?[a-zA-Z0-9_]+$/'],

            // Proof and instructions
            'proof_type' => ['required', 'string'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'proof_instructions' => ['nullable', 'string', 'max:2000'],

            // Requirements
            'min_followers' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'min_account_age_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'min_level' => ['nullable', 'integer', 'min:1', 'max:10'],
            'max_submissions_per_user' => ['nullable', 'integer', 'min:1', 'max:100'],

            // Scheduling
            'starts_at' => ['nullable', 'date', 'after:now'],
            'expires_at' => ['nullable', 'date', 'after:starts_at', 'after:now'],

            // Options
            'is_featured' => ['boolean'],
            'is_sample' => ['boolean'],
            'save_draft' => ['boolean'],

            // Step-based wizard tracking
            'current_step' => ['nullable', 'integer', 'min:1', 'max:4'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Task title is required.',
            'title.min' => 'Task title must be at least 3 characters.',
            'title.max' => 'Task title cannot exceed 255 characters.',
            'description.required' => 'Task description is required.',
            'description.min' => 'Description must be at least 10 characters.',
            'budget.required' => 'Budget is required.',
            'budget.min' => 'Minimum budget is ₦100.',
            'budget.max' => 'Maximum budget is ₦1,000,000.',
            'quantity.required' => 'Quantity is required.',
            'quantity.min' => 'Minimum quantity is 1.',
            'quantity.max' => 'Maximum quantity is 10,000.',
            'category_id.required' => 'Please select a task category.',
            'category_id.exists' => 'The selected category does not exist.',
            'platform.required' => 'Please select a platform.',
            'platform.in' => 'The selected platform is invalid.',
            'proof_type.required' => 'Please select a proof type.',
            'proof_type.in' => 'The selected proof type is invalid.',
            'target_url.url' => 'Please enter a valid URL.',
            'idempotency_token.required' => 'Security token is missing. Please refresh the page.',
            'idempotency_token.uuid' => 'Invalid security token format.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'task title',
            'description' => 'task description',
            'budget' => 'total budget',
            'quantity' => 'number of submissions',
            'category_id' => 'task category',
            'platform' => 'social platform',
            'proof_type' => 'proof type',
            'target_url' => 'target URL',
            'target_account' => 'target account',
            'hashtag' => 'hashtag',
            'instructions' => 'specific instructions',
            'worker_reward_per_task' => 'reward per task',
            'min_followers' => 'minimum followers',
            'min_account_age_days' => 'minimum account age',
            'min_level' => 'minimum user level',
            'starts_at' => 'start date',
            'expires_at' => 'expiration date',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional cross-field validation
            $this->validateBudgetQuantityRelation($validator);
            // Temporarily disabled to fix form submission
            // $this->validateCategoryPlatformMatch($validator);
        });
    }

    /**
     * Validate that budget and quantity produce a reasonable reward per task.
     */
    protected function validateBudgetQuantityRelation($validator): void
    {
        $budget = (float) $this->input('budget', 0);
        $quantity = (int) $this->input('quantity', 0);

        if ($budget > 0 && $quantity > 0) {
            $rewardPerTask = ($budget * 0.75) / $quantity;

            // Check if reward is too low (below minimum for any task)
            if ($rewardPerTask < 10 && !$this->input('save_draft')) {
                $validator->errors()->add(
                    'budget',
                    'The budget is too low for the selected quantity. Please increase the budget or reduce quantity.'
                );
            }
        }
    }

    /**
     * Validate that the selected category matches the platform.
     */
    protected function validateCategoryPlatformMatch($validator): void
    {
        $categoryId = $this->input('category_id');
        $platform = $this->input('platform');

        if ($categoryId && $platform) {
            $category = TaskCategory::find($categoryId);

            if ($category && $category->platform !== $platform) {
                $validator->errors()->add(
                    'category_id',
                    'The selected category does not match the chosen platform.'
                );
            }
        }
    }

    /**
     * Prepare the data for validation.
     * Sanitize and normalize inputs.
     */
    protected function prepareForValidation(): void
    {
        // Normalize hashtag - add # prefix if not present
        if ($this->has('hashtag') && $this->hashtag) {
            $hashtag = $this->hashtag;
            if (strpos($hashtag, '#') !== 0) {
                $this->merge(['hashtag' => '#' . $hashtag]);
            }
        }

        // Normalize target account - add @ prefix if not present
        if ($this->has('target_account') && $this->target_account) {
            $account = $this->target_account;
            if (strpos($account, '@') !== 0) {
                $this->merge(['target_account' => '@' . $account]);
            }
        }

        // Ensure numeric fields are properly cast
        $this->merge([
            'budget' => (float) str_replace([',', ' '], '', $this->budget ?? 0),
            'quantity' => (int) ($this->quantity ?? 0),
            'worker_reward_per_task' => $this->worker_reward_per_task 
                ? (float) str_replace([',', ' '], '', $this->worker_reward_per_task) 
                : null,
        ]);

        // In this UI, task_type select often carries the category id.
        // If category_id is missing but task_type is numeric, normalize category_id from task_type.
        if ((!$this->filled('category_id')) && is_numeric($this->input('task_type'))) {
            $this->merge(['category_id' => (int) $this->input('task_type')]);
        }

        // Derive platform from category only when category has a concrete platform value.
        // Do not overwrite a user-selected platform with null/empty category platform.
        if ($this->filled('category_id')) {
            $category = TaskCategory::find($this->input('category_id'));
            if ($category && !empty($category->platform)) {
                $this->merge([
                    'platform' => $category->platform,
                ]);
            }
        }
    }
}
