@php
    $navUser = auth()->user();
    $navWallet = $navUser->wallet ?? null;
    $navBalance = $navWallet ? ($navWallet->withdrawable_balance + $navWallet->promo_credit_balance) : 0;
@endphp

<!-- Mobile marketplace navigation -->
<div id="mobile-menu-overlay" class="fixed inset-0 z-40 hidden bg-black/60 lg:hidden" onclick="closeMobileMenu()"></div>
<aside id="mobile-menu" class="fixed inset-y-0 left-0 z-50 flex w-80 max-w-[88vw] -translate-x-full flex-col border-r border-dark-700 bg-dark-950 transition-transform duration-200 lg:hidden" aria-label="Mobile navigation">
    <div class="flex h-16 items-center justify-between border-b border-dark-700 px-5">
        <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="flex items-center gap-2.5" aria-label="SwiftKudi home">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-white"><i class="fas fa-briefcase"></i></span>
            <span class="font-heading text-lg font-bold text-white">SwiftKudi</span>
        </a>
        <button type="button" onclick="closeMobileMenu()" class="rounded-lg p-2 text-gray-400 hover:bg-dark-800 hover:text-white" aria-label="Close menu"><i class="fas fa-times"></i></button>
    </div>

    <div class="px-4 pt-4">
        <form method="GET" action="{{ route('marketplace.search') }}" class="marketplace-mobile-search">
            <i class="fas fa-search text-gray-500"></i>
            <input type="search" name="q" maxlength="120" placeholder="Search marketplace" aria-label="Search SwiftKudi marketplace">
        </form>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto p-4 pb-8">
        <a href="{{ route('jobs.index') }}" class="marketplace-nav-mobile {{ request()->routeIs('jobs.index', 'jobs.show') ? 'marketplace-nav-mobile-active' : '' }}"><i class="fas fa-search w-5"></i><span>Find Work</span></a>
        <a href="{{ route('freelancers.index') }}" class="marketplace-nav-mobile {{ request()->routeIs('freelancers.*', 'professional-services.directory', 'professional-services.provider-profile') ? 'marketplace-nav-mobile-active' : '' }}"><i class="fas fa-user-tie w-5"></i><span>Find Talent</span></a>
        <a href="{{ route('professional-services.index') }}" class="marketplace-nav-mobile {{ request()->routeIs('professional-services.index', 'professional-services.show') ? 'marketplace-nav-mobile-active' : '' }}"><i class="fas fa-layer-group w-5"></i><span>Services</span></a>
        @auth
            <div class="my-3 border-t border-dark-700"></div>
            <a href="{{ route('dashboard') }}" class="marketplace-nav-mobile {{ request()->routeIs('dashboard') ? 'marketplace-nav-mobile-active' : '' }}"><i class="fas fa-gauge w-5"></i><span>Dashboard</span></a>
            <a href="{{ route('my-work.index') }}" class="marketplace-nav-mobile {{ request()->routeIs('my-work.*') ? 'marketplace-nav-mobile-active' : '' }}"><i class="fas fa-list-check w-5"></i><span>My Work</span></a>
            @if(Route::has('contracts.index'))
                <a href="{{ route('contracts.index') }}" class="marketplace-nav-mobile {{ request()->routeIs('contracts.*') ? 'marketplace-nav-mobile-active' : '' }}"><i class="fas fa-file-signature w-5"></i><span>Contracts</span></a>
            @endif
            <a href="{{ route('jobs.applications') }}" class="marketplace-nav-mobile {{ request()->routeIs('jobs.applications') ? 'marketplace-nav-mobile-active' : '' }}"><i class="fas fa-paper-plane w-5"></i><span>My Proposals</span></a>
            <a href="{{ route('jobs.my-jobs') }}" class="marketplace-nav-mobile {{ request()->routeIs('jobs.my-jobs') ? 'marketplace-nav-mobile-active' : '' }}"><i class="fas fa-briefcase w-5"></i><span>My Jobs</span></a>
            <a href="{{ route('chat.index') }}" class="marketplace-nav-mobile {{ request()->routeIs('chat.*') ? 'marketplace-nav-mobile-active' : '' }}"><i class="fas fa-comments w-5"></i><span>Messages</span></a>
            <a href="{{ route('wallet.index') }}" class="marketplace-nav-mobile {{ request()->routeIs('wallet.*') ? 'marketplace-nav-mobile-active' : '' }}"><i class="fas fa-wallet w-5"></i><span>Wallet</span></a>
            <a href="{{ route('dashboard.profile') }}" class="marketplace-nav-mobile {{ request()->routeIs('dashboard.profile') ? 'marketplace-nav-mobile-active' : '' }}"><i class="fas fa-user w-5"></i><span>Profile</span></a>
            @if(Route::has('notification-settings.edit'))
                <a href="{{ route('notification-settings.edit') }}" class="marketplace-nav-mobile {{ request()->routeIs('notification-settings.*') ? 'marketplace-nav-mobile-active' : '' }}"><i class="fas fa-bell w-5"></i><span>Notification Settings</span></a>
            @endif
            @if($navUser && $navUser->is_admin)
                <a href="{{ route('admin.index') }}" class="marketplace-nav-mobile"><i class="fas fa-shield-halved w-5"></i><span>Admin</span></a>
            @endif
        @endauth
    </nav>

    @auth
        <div class="mt-auto border-t border-dark-700 bg-dark-950 p-4">
            <div class="mb-3 flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white">{{ strtoupper(substr($navUser->name, 0, 2)) }}</span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-white">{{ $navUser->name }}</p>
                    <p class="text-xs text-gray-400">₦{{ number_format($navBalance, 2) }} available</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="w-full rounded-lg border border-dark-700 px-4 py-2 text-sm font-medium text-gray-300 hover:border-red-500/50 hover:bg-red-500/10 hover:text-red-300">Sign out</button></form>
        </div>
    @endauth
