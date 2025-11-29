@extends('layouts.app')

@section('content')
<div class="flex">
    <!-- Sidebar - Dynamic based on role (Admin Toko or Kasir) -->
    @php
        $role = auth()->user()->getRoleNames()->first();
        $isAdminToko = $role === 'Admin Toko';
        $isKasir = $role === 'Kasir';

        $menus = [
            [
                'label' => 'Dashboard',
                'url' => '/dashboard',
                'icon' => '<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6\'></path></svg>'
            ]
        ];

        // POS Menu - both roles
        $menus[] = [
            'label' => 'POS',
            'url' => '/pos',
            'icon' => '<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z\'></path></svg>'
        ];

        // Admin Toko specific menus
        if($isAdminToko) {
            $menus[] = [
                'label' => 'Staff',
                'url' => '/staff',
                'icon' => '<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z\'></path></svg>'
            ];

            $menus[] = [
                'label' => 'Products',
                'icon' => '<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4\'></path></svg>',
                'submenu' => [
                    ['label' => 'Products', 'url' => '/products'],
                    ['label' => 'Categories', 'url' => '/categories']
                ]
            ];

            $menus[] = [
                'label' => 'Inventory',
                'icon' => '<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4\'></path></svg>',
                'submenu' => [
                    ['label' => 'Stock Overview', 'url' => '/inventory'],
                    ['label' => 'Stock Adjustments', 'url' => '/inventory/adjustments'],
                    ['label' => 'Stock Opname', 'url' => '/inventory/opname'],
                    ['label' => 'Unpacking', 'url' => '/inventory/unpacking']
                ]
            ];

            $menus[] = [
                'label' => 'Purchases',
                'url' => '/purchases',
                'icon' => '<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z\'></path></svg>'
            ];

            $menus[] = [
                'label' => 'Cash Management',
                'icon' => '<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z\'></path></svg>',
                'submenu' => [
                    ['label' => 'Cash Reconciliation', 'url' => '/cash/reconciliation'],
                    ['label' => 'Cash Deposits', 'url' => '/cash/deposits'],
                    ['label' => 'Registers', 'url' => '/cash/registers']
                ]
            ];
        }

        // Sessions - both roles
        $menus[] = [
            'label' => 'Sessions',
            'url' => '/sessions',
            'icon' => '<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z\'></path></svg>'
        ];

        // Customers - both roles
        $menus[] = [
            'label' => 'Customers',
            'url' => '/customers',
            'icon' => '<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z\'></path></svg>'
        ];

        // Reports - Admin Toko only
        if($isAdminToko) {
            $menus[] = [
                'label' => 'Reports',
                'icon' => '<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z\'></path></svg>',
                'submenu' => [
                    ['label' => 'Sales Report', 'url' => '/reports/sales'],
                    ['label' => 'Inventory Report', 'url' => '/reports/inventory'],
                    ['label' => 'Cashier Report', 'url' => '/reports/cashier']
                ]
            ];

            $menus[] = [
                'label' => 'Store Settings',
                'url' => '/store/settings',
                'icon' => '<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z\'></path><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M15 12a3 3 0 11-6 0 3 3 0 016 0z\'></path></svg>'
            ];
        }
    @endphp

    <x-sidebar :menus="$menus" />

    <!-- Main Content Area -->
    <div class="flex-1 ml-64">
        <!-- Navbar with Store & Tenant Name -->
        <x-navbar :title="$title ?? 'Dashboard'" />

        <!-- Page Content -->
        <div class="p-6">
            <!-- Store & Tenant Info Banner -->
            <div class="mb-4 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg p-4 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold">{{ auth()->user()->store->name }}</h3>
                        <p class="text-sm opacity-90">{{ auth()->user()->tenant->name }} • {{ auth()->user()->store->city }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm opacity-90">Your Role</p>
                        <p class="text-lg font-bold">{{ $role }}</p>
                    </div>
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
