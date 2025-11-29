<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Administrator SaaS - All permissions
        $adminSaasRole = Role::findByName('Administrator SaaS');
        $adminSaasRole->givePermissionTo(Permission::all());

        // Tenant Owner - Tenant-level permissions
        $tenantOwnerRole = Role::findByName('Tenant Owner');
        $tenantOwnerPermissions = [
            // Users
            'users.view.tenant',
            'users.create.tenant',
            'users.edit.tenant',
            'users.delete.tenant',
            'users.activate.tenant',

            // Stores
            'stores.view.tenant',
            'stores.create',
            'stores.edit.tenant',
            'stores.delete',
            'stores.activate',
            'stores.view-statistics',

            // Categories
            'categories.view.tenant',
            'categories.create',
            'categories.edit',
            'categories.delete',
            'categories.activate',
            'categories.bulk-import',
            'categories.bulk-delete',
            'categories.export',
            'categories.view-products',

            // Products
            'products.view.tenant',
            'products.create',
            'products.edit',
            'products.delete',
            'products.activate',
            'products.manage-images',
            'products.bulk-import',
            'products.bulk-price-update',
            'products.view-price-history',
            'products.export',

            // Inventory
            'inventory.view.tenant',
            'inventory.adjust.approve',
            'inventory.opname.approve',
            'inventory.transfer.approve',
            'inventory.unpacking.approve',
            'inventory.export',

            // Suppliers
            'suppliers.view.tenant',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.delete',
            'suppliers.activate',
            'suppliers.view-history',
            'suppliers.view-performance',
            'suppliers.export',

            // Purchase Orders
            'purchases.view.tenant',
            'purchases.approve',
            'purchases.reject',
            'purchases.export',

            // POS
            'pos.void-approve',

            // Sessions
            'sessions.view.tenant',
            'sessions.export',

            // Cash Management
            'cash.view-variance',
            'cash.view-trends',
            'cash.export',

            // Reports
            'reports.sales.view',
            'reports.inventory.view',
            'reports.financial.view',
            'reports.cashier.view',
            'reports.export.excel',
            'reports.export.pdf',
            'reports.export.csv',
            'reports.schedule',
            'reports.email',
            'reports.custom',

            // Dashboard
            'dashboard.view.tenant',

            // Settings
            'settings.notifications.manage',

            // Activity Logs
            'logs.view.tenant',
        ];
        $tenantOwnerRole->givePermissionTo($tenantOwnerPermissions);

        // Admin Toko - Store-level permissions
        $adminTokoRole = Role::findByName('Admin Toko');
        $adminTokoPermissions = [
            // Users
            'users.view.store',
            'users.create.store',
            'users.edit.store',
            'users.edit.own',
            'users.delete.store',
            'users.activate.store',
            'users.force-password-change',
            'users.send-activation',
            'users.logout-all-sessions',

            // Stores
            'stores.view.own',
            'stores.edit.own',
            'stores.manage-settings',
            'stores.view-statistics',
            'stores.transfer-stock',
            'stores.manage-registers',

            // Categories
            'categories.view.tenant',
            'categories.create',
            'categories.edit',
            'categories.delete',
            'categories.activate',
            'categories.export',
            'categories.view-products',

            // Products
            'products.view.tenant',
            'products.create',
            'products.edit',
            'products.activate',
            'products.manage-images',
            'products.bulk-price-update',
            'products.view-price-history',
            'products.override-store-price',
            'products.view-stock',
            'products.export',

            // Inventory
            'inventory.view.store',
            'inventory.adjust.create',
            'inventory.adjust.submit',
            'inventory.opname.create',
            'inventory.opname.submit',
            'inventory.transfer.create',
            'inventory.unpacking.create',
            'inventory.view-movements',
            'inventory.view-alerts',
            'inventory.export',

            // Suppliers
            'suppliers.view.tenant',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.view-history',

            // Purchase Orders
            'purchases.view.store',
            'purchases.create',
            'purchases.edit',
            'purchases.submit',
            'purchases.receive',
            'purchases.print',
            'purchases.export',

            // POS
            'pos.access',
            'pos.create-transaction',
            'pos.apply-discount-manager',
            'pos.hold-transaction',
            'pos.resume-transaction',
            'pos.void-approve',
            'pos.refund',
            'pos.view-history',
            'pos.reprint-receipt',
            'pos.email-receipt',

            // Sessions
            'sessions.view.store',
            'sessions.approve-close',
            'sessions.print-report',
            'sessions.export',

            // Cash Management
            'cash.reconcile',
            'cash.view-variance',
            'cash.manage-registers',
            'cash.assign-cashiers',
            'cash.create-deposit',
            'cash.export',

            // Customers
            'customers.view',
            'customers.create',
            'customers.edit',
            'customers.delete',
            'customers.view-history',
            'customers.manage-loyalty',
            'customers.export',

            // Reports
            'reports.sales.view',
            'reports.inventory.view',
            'reports.cashier.view',
            'reports.export.excel',
            'reports.export.pdf',
            'reports.export.csv',
            'reports.email',

            // Dashboard
            'dashboard.view.store',

            // Settings
            'settings.store.view',
            'settings.store.edit',
            'settings.notifications.manage',

            // Activity Logs
            'logs.view.store',
        ];
        $adminTokoRole->givePermissionTo($adminTokoPermissions);

        // Kasir - Own-level permissions only
        $kasirRole = Role::findByName('Kasir');
        $kasirPermissions = [
            // Users
            'users.view.own',
            'users.edit.own',

            // Products
            'products.view-stock',

            // POS
            'pos.access',
            'pos.create-transaction',
            'pos.apply-discount',
            'pos.hold-transaction',
            'pos.resume-transaction',
            'pos.void-request',
            'pos.view-history',
            'pos.reprint-receipt',
            'pos.email-receipt',

            // Sessions
            'sessions.open',
            'sessions.close',
            'sessions.view.own',
            'sessions.print-report',

            // Customers
            'customers.view',
            'customers.create',
            'customers.view-history',

            // Reports
            'reports.sales.view',
            'reports.export.excel',
            'reports.export.pdf',
            'reports.export.csv',

            // Dashboard
            'dashboard.view.cashier',

            // Activity Logs
            'logs.view.own',
        ];
        $kasirRole->givePermissionTo($kasirPermissions);

        $this->command->info('✓ Role-Permission mappings completed');
        $this->command->info('  - Administrator SaaS: ' . $adminSaasRole->permissions->count() . ' permissions');
        $this->command->info('  - Tenant Owner: ' . $tenantOwnerRole->permissions->count() . ' permissions');
        $this->command->info('  - Admin Toko: ' . $adminTokoRole->permissions->count() . ' permissions');
        $this->command->info('  - Kasir: ' . $kasirRole->permissions->count() . ' permissions');
    }
}
