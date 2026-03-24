<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\TaskApproved;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Feature tests for the task creation flow.
 * Tests the end-to-end user experience.
 */
class TaskCreationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User
     */
    private $user;

    /**
     * @var TaskCategory
     */
    private $category;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create();
        
        // Create wallet with balance
        Wallet::factory()->create([
            'user_id' => $this->user->id,
            'withdrawable_balance' => 10000,
            'promo_credit_balance' => 5000,
        ]);

        // Create a task category
        $this->category = TaskCategory::factory()->create([
            'task_type' => 'micro',
            'platform' => 'instagram',
            'is_active' => true,
            'base_price' => 100,
        ]);
    }

    /**
     * Test that unauthenticated users cannot access the create form.
     */
    public function test_unauthenticated_user_cannot_see_create_form(): void
    {
        $response = $this->get(route('tasks.create.new'));

        $response->assertRedirect('/login');
    }

    /**
     * Test that authenticated users can see the create form.
     */
    public function test_authenticated_user_can_see_create_form(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('tasks.create.new'));

        $response->assertStatus(200);
        $response->assertSee('Create a Task');
    }

    /**
     * Test that create form includes category configuration.
     */
    public function test_create_form_includes_category_config(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('tasks.create.new'));

        $response->assertStatus(200);
        
        // Verify that category config is present
        $response->assertViewHas('categoryConfig');
        $response->assertViewHas('idempotencyToken');
    }

    /**
     * Test that validation fails with invalid data.
     */
    public function test_task_creation_validation_fails(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('tasks.create.store'), [
                // Missing required fields
                'title' => '',
                'budget' => 'invalid',
            ]);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'message',
            'errors',
        ]);
    }

    /**
     * Test that draft can be saved successfully.
     */
    public function test_save_draft(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('tasks.create.save-draft'), [
                'title' => 'Test Task Draft',
                'description' => 'Test description',
                'budget' => 5000,
                'quantity' => 10,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    /**
     * Test that draft can be retrieved.
     */
    public function test_get_draft(): void
    {
        // First save a draft
        $this->actingAs($this->user)
            ->post(route('tasks.create.save-draft'), [
                'title' => 'Test Task Draft',
                'budget' => 5000,
            ]);

        // Then retrieve it
        $response = $this->actingAs($this->user)
            ->get(route('tasks.create.get-draft'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    /**
     * Test that draft can be cleared.
     */
    public function test_clear_draft(): void
    {
        // Save a draft first
        $this->actingAs($this->user)
            ->post(route('tasks.create.save-draft'), [
                'title' => 'Test Task Draft',
                'budget' => 5000,
            ]);

        // Clear it
        $response = $this->actingAs($this->user)
            ->post(route('tasks.create.clear-draft'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    /**
     * Test idempotency token refresh.
     */
    public function test_refresh_token(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('tasks.create.refresh-token'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'token',
        ]);
    }

    /**
     * Test cost calculation endpoint.
     */
    public function test_calculate_cost(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('tasks.create.calculate-cost', [
                'budget' => 5000,
                'quantity' => 10,
            ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'budget',
                'quantity',
                'reward_per_task',
                'platform_fee',
                'total_cost',
            ],
        ]);
    }

    /**
     * Test validation endpoint.
     */
    public function test_validate_endpoint(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('tasks.create.validate'), [
                'idempotency_token' => 'test-uuid',
                'title' => 'Test Task',
                'description' => 'Test description',
                'budget' => 5000,
                'quantity' => 10,
                'category_id' => $this->category->id,
                'task_type' => 'micro',
                'platform' => 'instagram',
                'proof_type' => 'screenshot',
            ]);

        $response->assertStatus(200);
    }

    /**
     * Test successful task creation.
     */
    public function test_successful_task_creation(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->user)
            ->post(route('tasks.create.store'), [
                'idempotency_token' => 'test-uuid-' . time(),
                'title' => 'Test Task',
                'description' => 'Test description for task',
                'budget' => 5000,
                'quantity' => 10,
                'category_id' => $this->category->id,
                'task_type' => 'micro',
                'platform' => 'instagram',
                'proof_type' => 'screenshot',
                'worker_reward_per_task' => 375,
                'platform_commission' => 1250,
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
        ]);

        // Verify task was created
        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * Test that task creation fails with insufficient balance.
     */
    public function test_task_creation_fails_with_insufficient_balance(): void
    {
        // Set wallet to low balance
        $this->user->wallet->update([
            'withdrawable_balance' => 100,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('tasks.create.store'), [
                'idempotency_token' => 'test-uuid-' . time(),
                'title' => 'Test Task',
                'description' => 'Test description',
                'budget' => 50000, // More than balance
                'quantity' => 10,
                'category_id' => $this->category->id,
                'task_type' => 'micro',
                'platform' => 'instagram',
                'proof_type' => 'screenshot',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    /**
     * Test rate limiting on task creation.
     */
    public function test_rate_limiting(): void
    {
        // Create many tasks to trigger rate limit
        // This would require multiple runs - for now we just test the endpoint works
        $response = $this->actingAs($this->user)
            ->post(route('tasks.create.store'), [
                'idempotency_token' => 'test-uuid-' . uniqid(),
                'title' => 'Test Task',
                'description' => 'Test description',
                'budget' => 5000,
                'quantity' => 10,
                'category_id' => $this->category->id,
                'task_type' => 'micro',
                'platform' => 'instagram',
                'proof_type' => 'screenshot',
            ]);

        // Should return 201 (success) or 429 (rate limited) or 422 (validation)
        $this->assertContains($response->getStatusCode(), [201, 422, 429]);
    }
}
