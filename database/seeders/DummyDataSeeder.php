<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Store;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StoreSetting;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting Dummy Data Seeding...');

        // 1. Create Super Admin
        $this->createSuperAdmin();

        // 2. Create Tenants
        $tenants = $this->createTenants();

        // 3. Create Stores for each tenant
        $stores = $this->createStores($tenants);

        // 4. Create Users
        $this->createUsers($tenants, $stores);

        // 5. Create Categories
        $categories = $this->createCategories($tenants);

        // 6. Create Products
        $products = $this->createProducts($tenants, $categories);

        // 7. Create Stocks
        $this->createStocks($products, $stores);

        // 8. Create Store Settings
        $this->createStoreSettings($stores);

        $this->command->info('✅ Dummy Data Seeding Completed!');
        $this->command->info('📊 Summary:');
        $this->command->info('  - 1 Super Admin');
        $this->command->info('  - 2 Tenants');
        $this->command->info('  - 6 Stores (3 per tenant)');
        $this->command->info('  - 24 Users + 1 Super Admin = 25 Users');
        $this->command->info('  - 20 Categories (10 per tenant)');
        $this->command->info('  - 100 Products (50 per tenant)');
        $this->command->info('  - Stock data for all stores');
        $this->command->info('  - Store settings for all stores');
    }

    private function createSuperAdmin(): void
    {
        $this->command->info('👤 Creating Super Admin...');

        $admin = User::create([
            'tenant_id' => null,
            'store_id' => null,
            'name' => 'Super Administrator',
            'email' => 'admin@kasir5.com',
            'password' => 'Admin@123',
            'phone' => '08123456789',
            'is_active' => true,
            'email_verified_at' => now(),
            'login_count' => 0,
        ]);

        $admin->assignRole('Administrator SaaS');

        $this->command->info('  ✓ Super Admin created: admin@kasir5.com / Admin@123');
    }

    private function createTenants(): array
    {
        $this->command->info('🏢 Creating Tenants...');

        $tenant1 = Tenant::create([
            'name' => 'ABC Retail Group',
            'slug' => 'abc-retail',
            'email' => 'owner@abcretail.com',
            'phone' => '02112345678',
            'is_active' => true,
            'activated_at' => now(),
            'subscription_status' => 'active',
            'subscription_ends_at' => now()->addYear(),
            'settings' => json_encode([
                'allow_multi_store' => true,
                'max_stores' => 10,
            ]),
        ]);

        $tenant2 = Tenant::create([
            'name' => 'XYZ Minimart',
            'slug' => 'xyz-minimart',
            'email' => 'owner@xyzmart.com',
            'phone' => '02187654321',
            'is_active' => true,
            'activated_at' => now(),
            'subscription_status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
            'settings' => json_encode([
                'allow_multi_store' => true,
                'max_stores' => 5,
            ]),
        ]);

        $this->command->info('  ✓ Tenant 1: ABC Retail Group (active)');
        $this->command->info('  ✓ Tenant 2: XYZ Minimart (trial)');

        return [$tenant1, $tenant2];
    }

    private function createStores(array $tenants): array
    {
        $this->command->info('🏪 Creating Stores...');

        $stores = [];

        // ABC Retail Stores
        $stores[] = Store::create([
            'tenant_id' => $tenants[0]->id,
            'name' => 'ABC Central Jakarta',
            'code' => 'ABC-JKT-001',
            'slug' => 'abc-central-jakarta',
            'address' => 'Jl. Sudirman No. 123',
            'city' => 'Jakarta Pusat',
            'province' => 'DKI Jakarta',
            'postal_code' => '10220',
            'phone' => '02112340001',
            'email' => 'jakarta@abcretail.com',
            'is_active' => true,
            'timezone' => 'Asia/Jakarta',
        ]);

        $stores[] = Store::create([
            'tenant_id' => $tenants[0]->id,
            'name' => 'ABC Bekasi',
            'code' => 'ABC-BKS-001',
            'slug' => 'abc-bekasi',
            'address' => 'Jl. Ahmad Yani No. 45',
            'city' => 'Bekasi',
            'province' => 'Jawa Barat',
            'postal_code' => '17141',
            'phone' => '02112340002',
            'email' => 'bekasi@abcretail.com',
            'is_active' => true,
            'timezone' => 'Asia/Jakarta',
        ]);

        $stores[] = Store::create([
            'tenant_id' => $tenants[0]->id,
            'name' => 'ABC Tangerang',
            'code' => 'ABC-TNG-001',
            'slug' => 'abc-tangerang',
            'address' => 'Jl. Gading Serpong Boulevard No. 78',
            'city' => 'Tangerang',
            'province' => 'Banten',
            'postal_code' => '15810',
            'phone' => '02112340003',
            'email' => 'tangerang@abcretail.com',
            'is_active' => true,
            'timezone' => 'Asia/Jakarta',
        ]);

        // XYZ Minimart Stores
        $stores[] = Store::create([
            'tenant_id' => $tenants[1]->id,
            'name' => 'XYZ Kelapa Gading',
            'code' => 'XYZ-KG-001',
            'slug' => 'xyz-kelapa-gading',
            'address' => 'Jl. Boulevard Raya Blok M No. 12',
            'city' => 'Jakarta Utara',
            'province' => 'DKI Jakarta',
            'postal_code' => '14240',
            'phone' => '02187650001',
            'email' => 'kelapagading@xyzmart.com',
            'is_active' => true,
            'timezone' => 'Asia/Jakarta',
        ]);

        $stores[] = Store::create([
            'tenant_id' => $tenants[1]->id,
            'name' => 'XYZ Pluit',
            'code' => 'XYZ-PLT-001',
            'slug' => 'xyz-pluit',
            'address' => 'Jl. Pluit Utara No. 34',
            'city' => 'Jakarta Utara',
            'province' => 'DKI Jakarta',
            'postal_code' => '14450',
            'phone' => '02187650002',
            'email' => 'pluit@xyzmart.com',
            'is_active' => true,
            'timezone' => 'Asia/Jakarta',
        ]);

        $stores[] = Store::create([
            'tenant_id' => $tenants[1]->id,
            'name' => 'XYZ Senayan',
            'code' => 'XYZ-SNY-001',
            'slug' => 'xyz-senayan',
            'address' => 'Jl. Asia Afrika No. 8',
            'city' => 'Jakarta Pusat',
            'province' => 'DKI Jakarta',
            'postal_code' => '10270',
            'phone' => '02187650003',
            'email' => 'senayan@xyzmart.com',
            'is_active' => true,
            'timezone' => 'Asia/Jakarta',
        ]);

        $this->command->info('  ✓ 6 Stores created');

        return $stores;
    }

    private function createUsers(array $tenants, array $stores): void
    {
        $this->command->info('👥 Creating Users...');

        // Tenant 1 (ABC Retail) - 12 users
        $tenant1Owner = User::create([
            'tenant_id' => $tenants[0]->id,
            'store_id' => null,
            'name' => 'John Doe',
            'email' => 'owner@abcretail.com',
            'password' => 'Owner@123',
            'phone' => '08111111111',
            'is_active' => true,
            'email_verified_at' => now(),
            'login_count' => 0,
        ]);
        $tenant1Owner->assignRole('Tenant Owner');

        // Admin Toko for ABC Central Jakarta
        $admin1 = User::create([
            'tenant_id' => $tenants[0]->id,
            'store_id' => $stores[0]->id,
            'name' => 'Ahmad Subagyo',
            'email' => 'admin.jakarta@abcretail.com',
            'password' => 'Admin@123',
            'phone' => '08121111111',
            'is_active' => true,
            'email_verified_at' => now(),
            'login_count' => 0,
        ]);
        $admin1->assignRole('Admin Toko');

        // Admin Toko for ABC Bekasi
        $admin2 = User::create([
            'tenant_id' => $tenants[0]->id,
            'store_id' => $stores[1]->id,
            'name' => 'Budi Santoso',
            'email' => 'admin.bekasi@abcretail.com',
            'password' => 'Admin@123',
            'phone' => '08122222222',
            'is_active' => true,
            'email_verified_at' => now(),
            'login_count' => 0,
        ]);
        $admin2->assignRole('Admin Toko');

        // 3 Kasir per store for ABC (9 kasir total)
        $kasirNames = [
            ['Siti Nurhaliza', 'Dewi Sartika', 'Fitri Handayani'],
            ['Rina Wijaya', 'Maya Kusuma', 'Lina Marlina'],
            ['Putri Ayu', 'Indah Permata', 'Sri Rahayu'],
        ];

        $kasirCounter = 1;
        foreach ([0, 1, 2] as $storeIndex) {
            foreach ($kasirNames[$storeIndex] as $kasirName) {
                $kasir = User::create([
                    'tenant_id' => $tenants[0]->id,
                    'store_id' => $stores[$storeIndex]->id,
                    'name' => $kasirName,
                    'email' => 'kasir' . $kasirCounter . '@abcretail.com',
                    'password' => 'Kasir@123',
                    'phone' => '0813' . str_pad($kasirCounter, 7, '0', STR_PAD_LEFT),
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'login_count' => 0,
                ]);
                $kasir->assignRole('Kasir');
                $kasirCounter++;
            }
        }

        // Tenant 2 (XYZ Minimart) - 12 users
        $tenant2Owner = User::create([
            'tenant_id' => $tenants[1]->id,
            'store_id' => null,
            'name' => 'Jane Smith',
            'email' => 'owner@xyzmart.com',
            'password' => 'Owner@123',
            'phone' => '08222222222',
            'is_active' => true,
            'email_verified_at' => now(),
            'login_count' => 0,
        ]);
        $tenant2Owner->assignRole('Tenant Owner');

        // Admin Toko for XYZ Kelapa Gading
        $admin3 = User::create([
            'tenant_id' => $tenants[1]->id,
            'store_id' => $stores[3]->id,
            'name' => 'Chandra Wijaya',
            'email' => 'admin.kelapagading@xyzmart.com',
            'password' => 'Admin@123',
            'phone' => '08233333333',
            'is_active' => true,
            'email_verified_at' => now(),
            'login_count' => 0,
        ]);
        $admin3->assignRole('Admin Toko');

        // Admin Toko for XYZ Pluit
        $admin4 = User::create([
            'tenant_id' => $tenants[1]->id,
            'store_id' => $stores[4]->id,
            'name' => 'Dedi Kurniawan',
            'email' => 'admin.pluit@xyzmart.com',
            'password' => 'Admin@123',
            'phone' => '08244444444',
            'is_active' => true,
            'email_verified_at' => now(),
            'login_count' => 0,
        ]);
        $admin4->assignRole('Admin Toko');

        // 3 Kasir per store for XYZ (9 kasir total)
        $kasirNames2 = [
            ['Eka Putri', 'Fina Agustina', 'Gita Savitri'],
            ['Hani Rahmawati', 'Ika Maulida', 'Jihan Fahira'],
            ['Kartika Sari', 'Laila Fitriani', 'Mira Andini'],
        ];

        $kasirCounter = 1;
        foreach ([3, 4, 5] as $storeIndex) {
            foreach ($kasirNames2[$storeIndex - 3] as $kasirName) {
                $kasir = User::create([
                    'tenant_id' => $tenants[1]->id,
                    'store_id' => $stores[$storeIndex]->id,
                    'name' => $kasirName,
                    'email' => 'kasir' . $kasirCounter . '@xyzmart.com',
                    'password' => 'Kasir@123',
                    'phone' => '0823' . str_pad($kasirCounter, 7, '0', STR_PAD_LEFT),
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'login_count' => 0,
                ]);
                $kasir->assignRole('Kasir');
                $kasirCounter++;
            }
        }

        $this->command->info('  ✓ 24 Users created (2 Owners, 4 Admin Toko, 18 Kasir)');
    }

    private function createCategories(array $tenants): array
    {
        $this->command->info('📂 Creating Categories...');

        $categories = [];

        $categoryNames = [
            'Beverages',
            'Snacks',
            'Groceries',
            'Personal Care',
            'Household',
            'Electronics',
            'Stationery',
            'Frozen Food',
            'Bakery',
            'Dairy',
        ];

        // Create for Tenant 1
        foreach ($categoryNames as $name) {
            $categories[] = Category::create([
                'tenant_id' => $tenants[0]->id,
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => 'Category for ' . $name,
                'parent_id' => null,
                'is_active' => true,
            ]);
        }

        // Create for Tenant 2
        foreach ($categoryNames as $name) {
            $categories[] = Category::create([
                'tenant_id' => $tenants[1]->id,
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => 'Category for ' . $name,
                'parent_id' => null,
                'is_active' => true,
            ]);
        }

        $this->command->info('  ✓ 20 Categories created (10 per tenant)');

        return $categories;
    }

    private function createProducts(array $tenants, array $categories): array
    {
        $this->command->info('📦 Creating Products...');

        $products = [];

        $productData = [
            // Beverages
            ['Coca Cola 330ml', 'pcs', 5000, 7000],
            ['Pepsi 330ml', 'pcs', 5000, 7000],
            ['Aqua 600ml', 'pcs', 2500, 3500],
            ['Teh Botol Sosro', 'pcs', 4000, 5500],
            ['Fanta Orange 330ml', 'pcs', 5000, 7000],
            // Snacks
            ['Chitato BBQ', 'pcs', 8000, 11000],
            ['Lays Classic', 'pcs', 9000, 12000],
            ['Oreo Original', 'pcs', 7000, 9500],
            ['Tango Wafer', 'pcs', 3500, 5000],
            ['Cheetos', 'pcs', 8500, 11500],
            // Groceries
            ['Beras Premium 5kg', 'kg', 55000, 75000],
            ['Minyak Goreng 1L', 'liter', 14000, 18000],
            ['Gula Pasir 1kg', 'kg', 12000, 15000],
            ['Tepung Terigu 1kg', 'kg', 10000, 13000],
            ['Kecap Manis ABC', 'pcs', 15000, 20000],
            // Personal Care
            ['Shampoo Pantene 170ml', 'pcs', 18000, 25000],
            ['Sabun Mandi Lifebuoy', 'pcs', 3500, 5000],
            ['Pasta Gigi Pepsodent', 'pcs', 9000, 12000],
            ['Tissue Paseo', 'pcs', 12000, 16000],
            ['Deodorant Rexona', 'pcs', 15000, 20000],
            // Household
            ['Detergen Rinso 800g', 'pcs', 18000, 24000],
            ['Sabun Cuci Piring Sunlight', 'pcs', 8000, 11000],
            ['Pembersih Lantai Wipol', 'pcs', 14000, 19000],
            ['Pewangi Pakaian Molto', 'pcs', 12000, 16000],
            ['Sapu Lidi', 'pcs', 15000, 20000],
            // Electronics
            ['Baterai AA Alkaline', 'pcs', 12000, 17000],
            ['Lampu LED 5W', 'pcs', 15000, 22000],
            ['Kabel USB Type-C', 'pcs', 25000, 35000],
            ['Earphone Standard', 'pcs', 35000, 50000],
            ['Powerbank 10000mAh', 'pcs', 120000, 175000],
            // Stationery
            ['Pulpen Standard', 'pcs', 2000, 3500],
            ['Buku Tulis 58 lembar', 'pcs', 4000, 6000],
            ['Pensil 2B', 'pcs', 2500, 4000],
            ['Penghapus Karet', 'pcs', 1500, 2500],
            ['Penggaris 30cm', 'pcs', 3000, 5000],
            // Frozen Food
            ['Nugget Ayam 500g', 'pcs', 25000, 35000],
            ['Sosis Sapi 500g', 'pcs', 30000, 42000],
            ['French Fries 1kg', 'kg', 22000, 32000],
            ['Es Krim Walls', 'pcs', 12000, 17000],
            ['Daging Burger Frozen', 'pcs', 35000, 48000],
            // Bakery
            ['Roti Tawar Sari Roti', 'pcs', 12000, 17000],
            ['Roti Sobek', 'pcs', 8000, 12000],
            ['Donat Mini', 'pcs', 15000, 22000],
            ['Kue Lapis', 'pcs', 18000, 25000],
            ['Brownies', 'pcs', 20000, 28000],
            // Dairy
            ['Susu Ultra 1L', 'pcs', 18000, 25000],
            ['Yogurt Cimory', 'pcs', 8000, 12000],
            ['Keju Kraft Singles', 'pcs', 35000, 48000],
            ['Butter Anchor 200g', 'pcs', 28000, 39000],
            ['Susu Kental Manis', 'pcs', 12000, 17000],
        ];

        // Create for Tenant 1 (first 50 products)
        $skuCounter = 1;
        foreach ($productData as $index => $data) {
            $categoryIndex = floor($index / 5); // 5 products per category
            $products[] = Product::create([
                'tenant_id' => $tenants[0]->id,
                'category_id' => $categories[$categoryIndex]->id,
                'name' => $data[0],
                'slug' => Str::slug($data[0]) . '-abc',
                'sku' => 'ABC-' . str_pad($skuCounter, 5, '0', STR_PAD_LEFT),
                'barcode' => '890' . str_pad($skuCounter, 10, '0', STR_PAD_LEFT),
                'description' => 'High quality ' . $data[0],
                'unit' => $data[1],
                'purchase_price' => $data[2],
                'selling_price' => $data[3],
                'min_stock' => 10,
                'max_stock' => 100,
                'is_active' => true,
            ]);
            $skuCounter++;
        }

        // Create for Tenant 2 (next 50 products)
        $skuCounter = 1;
        foreach ($productData as $index => $data) {
            $categoryIndex = floor($index / 5) + 10; // Categories 10-19 are for tenant 2
            $products[] = Product::create([
                'tenant_id' => $tenants[1]->id,
                'category_id' => $categories[$categoryIndex]->id,
                'name' => $data[0],
                'slug' => Str::slug($data[0]) . '-xyz',
                'sku' => 'XYZ-' . str_pad($skuCounter, 5, '0', STR_PAD_LEFT),
                'barcode' => '891' . str_pad($skuCounter, 10, '0', STR_PAD_LEFT),
                'description' => 'Premium ' . $data[0],
                'unit' => $data[1],
                'purchase_price' => $data[2],
                'selling_price' => $data[3],
                'min_stock' => 10,
                'max_stock' => 100,
                'is_active' => true,
            ]);
            $skuCounter++;
        }

        $this->command->info('  ✓ 100 Products created (50 per tenant)');

        return $products;
    }

    private function createStocks(array $products, array $stores): void
    {
        $this->command->info('📊 Creating Stock Data...');

        $stockCount = 0;
        foreach ($products as $product) {
            foreach ($stores as $store) {
                // Only create stock for products that belong to the same tenant as the store
                if ($product->tenant_id == $store->tenant_id) {
                    // Random quantity: some low stock, some normal, some overstock
                    $rand = rand(1, 100);
                    if ($rand <= 20) {
                        // 20% low stock (below min_stock)
                        $quantity = rand(1, 9);
                    } elseif ($rand <= 90) {
                        // 70% normal stock
                        $quantity = rand(10, 100);
                    } else {
                        // 10% overstock (above max_stock)
                        $quantity = rand(101, 150);
                    }

                    Stock::create([
                        'product_id' => $product->id,
                        'store_id' => $store->id,
                        'quantity' => $quantity,
                        'min_stock' => $product->min_stock,
                        'max_stock' => $product->max_stock,
                        'last_stock_opname_date' => now()->subDays(rand(1, 30)),
                    ]);
                    $stockCount++;
                }
            }
        }

        $this->command->info('  ✓ ' . $stockCount . ' Stock records created');
    }

    private function createStoreSettings(array $stores): void
    {
        $this->command->info('⚙️  Creating Store Settings...');

        $operatingHours = [
            'monday' => ['open' => '08:00', 'close' => '22:00', 'closed' => false],
            'tuesday' => ['open' => '08:00', 'close' => '22:00', 'closed' => false],
            'wednesday' => ['open' => '08:00', 'close' => '22:00', 'closed' => false],
            'thursday' => ['open' => '08:00', 'close' => '22:00', 'closed' => false],
            'friday' => ['open' => '08:00', 'close' => '22:00', 'closed' => false],
            'saturday' => ['open' => '08:00', 'close' => '23:00', 'closed' => false],
            'sunday' => ['open' => '08:00', 'close' => '23:00', 'closed' => false],
        ];

        foreach ($stores as $index => $store) {
            // Alternate settings for variety
            $taxEnabled = $index % 2 == 0;
            $roundingOptions = ['none', '100', '500', '1000'];
            $roundingRule = $roundingOptions[$index % 4];

            StoreSetting::create([
                'store_id' => $store->id,
                'operating_hours' => json_encode($operatingHours),
                'tax_enabled' => $taxEnabled,
                'tax_name' => 'PPN',  // Always set tax_name
                'tax_rate' => $taxEnabled ? 11.00 : 0,
                'tax_calculation' => $taxEnabled ? 'inclusive' : 'exclusive',
                'markup_percentage' => 30.00,
                'rounding_rule' => $roundingRule,
                'max_discount_per_item' => 10.00,
                'max_discount_per_transaction' => 15.00,
                'discount_requires_approval_above' => 5.00,
                'auto_print_receipt' => true,
            ]);
        }

        $this->command->info('  ✓ 6 Store Settings created');
    }
}
