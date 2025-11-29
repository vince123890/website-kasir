<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $roles = [
            'Administrator SaaS', // Super admin dengan akses penuh ke semua tenant dan pengaturan sistem
            'Tenant Owner',       // Pemilik tenant dengan akses ke semua toko dalam tenant
            'Admin Toko',         // Administrator toko dengan akses penuh ke toko tertentu
            'Kasir',              // Kasir dengan akses terbatas ke POS dan transaksi
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }

        $this->command->info('✓ 4 Roles created successfully');
    }
}
