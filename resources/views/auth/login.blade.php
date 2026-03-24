<x-guest-layout>
    <x-slot name="title">Login - SwiftKudi</x-slot>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <!-- Google One Tap -->
    @if(config('services.google.enabled'))
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>
        function handleCredentialResponse(response) {
            const form = document.getElementById('login-form');

            fetch('{{ route("auth.google.one-tap") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ credential: response.credential })
            })
            .then(async (res) => {
                let data = {};
                try {
                    data = await res.json();
                } catch (e) {
                    data = { success: false, message: 'Google sign-in failed. Please try again.' };
                }
                return { ok: res.ok, status: res.status, data };
            })
            .then(data => {
                if (data.data.success && data.data.redirect) {
                    window.location.href = data.data.redirect;
                    return;
                }

                const payload = data.data || { message: 'Google sign-in failed. Please try again.' };
                if (window.SwiftkudiFormFeedback && form) {
                    window.SwiftkudiFormFeedback.showValidationErrors(form, {
                        message: payload.message || payload.error || 'Google sign-in failed. Please try again.',
                        errors: payload.errors || null,
                        error_list: payload.error_list || null,
                    }, {
                        boxId: 'login-google-error-box',
                    });
                }
            })
            .catch(error => {
                if (window.SwiftkudiFormFeedback && form) {
                    window.SwiftkudiFormFeedback.showValidationErrors(form, {
                        message: 'Network error during Google sign-in. Please try again.',
                    }, {
                        boxId: 'login-google-error-box',
                    });
                }
            });
        }

        window.onload = function () {
            google.accounts.id.initialize({
                client_id: '{{ config("services.google.client_id") }}',
                callback: handleCredentialResponse,
                auto_select: true
            });
            google.accounts.id.renderButton(
                document.getElementById("google-btn"),
                { theme: "outline", size: "large", width: document.getElementById('google-btn').offsetWidth }
            );
            google.accounts.id.prompt(); // Show One Tap dialog
        }
    </script>
    @endif

    <form id="login-form" method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fas fa-envelope text-gray-400 dark:text-gray-500"></i>
                </div>
                <input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="email"
                    placeholder="name@example.com"
                    class="w-full pl-11 pr-4 py-3.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 transition-all">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Password</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fas fa-lock text-gray-400 dark:text-gray-500"></i>
                </div>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    placeholder="••••••••"
                    class="w-full pl-11 pr-4 py-3.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 transition-all">
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label for="remember" class="flex items-center cursor-pointer">
                <input id="remember" type="checkbox" name="remember"
                    class="w-5 h-5 rounded border-gray-300 dark:border-dark-600 text-indigo-600 focus:ring-indigo-500 bg-gray-50 dark:bg-dark-800 transition-colors">
                <span class="ml-3 text-sm text-gray-600 dark:text-gray-400">Remember me</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">
                    Forgot password?
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" class="w-full py-3.5 px-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all transform hover:scale-[1.02]">
                <i class="fas fa-sign-in-alt mr-2"></i>Sign in
            </button>
        </div>

        <!-- Divider -->
        <div class="relative flex items-center justify-center my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-200 dark:border-dark-600"></div>
            </div>
            <div class="relative bg-white dark:bg-dark-900 px-4 text-sm text-gray-500 dark:text-gray-400">
                or continue with
            </div>
        </div>

        <!-- Google Login Button -->
        @if(config('services.google.enabled'))
        <div class="flex justify-center">
            <a href="{{ route('auth.google') }}" class="w-full flex items-center justify-center gap-3 py-3.5 px-4 bg-white dark:bg-dark-800 border-2 border-gray-200 dark:border-dark-600 rounded-xl hover:border-gray-300 dark:hover:border-dark-500 hover:bg-gray-50 dark:hover:bg-dark-700 transition-all group">
                <svg class="w-5 h-5" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                <span class="font-medium text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">
                    Continue with Google
                </span>
            </a>
        </div>
        @endif

        <!-- Register Link -->
        <div class="text-center pt-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">
                    Create account
                </a>
            </p>
        </div>
    </form>

    <script>
        (function () {
            const form = document.getElementById('login-form');
            if (!form) return;

            form.addEventListener('submit', async function (e) {
                e.preventDefault();

                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn ? submitBtn.innerHTML : '';

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Signing in...';
                }

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });

                    const data = await response.json();

                    if (data.success && data.redirect) {
                        window.location.href = data.redirect;
                        return;
                    }

                    if ((response.status === 422 || data.errors || data.error_list) && window.SwiftkudiFormFeedback) {
                        window.SwiftkudiFormFeedback.showValidationErrors(form, data, {
                            boxId: 'login-error-box',
                        });
                    } else {
                        alert(data.message || 'We could not sign you in. Please check your credentials and try again.');
                    }
                } catch (error) {
                    if (window.SwiftkudiFormFeedback) {
                        window.SwiftkudiFormFeedback.showValidationErrors(form, {
                            message: 'Network error. Please check your internet connection and try again.',
                        }, {
                            boxId: 'login-error-box',
                        });
                    } else {
                        alert('Network error. Please try again.');
                    }
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                }
            });
        })();
    </script>
</x-guest-layout>
