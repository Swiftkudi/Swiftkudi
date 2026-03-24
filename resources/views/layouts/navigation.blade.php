<nav x-data="{ open: false }" class="bg-white border-b border-gray-200">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-lg">SK</span>
                        </div>
                        <span class="ml-2 text-xl font-bold text-gray-900">SwiftKudi</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-nav-link>

                    <x-nav-link :href="route('tasks.index')" :active="request()->routeIs('tasks.*')">
                        Tasks
                    </x-nav-link>

                    <x-nav-link :href="route('tasks.bundles')" :active="request()->routeIs('tasks.bundles')">
                        Bundles
                    </x-nav-link>

                    <x-nav-link :href="route('professional-services.index')" :active="request()->routeIs('professional-services.*')">
                        Hire
                    </x-nav-link>

                    <x-nav-link :href="route('growth.index')" :active="request()->routeIs('growth.*')">
                        Growth
                    </x-nav-link>

                    <x-nav-link :href="route('digital-products.index')" :active="request()->routeIs('digital-products.*')">
                        Products
                    </x-nav-link>

                    <x-nav-link :href="route('jobs.index')" :active="request()->routeIs('jobs.*')">
                        Jobs
                    </x-nav-link>

                    <x-nav-link :href="route('wallet.escrow')" :active="request()->routeIs('wallet.escrow')">
                        Escrow
                    </x-nav-link>

                    <x-nav-link :href="route('disputes.index')" :active="request()->routeIs('disputes.*')">
                        Disputes
                    </x-nav-link>

                    <x-nav-link :href="route('chat.index')" :active="request()->routeIs('chat.*')">
                        Messages
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:ml-6 sm:flex sm:items-center">
                <div class="ml-3 relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
                    <div @click="open = ! open" class="flex items-center cursor-pointer">
                        <button class="flex text-sm border-none focus:outline-none focus:border-none transition">
                            <div class="text-right mr-2">
                                <div class="font-medium text-gray-900">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-gray-500">
                                    Level {{ Auth::user()->level }} • {{ number_format(Auth::user()->experience_points) }} XP
                                </div>
                            </div>
                            <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                <span class="text-indigo-600 font-medium">{{ substr(Auth::user()->name, 0, 1) }}</span>
                            </div>
                        </button>
                    </div>

                    <div x-show="open" x-transition.opacity.duration.200ms @click="open = false"
                        class="fixed inset-0 z-10" style="display: none;"></div>

                    <div x-show="open" x-transition.opacity.duration.200ms
                        class="absolute right-0 z-20 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-1 focus:outline-none"
                        style="display: none;" @click="open = false">
                        <!-- Wallet Balance -->
                        @if(Auth::user()->wallet)
                        <div class="px-4 py-2 border-b border-gray-100">
                            <div class="text-xs text-gray-500">Balance</div>
                            <div class="font-medium text-green-600">
                                ₦{{ number_format(Auth::user()->wallet->withdrawable_balance + Auth::user()->wallet->promo_credit_balance, 2) }}
                            </div>
                        </div>
                        @endif

                        <!-- Dashboard -->
                        <x-dropdown-link :href="route('dashboard')">
                            <i class="fas fa-home mr-2"></i> Dashboard
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('wallet.index')">
                            <i class="fas fa-wallet mr-2"></i> Wallet
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('wallet.escrow')">
                            <i class="fas fa-shield-alt mr-2"></i> Escrow
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('tasks.my-tasks')">
                            <i class="fas fa-tasks mr-2"></i> My Tasks
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('referrals.index')">
                            <i class="fas fa-users mr-2"></i> Referrals
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('chat.index')">
                            <i class="fas fa-envelope mr-2"></i> Messages
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('disputes.index')">
                            <i class="fas fa-exclamation-circle mr-2"></i> Disputes
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('verification.index')">
                            <i class="fas fa-check-circle mr-2"></i> Verification
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('boost.index')">
                            <i class="fas fa-rocket mr-2"></i> Boost & Promo
                        </x-dropdown-link>

                        <!-- Professional Services -->
                        <div class="px-4 py-2 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Professional Services
                        </div>
                        <x-dropdown-link :href="route('professional-services.index')">
                            <i class="fas fa-search mr-2"></i> Browse Services
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('professional-services.my-services')">
                            <i class="fas fa-briefcase mr-2"></i> My Services
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('professional-services.orders.index')">
                            <i class="fas fa-shopping-cart mr-2"></i> Service Orders
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('professional-services.sales.index')">
                            <i class="fas fa-chart-line mr-2"></i> My Sales
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('professional-services.directory')">
                            <i class="fas fa-users mr-2"></i> Provider Directory
                        </x-dropdown-link>

                        <!-- Growth Marketplace -->
                        <div class="px-4 py-2 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Growth Marketplace
                        </div>
                        <x-dropdown-link :href="route('growth.index')">
                            <i class="fas fa-search mr-2"></i> Browse Growth
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('growth.my-listings')">
                            <i class="fas fa-list mr-2"></i> My Listings
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('growth.orders.index')">
                            <i class="fas fa-shopping-cart mr-2"></i> My Orders
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('growth.sales.index')">
                            <i class="fas fa-chart-line mr-2"></i> My Sales
                        </x-dropdown-link>

                        <!-- Digital Products -->
                        <div class="px-4 py-2 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Digital Products
                        </div>
                        <x-dropdown-link :href="route('digital-products.index')">
                            <i class="fas fa-search mr-2"></i> Browse Products
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('digital-products.my-products')">
                            <i class="fas fa-box mr-2"></i> My Products
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('digital-products.my-purchases')">
                            <i class="fas fa-shopping-bag mr-2"></i> My Purchases
                        </x-dropdown-link>

                        <!-- Jobs -->
                        <div class="px-4 py-2 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Job Board
                        </div>
                        <x-dropdown-link :href="route('jobs.index')">
                            <i class="fas fa-briefcase mr-2"></i> Browse Jobs
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('jobs.my-jobs')">
                            <i class="fas fa-list mr-2"></i> My Job Posts
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('jobs.applications')">
                            <i class="fas fa-paper-plane mr-2"></i> My Applications
                        </x-dropdown-link>

                        <!-- Settings & Admin -->
                        <div class="px-4 py-2 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Settings
                        </div>
                        <x-dropdown-link :href="route('settings.profile')">
                            <i class="fas fa-user mr-2"></i> Profile Settings
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('settings.index')">
                            <i class="fas fa-cog mr-2"></i> Account Settings
                        </x-dropdown-link>

                        <!-- Admin Link -->
                        @if(Auth::user()->is_admin)
                        <x-dropdown-link :href="route('admin.index')">
                            <i class="fas fa-cog mr-2"></i> Admin Panel
                        </x-dropdown-link>
                        @endif

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                <i class="fas fa-sign-out-alt mr-2"></i> {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                    <span class="sr-only">Open main menu</span>
                    <i class="fas fa-bars" x-show="!open"></i>
                    <i class="fas fa-times" x-show="open"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                Dashboard
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('tasks.index')" :active="request()->routeIs('tasks.*')">
                Tasks
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('referrals.index')" :active="request()->routeIs('referrals.*')">
                Referrals
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('dashboard.leaderboard')" :active="request()->routeIs('dashboard.leaderboard')">
                Leaderboard
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('professional-services.index')" :active="request()->routeIs('professional-services.*')">
                Hire
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('growth.index')" :active="request()->routeIs('growth.*')">
                Growth
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('digital-products.index')" :active="request()->routeIs('digital-products.*')">
                Products
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('digital-products.my-products')" :active="request()->routeIs('digital-products.my-products')">
                My Products
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('digital-products.my-purchases')" :active="request()->routeIs('digital-products.my-purchases')">
                My Purchases
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('growth.my-listings')" :active="request()->routeIs('growth.my-listings')">
                Growth My Listings
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('chat.index')" :active="request()->routeIs('chat.*')">
                Messages
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-4 border-t border-gray-200">
            <div class="flex items-center px-4">
                <div class="flex-shrink-0">
                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                        <span class="text-indigo-600 font-medium">{{ substr(Auth::user()->name, 0, 1) }}</span>
                    </div>
                </div>
                <div class="ml-3">
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('wallet.index')">
                    <i class="fas fa-wallet mr-2"></i> Wallet
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('dashboard.profile')">
                    <i class="fas fa-user mr-2"></i> Profile
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('tasks.my-tasks')">
                    <i class="fas fa-list mr-2"></i> My Tasks
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('professional-services.my-services')">
                    <i class="fas fa-briefcase mr-2"></i> My Services
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('professional-services.orders.index')">
                    <i class="fas fa-shopping-cart mr-2"></i> Service Orders
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('growth.my-listings')">
                    <i class="fas fa-chart-line mr-2"></i> My Growth Listings
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('chat.index')">
                    <i class="fas fa-comments mr-2"></i> Messages
                </x-responsive-nav-link>

                @if(Auth::user()->email === 'admin@swiftkudi.com')
                <x-responsive-nav-link :href="route('admin.index')">
                    <i class="fas fa-cog mr-2"></i> Admin Panel
                </x-responsive-nav-link>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        <i class="fas fa-sign-out-alt mr-2"></i> {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
