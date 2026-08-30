<?php

namespace App\Providers;

use App\Models\SystemSetting;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

$this->routes(function () {
             Route::prefix('api')
                 ->middleware('api')
                 ->namespace($this->namespace)
                 ->group(base_path('routes/api.php'));

             Route::middleware('web')
                 ->namespace($this->namespace)
                 ->group(base_path('routes/web.php'));

             $marketplaceDomain = null;
             if (config('marketplace.use_subdomain')) {
                 $marketplaceDomain = config('marketplace.full_domain') ?: (config('marketplace.subdomain') . '.' . config('marketplace.domain'));
             }

             $marketplaceRoutes = base_path('routes/marketplace.php');
             if (file_exists($marketplaceRoutes)) {
                 if ($marketplaceDomain) {
                     Route::domain($marketplaceDomain)
                         ->middleware('web')
                         ->namespace($this->namespace)
                         ->group($marketplaceRoutes);
                 } else {
                     Route::middleware('web')
                         ->prefix('marketplace')
                         ->namespace($this->namespace)
                         ->group($marketplaceRoutes);
                 }
             }
         });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });

        RateLimiter::for('auth-login', function (Request $request) {
            return $this->securityLimit(
                'login_rate_limit_attempts',
                'login_rate_limit_minutes',
                8,
                5,
                strtolower((string) $request->input('email')).'|'.$request->ip()
            );
        });

        RateLimiter::for('registration', function (Request $request) {
            return $this->securityLimit('registration_rate_limit_attempts', 'registration_rate_limit_minutes', 5, 15, $request->ip());
        });

        RateLimiter::for('password-reset', function (Request $request) {
            return $this->securityLimit(
                'password_reset_rate_limit_attempts',
                'password_reset_rate_limit_minutes',
                5,
                15,
                strtolower((string) $request->input('email')).'|'.$request->ip()
            );
        });

        RateLimiter::for('verification', function (Request $request) {
            return $this->securityLimit(
                'verification_rate_limit_attempts',
                'verification_rate_limit_minutes',
                6,
                1,
                (optional($request->user())->id ?: $request->ip())
            );
        });
    }

    /**
     * Build an admin-configurable limiter and safely fall back during installation.
     */
    private function securityLimit(string $attemptKey, string $minutesKey, int $defaultAttempts, int $defaultMinutes, string $identity): Limit
    {
        try {
            if (!SystemSetting::getBool('rate_limiting_enabled', true)) {
                return Limit::none();
            }

            $attempts = max(1, (int) SystemSetting::get($attemptKey, $defaultAttempts));
            $minutes = max(1, (int) SystemSetting::get($minutesKey, $defaultMinutes));
        } catch (\Throwable $e) {
            $attempts = $defaultAttempts;
            $minutes = $defaultMinutes;
        }

        return Limit::perMinutes($minutes, $attempts)->by($identity);
    }
}
