<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Module: Users (16 permissions)
            'users.view.all',
            'users.view.tenant',
            'users.view.store',
            'users.view.own',
            'users.create.tenant',
            'users.create.store',
            'users.edit.tenant',
            'users.edit.store',
            'users.edit.own',
            'users.delete.tenant',
            'users.delete.store',
            'users.activate.tenant',
            'users.activate.store',
            'users.force-password-change',
            'users.send-activation',
            'users.logout-all-sessions',

            // Module: Tenants (6 permissions)
            'tenants.view.all',
            'tenants.create',
            'tenants.edit',
            'tenants.delete',
            'tenants.activate',
            'tenants.manage-subscriptions',

            // Module: Stores (12 permissions)
            'stores.view.all',
            'stores.view.tenant',
            'stores.view.own',
            'stores.create',
            'stores.edit.tenant',
            'stores.edit.own',
            'stores.delete',
            'stores.activate',
            'stores.manage-settings',
            'stores.view-statistics',
            'stores.transfer-stock',
            'stores.manage-registers',

            // Module: Categories (9 permissions)
            'categories.view.tenant',
            'categories.create',
            'categories.edit',
            'categories.delete',
            'categories.activate',
            'categories.bulk-import',
            'categories.bulk-delete',
            'categories.export',
            'categories.view-products',

            // Module: Products (12 permissions)
            'products.view.tenant',
            'products.create',
            'products.edit',
            'products.delete',
            'products.activate',
            'products.manage-images',
            'products.bulk-import',
            'products.bulk-price-update',
            'products.view-price-history',
            'products.override-store-price',
            'products.view-stock',
            'products.export',

            // Module: Inventory (15 permissions)
            'inventory.view.tenant',
            'inventory.view.store',
            'inventory.adjust.create',
            'inventory.adjust.submit',
            'inventory.adjust.approve',
            'inventory.opname.create',
            'inventory.opname.submit',
            'inventory.opname.approve',
            'inventory.transfer.create',
            'inventory.transfer.approve',
            'inventory.unpacking.create',
            'inventory.unpacking.approve',
            'inventory.view-movements',
            'inventory.view-alerts',
            'inventory.export',

            // Module: Suppliers (8 permissions)
            'suppliers.view.tenant',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.delete',
            'suppliers.activate',
            'suppliers.view-history',
            'suppliers.view-performance',
            'suppliers.export',

            // Module: Purchase Orders (10 permissions)
            'purchases.view.tenant',
            'purchases.view.store',
            'purchases.create',
            'purchases.edit',
            'purchases.submit',
            'purchases.approve',
            'purchases.reject',
            'purchases.receive',
            'purchases.print',
            'purchases.export',

            // Module: POS Transactions (12 permissions)
            'pos.access',
            'pos.create-transaction',
            'pos.apply-discount',
            'pos.apply-discount-manager',
            'pos.hold-transaction',
            'pos.resume-transaction',
            'pos.void-request',
            'pos.void-approve',
            'pos.refund',
            'pos.view-history',
            'pos.reprint-receipt',
            'pos.email-receipt',

            // Module: Store Sessions (8 permissions)
            'sessions.open',
            'sessions.close',
            'sessions.view.own',
            'sessions.view.store',
            'sessions.view.tenant',
            'sessions.approve-close',
            'sessions.print-report',
            'sessions.export',

            // Module: Cash Management (7 permissions)
            'cash.reconcile',
            'cash.view-variance',
            'cash.manage-registers',
            'cash.assign-cashiers',
            'cash.create-deposit',
            'cash.view-trends',
            'cash.export',

            // Module: Customers (7 permissions)
            'customers.view',
            'customers.create',
            'customers.edit',
            'customers.delete',
            'customers.view-history',
            'customers.manage-loyalty',
            'customers.export',

            // Module: Reports (12 permissions)
            'reports.sales.view',
            'reports.inventory.view',
            'reports.financial.view',
            'reports.cashier.view',
            'reports.system.view',
            'reports.subscription.view',
            'reports.export.excel',
            'reports.export.pdf',
            'reports.export.csv',
            'reports.schedule',
            'reports.email',
            'reports.custom',

            // Module: Dashboard (4 permissions)
            'dashboard.view.admin',
            'dashboard.view.tenant',
            'dashboard.view.store',
            'dashboard.view.cashier',

            // Module: Roles & Permissions (5 permissions)
            'roles.view.all',
            'roles.create',
            'roles.edit',
            'roles.delete',
            'permissions.assign',

            // Module: Settings (8 permissions)
            'settings.system.view',
            'settings.system.edit',
            'settings.store.view',
            'settings.store.edit',
            'settings.backup.create',
            'settings.backup.restore',
            'settings.backup.download',
            'settings.notifications.manage',

            // Module: Activity Logs (4 permissions)
            'logs.view.all',
            'logs.view.tenant',
            'logs.view.store',
            'logs.view.own',

            // Module: Subscriptions (5 permissions)
            'subscriptions.view.all',
            'subscriptions.create',
            'subscriptions.edit',
            'subscriptions.approve',
            'subscriptions.billing',
        ];

        $permissionCount = 0;
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
            $permissionCount++;
        }

        $this->command->info("✓ {$permissionCount} Permissions created successfully");
    }
}
