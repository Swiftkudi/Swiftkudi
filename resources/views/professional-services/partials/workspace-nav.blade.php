@php($activeWorkspace = $activeWorkspace ?? '')
<nav class="marketplace-card mb-6 overflow-x-auto p-2" aria-label="Services workspace">
    <div class="flex min-w-max items-center gap-1">
        @foreach([
            ['key' => 'browse', 'route' => 'professional-services.index', 'icon' => 'fa-compass', 'label' => 'Browse'],
            ['key' => 'orders', 'route' => 'professional-services.orders.index', 'icon' => 'fa-bag-shopping', 'label' => 'Purchases'],
            ['key' => 'services', 'route' => 'professional-services.my-services', 'icon' => 'fa-briefcase', 'label' => 'My Services'],
            ['key' => 'sales', 'route' => 'professional-services.sales.index', 'icon' => 'fa-chart-line', 'label' => 'Sales'],
            ['key' => 'profile', 'route' => 'professional-services.edit-profile', 'icon' => 'fa-user-pen', 'label' => 'Profile'],
        ] as $item)
            <a href="{{ route($item['route']) }}"
               class="inline-flex min-h-[42px] items-center gap-2 rounded-lg px-3.5 py-2 text-sm font-semibold transition-colors {{ $activeWorkspace === $item['key'] ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
               @if($activeWorkspace === $item['key']) aria-current="page" @endif>
                <i class="fas {{ $item['icon'] }}" aria-hidden="true"></i>{{ $item['label'] }}
            </a>
        @endforeach
    </div>
</nav>
