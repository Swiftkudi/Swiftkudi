<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Task;
use App\Models\TaskCompletion;
use App\Models\Wallet;
use App\Services\SwiftKudiService;
use Illuminate\Support\Facades\Artisan;

class AutoApproveTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_approve_processes_pending_completions()
    {
        $client = User::factory()->create();
        $clientWallet = Wallet::create(['user_id' => $client->id, 'withdrawable_balance' => 0, 'promo_credit_balance' => 0, 'escrow_balance' => 10000]);

        $worker = User::factory()->create();
        $workerWallet = Wallet::create(['user_id' => $worker->id, 'withdrawable_balance' => 0, 'promo_credit_balance' => 0]);

        $task = Task::factory()->create(['user_id' => $client->id, 'worker_reward_per_task' => 1000]);

        $completion = TaskCompletion::create(['task_id' => $task->id, 'user_id' => $worker->id, 'status' => TaskCompletion::STATUS_PENDING, 'submitted_at' => now()->subDays(3)]);

        // Run the artisan command directly
        Artisan::call('tasks:auto-approve');

        $this->assertDatabaseHas('task_completions', ['id' => $completion->id, 'status' => TaskCompletion::STATUS_APPROVED]);
        $this->assertDatabaseHas('wallets', ['user_id' => $worker->id]);
    }
}
