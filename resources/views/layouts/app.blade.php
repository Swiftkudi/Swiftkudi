<!DOCTYPE html>
<html lang="en" class="dark no-css">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Earn Desk') }}</title>
    
    {{-- Favicon --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    {{-- Critical inline CSS: minimal styles for header, nav and main to avoid FOUC before full CSS loads. --}}
    <style>
        /* Instead of hiding the page, apply immediate dark-mode safe styles to avoid flash
           and ensure the page looks correct before the compiled CSS loads. */
        html.no-css body { background: #0f172a; color: #e5e7eb; visibility: visible; }
        html.no-css .bg-white { background: #1e293b; }
        html.no-css .bg-gray-50 { background: #1e293b; }
        html.no-css .bg-gray-100 { background: #334155; }
        html.no-css .text-gray-900 { color: #f1f5f9; }
        html.no-css .text-gray-700 { color: #e2e8f0; }
        html.no-css .text-gray-600 { color: #cbd5e1; }
        html.no-css .border-gray-200 { border-color: #334155; }
        html.no-css input, html.no-css select, html.no-css textarea { 
            background: #334155; 
            border-color: #475569; 
            color: #f1f5f9; 
        }

        /* Critical header styles so top nav doesn't flash unstyled (dark variant) */
        header { position: fixed; top: 0; left: 0; right: 0; z-index: 50; height: 64px; background: rgba(2,6,23,0.95); backdrop-filter: blur(8px); border-bottom: 1px solid #1e293b; }
        header .logo { display: inline-flex; align-items: center; gap: .5rem; }
        header a, header button { color: #e2e8f0; }
        main { padding-top: 72px; }

        /* Basic typography fallback so text layout is stable before fonts load */
        .font-body, body { font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }
        /* Hide the theme toggle since dark mode is permanent */
        #theme-toggle { display: none !important; }
        
        /* Form elements immediate styling */
        html.no-css input, html.no-css select, html.no-css textarea {
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
        }
        html.no-css button {
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            cursor: pointer;
        }
    </style>

    {{-- Preconnect to fonts and preload critical assets to reduce perceived load time --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="{{ mix('css/app.css') }}" as="style">
    <link rel="preload" href="{{ mix('js/app.js') }}" as="script">

    {{-- Use compiled Tailwind CSS (built by Laravel Mix). onload will unhide the page. --}}
    <link rel="stylesheet" href="{{ mix('css/app.css') }}" onload="document.documentElement.classList.remove('no-css'); document.documentElement.classList.add('css-loaded');">
    <noscript>
        <style>html.no-css body{visibility:visible;}</style>
    </noscript>
    <script src="https://unpkg.com/lucide@latest" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    <link id="heading-font-link" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link id="heading-font-link" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"></noscript>
    <link id="body-font-link" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link id="body-font-link" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"></noscript>
    
    <script>
        (function(){
            var __tdCfg = {
                darkMode: 'class',
                theme: {
                    extend: {
                        fontFamily: {
                            heading: ['var(--font-heading-name)', 'sans-serif'],
                            body: ['var(--font-body-name)', 'sans-serif'],
                        },
                        colors: {
                            primary: {
                                50: '#eff6ff',
                                100: '#dbeafe',
                                200: '#bfdbfe',
                                300: '#93c5fd',
                                400: '#60a5fa',
                                500: '#3b82f6',
                                600: '#2563eb',
                                700: '#1d4ed8',
                                800: '#1e40af',
                                900: '#1e3a8a',
                            },
                            dark: {
                                50: '#f8fafc',
                                100: '#f1f5f9',
                                200: '#e2e8f0',
                                300: '#cbd5e1',
                                400: '#94a3b8',
                                500: '#64748b',
                                600: '#475569',
                                700: '#334155',
                                800: '#1e293b',
                                900: '#0f172a',
                                950: '#020617',
                            }
                        }
                    }
                }
            };
            try{
                if (typeof tailwind !== 'undefined') {
                    tailwind.config = __tdCfg;
                } else {
                    // fallback for environments without tailwind-js loaded yet
                    window.__tailwind_config = __tdCfg;
                }
            }catch(e){
                // ignore
            }
        })();
    </script>

    <style>
        /* Ensure no default browser margin so sticky header sits at the top edge */
        body { margin: 0; padding: 0; }
         :root {
            --font-heading-name: 'Plus Jakarta Sans';
            --font-body-name: 'Inter';
        }
        
        /* Dark mode transitions */
        .dark body,
        .dark .bg-white,
        .dark .bg-gray-50,
        .dark .bg-gray-100,
        .dark .border-gray-200,
        .dark .border-gray-300,
        .dark .text-gray-900,
        .dark .text-gray-700,
        .dark .text-gray-600,
        .dark .text-gray-500,
        .dark .text-gray-400 {
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }
        
        /* Smooth theme switching */
        * {
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }

        /* Fallback: ensure main respects header height even before JS runs */
        /* Add a small safety offset so content isn't hidden under the fixed header
           if the header is taller than the initial CSS fallback or if JS hasn't run yet */
        main { padding-top: calc(var(--header-height, 64px) + 8px); }

        /* Ensure flash messages are visible and not hidden under the fixed header */
        #flash-messages {
            position: relative;
            z-index: 60; /* above header (header z-50) */
            padding-top: calc(var(--header-height, 64px) + 8px);
            pointer-events: auto;
        }

        /* ============================================
           ENHANCED UI STYLES
           ============================================ */
        
        /* Enhanced Input Fields - Prominent Borders & Better Padding */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="tel"],
        input[type="url"],
        input[type="search"],
        input[type="date"],
        input[type="datetime-local"],
        textarea,
        select {
            padding: 0.75rem 1rem;
            border-width: 2px;
            border-radius: 0.75rem;
            transition: all 0.2s ease;
        }
        
        /* Input focus states - prominent glow */
        input:focus,
        textarea:focus,
        select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
            outline: none;
        }
        
        .dark input:focus,
        .dark textarea:focus,
        .dark select:focus {
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.15);
        }
        
        /* Input placeholder styling */
        input::placeholder,
        textarea::placeholder {
            color: #9ca3af;
            opacity: 1;
        }
        
        .dark input::placeholder,
        .dark textarea::placeholder {
            color: #6b7280;
        }

        /* Enhanced Toggle Buttons - Always Visible Borders */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 52px;
            height: 28px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #e5e7eb;
            border: 2px solid #9ca3af;
            border-radius: 28px;
            transition: all 0.3s ease;
        }
        
        .toggle-slider:hover {
            border-color: #6b7280;
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            border-radius: 50%;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        input:checked + .toggle-slider {
            background-color: #3b82f6;
            border-color: #2563eb;
        }
        
        input:checked + .toggle-slider:before {
            transform: translateX(24px);
        }
        
        .dark .toggle-slider {
            background-color: #374151;
            border: 2px solid #a78bfa;
        }
        
        .dark .toggle-slider:hover {
            border-color: #c4b5fd;
        }
        
        .dark input:checked + .toggle-slider {
            background-color: #3b82f6;
            border-color: #bfdbfe;
        }

        /* Professional Tooltips */
        [data-tooltip] {
            position: relative;
        }
        
        [data-tooltip]::before {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%) translateY(-8px);
            padding: 0.5rem 0.75rem;
            background-color: #1f2937;
            color: white;
            font-size: 0.75rem;
            border-radius: 0.375rem;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            z-index: 100;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        [data-tooltip]:hover::before {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(-4px);
        }
        
        [data-tooltip].tooltip-top::before {
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%) translateY(-8px);
        }
        
        [data-tooltip].tooltip-top:hover::before {
            transform: translateX(-50%) translateY(-4px);
        }
        
        [data-tooltip].tooltip-bottom::before {
            top: 100%;
            bottom: auto;
            transform: translateX(-50%) translateY(8px);
        }
        
        [data-tooltip].tooltip-bottom:hover::before {
            transform: translateX(-50%) translateY(4px);
        }
        
        [data-tooltip].tooltip-left::before {
            bottom: auto;
            left: auto;
            right: 100%;
            top: 50%;
            transform: translateY(-50%) translateX(-8px);
        }
        
        [data-tooltip].tooltip-left:hover::before {
            transform: translateY(-50%) translateX(-4px);
        }
        
        [data-tooltip].tooltip-right::before {
            bottom: auto;
            left: 100%;
            top: 50%;
            transform: translateY(-50%) translateX(8px);
        }
        
        [data-tooltip].tooltip-right:hover::before {
            transform: translateY(-50%) translateX(4px);
        }

        /* Enhanced Buttons */
        .btn, button, a.btn, input[type="submit"] {
            padding: 0.625rem 1.25rem;
            border-radius: 0.625rem;
            font-weight: 500;
            transition: all 0.2s ease;
            border: 2px solid transparent;
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        /* Button variants */
        .btn-primary {
            background-color: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2563eb;
        }
        
        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #4b5563;
        }
        
        .btn-success {
            background-color: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #059669;
        }
        
        .btn-danger {
            background-color: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #dc2626;
        }

        /* Consistent Form Element Spacing */
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        
        .dark .form-label {
            color: #d1d5db;
        }
        
        .form-helper {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.375rem;
        }
        
        .form-error {
            font-size: 0.75rem;
            color: #ef4444;
            margin-top: 0.375rem;
        }

        /* Card enhancements */
        .card {
            border-radius: 1rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .dark .card {
            border-color: #374151;
        }
        
        .card-hover:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-2px);
            transition: all 0.2s ease;
        }

        /* Button click responsiveness - better UX */
        button, .btn, a.btn, input[type="submit"] {
            transform: translateZ(0);
            -webkit-transform: translateZ(0);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Button active/click state - scale down slightly for tactile feedback */
        button:active, .btn:active, button:focus, .btn:focus,
        a.btn:active, a.btn:focus,
        input[type="submit"]:active, input[type="submit"]:focus {
            transform: scale(0.97);
            -webkit-transform: scale(0.97);
        }

        /* Smooth transitions for buttons */
        button, .btn, a.btn {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Loading state for buttons */
        button.loading, .btn.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        button.loading::after, .btn.loading::after {
            content: "";
            display: inline-block;
            width: 12px;
            height: 12px;
            margin-left: 8px;
            border: 2px solid transparent;
            border-top-color: currentColor;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Ripple effect for buttons */
        .btn-ripple {
            position: relative;
            overflow: hidden;
        }

        .btn-ripple::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.3s, height 0.3s;
        }

        .btn-ripple:active::after {
            width: 200%;
            height: 200%;
        }

        /* Toast notifications - slide in from top */
        .toast {
            animation: slideInTop 0.3s ease-out;
        }

        @keyframes slideInTop {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Card hover effects */
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        /* Focus visible styles for accessibility */
        button:focus-visible, .btn:focus-visible,
        a:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible {
            outline: 2px solid #6366f1;
            outline-offset: 2px;
        }
    </style>
    
    <script>
        // Force permanent dark mode immediately to avoid any light flash
        (function(){
            try {
                localStorage.setItem('theme', 'dark');
                document.documentElement.classList.add('dark');
            } catch(e) { /* ignore */ }
        })();
    </script>
</head>
<body class="font-body bg-dark-950 text-gray-100 min-h-screen">
    <div class="min-h-screen flex flex-col">
        <!-- Mobile Menu Overlay -->
        <div id="mobile-menu-overlay" class="fixed inset-0 bg-black/60 z-40 hidden md:hidden" onclick="closeMobileMenu()"></div>
        
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="fixed top-0 left-0 h-full w-72 bg-dark-900 z-50 transform -translate-x-full transition-transform duration-300 md:hidden">
            <div class="p-4 border-b border-dark-700">
                <div class="flex items-center justify-between">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center mr-3">
                            <i class="fas fa-coins text-white text-lg"></i>
                        </div>
                        <span class="font-bold text-xl text-white">SwiftKudi</span>
                    </a>
                    <button onclick="closeMobileMenu()" class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-dark-800">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <nav class="p-4 space-y-2">
                <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-white hover:bg-dark-800' }} transition-all">
                    <i class="fas fa-home mr-3 w-5"></i>Dashboard
                </a>
                <a href="{{ route('dashboard.profile') }}" class="flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('dashboard.profile') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-white hover:bg-dark-800' }} transition-all">
                    <i class="fas fa-user mr-3 w-5"></i>Profile
                </a>
                <a href="{{ route('tasks.index') }}" class="flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('tasks.*') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-white hover:bg-dark-800' }} transition-all">
                    <i class="fas fa-tasks mr-3 w-5"></i>Tasks
                </a>
                <a href="{{ route('wallet.index') }}" class="flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('wallet.*') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-white hover:bg-dark-800' }} transition-all">
                    <i class="fas fa-wallet mr-3 w-5"></i>Wallet
                </a>
                <a href="{{ route('chat.index') }}" class="flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('chat.*') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-white hover:bg-dark-800' }} transition-all">
                    <i class="fas fa-comments mr-3 w-5"></i>Chat
                </a>
                @if(Auth::check() && Auth::user()->is_admin)
                <a href="{{ route('admin.index') }}" class="flex items-center px-4 py-3 rounded-lg text-gray-400 hover:text-white hover:bg-dark-800 transition-all">
                    <i class="fas fa-cog mr-3 w-5"></i>Admin
                </a>
                @endif
            </nav>
            @auth
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-dark-700">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-400">
                            <i class="fas fa-naira-sign mr-0.5"></i>{{ number_format(Auth::user()->wallet ? (Auth::user()->wallet->withdrawable_balance + Auth::user()->wallet->promo_credit_balance) : 0, 2) }}
                        </p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center px-4 py-2 rounded-lg bg-dark-800 text-gray-400 hover:text-red-400 hover:bg-red-500/10 transition-all">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </button>
                </form>
            </div>
            @endauth
        </div>

        <!-- Navigation (fixed to top) -->
        <header class="fixed top-0 left-0 right-0 z-50 bg-dark-900/95 border-b border-dark-700 backdrop-blur-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Mobile Menu Button -->
                    <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg text-gray-400 hover:text-white hover:bg-dark-800" onclick="openMobileMenu()">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    
                    <!-- Logo -->
                    <div class="flex items-center">
                        <a href="{{ route('dashboard') }}" class="flex items-center group">
                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center mr-3 shadow-lg shadow-indigo-500/30 group-hover:shadow-indigo-500/50 transition-all">
                                <i class="fas fa-coins text-white text-lg"></i>
                            </div>
                            <span class="font-bold text-xl bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">SwiftKudi</span>
                        </a>
                    </div>

                    <!-- Navigation -->
                    <nav class="hidden md:flex space-x-1">
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-indigo-400 hover:bg-dark-800' }} transition-all">
                            <i class="fas fa-home mr-2"></i>Dashboard
                        </a>
                        <a href="{{ route('dashboard.profile') }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard.profile') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-indigo-400 hover:bg-dark-800' }} transition-all">
                            <i class="fas fa-user mr-2"></i>Profile
                        </a>
                        <a href="{{ route('tasks.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('tasks.*') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-indigo-400 hover:bg-dark-800' }} transition-all">
                            <i class="fas fa-tasks mr-2"></i>Tasks
                        </a>
                        <a href="{{ route('wallet.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('wallet.*') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-indigo-400 hover:bg-dark-800' }} transition-all">
                            <i class="fas fa-wallet mr-2"></i>Wallet
                        </a>
                        <a href="{{ route('chat.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('chat.*') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-indigo-400 hover:bg-dark-800' }} transition-all">
                            <i class="fas fa-comments mr-2"></i>Chat
                        </a>
                        @if(Auth::check() && Auth::user()->is_admin)
                        <a href="{{ route('admin.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-400 hover:text-indigo-400 hover:bg-dark-800 transition-all">
                            <i class="fas fa-cog mr-2"></i>Admin
                        </a>
                        @endif
                    </nav>

                    <!-- Right Side -->
                    <div class="flex items-center space-x-3 md:space-x-4">
                        @auth
                            @php
                                $authUser = Auth::user();
                                $wallet = $authUser->wallet ?? null;
                                $balance = $wallet ? ($wallet->withdrawable_balance + $wallet->promo_credit_balance) : 0;
                            @endphp

                            <!-- Notification Bell -->
                            <div class="relative" id="notif-bell-wrapper">
                                <button id="notif-bell-btn"
                                    class="relative p-2 md:p-2.5 rounded-xl bg-dark-800 hover:bg-indigo-500/10 text-gray-400 hover:text-indigo-400 transition-all focus:outline-none"
                                    aria-label="Notifications"
                                    onclick="toggleNotifDropdown(event)">
                                    <i class="fas fa-bell text-lg"></i>
                                    <span id="notif-badge"
                                        class="absolute -top-1 -right-1 hidden min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full flex items-center justify-center leading-none">
                                        0
                                    </span>
                                </button>

                                <!-- Dropdown -->
                                <div id="notif-dropdown"
                                    class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-dark-900 border border-dark-700 rounded-2xl shadow-2xl shadow-black/40 z-[200] overflow-hidden"
                                    onclick="event.stopPropagation()">

                                    <!-- Header -->
                                    <div class="flex items-center justify-between px-4 py-3 border-b border-dark-700">
                                        <h3 class="text-sm font-semibold text-white">Notifications</h3>
                                        <div class="flex items-center gap-2">
                                            <button onclick="markAllNotifRead()"
                                                class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">
                                                Mark all read
                                            </button>
                                            <a href="{{ route('notifications.index') }}"
                                                class="text-xs text-gray-400 hover:text-white transition-colors ml-2">
                                                View all
                                            </a>
                                        </div>
                                    </div>

                                    <!-- List -->
                                    <div id="notif-list" class="max-h-[400px] overflow-y-auto divide-y divide-dark-700">
                                        <div id="notif-empty" class="hidden px-4 py-8 text-center">
                                            <i class="fas fa-bell-slash text-2xl text-gray-600 mb-2 block"></i>
                                            <p class="text-sm text-gray-500">No notifications yet</p>
                                        </div>
                                        <div id="notif-loading" class="px-4 py-6 text-center">
                                            <i class="fas fa-spinner fa-spin text-gray-500"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /Notification Bell -->

                            <div class="flex items-center space-x-2 md:space-x-3">
                                <div class="hidden sm:block text-right">
                                    <p class="text-sm font-semibold text-white">{{ $authUser->name }}</p>
                                    <p class="text-xs text-gray-400 flex items-center justify-end">
                                        <i class="fas fa-naira-sign mr-0.5"></i>{{ number_format($balance, 2) }}
                                    </p>
                                </div>
                                <div class="h-9 w-9 md:h-10 md:w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold shadow-lg shadow-indigo-500/30">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                                </div>
                                <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                                    @csrf
                                    <button type="submit" class="p-2 md:p-2.5 rounded-xl bg-dark-800 hover:bg-red-500/10 text-gray-400 hover:text-red-400 transition-all" title="Logout">
                                        <i class="fas fa-sign-out-alt"></i>
                                    </button>
                                </form>
                            </div>
                        @else
                            <a href="{{ route('login') }}" class="px-3 md:px-4 py-2 text-sm font-medium text-gray-400 hover:text-indigo-400 transition-all">Log in</a>
                            <a href="{{ route('register') }}" class="px-4 md:px-5 py-2 md:py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-sm font-medium transition-all shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50">Get Started</a>
                        @endauth
                    </div>
                </div>
            </div>
        </header>

        <!-- Alert messages -->
        <div id="flash-messages" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            @if(session('success'))
            <div class="mb-4 p-4 rounded-lg bg-green-500/10 border border-green-500/20 text-green-400 flex justify-between items-start">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-check-circle text-green-500 mt-1"></i>
                    <div>
                        <p class="font-semibold">{{ session('success') }}</p>
                    </div>
                </div>
                <button onclick="this.closest('.mb-4').remove()" class="text-green-400 hover:text-green-300 ml-4"><i class="fas fa-times"></i></button>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-4 p-4 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 flex justify-between items-start">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-exclamation-circle text-red-500 mt-1"></i>
                    <div>
                        <p class="font-semibold">{{ session('error') }}</p>
                    </div>
                </div>
                <button onclick="this.closest('.mb-4').remove()" class="text-red-400 hover:text-red-300 ml-4"><i class="fas fa-times"></i></button>
            </div>
            @endif

            @if(session('warning'))
            <div class="mb-4 p-4 rounded-lg bg-yellow-500/10 border border-yellow-500/20 text-yellow-400 flex justify-between items-start">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-exclamation-triangle text-yellow-500 mt-1"></i>
                    <div>
                        <p class="font-semibold">{{ session('warning') }}</p>
                    </div>
                </div>
                <button onclick="this.closest('.mb-4').remove()" class="text-yellow-400 hover:text-yellow-300 ml-4"><i class="fas fa-times"></i></button>
            </div>
            @endif

            @if(session('info'))
            <div class="mb-4 p-4 rounded-lg bg-blue-500/10 border border-blue-500/20 text-blue-400 flex justify-between items-start">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                    <div>
                        <p class="font-semibold">{{ session('info') }}</p>
                    </div>
                </div>
                <button onclick="this.closest('.mb-4').remove()" class="text-blue-400 hover:text-blue-300 ml-4"><i class="fas fa-times"></i></button>
            </div>
            @endif

            @if($errors->any())
            <div class="mb-4 p-4 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-exclamation-circle text-red-500 mt-1"></i>
                    <div>
                        <p class="font-semibold">Please fix the following errors:</p>
                        <ul class="mt-2 list-disc list-inside text-sm">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Page Content -->
        <main class="flex-1">
            @yield('content')
        </main>
        
        <!-- Footer -->
        <footer class="bg-dark-900 border-t border-dark-700 py-4 md:py-6 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="flex items-center mb-3 md:mb-0">
                        <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center mr-2">
                            <i class="fas fa-coins text-white text-sm"></i>
                        </div>
                        <span class="font-bold text-white">SwiftKudi</span>
                    </div>
                    <p class="text-sm text-gray-500">© {{ date('Y') }} SwiftKudi. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>

    <script>
        // Mobile menu functionality
        function openMobileMenu() {
            document.getElementById('mobile-menu').classList.remove('-translate-x-full');
            document.getElementById('mobile-menu-overlay').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeMobileMenu() {
            document.getElementById('mobile-menu').classList.add('-translate-x-full');
            document.getElementById('mobile-menu-overlay').classList.add('hidden');
            document.body.style.overflow = '';
        }
    </script>

    {{-- Compiled application JS (Alpine, etc.). Use mix to reference built asset. --}}
    <script src="{{ mix('js/app.js') }}" defer></script>

    <script>
        // Dynamic main padding to account for fixed header height
        (function(){
            function adjustMainPadding(){
                 try{
                     var header = document.querySelector('header');
                     var footer = document.querySelector('footer');
                     var main = document.querySelector('main');
                     if(!header || !main) return;
                     var headerHeight = header.getBoundingClientRect().height || header.offsetHeight || 64;
                     var footerHeight = footer ? (footer.getBoundingClientRect().height || footer.offsetHeight || 0) : 0;

                    // expose CSS variables so pages can use them in calc() for min-height/centering
                    document.documentElement.style.setProperty('--header-height', headerHeight + 'px');
                    document.documentElement.style.setProperty('--footer-height', footerHeight + 'px');

                    // Add a small safety offset to the padding so content won't sit beneath
                    // the fixed header if the header becomes taller (wrapped nav, auth area)
                    var SAFE_OFFSET = 8; // px
                    main.style.paddingTop = (headerHeight + SAFE_OFFSET) + 'px';
                    // ensure main fills remaining viewport so content can vertically center when desired
                    main.style.minHeight = 'calc(100vh - ' + (headerHeight + SAFE_OFFSET) + 'px - ' + footerHeight + 'px)';
                 }catch(e){ /* ignore */ }
             }
            // run on DOM ready and on resize
            document.addEventListener('DOMContentLoaded', adjustMainPadding);
            window.addEventListener('resize', adjustMainPadding);
            // extra call after fonts/assets settle
            setTimeout(adjustMainPadding, 120);
            // call immediately in case DOMContentLoaded already fired
            try { adjustMainPadding(); } catch(e){}
        })();
    </script>

    <script>
        // Initialize Lucide icons - wait for script to load
        function initLucideIcons() {
            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                lucide.createIcons();
            } else {
                // Retry after a short delay if not loaded yet
                setTimeout(initLucideIcons, 100);
            }
        }
        // Start checking for lucide
        initLucideIcons();
    </script>

    {{-- Render page-specific scripts pushed via @push('scripts') --}}
    <script>
        // Auto-dismiss flash messages after 6 seconds
        setTimeout(() => {
            try {
                const container = document.getElementById('flash-messages');
                if (!container) return;
                const msgs = container.querySelectorAll('.mb-4');
                msgs.forEach(m => m.remove());
            } catch (e) {
                // ignore
            }
        }, 6000);
    </script>

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

    @auth
    <script>
    /* =========================================================
       In-App Notification Bell
    ========================================================= */
    (function () {
        'use strict';

        const FEED_URL         = '{{ route("notifications.feed") }}';
        const READ_ALL_URL     = '{{ route("notifications.read-all") }}';
        const CSRF_TOKEN       = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const POLL_INTERVAL_MS = 30000; // poll every 30 s

        let dropdownOpen = false;
        let lastFetch    = 0;

        // --- Type → icon / colour ---
        const typeStyles = {
            task_approved  : { icon: 'fa-check-circle',   colour: 'text-green-400'  },
            task_rejected  : { icon: 'fa-times-circle',   colour: 'text-red-400'    },
            new_task       : { icon: 'fa-tasks',           colour: 'text-indigo-400' },
            earnings       : { icon: 'fa-coins',           colour: 'text-yellow-400' },
            withdrawal     : { icon: 'fa-wallet',          colour: 'text-blue-400'   },
            level_up       : { icon: 'fa-arrow-up',        colour: 'text-purple-400' },
            badge_earned   : { icon: 'fa-medal',           colour: 'text-yellow-400' },
            referral       : { icon: 'fa-users',           colour: 'text-green-400'  },
            system         : { icon: 'fa-bell',            colour: 'text-gray-400'   },
        };

        function iconFor(type) {
            const s = typeStyles[type] || typeStyles['system'];
            return `<i class="fas ${s.icon} ${s.colour}"></i>`;
        }

        function timeAgo(dateStr) {
            const diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
            if (diff < 60)   return diff + 's ago';
            if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
            if (diff < 86400)return Math.floor(diff / 3600) + 'h ago';
            return Math.floor(diff / 86400) + 'd ago';
        }

        function updateBadge(count) {
            const badge = document.getElementById('notif-badge');
            if (!badge) return;
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('hidden');
                badge.classList.add('flex');
            } else {
                badge.classList.add('hidden');
                badge.classList.remove('flex');
            }
        }

        function renderNotifications(items) {
            const list    = document.getElementById('notif-list');
            const empty   = document.getElementById('notif-empty');
            const loading = document.getElementById('notif-loading');
            if (!list) return;

            if (loading) loading.remove();

            // remove old items (keep empty placeholder)
            list.querySelectorAll('.notif-item').forEach(el => el.remove());

            if (!items || items.length === 0) {
                if (empty) empty.classList.remove('hidden');
                return;
            }
            if (empty) empty.classList.add('hidden');

            items.forEach(n => {
                const item = document.createElement('div');
                item.className = 'notif-item flex items-start gap-3 px-4 py-3 cursor-pointer transition-colors ' +
                    (n.is_read ? 'hover:bg-dark-800' : 'bg-indigo-500/5 hover:bg-indigo-500/10 border-l-2 border-indigo-500');
                item.dataset.id = n.id;
                item.innerHTML =
                    `<div class="mt-0.5 flex-shrink-0 w-7 h-7 rounded-full bg-dark-800 flex items-center justify-center text-sm">
                        ${iconFor(n.type)}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">${escHtml(n.title)}</p>
                        <p class="text-xs text-gray-400 line-clamp-2 mt-0.5">${escHtml(n.message)}</p>
                        <p class="text-[10px] text-gray-600 mt-1">${timeAgo(n.created_at)}</p>
                    </div>
                    ${n.is_read ? '' : '<div class="mt-1 flex-shrink-0 w-2 h-2 rounded-full bg-indigo-500"></div>'}`;

                item.addEventListener('click', () => markOneRead(n.id, item));
                list.appendChild(item);
            });
        }

        function escHtml(str) {
            return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        async function fetchNotifications(force) {
            const now = Date.now();
            if (!force && now - lastFetch < 10000) return; // debounce
            lastFetch = now;

            try {
                const res  = await fetch(FEED_URL, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();
                updateBadge(data.unread_count || 0);
                if (dropdownOpen) {
                    renderNotifications(data.notifications || []);
                }
            } catch (_) { /* silent */ }
        }

        async function markOneRead(id, itemEl) {
            // Optimistically update UI
            if (itemEl) {
                itemEl.classList.remove('bg-indigo-500/5', 'hover:bg-indigo-500/10', 'border-l-2', 'border-indigo-500');
                itemEl.classList.add('hover:bg-dark-800');
                const dot = itemEl.querySelector('.rounded-full.bg-indigo-500');
                if (dot) dot.remove();
            }

            try {
                await fetch('{{ url("/notifications") }}/' + id + '/read', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                });
                await fetchNotifications(true);
            } catch (_) { /* silent */ }
        }

        window.markAllNotifRead = async function () {
            try {
                await fetch(READ_ALL_URL, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                });
                updateBadge(0);
                // Remove unread indicators from all items
                document.querySelectorAll('.notif-item').forEach(el => {
                    el.classList.remove('bg-indigo-500/5', 'hover:bg-indigo-500/10', 'border-l-2', 'border-indigo-500');
                    el.classList.add('hover:bg-dark-800');
                    const dot = el.querySelector('.rounded-full.bg-indigo-500');
                    if (dot) dot.remove();
                });
            } catch (_) { /* silent */ }
        };

        window.toggleNotifDropdown = function (e) {
            e.stopPropagation();
            const dd = document.getElementById('notif-dropdown');
            if (!dd) return;
            dropdownOpen = !dropdownOpen;
            dd.classList.toggle('hidden', !dropdownOpen);
            if (dropdownOpen) {
                fetchNotifications(true);
            }
        };

        // Close on outside click
        document.addEventListener('click', function (e) {
            const wrapper = document.getElementById('notif-bell-wrapper');
            if (wrapper && !wrapper.contains(e.target)) {
                dropdownOpen = false;
                const dd = document.getElementById('notif-dropdown');
                if (dd) dd.classList.add('hidden');
            }
        });

        // Initial fetch + poll
        fetchNotifications(true);
        setInterval(() => fetchNotifications(false), POLL_INTERVAL_MS);
    }());
    </script>
    <script>
    // ── Web Push Subscription ─────────────────────────────────────────────────
    (function () {
        const VAPID_PUBLIC_KEY = @json(config('services.vapid.public_key'));
        if (!VAPID_PUBLIC_KEY) return; // keys not configured yet

        if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;

        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64  = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            const raw     = atob(base64);
            const output  = new Uint8Array(raw.length);
            for (let i = 0; i < raw.length; ++i) output[i] = raw.charCodeAt(i);
            return output;
        }

        async function registerPush() {
            try {
                const reg = await navigator.serviceWorker.register('/sw.js', { scope: '/' });
                await navigator.serviceWorker.ready;

                if (Notification.permission === 'denied') return;

                let sub = await reg.pushManager.getSubscription();
                if (!sub) {
                    if (Notification.permission === 'default') {
                        const permission = await Notification.requestPermission();
                        if (permission !== 'granted') return;
                    }
                    sub = await reg.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
                    });
                }

                // POST subscription to server
                await fetch('/push/subscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        endpoint: sub.endpoint,
                        keys: {
                            p256dh: btoa(String.fromCharCode(...new Uint8Array(sub.getKey('p256dh')))),
                            auth:   btoa(String.fromCharCode(...new Uint8Array(sub.getKey('auth')))),
                        },
                        contentEncoding: (PushManager.supportedContentEncodings || ['aesgcm'])[0],
                    }),
                });
            } catch (e) {
                // Silently ignore — push is a progressive enhancement
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', registerPush);
        } else {
            registerPush();
        }
    }());
    </script>
    @endauth

    @stack('scripts')
</body>
</html>
