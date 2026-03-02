<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Earn Desk') }}</title>
    
    {{-- Favicon --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    {{-- Laravel Mix Assets - Tailwind CSS compiled via Mix --}}
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <script src="{{ mix('js/app.js') }}" defer></script>
    
    {{-- External Libraries --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --font-heading-name: 'Plus Jakarta Sans';
            --font-body-name: 'Inter';
        }
        
        /* Dark mode base styles */
        body {
            background-color: #020617;
            color: #f1f5f9;
        }

        /* Form controls */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        select,
        textarea {
            background-color: #0f172a !important;
            color: #e2e8f0 !important;
            border-color: #334155 !important;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #6366f1 !important;
            outline: none;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        input::placeholder,
        textarea::placeholder {
            color: #64748b !important;
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #0f172a;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
    </style>
</head>
<body class="font-body bg-dark-950 text-gray-100 min-h-screen">
    <!-- Background decoration -->
    <div class="fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-indigo-500/10 rounded-full blur-3xl"></div>
    </div>

    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="py-4 lg:py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <a href="{{ route('dashboard') }}" class="flex items-center group">
                    <div class="w-9 h-9 lg:w-10 lg:h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center mr-2 lg:mr-3 shadow-lg shadow-indigo-500/20">
                        <i class="fas fa-coins text-white text-base lg:text-lg"></i>
                    </div>
                    <span class="font-bold text-lg lg:text-xl bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">SwiftKudi</span>
                </a>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8 py-4 lg:py-8">
            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
        </main>

        <!-- Footer -->
        <footer class="py-4 lg:py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-sm text-gray-500">© {{ date('Y') }} SwiftKudi. All rights reserved.</p>
            </div>
        </footer>
    </div>

    <script>
        // Shared helpers for user-friendly form validation feedback across AJAX forms.
        (function () {
            function normalizeErrors(payload) {
                const allErrors = [];

                if (!payload) {
                    return allErrors;
                }

                if (Array.isArray(payload.error_list) && payload.error_list.length) {
                    allErrors.push(...payload.error_list);
                    return allErrors;
                }

                if (payload.errors && typeof payload.errors === 'object') {
                    Object.entries(payload.errors).forEach(([field, messages]) => {
                        const label = String(field).replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
                        if (Array.isArray(messages) && messages.length > 0) {
                            allErrors.push(label + ': ' + messages[0]);
                        }
                    });
                }

                if (!allErrors.length && payload.message) {
                    allErrors.push(payload.message);
                }

                return allErrors;
            }

            function showValidationErrors(form, payload, options) {
                if (!form) {
                    return;
                }

                const opts = Object.assign({
                    boxId: 'global-form-error-box',
                    title: 'Please correct the following and try again:',
                }, options || {});

                const existing = document.getElementById(opts.boxId);
                if (existing) {
                    existing.remove();
                }

                const errors = normalizeErrors(payload);
                if (!errors.length) {
                    errors.push('Some submitted data is invalid. Please review the form and try again.');
                }

                const box = document.createElement('div');
                box.id = opts.boxId;
                box.className = 'mb-4 p-4 rounded-lg bg-red-50 text-red-800 border border-red-200';
                box.innerHTML =
                    '<div class="flex items-start">' +
                        '<i class="fas fa-exclamation-circle mt-0.5 mr-3 flex-shrink-0"></i>' +
                        '<div>' +
                            '<p class="font-semibold mb-1">' + opts.title + '</p>' +
                            '<ul class="list-disc pl-5">' + errors.map(function (err) { return '<li>' + err + '</li>'; }).join('') + '</ul>' +
                        '</div>' +
                    '</div>';

                form.insertAdjacentElement('afterbegin', box);
                box.scrollIntoView({ behavior: 'smooth', block: 'center' });

                if (payload && payload.errors && typeof payload.errors === 'object') {
                    const firstField = Object.keys(payload.errors)[0];
                    if (firstField) {
                        const input = form.querySelector('[name="' + firstField + '"]');
                        if (input && typeof input.focus === 'function') {
                            input.focus();
                        }
                    }
                }
            }

            window.SwiftkudiFormFeedback = {
                showValidationErrors: showValidationErrors,
                normalizeErrors: normalizeErrors,
            };
        })();
    </script>
</body>
</html>
