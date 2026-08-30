<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Transaction;
use App\Models\Task;
use App\Models\Job;
use App\Models\GrowthListing;
use App\Models\ProfessionalService;
use App\Models\DigitalProduct;
use App\Observers\TransactionObserver;
use App\Services\TaskCreationService;
use App\Services\TaskService;
use App\Services\ProfessionalServiceService;
use App\Services\GrowthService;
use App\Repositories\TaskRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register TaskRepository as singleton
        $this->app->singleton(TaskRepository::class, function ($app) {
            return new TaskRepository();
        });

        // Register TaskCreationService
        $this->app->singleton(TaskCreationService::class, function ($app) {
            return new TaskCreationService(
                $app->make(TaskRepository::class),
                $app->make(\App\Services\SwiftKudiService::class),
                $app->make(\App\Services\TaskGateProgressService::class),
                $app->make(\App\Services\NotificationManager::class)
            );
        });

        // Register new TaskService
        $this->app->singleton(TaskService::class, function ($app) {
            return new TaskService(
                $app->make(\App\Services\SwiftKudiService::class),
                $app->make(\App\Services\TaskGateProgressService::class)
            );
        });

        // Register ProfessionalServiceService
        $this->app->singleton(ProfessionalServiceService::class, function ($app) {
            return new ProfessionalServiceService(
                $app->make(\App\Services\NotificationManager::class),
                $app->make(\App\Services\MarketplaceService::class)
            );
        });

        // Register GrowthService
        $this->app->singleton(GrowthService::class, function ($app) {
            return new GrowthService(
                $app->make(\App\Services\MarketplaceService::class),
                $app->make(\App\Services\NotificationManager::class)
            );
        });

        // Register NotificationManager
        $this->app->singleton(\App\Services\NotificationManager::class, function ($app) {
            return new \App\Services\NotificationManager(
                $app->make(\App\Services\NotificationDispatchService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            'task' => Task::class,
            'tasks' => Task::class,
            'job' => Job::class,
            'jobs' => Job::class,
            'growth_service' => GrowthListing::class,
            'growth' => GrowthListing::class,
            'professional_service' => ProfessionalService::class,
            'service' => ProfessionalService::class,
            'digital_product' => DigitalProduct::class,
            'product' => DigitalProduct::class,
        ]);

        // Force HTTPS in production (for Render and other cloud providers)
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Apply one centralized mail configuration for web requests and queue workers.
        // Failure here must never make the application unavailable.
        try {
            $this->app->make(\App\Services\MailConfigurationService::class)->apply();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Runtime mail configuration could not be applied', [
                'error' => $e->getMessage(),
            ]);
        }

        // Register model observers (safe/no-op if registration fails)
        try {
            Transaction::observe(TransactionObserver::class);
        } catch (\Throwable $e) {
            // ignore observer registration issues
        }
    }
}
