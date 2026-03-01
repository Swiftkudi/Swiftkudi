<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel - SwiftKudi')</title>
    
    {{-- Favicon --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    {{-- Laravel Mix Assets - Tailwind CSS compiled via Mix --}}
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <script src="{{ mix('js/app.js') }}" defer></script>
    
    {{-- External Libraries --}}
    <script src="https://unpkg.com/lucide@latest"></script>
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
        input[type="tel"],
        input[type="url"],
        select,
        textarea {
            background-color: #0f172a !important;
            color: #e2e8f0 !important;
            border-color: #334155 !important;
            border-width: 1px !important;
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

        /* Checkbox styling */
        input[type="checkbox"] {
            accent-color: #6366f1;
            background-color: #1e293b;
            border-color: #475569;
        }

        /* Label styling */
        label {
            color: #cbd5e1;
        }

        /* Table styling */
        table {
            color: #e2e8f0;
        }
        thead th {
            color: #94a3b8;
        }

        /* Card styling */
        .card {
            background-color: #0f172a;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
            border-radius: 0.5rem;
            border: 1px solid #1e293b;
        }

        /* Pagination styling */
        .pagination a,
        .pagination span {
            background-color: #0f172a !important;
            color: #e2e8f0 !important;
            border-color: #334155 !important;
        }

        /* Button transitions */
        button, .btn, a.btn {
            transition: all 0.2s ease;
        }

        button:active, .btn:active {
            transform: scale(0.98);
        }

        /* Focus styles */
        button:focus-visible,
        a:focus-visible,
        input:focus-visible {
            outline: 2px solid #6366f1;
            outline-offset: 2px;
        }

        /* Mobile sidebar overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 40;
        }
        
        .sidebar-overlay.active {
            display: block;
        }

        /* Mobile sidebar */
        @media (max-width: 1023px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                z-index: 50;
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0 !important;
            }
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #0f172a;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }
    </style>
</head>
<body class="font-body bg-dark-950 text-gray-100 min-h-screen">
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay" class="sidebar-overlay lg:hidden"></div>
    
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar w-64 bg-dark-900 border-r border-dark-700 flex flex-col fixed h-full">
            <!-- Logo -->
            <div class="h-16 flex items-center justify-between px-4 lg:px-6 border-b border-dark-700">
                <a href="{{ route('dashboard') }}" class="flex items-center group">
                    <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-coins text-white text-sm"></i>
                    </div>
                    <span class="font-bold text-lg bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">SwiftKudi</span>
                </a>
                <!-- Mobile close button -->
                <button id="close-sidebar" class="lg:hidden p-2 rounded-lg text-gray-400 hover:text-white hover:bg-dark-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Admin Badge -->
            <div class="px-4 py-3 border-b border-dark-700">
                <div class="flex items-center px-3 py-2 bg-indigo-500/10 rounded-lg">
                    <i class="fas fa-shield-alt text-indigo-400 mr-3"></i>
                    <span class="text-sm font-medium text-indigo-400">Admin Panel</span>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4">
                <ul class="space-y-1 px-3">
                    <!-- Dashboard -->
                    <li>
                        <a href="{{ route('admin.index') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.index') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-indigo-400 hover:bg-dark-800' }}">
                            <i class="fas fa-home w-5 mr-2"></i>
                            Dashboard
                        </a>
                    </li>

                    <!-- Analytics -->
                    <li>
                        <a href="{{ route('admin.analytics') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.analytics') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-indigo-400 hover:bg-dark-800' }}">
                            <i class="fas fa-chart-line w-5 mr-2"></i>
                            Analytics
                        </a>
                    </li>

                    <!-- Revenue -->
                    <li>
                        <a href="{{ route('admin.revenue.index') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.revenue*') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-indigo-400 hover:bg-dark-800' }}">
                            <i class="fas fa-chart-pie w-5 mr-2"></i>
                            Revenue
                        </a>
                    </li>

                    <li class="pt-4 pb-2">
                        <span class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Management</span>
                    </li>

                    <!-- Users -->
                    <li>
                        <a href="{{ route('admin.users') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.users') || request()->routeIs('admin.user-details') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-indigo-400 hover:bg-dark-800' }}">
                            <i class="fas fa-users w-5 mr-2"></i>
                            Users
                        </a>
                    </li>

                    <!-- Tasks -->
                    <li>
                        <a href="{{ route('admin.tasks') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.tasks') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-indigo-400 hover:bg-dark-800' }}">
                            <i class="fas fa-tasks w-5 mr-2"></i>
                            Tasks
                        </a>
                    </li>

                    <!-- Completions -->
                    <li>
                        <a href="{{ route('admin.completions') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.completions') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-indigo-400 hover:bg-dark-800' }}">
                            <i class="fas fa-check-circle w-5 mr-2"></i>
                            Completions
                        </a>
                    </li>

                    <!-- Withdrawals -->
                    <li>
                        <a href="{{ route('admin.withdrawals') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.withdrawals') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-indigo-400 hover:bg-dark-800' }}">
                            <i class="fas fa-money-bill-wave w-5 mr-2"></i>
                            Withdrawals
                        </a>
                    </li>

                    <!-- Fraud Logs -->
                    <li>
                        <a href="{{ route('admin.fraud-logs') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.fraud-logs') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-indigo-400 hover:bg-dark-800' }}">
                            <i class="fas fa-exclamation-triangle w-5 mr-2"></i>
                            Fraud Logs
                        </a>
                    </li>

                    <!-- Referrals -->
                    <li>
                        <a href="{{ route('admin.referrals') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.referrals*') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-indigo-400 hover:bg-dark-800' }}">
                            <i class="fas fa-user-friends w-5 mr-2"></i>
                            Referrals
                        </a>
                    </li>

                    <!-- Activation -->
                    <li>
                        <a href="{{ route('admin.activations') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.activations*') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-indigo-400 hover:bg-dark-800' }}">
                            <i class="fas fa-rocket w-5 mr-2"></i>
                            Activations
                        </a>
                    </li>

                    <!-- Growth Listings -->
                    <li>
                        <a href="{{ route('admin.growth-listings') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.growth-listings*') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-indigo-400 hover:bg-dark-800' }}">
                            <i class="fas fa-chart-line w-5 mr-2"></i>
                            Growth Listings
                        </a>
                    </li>

                    <!-- Digital Products -->
                    <li>
                        <a href="{{ route('admin.digital-products') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.digital-products*') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-indigo-400 hover:bg-dark-800' }}">
                            <i class="fas fa-box w-5 mr-2"></i>
                            Digital Products
                        </a>
                    </li>

                    <li class="pt-4 pb-2">
                        <span class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">System Settings</span>
                    </li>

                    <!-- General Settings -->
                    <li>
                        <a href="{{ route('admin.settings') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.settings') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-indigo-400 hover:bg-dark-800' }}">
                            <i class="fas fa-cog w-5 mr-2"></i>
                            General
                        </a>
                    </li>

                    <!-- Commission Settings -->
                    <li>
                        <a href="{{ route('admin.settings.commission') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.settings.commission') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-indigo-400 hover:bg-dark-800' }}">
                            <i class="fas fa-percentage w-5 mr-2"></i>
                            Commission
                        </a>
                    </li>

                    <!-- Payment Settings -->
                    <li>
                        <a href="{{ route('admin.settings.payment') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.settings.payment') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-indigo-400 hover:bg-dark-800' }}">
                            <i class="fas fa-credit-card w-5 mr-2"></i>
                            Payment
                        </a>
                    </li>

                    <!-- Notification Settings -->
                    <li>
                        <a href="{{ route('admin.settings.notification') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.settings.notification') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-indigo-400 hover:bg-dark-800' }}">
                            <i class="fas fa-bell w-5 mr-2"></i>
                            Notifications
                        </a>
                    </li>

                    <!-- Security Settings -->
                    <li>
                        <a href="{{ route('admin.settings.security') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.settings.security') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-indigo-400 hover:bg-dark-800' }}">
                            <i class="fas fa-shield-alt w-5 mr-2"></i>
                            Security
                        </a>
                    </li>

                    <!-- Cron Jobs -->
                    <li>
                        <a href="{{ route('admin.settings.cron') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.settings.cron') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-indigo-400 hover:bg-dark-800' }}">
                            <i class="fas fa-clock w-5 mr-2"></i>
                            Cron Jobs
                        </a>
                    </li>

                    <!-- Audit Logs -->
                    <li>
                        <a href="{{ route('admin.settings.audit') }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.settings.audit') ? 'bg-indigo-500/10 text-indigo-400' : 'text-gray-400 hover:text-indigo-400 hover:bg-dark-800' }}">
                            <i class="fas fa-history w-5 mr-2"></i>
                            Audit Logs
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Bottom Section -->
            <div class="border-t border-dark-700 p-4">
                <!-- Back to Site -->
                <a href="{{ route('dashboard') }}" class="w-full flex items-center justify-center px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white transition-all">
                    <i class="fas fa-arrow-left mr-2"></i>
                    <span class="text-sm">Back to Site</span>
                </a>

                <!-- Logout -->
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center px-4 py-2 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 transition-all">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        <span class="text-sm">Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main-content flex-1 lg:ml-64">
            <!-- Top Header -->
            <header class="h-16 bg-dark-900 border-b border-dark-700 flex items-center justify-between px-4 lg:px-6 sticky top-0 z-30">
                <div class="flex items-center">
                    <!-- Mobile menu button -->
                    <button id="mobile-menu-btn" class="lg:hidden p-2 mr-3 rounded-lg text-gray-400 hover:text-white hover:bg-dark-800">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h1 class="text-lg lg:text-xl font-bold text-white">@yield('page-title', 'Admin Panel')</h1>
                </div>

                <div class="flex items-center space-x-2 lg:space-x-4">
                    <!-- Quick Settings Link -->
                    <a href="{{ route('admin.settings') }}" title="Settings" class="inline-flex items-center px-2 lg:px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:bg-dark-800">
                        <i class="fas fa-cog w-4 mr-0 lg:mr-2"></i>
                        <span class="hidden lg:inline">Settings</span>
                    </a>

                    <!-- Admin User Info -->
                    <div class="flex items-center space-x-2 lg:space-x-3">
                        <div class="text-right hidden md:block">
                            <p class="text-sm font-medium text-white">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-400">Administrator</p>
                        </div>
                        <div class="h-9 w-9 lg:h-10 lg:w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-4 lg:p-6">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        // Mobile sidebar functionality
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const closeSidebarBtn = document.getElementById('close-sidebar');

        function openSidebar() {
            sidebar.classList.add('open');
            sidebarOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', openSidebar);
        }

        if (closeSidebarBtn) {
            closeSidebarBtn.addEventListener('click', closeSidebar);
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', closeSidebar);
        }

        // Close sidebar on window resize if larger than mobile breakpoint
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                closeSidebar();
            }
        });

        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</body>
</html>
