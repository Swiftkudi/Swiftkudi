<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for saving task drafts (autosave functionality).
 * Less strict validation since it's just saving a draft.
 */
class SaveTaskDraftRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            // Optional idempotency token for drafts
            'idempotency_token' => ['nullable', 'uuid', 'max:36'],

            // Basic task information - all optional for drafts
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],

            // Category and type - optional for drafts
            'category_id' => ['nullable', 'integer'],
            'task_type' => ['nullable', 'string', 'max:100'],
            'platform' => ['nullable', 'string', 'max:100'],

            // Budget and quantity - optional for drafts
            'budget' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'quantity' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'worker_reward_per_task' => ['nullable', 'numeric', 'min:0', 'max:100000'],

            // Target details
            'target_url' => ['nullable', 'string', 'max:2048'],
            'target_account' => ['nullable', 'string', 'max:100'],
            'hashtag' => ['nullable', 'string', 'max:100'],

            // Proof and instructions
            'proof_type' => ['nullable', 'string', 'max:100'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'proof_instructions' => ['nullable', 'string', 'max:2000'],

            // Requirements
            'min_followers' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'min_account_age_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'min_level' => ['nullable', 'integer', 'min:1', 'max:10'],
            'max_submissions_per_user' => ['nullable', 'integer', 'min:1', 'max:100'],

            // Scheduling
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],

            // Options
            'is_featured' => ['boolean'],
            'is_sample' => ['boolean'],

            // Step-based wizard tracking
            'current_step' => ['nullable', 'integer', 'min:1', 'max:4'],
        ];
    }

    /**
     * Prepare the data for validation - same as CreateTaskRequest.
     */
    protected function prepareForValidation(): void
    {
        // Normalize hashtag
        if ($this->has('hashtag') && $this->hashtag) {
            $hashtag = $this->hashtag;
            if (strpos($hashtag, '#') !== 0) {
                $this->merge(['hashtag' => '#' . $hashtag]);
            }
        }

        // Normalize target account
        if ($this->has('target_account') && $this->target_account) {
            $account = $this->target_account;
            if (strpos($account, '@') !== 0) {
                $this->merge(['target_account' => '@' . $account]);
            }
        }

        // Ensure numeric fields are properly cast
        if ($this->has('budget')) {
            $this->merge([
                'budget' => (float) str_replace([',', ' '], '', $this->budget ?? 0),
            ]);
        }

        if ($this->has('quantity')) {
            $this->merge([
                'quantity' => (int) ($this->quantity ?? 0),
            ]);
        }

        if ($this->has('worker_reward_per_task')) {
            $this->merge([
                'worker_reward_per_task' => $this->worker_reward_per_task 
                    ? (float) str_replace([',', ' '], '', $this->worker_reward_per_task) 
                    : null,
            ]);
        }
    }
}
