<?php

namespace App\Http\Requests\Auth;

use App\Models\SystemSetting;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate()
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            $maxAttempts = $this->maxAttempts();
            RateLimiter::hit($this->throttleKey(), $this->decaySeconds());
            $attempts = RateLimiter::attempts($this->throttleKey());
            $remaining = max(0, $maxAttempts - $attempts);

            $message = trans('auth.failed');
            if ($remaining > 0) {
                $message .= ' ' . $remaining . ' sign-in attempt' . ($remaining === 1 ? '' : 's') . ' remaining before a temporary lock.';
            }

            throw ValidationException::withMessages([
                'email' => $message,
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited()
    {
        if (!$this->rateLimitingEnabled() || ! RateLimiter::tooManyAttempts($this->throttleKey(), $this->maxAttempts())) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    private function rateLimitingEnabled(): bool
    {
        try {
            return SystemSetting::getBool('rate_limiting_enabled', true);
        } catch (\Throwable $e) {
            return true;
        }
    }

    private function maxAttempts(): int
    {
        try {
            return max(1, (int) SystemSetting::get('login_rate_limit_attempts', 5));
        } catch (\Throwable $e) {
            return 5;
        }
    }

    private function decaySeconds(): int
    {
        try {
            return max(60, (int) SystemSetting::get('login_rate_limit_minutes', 5) * 60);
        } catch (\Throwable $e) {
            return 300;
        }
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey()
    {
        return Str::lower($this->input('email')).'|'.$this->ip();
    }
}