</aside>

<header class="fixed inset-x-0 top-0 z-50 border-b border-dark-700 bg-dark-950/95 backdrop-blur-xl">
    <div class="mx-auto flex h-16 max-w-[1440px] items-center gap-3 px-4 sm:px-6 lg:px-8">
        <button id="mobile-menu-btn" type="button" onclick="openMobileMenu()" class="rounded-lg p-2 text-gray-400 hover:bg-dark-800 hover:text-white lg:hidden" aria-label="Open menu"><i class="fas fa-bars"></i></button>

        <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="flex flex-shrink-0 items-center gap-2.5" aria-label="SwiftKudi home">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-white shadow-sm"><i class="fas fa-briefcase"></i></span>
            <span class="hidden font-heading text-lg font-bold tracking-tight text-white sm:inline">SwiftKudi</span>
        </a>

        <nav class="ml-2 hidden h-full items-center gap-1 lg:flex" aria-label="Marketplace navigation">
            <a href="{{ route('jobs.index') }}" class="marketplace-nav-link {{ request()->routeIs('jobs.index', 'jobs.show') ? 'marketplace-nav-link-active' : '' }}">Find Work</a>
            <a href="{{ route('freelancers.index') }}" class="marketplace-nav-link {{ request()->routeIs('freelancers.*', 'professional-services.directory', 'professional-services.provider-profile') ? 'marketplace-nav-link-active' : '' }}">Find Talent</a>
            <a href="{{ route('professional-services.index') }}" class="marketplace-nav-link {{ request()->routeIs('professional-services.index', 'professional-services.show') ? 'marketplace-nav-link-active' : '' }}">Services</a>
            @auth
                <a href="{{ route('my-work.index') }}" class="marketplace-nav-link {{ request()->routeIs('my-work.*', 'contracts.*', 'jobs.applications', 'jobs.my-jobs') ? 'marketplace-nav-link-active' : '' }}">My Work</a>
                <a href="{{ route('chat.index') }}" class="marketplace-nav-link {{ request()->routeIs('chat.*') ? 'marketplace-nav-link-active' : '' }}">Messages</a>
            @endauth
        </nav>

        <form method="GET" action="{{ route('marketplace.search') }}" class="marketplace-header-search ml-auto hidden md:flex" role="search">
            <i class="fas fa-search text-gray-500"></i>
            <input type="search" name="q" value="{{ request()->routeIs('marketplace.search') ? request('q') : '' }}" maxlength="120" placeholder="Search jobs, talent, services" aria-label="Search SwiftKudi marketplace">
            <select name="scope" aria-label="Search type">
                <option value="all" @selected(request()->routeIs('marketplace.search') && request('scope', 'all') === 'all')>All</option>
                <option value="jobs" @selected(request()->routeIs('marketplace.search') && request('scope') === 'jobs')>Jobs</option>
                <option value="talent" @selected(request()->routeIs('marketplace.search') && request('scope') === 'talent')>Talent</option>
                <option value="services" @selected(request()->routeIs('marketplace.search') && request('scope') === 'services')>Services</option>
            </select>
        </form>

        <div class="{{ auth()->check() ? '' : 'ml-auto' }} flex items-center gap-2">
            @auth
                <a href="{{ route('wallet.index') }}" class="hidden rounded-lg px-3 py-2 text-right hover:bg-dark-800 sm:block" aria-label="Wallet balance">
                    <span class="block text-[10px] font-medium uppercase tracking-wide text-gray-500">Available</span>
                    <span class="block text-sm font-semibold text-gray-200">₦{{ number_format($navBalance, 2) }}</span>
                </a>

                <div class="relative" id="notif-bell-wrapper">
                    <button id="notif-bell-btn" type="button" class="relative flex h-10 w-10 items-center justify-center rounded-full text-gray-400 hover:bg-dark-800 hover:text-white" aria-label="Notifications" onclick="toggleNotifDropdown(event)">
                        <i class="far fa-bell"></i>
                        <span id="notif-badge" class="absolute -right-0.5 -top-0.5 hidden min-w-[18px] h-[18px] items-center justify-center rounded-full bg-indigo-500 px-1 text-[10px] font-bold leading-none text-white">0</span>
                    </button>
                    <div id="notif-dropdown" class="absolute right-0 z-[200] mt-2 hidden w-[min(24rem,calc(100vw-2rem))] overflow-hidden rounded-xl border border-dark-700 bg-dark-900 shadow-2xl" onclick="event.stopPropagation()">
                        <div class="flex items-center justify-between border-b border-dark-700 px-4 py-3">
                            <h3 class="text-sm font-semibold text-white">Notifications</h3>
                            <div class="flex items-center gap-3"><button type="button" onclick="markAllNotifRead()" class="text-xs font-medium text-indigo-400 hover:text-indigo-300">Mark all read</button><a href="{{ route('notifications.index') }}" class="text-xs text-gray-400 hover:text-white">View all</a></div>
                        </div>
                        <div id="notif-list" class="max-h-[420px] divide-y divide-dark-700 overflow-y-auto">
                            <div id="notif-empty" class="hidden px-4 py-10 text-center"><i class="far fa-bell-slash mb-2 block text-2xl text-gray-600"></i><p class="text-sm text-gray-500">No notifications yet</p></div>
                            <div id="notif-loading" class="px-4 py-7 text-center"><i class="fas fa-spinner fa-spin text-gray-500"></i></div>
                        </div>
                    </div>
                </div>

                <details class="relative group">
                    <summary class="flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white ring-2 ring-transparent hover:ring-indigo-400/40" aria-label="Account menu">{{ strtoupper(substr($navUser->name, 0, 2)) }}</summary>
                    <div class="absolute right-0 mt-2 w-64 rounded-xl border border-dark-700 bg-dark-900 p-2 shadow-2xl">
                        <div class="border-b border-dark-700 px-3 py-2.5"><p class="truncate text-sm font-semibold text-white">{{ $navUser->name }}</p><p class="truncate text-xs text-gray-500">{{ $navUser->email }}</p></div>
                        <a href="{{ route('dashboard.profile') }}" class="marketplace-dropdown-link"><i class="far fa-user w-5 text-gray-500"></i>Profile</a>
                        <a href="{{ route('professional-services.edit-profile') }}" class="marketplace-dropdown-link"><i class="fas fa-user-tie w-5 text-gray-500"></i>Freelancer Profile</a>
                        @if(Route::has('notification-settings.edit'))<a href="{{ route('notification-settings.edit') }}" class="marketplace-dropdown-link"><i class="far fa-bell w-5 text-gray-500"></i>Notification Settings</a>@endif
                        @if($navUser->is_admin)<a href="{{ route('admin.index') }}" class="marketplace-dropdown-link"><i class="fas fa-shield-halved w-5 text-gray-500"></i>Admin</a>@endif
                        <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-dark-700 pt-1">@csrf<button type="submit" class="marketplace-dropdown-link w-full text-left text-red-300 hover:bg-red-500/10"><i class="fas fa-arrow-right-from-bracket w-5"></i>Sign out</button></form>
                    </div>
                </details>
            @else
                <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-gray-300 hover:bg-dark-800 hover:text-white">Log in</a>
                <a href="{{ route('register') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Sign up</a>
            @endauth
        </div>
    </div>
</header>
