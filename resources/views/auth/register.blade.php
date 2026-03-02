<x-guest-layout>
    <x-slot name="title">Register - SwiftKudi</x-slot>

    <!-- Google One Tap -->
    @if(config('services.google.enabled'))
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>
        function handleCredentialResponse(response) {
            const form = document.getElementById('register-form');

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
                    data = { success: false, message: 'Google sign-up failed. Please try again.' };
                }
                return { ok: res.ok, status: res.status, data };
            })
            .then(data => {
                if (data.data.success && data.data.redirect) {
                    window.location.href = data.data.redirect;
                    return;
                }

                const payload = data.data || { message: 'Google sign-up failed. Please try again.' };
                if (window.SwiftkudiFormFeedback && form) {
                    window.SwiftkudiFormFeedback.showValidationErrors(form, {
                        message: payload.message || payload.error || 'Google sign-up failed. Please try again.',
                        errors: payload.errors || null,
                        error_list: payload.error_list || null,
                    }, {
                        boxId: 'register-google-error-box',
                    });
                }
            })
            .catch(error => {
                if (window.SwiftkudiFormFeedback && form) {
                    window.SwiftkudiFormFeedback.showValidationErrors(form, {
                        message: 'Network error during Google sign-up. Please try again.',
                    }, {
                        boxId: 'register-google-error-box',
                    });
                }
            });
        }

        window.onload = function () {
            google.accounts.id.initialize({
                client_id: '{{ config("services.google.client_id") }}',
                callback: handleCredentialResponse
            });
            google.accounts.id.renderButton(
                document.getElementById("google-btn-register"),
                { theme: "outline", size: "large", width: document.getElementById('google-btn-register').offsetWidth }
            );
        }
    </script>
    @endif

    <form id="register-form" method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Full Name</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fas fa-user text-gray-400 dark:text-gray-500"></i>
                </div>
                <input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name"
                    placeholder="John Doe"
                    class="w-full pl-11 pr-4 py-3.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 transition-all">
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Email Address</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fas fa-envelope text-gray-400 dark:text-gray-500"></i>
                </div>
                <input id="email" type="email" name="email" :value="old('email')" required autocomplete="email"
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
                <input id="password" type="password" name="password" required autocomplete="new-password"
                    placeholder="••••••••"
                    class="w-full pl-11 pr-4 py-3.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 transition-all">
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Confirm Password</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fas fa-lock text-gray-400 dark:text-gray-500"></i>
                </div>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                    placeholder="••••••••"
                    class="w-full pl-11 pr-4 py-3.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 transition-all">
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Referral Code (optional) -->
        <div>
            <label for="referral_code" class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Referral Code (optional)</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fas fa-tag text-gray-400 dark:text-gray-500"></i>
                </div>
                <input id="referral_code" type="text" name="referral_code" value="{{ old('referral_code', $referralCode ?? '') }}" autocomplete="off"
                    placeholder="Enter referral code if you have one"
                    class="w-full pl-11 pr-4 py-3.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 transition-all">
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Don't have a code? Leave blank.</p>
            <x-input-error :messages="$errors->get('referral_code')" class="mt-2" />
        </div>

        <!-- Terms -->
        <div class="flex items-start">
            <input id="terms" type="checkbox" name="terms" required
                class="w-5 h-5 rounded border-gray-300 dark:border-dark-600 text-indigo-600 focus:ring-indigo-500 bg-gray-50 dark:bg-dark-800 mt-0.5 transition-colors">
            <label for="terms" class="ml-3 text-sm text-gray-600 dark:text-gray-400">
                I agree to the <a href="{{ route('legal.terms') }}" class="font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">Terms of Service</a> and <a href="{{ route('legal.privacy') }}" class="font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">Privacy Policy</a>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" class="w-full py-3.5 px-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all transform hover:scale-[1.02]">
                <i class="fas fa-user-plus mr-2"></i>Create Account
            </button>
        </div>

        <!-- Divider -->
        <div class="relative flex items-center justify-center my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-200 dark:border-dark-600"></div>
            </div>
            <div class="relative bg-white dark:bg-dark-900 px-4 text-sm text-gray-500 dark:text-gray-400">
                or sign up with
            </div>
        </div>

        <!-- Google Signup Button -->
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
                    Sign up with Google
                </span>
            </a>
        </div>
        @endif

        <!-- Login Link -->
        <div class="text-center pt-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Already have an account?
                <a href="{{ route('login') }}" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">
                    Sign in
                </a>
            </p>
        </div>
    </form>

    <script>
        (function () {
            const form = document.getElementById('register-form');
            if (!form) return;

            form.addEventListener('submit', async function (e) {
                e.preventDefault();

                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn ? submitBtn.innerHTML : '';

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating account...';
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
                            boxId: 'register-error-box',
                        });
                    } else {
                        alert(data.message || 'We could not create your account. Please check the form and try again.');
                    }
                } catch (error) {
                    if (window.SwiftkudiFormFeedback) {
                        window.SwiftkudiFormFeedback.showValidationErrors(form, {
                            message: 'Network error. Please check your internet connection and try again.',
                        }, {
                            boxId: 'register-error-box',
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
