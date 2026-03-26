<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use App\Services\AccountTypeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Account type service instance.
     */
    protected AccountTypeService $accountTypeService;

    /**
     * Constructor.
     */
    public function __construct(AccountTypeService $accountTypeService)
    {
        $this->accountTypeService = $accountTypeService;
    }

    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user && $user->isSuspended()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Your account has been suspended. Please contact support.',
            ]);
        }

        // Send onboarding reminder to users without account type (non-blocking)
        if ($user) {
            try {
                $this->accountTypeService->sendOnboardingReminder($user);
            } catch (\Exception $e) {
                // Log but don't block login if notification fails
                \Illuminate\Support\Facades\Log::warning('Onboarding reminder failed during login', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $request->session()->regenerate();

        $redirectUrl = redirect()->intended(RouteServiceProvider::HOME)->getTargetUrl();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Login successful. Redirecting to your dashboard...',
                'redirect' => $redirectUrl,
            ]);
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
