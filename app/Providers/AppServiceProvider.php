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
                $app->make(\App\Services\TaskGateProgressService::class)
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
            return new ProfessionalServiceService();
        });

        // Register GrowthService
        $this->app->singleton(GrowthService::class, function ($app) {
            return new GrowthService();
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

        // Apply mail configuration from system settings if present
        try {
            $enabled = SystemSetting::getBool('smtp_enabled', false);

            if ($enabled) {
                $selectedDriver = SystemSetting::get('smtp_driver', config('mail.default'));
                $isTurbo = $selectedDriver === 'turbosmtp';
                $driver = $isTurbo ? 'smtp' : $selectedDriver;

                $host = SystemSetting::get('smtp_host', config('mail.mailers.smtp.host'));
                if (empty($host) && $isTurbo) {
                    $host = config('services.turbosmtp.server', $host);
                }

                $port = SystemSetting::getNumber('smtp_port', config('mail.mailers.smtp.port'));
                if ((empty($port) || $port <= 0) && $isTurbo) {
                    $port = (int) config('services.turbosmtp.port', 587);
                }

                $username = SystemSetting::get('smtp_username', config('mail.mailers.smtp.username'));
                if (empty($username) && $isTurbo) {
                    $username = config('services.turbosmtp.username', $username);
                }

                $password = SystemSetting::getDecrypted('smtp_password', config('mail.mailers.smtp.password'));
                if (empty($password) && $isTurbo) {
                    $password = config('services.turbosmtp.password', $password);
                }

                $encryption = strtolower((string) SystemSetting::get('smtp_encryption', config('mail.mailers.smtp.encryption')));
                if (in_array($encryption, ['', 'none', 'null'], true)) {
                    $encryption = null;
                }

                $port = (int) $port;
                if ($port <= 0) {
                    $port = $encryption === 'ssl' ? 465 : 587;
                }

                if ($encryption === 'ssl' && $port === 587) {
                    $port = 465;
                }
                if (($encryption === 'tls' || $encryption === null) && $port === 465) {
                    $port = 587;
                }

                $fromAddress = SystemSetting::get('smtp_from_email', config('mail.from.address'));
                if (empty($fromAddress) && $isTurbo) {
                    $fromAddress = config('services.turbosmtp.from_address', $fromAddress);
                }

                $fromName = SystemSetting::get('smtp_from_name', config('mail.from.name'));
                if (empty($fromName) && $isTurbo) {
                    $fromName = config('services.turbosmtp.from_name', $fromName);
                }

                Config::set('mail.default', $driver);
                Config::set('mail.mailers.smtp.host', $host);
                Config::set('mail.mailers.smtp.port', $port);
                Config::set('mail.mailers.smtp.username', $username);
                Config::set('mail.mailers.smtp.password', $password);
                Config::set('mail.mailers.smtp.encryption', $encryption);
                Config::set('mail.mailers.smtp.timeout', 30);
                Config::set('mail.mailers.smtp.auth_mode', null);
                Config::set('mail.from.address', $fromAddress);
                Config::set('mail.from.name', $fromName);

                // Rebind mailer to ensure runtime picks up new config
                if (app()->bound('mail.manager')) {
                    app()->forgetInstance('mail.manager');
                }
                if (app()->bound('mailer')) {
                    app()->forgetInstance('mailer');
                }
            }
        } catch (\Exception $e) {
            // Do not break app boot on errors related to system settings
        }

        // Register model observers (safe/no-op if registration fails)
        try {
            Transaction::observe(TransactionObserver::class);
        } catch (\Throwable $e) {
            // ignore observer registration issues
        }
    }
}
