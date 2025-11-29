@extends('layouts.app')

@section('content')
<div class="flex">
    <!-- Sidebar -->
    <x-sidebar :menus="[
        [
            'label' => 'Dashboard',
            'url' => '/dashboard',
            'icon' => '<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6\'></path></svg>'
        ],
        [
            'label' => 'Stores',
            'url' => '/stores',
            'icon' => '<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4\'></path></svg>'
        ],
        [
            'label' => 'Users',
            'url' => '/users',
            'icon' => '<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z\'></path></svg>'
        ],
        [
            'label' => 'Products',
            'icon' => '<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4\'></path></svg>',
            'submenu' => [
                ['label' => 'Categories', 'url' => '/categories'],
                ['label' => 'Products', 'url' => '/products'],
                ['label' => 'Price History', 'url' => '/products/price-history']
            ]
        ],
        [
            'label' => 'Inventory',
            'icon' => '<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4\'></path></svg>',
            'submenu' => [
                ['label' => 'Stock Overview', 'url' => '/inventory'],
                ['label' => 'Stock Adjustments', 'url' => '/inventory/adjustments'],
                ['label' => 'Stock Opname', 'url' => '/inventory/opname'],
                ['label' => 'Stock Transfer', 'url' => '/inventory/transfers'],
                ['label' => 'Unpacking', 'url' => '/inventory/unpacking'],
                ['label' => 'Stock Movements', 'url' => '/inventory/movements']
            ]
        ],
        [
            'label' => 'Suppliers',
            'url' => '/suppliers',
            'icon' => '<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z\'></path></svg>'
        ],
        [
            'label' => 'Purchases',
            'url' => '/purchases',
            'icon' => '<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z\'></path></svg>'
        ],
        [
            'label' => 'Reports',
            'icon' => '<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z\'></path></svg>',
            'submenu' => [
                ['label' => 'Sales Report', 'url' => '/reports/sales'],
                ['label' => 'Inventory Report', 'url' => '/reports/inventory'],
                ['label' => 'Financial Report', 'url' => '/reports/financial'],
                ['label' => 'Cashier Report', 'url' => '/reports/cashier']
            ]
        ]
    ]" />

    <!-- Main Content Area -->
    <div class="flex-1 ml-64">
        <!-- Navbar with Tenant Name -->
        <x-navbar :title="$title ?? 'Dashboard'" />

        <!-- Page Content -->
        <div class="p-6">
            <!-- Tenant Info Banner -->
            <div class="mb-4 bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-indigo-900">{{ auth()->user()->tenant->name }}</h3>
                        <p class="text-sm text-indigo-600">Subscription: <x-badge :color="auth()->user()->tenant->subscription_status === 'active' ? 'green' : 'yellow'" :text="ucfirst(auth()->user()->tenant->subscription_status)" /></p>
                    </div>
                    @if(auth()->user()->tenant->subscription_status === 'trial')
                        <div class="text-right">
                            <p class="text-sm text-indigo-800">Trial ends in:</p>
                            <p class="text-lg font-bold text-indigo-900">{{ now()->diffInDays(auth()->user()->tenant->trial_ends_at) }} days</p>
                        </div>
                    @endif
                </div>
            </div>

            @if(isset($breadcrumb))
                <x-breadcrumb :items="$breadcrumb" />
            @endif

            @yield('page-content')
        </div>
    </div>
</div>
@endsection
