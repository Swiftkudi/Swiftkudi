<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Services\SwiftKudiService;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        // Ensure permanent referral task exists when app boots
        // Only run if the column exists (after migrations)
        try {
            if (\Schema::hasColumn('tasks', 'is_permanent_referral')) {
                SwiftKudiService::ensurePermanentReferralTask();
            }
        } catch (\Exception $e) {
            // Ignore if table doesn't exist yet
        }
        
        // Also ensure it's created/updated on every user login
        Event::listen(Authenticated::class, function ($event) {
            try {
                if (\Schema::hasColumn('tasks', 'is_permanent_referral')) {
                    SwiftKudiService::ensurePermanentReferralTask();
                }
            } catch (\Exception $e) {
                // Ignore if table doesn't exist yet
            }
        });
    }
}
