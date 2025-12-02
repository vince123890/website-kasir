<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Menu Configuration for All Roles
    |--------------------------------------------------------------------------
    |
    | This file contains menu structure for all user roles in the system.
    | Each menu item can have:
    | - label: Display name
    | - route: Route name
    | - icon: Heroicon name (outline version)
    | - permission: Permission required to access
    | - badge: Function name to get badge count
    | - children: Sub-menu items (nested)
    |
    */

    'Administrator SaaS' => [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'home',
            'permission' => null,
        ],
        [
            'label' => 'Tenants Management',
            'route' => 'admin.tenants.index',
            'icon' => 'building-office',
            'permission' => null,
            'children' => [
                [
                    'label' => 'All Tenants',
                    'route' => 'admin.tenants.index',
                    'icon' => 'list-bullet',
                ],
                [
                    'label' => 'Add New Tenant',
                    'route' => 'admin.tenants.create',
                    'icon' => 'plus-circle',
                ],
                [
                    'label' => 'Active Tenants',
                    'route' => 'admin.tenants.index',
                    'icon' => 'check-circle',
                    'query' => ['status' => 'active'],
                ],
                [
                    'label' => 'Trial Tenants',
                    'route' => 'admin.tenants.index',
                    'icon' => 'clock',
                    'query' => ['status' => 'trial'],
                ],
                [
                    'label' => 'Expired Tenants',
                    'route' => 'admin.tenants.index',
                    'icon' => 'x-circle',
                    'query' => ['status' => 'expired'],
                ],
            ],
        ],
        [
            'label' => 'Users Management',
            'route' => 'admin.users.index',
            'icon' => 'users',
            'permission' => null,
            'children' => [
                [
                    'label' => 'All Users',
                    'route' => 'admin.users.index',
                    'icon' => 'list-bullet',
                ],
                [
                    'label' => 'Add New User',
                    'route' => 'admin.users.create',
                    'icon' => 'user-plus',
                ],
                [
                    'label' => 'Active Users',
                    'route' => 'admin.users.index',
                    'icon' => 'check-circle',
                    'query' => ['status' => 'active'],
                ],
                [
                    'label' => 'Inactive Users',
                    'route' => 'admin.users.index',
                    'icon' => 'x-circle',
                    'query' => ['status' => 'inactive'],
                ],
            ],
        ],
        [
            'label' => 'System Settings',
            'route' => 'admin.settings.index',
            'icon' => 'cog-6-tooth',
            'permission' => null,
            'children' => [
                [
                    'label' => 'General Settings',
                    'route' => 'admin.settings.index',
                    'icon' => 'adjustments-horizontal',
                    'query' => ['tab' => 'general'],
                ],
                [
                    'label' => 'Email Settings',
                    'route' => 'admin.settings.index',
                    'icon' => 'envelope',
                    'query' => ['tab' => 'email'],
                ],
                [
                    'label' => 'Notification Settings',
                    'route' => 'admin.settings.index',
                    'icon' => 'bell',
                    'query' => ['tab' => 'notifications'],
                ],
                [
                    'label' => 'Security Settings',
                    'route' => 'admin.settings.index',
                    'icon' => 'shield-check',
                    'query' => ['tab' => 'security'],
                ],
                [
                    'label' => 'Backup Settings',
                    'route' => 'admin.settings.index',
                    'icon' => 'archive-box',
                    'query' => ['tab' => 'backups'],
                ],
            ],
        ],
        [
            'label' => 'Activity Logs',
            'route' => 'admin.activity-logs.index',
            'icon' => 'document-text',
            'permission' => null,
        ],
        [
            'label' => 'Reports',
            'route' => 'admin.reports.index',
            'icon' => 'chart-bar',
            'permission' => null,
            'children' => [
                [
                    'label' => 'System Report',
                    'route' => 'admin.reports.system',
                    'icon' => 'server',
                ],
                [
                    'label' => 'Subscription Report',
                    'route' => 'admin.reports.subscription',
                    'icon' => 'credit-card',
                ],
                [
                    'label' => 'Revenue Report',
                    'route' => 'admin.reports.revenue',
                    'icon' => 'banknotes',
                ],
            ],
        ],
    ],

    'Tenant Owner' => [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'home',
            'permission' => null,
        ],
        [
            'label' => 'Stores Management',
            'route' => 'stores.index',
            'icon' => 'building-storefront',
            'permission' => null,
            'children' => [
                [
                    'label' => 'All Stores',
                    'route' => 'stores.index',
                    'icon' => 'list-bullet',
                ],
                [
                    'label' => 'Add New Store',
                    'route' => 'stores.create',
                    'icon' => 'plus-circle',
                ],
            ],
        ],
        [
            'label' => 'Users Management',
            'route' => 'users.index',
            'icon' => 'users',
            'permission' => null,
            'children' => [
                [
                    'label' => 'All Users',
                    'route' => 'users.index',
                    'icon' => 'list-bullet',
                ],
                [
                    'label' => 'Add New User',
                    'route' => 'users.create',
                    'icon' => 'user-plus',
                ],
            ],
        ],
        [
            'label' => 'Products',
            'route' => 'products.index',
            'icon' => 'cube',
            'permission' => null,
            'children' => [
                [
                    'label' => 'All Products',
                    'route' => 'products.index',
                    'icon' => 'list-bullet',
                ],
                [
                    'label' => 'Add New Product',
                    'route' => 'products.create',
                    'icon' => 'plus-circle',
                ],
                [
                    'label' => 'Categories',
                    'route' => 'categories.index',
                    'icon' => 'tag',
                ],
                [
                    'label' => 'Bulk Import',
                    'route' => 'products.bulkImport',
                    'icon' => 'arrow-up-tray',
                ],
            ],
        ],
        [
            'label' => 'Suppliers',
            'route' => 'suppliers.index',
            'icon' => 'truck',
            'permission' => null,
            'children' => [
                [
                    'label' => 'All Suppliers',
                    'route' => 'suppliers.index',
                    'icon' => 'list-bullet',
                ],
                [
                    'label' => 'Add New Supplier',
                    'route' => 'suppliers.create',
                    'icon' => 'plus-circle',
                ],
            ],
        ],
        [
            'label' => 'Purchase Orders',
            'route' => 'purchases.index',
            'icon' => 'shopping-cart',
            'permission' => null,
            'children' => [
                [
                    'label' => 'All Purchase Orders',
                    'route' => 'purchases.index',
                    'icon' => 'list-bullet',
                ],
                [
                    'label' => 'Pending Approval',
                    'route' => 'purchases.index',
                    'icon' => 'clock',
                    'query' => ['status' => 'pending'],
                    'badge' => 'getPendingPurchaseOrdersCount',
                ],
                [
                    'label' => 'Approved',
                    'route' => 'purchases.index',
                    'icon' => 'check-circle',
                    'query' => ['status' => 'approved'],
                ],
            ],
        ],
        [
            'label' => 'Inventory',
            'route' => 'inventory.opname.index',
            'icon' => 'archive-box',
            'permission' => null,
            'children' => [
                [
                    'label' => 'Stock Opname',
                    'route' => 'inventory.opname.index',
                    'icon' => 'clipboard-document-list',
                    'badge' => 'getPendingStockOpnameCount',
                ],
                [
                    'label' => 'Stock Adjustment',
                    'route' => 'inventory.adjustments.index',
                    'icon' => 'adjustments-horizontal',
                    'badge' => 'getPendingStockAdjustmentCount',
                ],
                [
                    'label' => 'Unpacking',
                    'route' => 'inventory.unpacking.index',
                    'icon' => 'cube-transparent',
                    'badge' => 'getPendingUnpackingCount',
                ],
            ],
        ],
        [
            'label' => 'Customers',
            'route' => 'customers.index',
            'icon' => 'user-group',
            'permission' => null,
            'children' => [
                [
                    'label' => 'All Customers',
                    'route' => 'customers.index',
                    'icon' => 'list-bullet',
                ],
                [
                    'label' => 'Add New Customer',
                    'route' => 'customers.create',
                    'icon' => 'user-plus',
                ],
            ],
        ],
        [
            'label' => 'Reports',
            'route' => 'reports.index',
            'icon' => 'chart-bar',
            'permission' => null,
            'children' => [
                [
                    'label' => 'Sales Report',
                    'route' => 'reports.sales',
                    'icon' => 'banknotes',
                ],
                [
                    'label' => 'Inventory Report',
                    'route' => 'reports.inventory',
                    'icon' => 'cube',
                ],
                [
                    'label' => 'Financial Report',
                    'route' => 'reports.financial',
                    'icon' => 'chart-pie',
                ],
                [
                    'label' => 'Cashier Performance',
                    'route' => 'reports.cashier',
                    'icon' => 'user',
                ],
            ],
        ],
        [
            'label' => 'Settings',
            'route' => 'settings.index',
            'icon' => 'cog-6-tooth',
            'permission' => null,
        ],
    ],

    'Admin Toko' => [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'home',
            'permission' => null,
        ],
        [
            'label' => 'POS',
            'route' => 'pos.index',
            'icon' => 'computer-desktop',
            'permission' => null,
        ],
        [
            'label' => 'Transactions',
            'route' => 'pos.index',
            'icon' => 'receipt-percent',
            'permission' => null,
            'children' => [
                [
                    'label' => 'All Transactions',
                    'route' => 'pos.index',
                    'icon' => 'list-bullet',
                    'query' => ['view' => 'all'],
                ],
                [
                    'label' => 'Today\'s Transactions',
                    'route' => 'pos.index',
                    'icon' => 'calendar',
                    'query' => ['view' => 'today'],
                ],
                [
                    'label' => 'Void Requests',
                    'route' => 'pos.index',
                    'icon' => 'x-circle',
                    'query' => ['view' => 'void'],
                    'badge' => 'getPendingVoidRequestsCount',
                ],
            ],
        ],
        [
            'label' => 'Store Sessions',
            'route' => 'sessions.index',
            'icon' => 'clock',
            'permission' => null,
            'children' => [
                [
                    'label' => 'All Sessions',
                    'route' => 'sessions.index',
                    'icon' => 'list-bullet',
                ],
                [
                    'label' => 'Pending Approvals',
                    'route' => 'sessions.pendingApprovals',
                    'icon' => 'exclamation-circle',
                    'badge' => 'getPendingSessionApprovalsCount',
                ],
            ],
        ],
        [
            'label' => 'Staff Management',
            'route' => 'staff.index',
            'icon' => 'users',
            'permission' => null,
            'children' => [
                [
                    'label' => 'All Staff',
                    'route' => 'staff.index',
                    'icon' => 'list-bullet',
                ],
                [
                    'label' => 'Add New Staff',
                    'route' => 'staff.create',
                    'icon' => 'user-plus',
                ],
            ],
        ],
        [
            'label' => 'Products',
            'route' => 'products.index',
            'icon' => 'cube',
            'permission' => null,
            'children' => [
                [
                    'label' => 'All Products',
                    'route' => 'products.index',
                    'icon' => 'list-bullet',
                ],
                [
                    'label' => 'Add New Product',
                    'route' => 'products.create',
                    'icon' => 'plus-circle',
                ],
                [
                    'label' => 'Categories',
                    'route' => 'categories.index',
                    'icon' => 'tag',
                ],
                [
                    'label' => 'Low Stock',
                    'route' => 'products.index',
                    'icon' => 'exclamation-triangle',
                    'query' => ['stock' => 'low'],
                    'badge' => 'getLowStockCount',
                ],
            ],
        ],
        [
            'label' => 'Suppliers',
            'route' => 'suppliers.index',
            'icon' => 'truck',
            'permission' => null,
            'children' => [
                [
                    'label' => 'All Suppliers',
                    'route' => 'suppliers.index',
                    'icon' => 'list-bullet',
                ],
                [
                    'label' => 'Add New Supplier',
                    'route' => 'suppliers.create',
                    'icon' => 'plus-circle',
                ],
            ],
        ],
        [
            'label' => 'Purchase Orders',
            'route' => 'purchases.index',
            'icon' => 'shopping-cart',
            'permission' => null,
            'children' => [
                [
                    'label' => 'All Purchase Orders',
                    'route' => 'purchases.index',
                    'icon' => 'list-bullet',
                ],
                [
                    'label' => 'Create Purchase Order',
                    'route' => 'purchases.create',
                    'icon' => 'plus-circle',
                ],
                [
                    'label' => 'Pending Receipt',
                    'route' => 'purchases.index',
                    'icon' => 'clock',
                    'query' => ['status' => 'approved'],
                ],
            ],
        ],
        [
            'label' => 'Inventory',
            'route' => 'inventory.opname.index',
            'icon' => 'archive-box',
            'permission' => null,
            'children' => [
                [
                    'label' => 'Stock Opname',
                    'route' => 'inventory.opname.index',
                    'icon' => 'clipboard-document-list',
                ],
                [
                    'label' => 'Stock Adjustment',
                    'route' => 'inventory.adjustments.index',
                    'icon' => 'adjustments-horizontal',
                ],
                [
                    'label' => 'Unpacking',
                    'route' => 'inventory.unpacking.index',
                    'icon' => 'cube-transparent',
                ],
            ],
        ],
        [
            'label' => 'Customers',
            'route' => 'customers.index',
            'icon' => 'user-group',
            'permission' => null,
            'children' => [
                [
                    'label' => 'All Customers',
                    'route' => 'customers.index',
                    'icon' => 'list-bullet',
                ],
                [
                    'label' => 'Add New Customer',
                    'route' => 'customers.create',
                    'icon' => 'user-plus',
                ],
            ],
        ],
        [
            'label' => 'Cash Management',
            'route' => 'sessions.index',
            'icon' => 'banknotes',
            'permission' => null,
        ],
        [
            'label' => 'Reports',
            'route' => 'reports.index',
            'icon' => 'chart-bar',
            'permission' => null,
            'children' => [
                [
                    'label' => 'Sales Report',
                    'route' => 'reports.sales',
                    'icon' => 'banknotes',
                ],
                [
                    'label' => 'Inventory Report',
                    'route' => 'reports.inventory',
                    'icon' => 'cube',
                ],
                [
                    'label' => 'Cashier Performance',
                    'route' => 'reports.cashier',
                    'icon' => 'user',
                ],
            ],
        ],
        [
            'label' => 'Store Settings',
            'route' => 'stores.settings',
            'icon' => 'cog-6-tooth',
            'permission' => null,
        ],
    ],

    'Kasir' => [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'home',
            'permission' => null,
        ],
        [
            'label' => 'POS',
            'route' => 'pos.index',
            'icon' => 'computer-desktop',
            'permission' => null,
        ],
        [
            'label' => 'My Sessions',
            'route' => 'sessions.index',
            'icon' => 'clock',
            'permission' => null,
        ],
        [
            'label' => 'My Transactions',
            'route' => 'pos.index',
            'icon' => 'receipt-percent',
            'permission' => null,
            'query' => ['cashier' => 'me'],
        ],
        [
            'label' => 'Pending Transactions',
            'route' => 'pos.index',
            'icon' => 'pause-circle',
            'permission' => null,
            'query' => ['view' => 'pending'],
            'badge' => 'getMyPendingTransactionsCount',
        ],
        [
            'label' => 'Void Requests',
            'route' => 'pos.index',
            'icon' => 'x-circle',
            'permission' => null,
            'query' => ['view' => 'void', 'cashier' => 'me'],
        ],
        [
            'label' => 'Customers',
            'route' => 'customers.index',
            'icon' => 'user-group',
            'permission' => null,
            'children' => [
                [
                    'label' => 'Search Customer',
                    'route' => 'customers.searchByPhone',
                    'icon' => 'magnifying-glass',
                ],
                [
                    'label' => 'Add New Customer',
                    'route' => 'customers.create',
                    'icon' => 'user-plus',
                ],
            ],
        ],
        [
            'label' => 'Products',
            'route' => 'products.index',
            'icon' => 'cube',
            'permission' => null,
        ],
        [
            'label' => 'My Profile',
            'route' => 'profile.edit',
            'icon' => 'user',
            'permission' => null,
        ],
        [
            'label' => 'Change Password',
            'route' => 'profile.edit',
            'icon' => 'key',
            'permission' => null,
            'query' => ['tab' => 'password'],
        ],
        [
            'label' => 'My Activity Log',
            'route' => 'profile.edit',
            'icon' => 'document-text',
            'permission' => null,
            'query' => ['tab' => 'activity'],
        ],
    ],
];
