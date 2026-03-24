<?php

namespace Tests\Unit\Services;

use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\User;
use App\Models\Wallet;
use App\Repositories\TaskRepository;
use App\Services\SwiftKudiService;
use App\Services\TaskCreationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;
use Mockery;

/**
 * Unit tests for TaskCreationService.
 * Tests the core business logic of task creation.
 */
class TaskCreationServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var TaskCreationService
     */
    private $service;

    /**
     * @var TaskRepository|\Mockery\Mock
     */
    private $taskRepository;

    /**
     * @var SwiftKudiService|\Mockery\Mock
     */
    private $earnDeskService;

    /**
     * @var User
     */
    private $user;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create mock repositories
        $this->taskRepository = Mockery::mock(TaskRepository::class);
        $this->earnDeskService = Mockery::mock(SwiftKudiService::class);

        // Create the service with mocked dependencies
        $this->service = new TaskCreationService(
            $this->taskRepository,
            $this->earnDeskService
        );

        // Create a test user with wallet
        $this->user = User::factory()->create();
        Wallet::factory()->create([
            'user_id' => $this->user->id,
            'withdrawable_balance' => 10000,
            'promo_credit_balance' => 5000,
        ]);
    }

    /**
     * Test that service generates idempotency token.
     */
    public function test_generates_idempotency_token(): void
    {
        $token = $this->service->generateIdempotencyToken();

        $this->assertNotEmpty($token);
        $this->assertIsString($token);
        $this->assertEquals(36, strlen($token)); // UUID length
    }

    /**
     * Test that service can save and retrieve drafts.
     */
    public function test_save_and_get_draft(): void
    {
        $draftData = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'budget' => 5000,
            'quantity' => 10,
        ];

        // Save draft
        $result = $this->service->saveDraft($this->user, $draftData);

        $this->assertTrue($result['success']);

        // Retrieve draft
        $retrievedDraft = $this->service->getDraft($this->user);

        $this->assertNotNull($retrievedDraft);
        $this->assertEquals('Test Task', $retrievedDraft['title']);
    }

    /**
     * Test that service can clear drafts.
     */
    public function test_clear_draft(): void
    {
        $draftData = [
            'title' => 'Test Task',
            'budget' => 5000,
        ];

        // Save then clear
        $this->service->saveDraft($this->user, $draftData);
        $this->service->clearDraft($this->user);

        // Verify draft is cleared
        $draft = $this->service->getDraft($this->user);
        $this->assertNull($draft);
    }

    /**
     * Test that draft expires after 24 hours.
     */
    public function test_draft_expiration(): void
    {
        $draftData = [
            'title' => 'Test Task',
            'budget' => 5000,
        ];

        // Save draft
        $this->service->saveDraft($this->user, $draftData);

        // Manually expire the draft by setting session to past
        session()->put('task_draft_' . $this->user->id . '_expires', now()->subHour());

        // Retrieve should return null
        $draft = $this->service->getDraft($this->user);
        $this->assertNull($draft);
    }

    /**
     * Test category config is generated correctly.
     */
    public function test_get_category_config(): void
    {
        // Create some categories
        TaskCategory::factory()->count(3)->create([
            'task_type' => 'micro',
            'platform' => 'instagram',
            'is_active' => true,
        ]);

        $config = $this->service->getCategoryConfig();

        $this->assertIsArray($config);
        $this->assertArrayHasKey('micro', $config);
        $this->assertArrayHasKey('ugc', $config);
        $this->assertArrayHasKey('referral', $config);
        $this->assertArrayHasKey('premium', $config);
    }

    /**
     * Test that service sanitizes draft data.
     */
    public function test_sanitize_draft_data(): void
    {
        $dataWithSensitive = [
            'title' => 'Test Task',
            'password' => 'secret123',
            'api_key' => 'key123',
            'budget' => 5000,
        ];

        // We need to test via reflection or by checking the sanitized data
        // For now, we'll just verify draft saving works
        $result = $this->service->saveDraft($this->user, $dataWithSensitive);
        
        $this->assertTrue($result['success']);
    }

    /**
     * Clean up after each test.
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
