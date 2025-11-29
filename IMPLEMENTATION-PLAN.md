# IMPLEMENTATION PLAN - KASIR-5 POS SYSTEM (COMPLETE)
> Master Plan untuk Development Sistematis - FULL COVERAGE
> Target: Aplikasi POS Multi-Tenant Production-Ready
> Developer: Claude Code (AI Assistant)
> Project Manager: Vincent Edy Hartono
> Version: 2.0 (Complete)

---

## 🎯 TUJUAN PROJECT

Membuat **Aplikasi POS Multi-Tenant Production-Ready** dengan Laravel yang mencakup:
- ✅ Semua 132 sub-menus dari 4 roles
- ✅ 25 Major Modules (full CRUD + business logic)
- ✅ 225+ Detail Features (UI/UX, business rules, security)
- ✅ UI/UX yang smooth dengan Blade + Tailwind + Alpine.js
- ✅ Role-based access control (84+ permissions)
- ✅ Multi-tenant security & isolation
- ✅ Siap deploy ke Hostinger Shared Hosting

---

## 📊 COVERAGE ANALYSIS

### Fitur yang Akan Diimplementasikan:
- ✅ **25 Major Modules** (Infrastructure & CRUD)
- ✅ **40 Advanced Features** (Business logic)
- ✅ **60 UI/UX Components** (Forms, tables, modals, charts)
- ✅ **30 Technical Infrastructure** (Middleware, scopes, security)
- ✅ **20 Database Details** (Specific fields & relationships)
- ✅ **30 Business Rules** (Auto-numbering, thresholds, validations)
- ✅ **20 Integration Points** (Email, notifications, exports)

**Total: 225+ Features**

---

## 📋 STRATEGY DEVELOPMENT

### Pendekatan: **PROGRESSIVE IMPLEMENTATION**
Development dibagi dalam 4 tier berdasarkan prioritas:

**TIER 1 - CRITICAL (Phase 0-5):** Foundation + Core CRUD + Security
**TIER 2 - HIGH PRIORITY (Phase 6-11):** Inventory + POS + Approvals
**TIER 3 - MEDIUM PRIORITY (Phase 12-17):** Reports + Advanced Features
**TIER 4 - POLISH (Phase 18-20):** UI/UX Enhancement + Testing + Deployment

---

## 🗓️ IMPLEMENTATION PHASES

---

## ═══════════════════════════════════════════════════════
## TIER 1: CRITICAL FOUNDATION (Phase 0-5)
## ═══════════════════════════════════════════════════════

### **PHASE 0: PERSIAPAN & SETUP** (Hari 1-2)
**Status:** ✅ COMPLETED
**Estimasi:** 6-8 jam
**Priority:** CRITICAL

#### Checklist:
- [x] **Laravel Installation**
  - [x] Install Laravel 11 via Composer
  - [x] Verify PHP 8.2+ installed
  - [x] Setup database (MySQL 8.0+)
  - [x] Configure .env file
  - [x] Test `php artisan serve`

- [x] **Dependencies Installation**
  - [x] `composer require spatie/laravel-permission`
  - [x] `composer require laravel/breeze --dev`
  - [x] `php artisan breeze:install blade`
  - [x] `npm install && npm run build`
  - [ ] Install Alpine.js (via CDN in layout)
  - [ ] Install Chart.js (for dashboards)

- [x] **Git Setup**
  - [x] `git init`
  - [x] Create .gitignore (Laravel default)
  - [x] Initial commit
  - [x] Create repository on GitHub/GitLab

- [x] **Environment Configuration**
  - [x] APP_NAME="KASIR-5"
  - [x] APP_ENV=local
  - [x] APP_DEBUG=true
  - [x] Database credentials
  - [ ] Mail configuration (SMTP)
  - [x] Timezone: Asia/Jakarta

**Output:**
- ✅ Laravel berjalan di http://localhost
- ✅ Database terkoneksi
- ✅ Authentication (Breeze) installed

**Validation:**
- Visit http://localhost → Welcome page
- Visit /login → Login page
- Run `php artisan migrate` → Success

---

### **PHASE 1: DATABASE ARCHITECTURE** (Hari 2-3)
**Status:** ✅ COMPLETED
**Estimasi:** 8-10 jam
**Priority:** CRITICAL

#### Checklist:

- [x] **Folder Structure**
  - [x] Create `app/Repositories/`
  - [x] Create `app/Repositories/Contracts/`
  - [x] Create `app/Services/`
  - [x] Create `app/Http/Controllers/Base/`
  - [x] Create `app/Traits/`
  - [x] Create `app/Helpers/`

- [x] **Core Migrations**
  - [x] **tenants** table
    ```php
    - id, name, slug (unique), email, phone
    - is_active, activated_at, deactivated_at
    - subscription_status (trial/active/expired/cancelled)
    - trial_ends_at, subscription_ends_at
    - settings (JSON)
    - timestamps, soft deletes
    ```

  - [x] **stores** table
    ```php
    - id, tenant_id (FK)
    - name, code (unique per tenant), slug
    - address, city, province, postal_code
    - phone, email
    - is_active, timezone
    - logo_path
    - timestamps, soft deletes
    ```

  - [x] **users** table (extend Laravel default)
    ```php
    - id, tenant_id (FK, nullable for Super Admin)
    - store_id (FK, nullable)
    - name, email (unique), email_verified_at
    - password, remember_token
    - phone, avatar_path
    - is_active
    - activation_code (6 digits)
    - activation_code_expires_at
    - must_change_password (boolean)
    - password_expires_at
    - last_login_at, login_count
    - last_login_ip, last_login_device
    - timestamps, soft deletes
    ```

  - [x] **categories** table
    ```php
    - id, tenant_id (FK)
    - name, slug
    - description (nullable)
    - parent_id (FK, nullable - for sub-categories)
    - is_active
    - timestamps, soft deletes
    ```

  - [x] **products** table
    ```php
    - id, tenant_id (FK)
    - category_id (FK)
    - name, slug
    - sku (unique per tenant)
    - barcode (nullable, for POS scanning)
    - description (text, nullable)
    - unit (pcs/box/kg/liter/etc)
    - purchase_price, selling_price
    - min_stock, max_stock
    - image_path (nullable)
    - is_active
    - timestamps, soft deletes
    ```

  - [x] **stocks** table ⭐ CRITICAL
    ```php
    - id, product_id (FK), store_id (FK)
    - quantity (current stock)
    - min_stock (store-specific override)
    - max_stock (store-specific override)
    - last_stock_opname_date
    - timestamps
    - Unique: (product_id, store_id)
    ```

  - [x] **product_store_prices** table
    ```php
    - id, product_id (FK), store_id (FK)
    - price (store-specific override)
    - is_active
    - timestamps
    - Unique: (product_id, store_id)
    ```

  - [x] **price_histories** table
    ```php
    - id, product_id (FK)
    - store_id (FK, nullable - null = tenant level)
    - old_price, new_price
    - changed_by_user_id (FK)
    - changed_at
    ```

  - [x] **stock_movements** table
    ```php
    - id, product_id (FK), store_id (FK)
    - type (IN/OUT/ADJ/OPNAME/TRANSFER)
    - quantity (+ or -)
    - reference_type (PurchaseOrder/Transaction/Adjustment/etc)
    - reference_id
    - notes
    - created_by_user_id (FK)
    - timestamps
    ```

- [x] **Spatie Permission Tables**
  - [x] Run: `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`
  - [x] Run: `php artisan migrate`
  - [x] Tables: roles, permissions, model_has_roles, model_has_permissions, role_has_permissions

- [x] **Store Settings Table**
  - [x] **store_settings** table
    ```php
    - id, store_id (FK, unique)
    - operating_hours (JSON)
    - tax_enabled (boolean)
    - tax_name (VAT/PPN/Tax)
    - tax_rate (decimal)
    - tax_calculation (inclusive/exclusive)
    - markup_percentage (decimal)
    - rounding_rule (none/100/500/1000)
    - max_discount_per_item (decimal)
    - max_discount_per_transaction (decimal)
    - discount_requires_approval_above (decimal)
    - auto_print_receipt (boolean)
    - timestamps
    ```

**Output:**
- ✅ All database tables created
- ✅ Foreign keys & indexes setup
- ✅ Database schema complete

**Validation:**
- Run `php artisan migrate:status` → All migrations done
- Check database → All tables exist

---

### **PHASE 2: MODELS & RELATIONSHIPS** (Hari 3-4)
**Status:** ✅ COMPLETED
**Estimasi:** 6-8 jam
**Priority:** CRITICAL

#### Checklist:

- [x] **Base Classes**
  - [x] **app/Http/Controllers/Base/BaseController.php**
    ```php
    - CRUD methods: index, create, store, show, edit, update, destroy
    - Flash messages helper
    - Permission checking helper
    ```

  - [x] **app/Repositories/Contracts/BaseRepositoryInterface.php**
    ```php
    - Interface: all, find, create, update, delete, restore
    - Scope methods: byTenant, byStore, active
    ```

  - [x] **app/Services/BaseService.php**
    ```php
    - Abstract class with common business logic
    - Transaction handling
    - Event dispatching
    ```

- [x] **Models with Relationships**
  - [x] **Tenant.php**
    ```php
    - HasMany: stores, users, products, categories
    - SoftDeletes trait
    - Casts: settings => array
    - Accessors: isActive, isTrialActive
    - Scopes: active, trial, subscribed
    ```

  - [x] **Store.php**
    ```php
    - BelongsTo: tenant
    - HasMany: users, stocks, transactions, sessions
    - HasOne: storeSettings
    - SoftDeletes trait
    - Global scope: TenantScope
    - Accessors: fullAddress
    ```

  - [x] **User.php**
    ```php
    - BelongsTo: tenant, store
    - HasMany: transactions, activities, stockMovements
    - Spatie traits: HasRoles, HasPermissions
    - SoftDeletes trait
    - Hidden: password, activation_code
    - Casts: last_login_at => datetime
    - Mutators: password (auto-hash)
    - Scopes: active, byTenant, byStore
    ```

  - [x] **Category.php**
    ```php
    - BelongsTo: tenant, parent (self)
    - HasMany: products, children (self)
    - SoftDeletes trait
    - Global scope: TenantScope
    ```

  - [x] **Product.php**
    ```php
    - BelongsTo: tenant, category
    - HasMany: stocks, priceHistories, transactionItems
    - HasMany: storeSpecificPrices (product_store_prices)
    - SoftDeletes trait
    - Global scope: TenantScope
    - Accessors: stockStatus, totalStockValue
    - Methods: getStockByStore($storeId), getPriceByStore($storeId)
    ```

  - [x] **Stock.php**
    ```php
    - BelongsTo: product, store
    - HasMany: stockMovements
    - Accessors: isLowStock, isOverstock
    - Scopes: lowStock, overstock
    ```

  - [x] **StockMovement.php**
    ```php
    - BelongsTo: product, store, createdBy (User)
    - MorphTo: reference (polymorphic)
    - Casts: created_at => datetime
    ```

**Output:**
- ✅ All models created with relationships
- ✅ Base classes ready for reuse
- ✅ Global scopes configured

**Validation:**
- Test relationship: `Tenant::first()->stores`
- Test scope: `User::active()->get()`

---

### **PHASE 3: AUTHENTICATION & AUTHORIZATION** (Hari 4-5)
**Status:** ✅ COMPLETED
**Estimasi:** 8-10 jam
**Priority:** CRITICAL

#### Checklist:

- [x] **Customize Breeze Authentication**
  - [ ] Modify registration form (add tenant_id, store_id)
  - [ ] Update RegisteredUserController
  - [x] Update login to track last_login_at, IP, device
  - [ ] Update LoginRequest validation

- [x] **Seeders - Roles (4 roles)**
  - [x] **RoleSeeder.php**
    ```php
    - Role: "Administrator SaaS" (guard: web)
    - Role: "Tenant Owner" (guard: web)
    - Role: "Admin Toko" (guard: web)
    - Role: "Kasir" (guard: web)
    ```

- [x] **Seeders - Permissions (160 permissions)**
  - [x] **PermissionSeeder.php**

    **Module: Users (16 permissions)**
    - users.view.all (Super Admin only)
    - users.view.tenant (Tenant Owner)
    - users.view.store (Admin Toko)
    - users.view.own (Kasir - profile)
    - users.create.tenant (Tenant Owner)
    - users.create.store (Admin Toko)
    - users.edit.tenant (Tenant Owner)
    - users.edit.store (Admin Toko)
    - users.edit.own (Kasir - profile)
    - users.delete.tenant (Tenant Owner)
    - users.delete.store (Admin Toko)
    - users.activate.tenant (Tenant Owner)
    - users.activate.store (Admin Toko)
    - users.force-password-change (Admin Toko)
    - users.send-activation (Admin Toko)
    - users.logout-all-sessions (Admin Toko)

    **Module: Tenants (6 permissions)**
    - tenants.view.all (Super Admin)
    - tenants.create (Super Admin)
    - tenants.edit (Super Admin)
    - tenants.delete (Super Admin)
    - tenants.activate (Super Admin)
    - tenants.manage-subscriptions (Super Admin)

    **Module: Stores (12 permissions)**
    - stores.view.all (Super Admin)
    - stores.view.tenant (Tenant Owner)
    - stores.view.own (Admin Toko)
    - stores.create (Tenant Owner)
    - stores.edit.tenant (Tenant Owner)
    - stores.edit.own (Admin Toko - settings)
    - stores.delete (Tenant Owner)
    - stores.activate (Tenant Owner)
    - stores.manage-settings (Admin Toko)
    - stores.view-statistics (Tenant Owner, Admin Toko)
    - stores.transfer-stock (Admin Toko)
    - stores.manage-registers (Admin Toko)

    **Module: Categories (9 permissions)**
    - categories.view.tenant (Tenant Owner, Admin Toko)
    - categories.create (Tenant Owner, Admin Toko)
    - categories.edit (Tenant Owner, Admin Toko)
    - categories.delete (Tenant Owner, Admin Toko)
    - categories.activate (Tenant Owner, Admin Toko)
    - categories.bulk-import (Tenant Owner)
    - categories.bulk-delete (Tenant Owner)
    - categories.export (Tenant Owner, Admin Toko)
    - categories.view-products (All)

    **Module: Products (12 permissions)**
    - products.view.tenant (Tenant Owner, Admin Toko)
    - products.create (Tenant Owner, Admin Toko)
    - products.edit (Tenant Owner, Admin Toko)
    - products.delete (Tenant Owner)
    - products.activate (Tenant Owner, Admin Toko)
    - products.manage-images (Admin Toko)
    - products.bulk-import (Tenant Owner)
    - products.bulk-price-update (Tenant Owner, Admin Toko)
    - products.view-price-history (Tenant Owner, Admin Toko)
    - products.override-store-price (Admin Toko)
    - products.view-stock (Admin Toko, Kasir)
    - products.export (Tenant Owner, Admin Toko)

    **Module: Inventory (15 permissions)**
    - inventory.view.tenant (Tenant Owner)
    - inventory.view.store (Admin Toko)
    - inventory.adjust.create (Admin Toko)
    - inventory.adjust.submit (Admin Toko)
    - inventory.adjust.approve (Tenant Owner)
    - inventory.opname.create (Admin Toko)
    - inventory.opname.submit (Admin Toko)
    - inventory.opname.approve (Tenant Owner)
    - inventory.transfer.create (Admin Toko)
    - inventory.transfer.approve (Tenant Owner)
    - inventory.unpacking.create (Admin Toko)
    - inventory.unpacking.approve (Tenant Owner)
    - inventory.view-movements (Admin Toko)
    - inventory.view-alerts (Admin Toko)
    - inventory.export (Tenant Owner, Admin Toko)

    **Module: Suppliers (8 permissions)**
    - suppliers.view.tenant (Tenant Owner, Admin Toko)
    - suppliers.create (Tenant Owner, Admin Toko)
    - suppliers.edit (Tenant Owner, Admin Toko)
    - suppliers.delete (Tenant Owner)
    - suppliers.activate (Tenant Owner, Admin Toko)
    - suppliers.view-history (Tenant Owner, Admin Toko)
    - suppliers.view-performance (Tenant Owner)
    - suppliers.export (Tenant Owner)

    **Module: Purchase Orders (10 permissions)**
    - purchases.view.tenant (Tenant Owner)
    - purchases.view.store (Admin Toko)
    - purchases.create (Admin Toko)
    - purchases.edit (Admin Toko - draft only)
    - purchases.submit (Admin Toko)
    - purchases.approve (Tenant Owner)
    - purchases.reject (Tenant Owner)
    - purchases.receive (Admin Toko)
    - purchases.print (Admin Toko)
    - purchases.export (Tenant Owner, Admin Toko)

    **Module: POS Transactions (12 permissions)**
    - pos.access (Kasir, Admin Toko)
    - pos.create-transaction (Kasir)
    - pos.apply-discount (Kasir - limited)
    - pos.apply-discount-manager (Admin Toko - unlimited)
    - pos.hold-transaction (Kasir)
    - pos.resume-transaction (Kasir)
    - pos.void-request (Kasir)
    - pos.void-approve (Admin Toko, Tenant Owner)
    - pos.refund (Admin Toko)
    - pos.view-history (Kasir - own, Admin Toko - all)
    - pos.reprint-receipt (Kasir, Admin Toko)
    - pos.email-receipt (Kasir)

    **Module: Store Sessions (8 permissions)**
    - sessions.open (Kasir)
    - sessions.close (Kasir)
    - sessions.view.own (Kasir)
    - sessions.view.store (Admin Toko)
    - sessions.view.tenant (Tenant Owner)
    - sessions.approve-close (Admin Toko)
    - sessions.print-report (Kasir, Admin Toko)
    - sessions.export (Admin Toko)

    **Module: Cash Management (7 permissions)**
    - cash.reconcile (Admin Toko)
    - cash.view-variance (Admin Toko, Tenant Owner)
    - cash.manage-registers (Admin Toko)
    - cash.assign-cashiers (Admin Toko)
    - cash.create-deposit (Admin Toko)
    - cash.view-trends (Tenant Owner)
    - cash.export (Tenant Owner, Admin Toko)

    **Module: Customers (7 permissions)**
    - customers.view (Kasir, Admin Toko)
    - customers.create (Kasir)
    - customers.edit (Admin Toko)
    - customers.delete (Admin Toko)
    - customers.view-history (Kasir, Admin Toko)
    - customers.manage-loyalty (Admin Toko)
    - customers.export (Admin Toko)

    **Module: Reports (12 permissions)**
    - reports.sales.view (All roles)
    - reports.inventory.view (Tenant Owner, Admin Toko)
    - reports.financial.view (Tenant Owner)
    - reports.cashier.view (Admin Toko, Tenant Owner)
    - reports.system.view (Super Admin)
    - reports.subscription.view (Super Admin)
    - reports.export.excel (All roles)
    - reports.export.pdf (All roles)
    - reports.export.csv (All roles)
    - reports.schedule (Tenant Owner)
    - reports.email (Tenant Owner, Admin Toko)
    - reports.custom (Tenant Owner)

    **Module: Dashboard (4 permissions)**
    - dashboard.view.admin (Super Admin)
    - dashboard.view.tenant (Tenant Owner)
    - dashboard.view.store (Admin Toko)
    - dashboard.view.cashier (Kasir)

    **Module: Roles & Permissions (5 permissions)**
    - roles.view.all (Super Admin)
    - roles.create (Super Admin)
    - roles.edit (Super Admin)
    - roles.delete (Super Admin)
    - permissions.assign (Super Admin)

    **Module: Settings (8 permissions)**
    - settings.system.view (Super Admin)
    - settings.system.edit (Super Admin)
    - settings.store.view (Admin Toko)
    - settings.store.edit (Admin Toko)
    - settings.backup.create (Super Admin)
    - settings.backup.restore (Super Admin)
    - settings.backup.download (Super Admin)
    - settings.notifications.manage (Tenant Owner, Admin Toko)

    **Module: Activity Logs (4 permissions)**
    - logs.view.all (Super Admin)
    - logs.view.tenant (Tenant Owner)
    - logs.view.store (Admin Toko)
    - logs.view.own (Kasir)

    **Module: Subscriptions (5 permissions)**
    - subscriptions.view.all (Super Admin)
    - subscriptions.create (Super Admin)
    - subscriptions.edit (Super Admin)
    - subscriptions.approve (Super Admin)
    - subscriptions.billing (Super Admin)

- [x] **RolePermissionSeeder.php**
  - [x] Assign all permissions to roles
  - [x] Super Admin → All 160 permissions
  - [x] Tenant Owner → 67 Tenant-level permissions
  - [x] Admin Toko → 92 Store-level permissions
  - [x] Kasir → 25 Own-level permissions only

- [x] **Middleware**
  - [x] **app/Http/Middleware/TenantMiddleware.php**
    ```php
    - Check user has tenant_id
    - Set global scope for tenant
    - Abort 403 if accessing other tenant
    ```

  - [x] **app/Http/Middleware/StoreMiddleware.php**
    ```php
    - Check user has store_id (for store-level routes)
    - Set global scope for store
    - Abort 403 if accessing other store
    ```

  - [x] Register middleware in `bootstrap/app.php`

- [x] **Global Scopes**
  - [x] **app/Models/Scopes/TenantScope.php**
    ```php
    - Auto-filter by auth()->user()->tenant_id
    - Apply to: Store, User, Category, Product, etc.
    ```

  - [x] **app/Models/Scopes/StoreScope.php**
    ```php
    - Auto-filter by auth()->user()->store_id
    - Apply to: Stock, Transaction, Session, etc.
    ```

  - [x] Boot scopes in respective models (Store, User, Category, Product, Stock)

**Output:**
- ✅ 4 Roles seeded (Administrator SaaS, Tenant Owner, Admin Toko, Kasir)
- ✅ 160 Permissions seeded across 16 modules
- ✅ Role-Permission mappings complete
- ✅ Middleware & scopes functional
- ✅ Login tracking implemented

**Validation:**
- Run: `php artisan db:seed --class=RoleSeeder`
- Run: `php artisan db:seed --class=PermissionSeeder`
- Run: `php artisan db:seed --class=RolePermissionSeeder`
- Test: `auth()->user()->hasPermissionTo('users.view.all')`

---

### **PHASE 4: DUMMY DATA SEEDING** (Hari 5)
**Status:** ✅ COMPLETED
**Estimasi:** 4-6 jam
**Priority:** CRITICAL

#### Checklist:

- [x] **DummyDataSeeder.php**

  - [x] **Super Admin User**
    ```php
    - Email: admin@kasir5.com
    - Password: Admin@123
    - Role: Administrator SaaS
    - tenant_id: null
    - store_id: null
    ```

  - [x] **2 Tenants**
    ```php
    Tenant 1:
    - Name: "ABC Retail Group"
    - Slug: "abc-retail"
    - Email: owner@abcretail.com
    - Status: active

    Tenant 2:
    - Name: "XYZ Minimart"
    - Slug: "xyz-minimart"
    - Email: owner@xyzmart.com
    - Status: trial
    ```

  - [x] **Stores (3 per tenant = 6 stores)**
    ```php
    ABC Retail:
    - Store 1: "ABC Central Jakarta"
    - Store 2: "ABC Bekasi"
    - Store 3: "ABC Tangerang"

    XYZ Minimart:
    - Store 1: "XYZ Kelapa Gading"
    - Store 2: "XYZ Pluit"
    - Store 3: "XYZ Senayan"
    ```

  - [x] **Users**
    ```php
    Per Tenant:
    - 1 Tenant Owner
    - 2 Admin Toko (1 per store, store 3 no admin)
    - 3 Kasir per store (9 kasir per tenant)

    Total: 1 Super Admin + 24 users = 25 users
    ```

  - [x] **Categories (10 per tenant = 20 categories)**
    ```php
    - Beverages, Snacks, Groceries, Personal Care
    - Household, Electronics, Stationery, Frozen Food
    - Bakery, Dairy
    ```

  - [x] **Products (50 per tenant = 100 products)**
    ```php
    - Mix across all categories
    - With realistic SKU, barcode, prices
    - Purchase price + Selling price
    - Min/max stock levels
    ```

  - [x] **Stocks (per store)**
    ```php
    - Assign random quantities to products
    - Some low stock (< min_stock)
    - Some overstock (> max_stock)
    - Some normal
    ```

  - [x] **Store Settings (per store)**
    ```php
    - Tax enabled: true/false (mix)
    - Tax rate: 11% (PPN Indonesia)
    - Rounding: 100/500/1000
    - Discount limits
    ```

- [x] **Run all seeders**
  ```php
  php artisan db:seed --class=RoleSeeder
  php artisan db:seed --class=PermissionSeeder
  php artisan db:seed --class=RolePermissionSeeder
  php artisan db:seed --class=DummyDataSeeder
  ```

**Output:**
- ✅ 25 Users created (1 Super Admin + 24 tenant users)
- ✅ 2 Tenants with 3 stores each
- ✅ 100 Products with stock data
- ✅ Ready for testing authentication

**Validation:**
- Login as admin@kasir5.com → Access /admin/dashboard
- Login as tenant owner → Access /dashboard
- Login as kasir → Limited access

---

### **PHASE 5: UI TEMPLATE & LAYOUTS** (Hari 6-7)
**Status:** ✅ COMPLETED
**Estimasi:** 10-12 jam
**Priority:** CRITICAL

#### Checklist:

- [x] **Master Layouts**

  - [x] **resources/views/layouts/app.blade.php** (Base layout)
    ```blade
    - <!DOCTYPE html> structure
    - <head> with Tailwind CSS, Alpine.js, Chart.js
    - <body> with @yield('content')
    - Include sidebar, navbar, footer
    - Flash messages display
    - CSRF token
    ```

  - [x] **resources/views/layouts/admin.blade.php** (Super Admin)
    ```blade
    - Extends app.blade.php
    - Sidebar with Super Admin menus
    - Dark theme (optional)
    ```

  - [x] **resources/views/layouts/tenant.blade.php** (Tenant Owner)
    ```blade
    - Extends app.blade.php
    - Sidebar with Tenant Owner menus
    - Tenant name display
    ```

  - [x] **resources/views/layouts/store.blade.php** (Admin Toko & Kasir)
    ```blade
    - Extends app.blade.php
    - Sidebar with Store menus (dynamic by role)
    - Store name + Tenant name display
    ```

  - [x] **resources/views/layouts/guest.blade.php** (Login/Register)
    ```blade
    - Clean layout without sidebar
    - Centered form
    - Branding
    ```

- [x] **Blade Components**

  - [x] **resources/views/components/sidebar.blade.php**
    ```blade
    - Props: $menus (array)
    - Dynamic menu rendering
    - Active state highlighting
    - Collapsible sub-menus (Alpine.js)
    - Icons (Heroicons)
    - User info at bottom
    - Logout button
    ```

  - [x] **resources/views/components/navbar.blade.php**
    ```blade
    - Breadcrumb
    - User dropdown (profile, settings, logout)
    - Notifications icon (with badge)
    - Mobile hamburger menu
    ```

  - [x] **resources/views/components/breadcrumb.blade.php**
    ```blade
    - Props: $items (array)
    - Render: Home > Section > Current Page
    - Last item not clickable
    ```

  - [x] **resources/views/components/alert.blade.php**
    ```blade
    - Props: $type (success/error/warning/info)
    - Auto-dismiss after 5 seconds (Alpine.js)
    - Close button
    - Icons per type
    ```

  - [x] **resources/views/components/table.blade.php**
    ```blade
    - Props: $headers, $rows
    - Responsive table
    - Sortable headers (optional)
    - Action buttons column
    - Empty state message
    ```

  - [x] **resources/views/components/pagination.blade.php**
    ```blade
    - Props: $paginator
    - Prev/Next buttons
    - Page numbers (1 2 3 ... 10)
    - Per-page selector (15/30/50/100)
    - "Showing X to Y of Z records"
    ```

  - [x] **resources/views/components/modal.blade.php**
    ```blade
    - Props: $id, $title
    - Alpine.js toggle
    - Backdrop
    - Close button
    - @slot for content
    - @slot for footer (buttons)
    ```

  - [x] **resources/views/components/confirm-delete.blade.php**
    ```blade
    - Reusable delete confirmation modal
    - Props: $action (form action URL)
    - "Are you sure?" message
    - Delete/Cancel buttons
    ```

  - [x] **Form Components**

    - [x] **components/form/input.blade.php**
      ```blade
      - Props: $name, $label, $type, $value, $required, $error
      - Label above input
      - Error message below
      - Tailwind styling
      ```

    - [x] **components/form/textarea.blade.php**
      ```blade
      - Props: $name, $label, $rows, $value
      ```

    - [x] **components/form/select.blade.php**
      ```blade
      - Props: $name, $label, $options, $selected
      - Placeholder option
      ```

    - [x] **components/form/checkbox.blade.php**
      ```blade
      - Props: $name, $label, $checked
      - Inline label
      ```

    - [x] **components/form/radio.blade.php**
      ```blade
      - Props: $name, $label, $options, $selected
      ```

    - [x] **components/form/file.blade.php**
      ```blade
      - Props: $name, $label, $accept
      - File preview (for images)
      ```

    - [x] **components/form/datepicker.blade.php**
      ```blade
      - Props: $name, $label, $value
      - HTML5 date input or Flatpickr
      ```

  - [x] **components/badge.blade.php**
    ```blade
    - Props: $color (green/red/yellow/blue), $text
    - Pills: Active/Inactive, Low Stock, etc.
    ```

  - [x] **components/button.blade.php**
    ```blade
    - Props: $type (primary/secondary/danger), $text, $icon
    - Consistent button styling
    ```

  - [x] **components/card.blade.php**
    ```blade
    - Props: $title
    - White card with shadow
    - Header, body, footer slots
    ```

  - [x] **components/stat-card.blade.php**
    ```blade
    - Props: $title, $value, $icon, $color
    - For dashboard statistics
    - Icon on left, value on right
    - Trend indicator (optional)
    ```

- [x] **Tailwind CSS Configuration**
  - [x] **tailwind.config.js**
    ```js
    - Custom colors (primary, secondary, danger, success, warning, info)
    - Custom fonts (Inter/Poppins from Google Fonts)
    - Custom breakpoints
    - Dark mode class strategy
    ```

  - [x] Compile: `npm run build`

- [x] **Alpine.js Setup**
  - [x] Include via CDN in layout head
  - [x] Test dropdown functionality
  - [x] Test modal toggle
  - [x] Test collapsible menu

- [x] **Chart.js Setup**
  - [x] Include via CDN
  - [x] Create test chart on dashboard

**Output:**
- ✅ Complete UI component library
- ✅ 4 Layout templates
- ✅ Reusable Blade components (20+ components)
- ✅ Consistent styling across all pages
- ✅ Mobile responsive

**Validation:**
- Visit /dashboard → Layout loads correctly
- Test sidebar menu → Collapsible works
- Test modal → Opens/closes
- Test form components → Styled correctly

---

## ═══════════════════════════════════════════════════════
## TIER 2: CORE MODULES (Phase 6-11)
## ═══════════════════════════════════════════════════════

### **PHASE 6: MODULE - USERS MANAGEMENT** (Hari 7-8)
**Status:** ✅ COMPLETED
**Estimasi:** 10-12 jam
**Priority:** HIGH

#### Checklist:

- [x] **Routes (web.php)**
  ```php
  Super Admin:
  - GET /admin/users → index (all users)
  - GET /admin/users/create → create
  - POST /admin/users → store
  - GET /admin/users/{id} → show
  - GET /admin/users/{id}/edit → edit
  - PUT /admin/users/{id} → update
  - DELETE /admin/users/{id} → destroy
  - POST /admin/users/{id}/restore → restore

  Tenant Owner:
  - GET /users → index (tenant users)
  - Same CRUD endpoints

  Admin Toko:
  - GET /staff → index (store users)
  - Same CRUD endpoints
  ```

- [x] **UserRepository**
  - [x] **app/Repositories/UserRepository.php**
    ```php
    - getAllPaginated($perPage, $search, $filters)
    - getByTenant($tenantId, $perPage)
    - getByStore($storeId, $perPage)
    - create($data)
    - update($id, $data)
    - delete($id) // soft delete
    - restore($id)
    - generateActivationCode()
    - checkActivationCode($userId, $code)
    ```

- [x] **UserService**
  - [x] **app/Services/UserService.php**
    ```php
    - createUser($data) // hash password, generate code
    - updateUser($id, $data)
    - deleteUser($id) // check dependencies
    - restoreUser($id)
    - sendActivationEmail($userId)
    - activateUser($userId, $code)
    - forcePasswordChange($userId)
    - logoutAllSessions($userId)
    - trackLogin($userId, $ip, $device)
    ```

- [x] **UserRequest**
  - [x] **app/Http/Requests/UserRequest.php**
    ```php
    - rules() for create & update
    - Validation:
      - name: required, string, max:255
      - email: required, email, unique (except current)
      - phone: nullable, regex (phone format)
      - tenant_id: required_unless (Super Admin)
      - store_id: nullable
      - password: required on create, min:8, confirmed
      - role: required, exists:roles
    ```

- [x] **UserController**
  - [x] **app/Http/Controllers/UserController.php**
    ```php
    - index()
      → Get users based on role scope
      → Search: name, email, phone
      → Filter: role, status (active/inactive), store
      → Pagination: 15 per page
      → Return view with users

    - create()
      → Get roles for dropdown (based on permission)
      → Get tenants (Super Admin) or stores (Tenant Owner)
      → Return create form

    - store(UserRequest $request)
      → Validate
      → Call UserService->createUser()
      → Send activation email if checked
      → Flash success message
      → Redirect to index

    - show($id)
      → Get user with relationships (role, tenant, store)
      → Activity log (last 10 activities)
      → Login history
      → Return show view

    - edit($id)
      → Get user
      → Check permission (own tenant/store)
      → Get roles, tenants, stores for dropdowns
      → Return edit form

    - update($id, UserRequest $request)
      → Validate
      → Call UserService->updateUser()
      → Flash success
      → Redirect to index

    - destroy($id)
      → Check if user has transactions/activities
      → If yes: offer reassignment modal
      → Call UserService->deleteUser()
      → Flash success
      → Redirect

    - restore($id)
      → Restore soft deleted user
      → Flash success
      → Redirect
    ```

- [x] **Views**

  - [x] **resources/views/users/index.blade.php**
    ```blade
    - Page title: "Users Management"
    - Breadcrumb: Dashboard > Users
    - Search bar (name, email, phone)
    - Filters: Role, Status, Store (dropdowns)
    - Button: "Add User" (if has permission)
    - Table columns:
      → Name
      → Email
      → Phone
      → Role (badge)
      → Store
      → Status (badge: Active/Inactive)
      → Last Login
      → Actions (Edit, Delete, View)
    - Pagination
    - Empty state: "No users found"
    ```

  - [x] **resources/views/users/create.blade.php**
    ```blade
    - Form with fields:
      → Name (input)
      → Email (input)
      → Phone (input)
      → Role (select dropdown)
      → Tenant (select - Super Admin only)
      → Store (select - optional)
      → Password (input type=password)
      → Confirm Password (input type=password)
      → Avatar (file upload - optional)
      → Send Activation Email (checkbox)
      → Force Password Change on First Login (checkbox)
      → Status (radio: Active/Inactive)
    - Submit button
    - Cancel button
    - Validation errors display
    ```

  - [x] **resources/views/users/edit.blade.php**
    ```blade
    - Same as create but:
      → Pre-filled values
      → Password optional
      → Show "Last Login" info
      → Button: "Force Logout All Sessions"
      → Button: "Resend Activation Email"
    ```

  - [x] **resources/views/users/show.blade.php**
    ```blade
    - User details card:
      → Avatar
      → Name, Email, Phone
      → Role (badge)
      → Tenant, Store
      → Status
      → Activation Status
      → Last Login (time ago)
      → Login Count
      → Password Expires At
      → Created At, Updated At
    - Activity Log table (last 10)
    - Login History table (last 10)
    - Buttons:
      → Edit
      → Delete
      → Force Logout All Sessions
      → Send Activation Email
    ```

- [x] **Additional Features**

  - [x] **Activation Code System**
    ```php
    - Generate 6-digit code on user creation
    - Expiry: 24 hours
    - Email template: "Your activation code is: 123456"
    - Validation on first login
    - Redirect to activate page if not activated
    ```

  - [x] **Password Expiry Tracking**
    ```php
    - Calculate password_expires_at (90 days from now)
    - Middleware: CheckPasswordExpiry
    - Warning 7 days before expiry
    - Force change password page
    ```

  - [x] **Last Login Tracking**
    ```php
    - Update on every login:
      → last_login_at
      → last_login_ip (request()->ip())
      → last_login_device (User-Agent)
      → login_count++
    ```

  - [x] **Cascade Deletion Handling**
    ```blade
    - Modal: "This user has X transactions. Reassign to:"
    - Dropdown: Other users
    - Button: "Delete & Reassign"
    - Or: "Cannot delete" if data cannot be reassigned
    ```

  - [x] **Logout All Sessions**
    ```php
    - Delete all session tokens
    - Force re-login
    - Flash message: "All sessions logged out"
    ```

  - [x] **Bulk Actions**
    ```blade
    - Checkboxes on table rows
    - Bulk dropdown: Activate / Deactivate / Delete
    - Confirm modal
    ```

**Output:**
- ✅ Full CRUD for Users
- ✅ Role-based filtering (Super Admin sees all, Tenant Owner sees tenant users, Admin Toko sees store users)
- ✅ Activation system
- ✅ Password expiry
- ✅ Login tracking
- ✅ Bulk actions

**Validation:**
- Create user → Activation email sent
- Edit user → Changes saved
- Delete user → Soft deleted
- Restore user → Active again
- Search & filters work
- Permissions enforced

---

### **PHASE 7: MODULE - TENANTS MANAGEMENT** (Hari 8-9)
**Status:** 🟡 PENDING
**Estimasi:** 8-10 jam
**Priority:** HIGH

#### Checklist:

- [ ] **Routes (web.php)**
  ```php
  Super Admin only:
  - GET /admin/tenants → index
  - GET /admin/tenants/create → create
  - POST /admin/tenants → store
  - GET /admin/tenants/{id} → show
  - GET /admin/tenants/{id}/edit → edit
  - PUT /admin/tenants/{id} → update
  - DELETE /admin/tenants/{id} → destroy
  - POST /admin/tenants/{id}/restore → restore
  - POST /admin/tenants/{id}/activate → activate
  - POST /admin/tenants/{id}/deactivate → deactivate
  ```

- [ ] **TenantRepository**
  - [ ] **app/Repositories/TenantRepository.php**
    ```php
    - getAllPaginated($perPage, $search, $filters)
    - getWithStatistics($id) // stores, users, products count
    - create($data)
    - update($id, $data)
    - delete($id)
    - restore($id)
    - activate($id)
    - deactivate($id)
    ```

- [ ] **TenantService**
  - [ ] **app/Services/TenantService.php**
    ```php
    - createTenant($data)
      → Generate unique slug
      → Create tenant
      → Auto-create owner account (if checked)
      → Send welcome email

    - updateTenant($id, $data)
      → Validate slug uniqueness
      → Update tenant
      → Notify owner if status changed

    - deleteTenant($id)
      → Check if has active subscriptions
      → Soft delete tenant + cascade (stores, users)

    - activateTenant($id)
      → Set is_active = true
      → Send activation email
      → Option: activate all stores too

    - deactivateTenant($id)
      → Set is_active = false
      → Send notification
      → Option: deactivate all stores too
    ```

- [ ] **TenantRequest**
  - [ ] **app/Http/Requests/TenantRequest.php**
    ```php
    - name: required, string, max:255
    - slug: required, unique, lowercase, no spaces
    - email: required, email, unique
    - phone: required
    - subscription_status: required, in:trial,active,expired,cancelled
    - trial_ends_at: nullable, date
    - settings: nullable, json
    ```

- [ ] **TenantController**
  - [ ] **app/Http/Controllers/TenantController.php**
    ```php
    - index()
      → Search: name, email, slug
      → Filter: subscription_status, is_active
      → Sort: created_at desc
      → Pagination: 15
      → Show counts: stores, users, products

    - create()
      → Return form

    - store(TenantRequest $request)
      → Validate
      → Call TenantService->createTenant()
      → Flash success
      → Redirect to tenants.index

    - show($id)
      → Get tenant with statistics
      → Show stores list
      → Show users count by role
      → Show subscription info
      → Show activity timeline
      → Charts: Sales trend (last 30 days)

    - edit($id)
      → Get tenant
      → Return form

    - update($id, TenantRequest $request)
      → Validate
      → Call TenantService->updateTenant()
      → Flash success
      → Redirect

    - destroy($id)
      → Call TenantService->deleteTenant()
      → Flash success
      → Redirect

    - activate($id)
      → Call TenantService->activateTenant()
      → Flash success
      → Redirect

    - deactivate($id)
      → Call TenantService->deactivateTenant()
      → Flash success
      → Redirect
    ```

- [ ] **Views**

  - [ ] **resources/views/tenants/index.blade.php**
    ```blade
    - Page title: "Tenants Management"
    - Search bar
    - Filters: Subscription Status, Active Status
    - Button: "Add Tenant"
    - Table columns:
      → Name
      → Slug
      → Email / Phone
      → Subscription Status (badge)
      → Trial Ends At
      → Stores Count
      → Users Count
      → Status (Active/Inactive badge)
      → Actions (View, Edit, Delete, Activate/Deactivate)
    - Pagination
    ```

  - [ ] **resources/views/tenants/create.blade.php**
    ```blade
    - Form fields:
      → Name (input)
      → Slug (input - auto-generate from name)
      → Email (input)
      → Phone (input)
      → Subscription Status (select: trial/active/expired/cancelled)
      → Trial Ends At (datepicker - if trial)
      → Subscription Ends At (datepicker - if active)
      → Auto-create Owner Account (checkbox)
        → If checked: show owner email input
      → Status (radio: Active/Inactive)
    - Submit & Cancel buttons
    ```

  - [ ] **resources/views/tenants/edit.blade.php**
    ```blade
    - Same as create but pre-filled
    - Warning: "Changing slug will affect all URLs!"
    - Show created_at, updated_at
    ```

  - [ ] **resources/views/tenants/show.blade.php**
    ```blade
    - Tenant Info Card:
      → Name, Slug, Email, Phone
      → Subscription Status (badge)
      → Trial/Subscription dates
      → Status (Active/Inactive)
      → Created At, Updated At

    - Statistics Cards:
      → Total Stores
      → Total Users
      → Total Products
      → Total Sales (last 30 days)

    - Chart: Sales Trend (Line chart - last 30 days)

    - Stores Table:
      → Store Name
      → Location
      → Status
      → Users Count
      → Link to store detail

    - Users Breakdown:
      → By Role (Tenant Owner: X, Admin Toko: X, Kasir: X)

    - Activity Timeline:
      → Recent activities
      → User creations, store creations, etc.

    - Action Buttons:
      → Edit Tenant
      → Activate / Deactivate
      → Delete Tenant
      → Manage Subscription
    ```

- [ ] **Additional Features**

  - [ ] **Auto-create Owner Account**
    ```php
    - Checkbox on create form
    - If checked:
      → Auto-fill email from tenant email
      → Generate random password
      → Send credentials email
      → Assign "Tenant Owner" role
    ```

  - [ ] **Slug Auto-generation**
    ```javascript
    - Alpine.js: watch name input
    - Convert to slug: lowercase, replace spaces with dash
    - Check uniqueness (AJAX)
    - Display: "Slug available ✓" or "Slug taken ✗"
    ```

  - [ ] **Cascade Activate/Deactivate**
    ```blade
    - Modal: "Also activate all stores?"
    - Checkbox: "Activate all stores"
    - Checkbox: "Activate all users"
    - Preview: "X stores, Y users will be activated"
    ```

  - [ ] **Notification System**
    ```php
    - On activate: Send email to tenant owner
    - On deactivate: Send notification
    - On subscription expiry: Send warning email
    ```

**Output:**
- ✅ Full CRUD for Tenants
- ✅ Auto-create owner account
- ✅ Tenant statistics & charts
- ✅ Activate/Deactivate with cascade
- ✅ Slug validation & auto-generation
- ✅ Email notifications

**Validation:**
- Create tenant → Owner account created
- Activate tenant → All stores activated (if checked)
- Edit slug → Warning shown
- Delete tenant → Soft deleted with cascade
- Statistics display correctly

---

### **PHASE 8: MODULE - STORES MANAGEMENT** (Hari 9-10)
**Status:** 🟡 PENDING
**Estimasi:** 8-10 jam
**Priority:** HIGH

#### Checklist:

- [ ] **Routes (web.php)**
  ```php
  Tenant Owner & Admin Toko:
  - GET /stores → index
  - GET /stores/create → create
  - POST /stores → store
  - GET /stores/{id} → show
  - GET /stores/{id}/edit → edit
  - PUT /stores/{id} → update
  - DELETE /stores/{id} → destroy
  - GET /stores/{id}/settings → settings (Admin Toko)
  - PUT /stores/{id}/settings → updateSettings
  ```

- [ ] **StoreRepository**
  - [ ] **app/Repositories/StoreRepository.php**
    ```php
    - getByTenant($tenantId, $perPage)
    - getWithStatistics($id)
    - create($data)
    - update($id, $data)
    - delete($id)
    - updateSettings($storeId, $settingsData)
    ```

- [ ] **StoreService**
  - [ ] **app/Services/StoreService.php**
    ```php
    - createStore($data)
      → Generate unique code
      → Create store
      → Create default store_settings
      → Send email to assigned Admin Toko

    - updateStore($id, $data)
      → Update store
      → Notify if status changed

    - deleteStore($id)
      → Check if has active transactions
      → Soft delete

    - updateStoreSettings($storeId, $settings)
      → Update store_settings record
      → Validate operating hours JSON
      → Validate tax settings
    ```

- [ ] **StoreRequest**
  - [ ] **app/Http/Requests/StoreRequest.php**
    ```php
    - name: required, string, max:255
    - code: required, unique per tenant
    - address: required
    - city, province, postal_code: required
    - phone: required
    - email: email, nullable
    - timezone: required, in:timezones list
    - logo_path: nullable, image, max:2MB
    ```

- [ ] **StoreController**
  - [ ] **app/Http/Controllers/StoreController.php**
    ```php
    - index()
      → Get stores by tenant (auto-scoped)
      → Search: name, code, city
      → Filter: status, city
      → Show statistics per store
      → Pagination

    - create()
      → Get tenant_id from auth user
      → Return form

    - store(StoreRequest $request)
      → Validate
      → Call StoreService->createStore()
      → Flash success
      → Redirect to stores.index

    - show($id)
      → Get store with statistics
      → Show users (Admin Toko, Kasir)
      → Show inventory summary
      → Show sales summary (today, this month)
      → Chart: Hourly sales (today)

    - edit($id)
      → Check permission
      → Get store
      → Return form

    - update($id, StoreRequest $request)
      → Call StoreService->updateStore()
      → Flash success
      → Redirect

    - destroy($id)
      → Call StoreService->deleteStore()
      → Flash success
      → Redirect

    - settings($id)
      → Get store with settings
      → Return settings form

    - updateSettings($id, Request $request)
      → Validate
      → Call StoreService->updateStoreSettings()
      → Flash success
      → Redirect
    ```

- [ ] **Views**

  - [ ] **resources/views/stores/index.blade.php**
    ```blade
    - Page title: "Stores Management"
    - Search bar
    - Filter: City, Status
    - Button: "Add Store" (Tenant Owner)
    - Cards layout (grid):
      → Store Name
      → Code
      → Address
      → Phone
      → Status badge
      → Users count
      → Today's Sales
      → Actions (View, Edit, Settings, Delete)
    - Pagination
    ```

  - [ ] **resources/views/stores/create.blade.php**
    ```blade
    - Form fields:
      → Name (input)
      → Code (input - auto-generate or manual)
      → Address (textarea)
      → City (input)
      → Province (select dropdown)
      → Postal Code (input)
      → Phone (input)
      → Email (input)
      → Timezone (select dropdown)
      → Logo (file upload)
      → Status (radio: Active/Inactive)
    - Submit & Cancel
    ```

  - [ ] **resources/views/stores/edit.blade.php**
    ```blade
    - Same as create but pre-filled
    ```

  - [ ] **resources/views/stores/show.blade.php**
    ```blade
    - Store Info Card:
      → Logo
      → Name, Code
      → Full Address
      → Phone, Email
      → Timezone
      → Status
      → Created At

    - Statistics Cards:
      → Total Users (Admin Toko + Kasir)
      → Today's Sales
      → This Month's Sales
      → Total Products in Stock

    - Chart: Hourly Sales (Today) - Bar chart

    - Users Table:
      → Name
      → Role
      → Status
      → Last Login
      → Actions

    - Inventory Alerts:
      → Low Stock Items (count)
      → Out of Stock Items (count)
      → Overstock Items (count)

    - Action Buttons:
      → Edit Store
      → Store Settings
      → Delete Store
      → View Transactions
      → View Inventory
    ```

  - [ ] **resources/views/stores/settings.blade.php**
    ```blade
    - Tabs:
      1. General Settings
      2. Tax Settings
      3. Pricing Rules
      4. Operating Hours

    - Tab 1: General
      → Store Name (readonly)
      → Auto-print Receipt (checkbox)

    - Tab 2: Tax Settings
      → Enable Tax (checkbox)
      → Tax Name (input: VAT/PPN/Tax)
      → Tax Rate (input: %, decimal)
      → Tax Calculation (radio: Inclusive/Exclusive)
      → Preview: Example calculation

    - Tab 3: Pricing Rules
      → Markup Percentage (input: %)
      → Rounding Rule (select: None/100/500/1000)
      → Max Discount per Item (input: %)
      → Max Discount per Transaction (input: %)
      → Discount Requires Approval Above (input: %)
      → Preview: Examples

    - Tab 4: Operating Hours
      → Table with days of week
      → Open Time (time picker)
      → Close Time (time picker)
      → Closed (checkbox)
      → Public Holidays (date list + closed checkbox)

    - Save button
    ```

- [ ] **Additional Features**

  - [ ] **Operating Hours UI**
    ```blade
    - Table:
      | Day       | Open Time | Close Time | Closed |
      |-----------|-----------|------------|--------|
      | Monday    | 08:00     | 22:00      | □      |
      | Tuesday   | 08:00     | 22:00      | □      |
      | ...

    - Store as JSON in store_settings.operating_hours
    ```

  - [ ] **Timezone Selector**
    ```php
    - Dropdown with all PHP timezones
    - Auto-detect from browser (JavaScript)
    - Default: Asia/Jakarta
    - Display all times in store timezone
    ```

  - [ ] **Tax Calculation Preview**
    ```blade
    - Example:
      Product Price: Rp 100,000
      Tax Rate: 11%

      If Inclusive:
      Price incl. tax: Rp 100,000
      Tax amount: Rp 9,910
      Price excl. tax: Rp 90,090

      If Exclusive:
      Price excl. tax: Rp 100,000
      Tax amount: Rp 11,000
      Price incl. tax: Rp 111,000
    ```

  - [ ] **Rounding Preview**
    ```blade
    - Example:
      Subtotal: Rp 127,350

      No Rounding: Rp 127,350
      Round to 100: Rp 127,400
      Round to 500: Rp 127,500
      Round to 1000: Rp 127,000
    ```

  - [ ] **Logo Upload**
    ```php
    - Validation: image, max:2MB, JPG/PNG
    - Store in: storage/app/public/stores/logos/
    - Preview thumbnail
    - Delete old logo on update
    - Used on receipts
    ```

  - [ ] **Store Notification**
    ```php
    - On create: Email to assigned Admin Toko
    - Email template: "You've been assigned to [Store Name]"
    - Include login credentials if new user
    ```

**Output:**
- ✅ Full CRUD for Stores
- ✅ Store settings management (Tax, Pricing, Operating Hours)
- ✅ Logo upload
- ✅ Store statistics & charts
- ✅ Timezone configuration
- ✅ Email notifications

**Validation:**
- Create store → Default settings created
- Edit settings → Changes saved
- Tax calculation preview works
- Operating hours saved as JSON
- Logo upload successful
- Timezone applied correctly

---

### **PHASE 9: MODULE - CATEGORIES MANAGEMENT** (Hari 10)
**Status:** 🟡 PENDING
**Estimasi:** 6-8 jam
**Priority:** HIGH

#### Checklist:

- [ ] **Routes (web.php)**
  ```php
  Tenant Owner & Admin Toko:
  - GET /categories → index
  - GET /categories/create → create
  - POST /categories → store
  - GET /categories/{id}/edit → edit
  - PUT /categories/{id} → update
  - DELETE /categories/{id} → destroy
  - POST /categories/bulk-delete → bulkDelete
  - GET /categories/export → export (Excel/CSV)
  ```

- [ ] **CategoryRepository**
  - [ ] **app/Repositories/CategoryRepository.php**
    ```php
    - getByTenant($tenantId, $perPage, $search)
    - getAllActive() // for dropdowns
    - getWithProductCount($id)
    - create($data)
    - update($id, $data)
    - delete($id)
    - bulkDelete($ids)
    - checkHasProducts($id)
    ```

- [ ] **CategoryService**
  - [ ] **app/Services/CategoryService.php**
    ```php
    - createCategory($data)
      → Generate slug from name
      → Create category

    - updateCategory($id, $data)
      → Regenerate slug if name changed
      → Update category

    - deleteCategory($id)
      → Check if has products
      → If yes: prevent deletion OR offer reassignment
      → Soft delete

    - bulkDelete($ids)
      → Validate all IDs
      → Check each for products
      → Delete if safe
    ```

- [ ] **CategoryRequest**
  - [ ] **app/Http/Requests/CategoryRequest.php**
    ```php
    - name: required, string, max:255, unique per tenant
    - description: nullable, string
    - parent_id: nullable, exists:categories,id
    - is_active: boolean
    ```

- [ ] **CategoryController**
  - [ ] **app/Http/Controllers/CategoryController.php**
    ```php
    - index()
      → Get categories by tenant
      → Search: name
      → Filter: status (active/inactive), parent
      → Show product count per category
      → Pagination: 15

    - create()
      → Get parent categories for dropdown
      → Return form

    - store(CategoryRequest $request)
      → Validate
      → Call CategoryService->createCategory()
      → Flash success
      → Redirect

    - edit($id)
      → Get category
      → Get parent categories (exclude self)
      → Return form

    - update($id, CategoryRequest $request)
      → Call CategoryService->updateCategory()
      → Flash success
      → Redirect

    - destroy($id)
      → Check if has products
      → If yes: show error OR reassignment modal
      → Call CategoryService->deleteCategory()
      → Flash success
      → Redirect

    - bulkDelete(Request $request)
      → Validate IDs array
      → Call CategoryService->bulkDelete()
      → Flash success
      → Redirect

    - export(Request $request)
      → Get all categories
      → Export to Excel/CSV
      → Download file
    ```

- [ ] **Views**

  - [ ] **resources/views/categories/index.blade.php**
    ```blade
    - Page title: "Categories"
    - Search bar
    - Filter: Status, Parent Category
    - Buttons:
      → "Add Category"
      → "Bulk Delete" (if rows selected)
      → "Export to Excel"

    - Table:
      → Checkbox (for bulk actions)
      → Name
      → Slug
      → Parent Category
      → Products Count
      → Status (badge)
      → Actions (Edit, Delete)

    - Pagination
    - Empty state: "No categories found. Create your first category!"
    ```

  - [ ] **resources/views/categories/create.blade.php**
    ```blade
    - Form:
      → Name (input)
      → Slug (input - auto-generated, editable)
      → Description (textarea)
      → Parent Category (select dropdown - optional for sub-categories)
      → Status (radio: Active/Inactive)
    - Submit & Cancel
    ```

  - [ ] **resources/views/categories/edit.blade.php**
    ```blade
    - Same as create but pre-filled
    - Show product count: "X products in this category"
    ```

- [ ] **Additional Features**

  - [ ] **Slug Auto-generation**
    ```javascript
    - Alpine.js: watch name input
    - Convert to slug: lowercase, spaces to dash
    - Display in slug input (editable)
    ```

  - [ ] **Prevent Delete if Has Products**
    ```blade
    - On delete click:
      → Check if category.products_count > 0
      → If yes: Modal "Cannot delete. Category has X products. Reassign to:"
      → Dropdown: Other categories
      → Button: "Reassign & Delete"
      → Or: Cancel
    ```

  - [ ] **Bulk Delete**
    ```blade
    - Checkboxes on table rows
    - Select All checkbox
    - Button: "Delete Selected" (red, disabled if none selected)
    - Confirm modal: "Delete X categories?"
    ```

  - [ ] **Export to Excel**
    ```php
    - Use Laravel Excel or manual CSV
    - Columns: Name, Slug, Description, Parent, Products Count, Status, Created At
    - Filename: categories-{date}.xlsx
    ```

  - [ ] **Hierarchical Display (Optional)**
    ```blade
    - Tree structure with indentation:
      Electronics
        → Mobile Phones
        → Laptops
      Food & Beverages
        → Snacks
        → Drinks
    ```

**Output:**
- ✅ Full CRUD for Categories
- ✅ Slug auto-generation
- ✅ Sub-categories support (parent-child)
- ✅ Prevent delete if has products
- ✅ Bulk delete
- ✅ Export to Excel

**Validation:**
- Create category → Slug generated
- Create sub-category → Parent assigned
- Delete category with products → Warning shown
- Bulk delete → Multiple deleted
- Export → Excel downloaded
- Search & filters work

---

### **PHASE 10: MODULE - PRODUCTS MANAGEMENT** (Hari 11-12)
**Status:** 🟡 PENDING
**Estimasi:** 12-14 jam
**Priority:** HIGH

#### Checklist:

- [ ] **Routes (web.php)**
  ```php
  Tenant Owner & Admin Toko:
  - GET /products → index
  - GET /products/create → create
  - POST /products → store
  - GET /products/{id} → show
  - GET /products/{id}/edit → edit
  - PUT /products/{id} → update
  - DELETE /products/{id} → destroy
  - POST /products/bulk-import → bulkImport
  - POST /products/bulk-price-update → bulkPriceUpdate
  - GET /products/export → export
  - GET /products/download-template → downloadTemplate
  - GET /products/{id}/price-history → priceHistory
  - POST /products/{id}/override-price → overrideStorePrice
  ```

- [ ] **ProductRepository**
  - [ ] **app/Repositories/ProductRepository.php**
    ```php
    - getByTenant($tenantId, $perPage, $search, $filters)
    - getWithStocks($id) // with stock per store
    - create($data)
    - update($id, $data)
    - delete($id)
    - bulkImport($data)
    - bulkPriceUpdate($filters, $changeType, $value)
    - getPriceHistory($productId)
    - overrideStorePrice($productId, $storeId, $price)
    - checkDuplicateSKU($sku, $tenantId, $excludeId = null)
    ```

- [ ] **ProductService**
  - [ ] **app/Services/ProductService.php**
    ```php
    - createProduct($data)
      → Generate SKU if not provided
      → Upload image if provided
      → Create product
      → Create initial stocks for all stores (qty = 0)
      → Log price history

    - updateProduct($id, $data)
      → Update product
      → If price changed: log to price_histories
      → Update image if uploaded

    - deleteProduct($id)
      → Check if has stock movements
      → Soft delete

    - generateSKU($tenantId, $categoryId)
      → Pattern: {CATEGORY_CODE}-{YYYYMMDD}-{SEQUENCE}
      → Example: BEV-20251129-001

    - bulkImportFromExcel($file)
      → Read Excel file
      → Validate rows
      → Create/Update products
      → Return success/error report

    - bulkPriceUpdate($filters, $changeType, $value)
      → Get products matching filters
      → Calculate new price (% increase/decrease or fixed)
      → Update all
      → Log price histories

    - uploadProductImage($file)
      → Validate image
      → Resize to 800x800
      → Generate thumbnail 200x200
      → Store in storage/products/
      → Return path

    - overrideStorePrice($productId, $storeId, $price)
      → Create/Update product_store_prices
      → Log price history
    ```

- [ ] **ProductRequest**
  - [ ] **app/Http/Requests/ProductRequest.php**
    ```php
    - name: required, string, max:255
    - sku: required, unique per tenant
    - barcode: nullable, string
    - category_id: required, exists:categories
    - description: nullable, string
    - unit: required, in:pcs,box,carton,kg,liter,dozen
    - purchase_price: required, numeric, min:0
    - selling_price: required, numeric, gt:purchase_price
    - min_stock: nullable, integer, min:0
    - max_stock: nullable, integer, gt:min_stock
    - image: nullable, image, max:5MB, mimes:jpg,png,webp
    - is_active: boolean
    ```

- [ ] **ProductController**
  - [ ] **app/Http/Controllers/ProductController.php**
    ```php
    - index()
      → Get products by tenant
      → Search: name, SKU, barcode
      → Filters: category, status, stock level (low/normal/over)
      → Sort: name, SKU, price, created_at
      → Show stock per store
      → Pagination: 15

    - create()
      → Get categories dropdown
      → Return form

    - store(ProductRequest $request)
      → Validate (check duplicate SKU real-time)
      → Call ProductService->createProduct()
      → Flash success
      → Redirect to products.index

    - show($id)
      → Get product with relationships
      → Show stock per store (table)
      → Show price history (last 10)
      → Show recent stock movements (last 10)
      → Chart: Stock movement trend (last 30 days)

    - edit($id)
      → Get product
      → Get categories
      → Return form

    - update($id, ProductRequest $request)
      → Call ProductService->updateProduct()
      → Flash success
      → Redirect

    - destroy($id)
      → Check if has stock movements or transactions
      → Call ProductService->deleteProduct()
      → Flash success
      → Redirect

    - bulkImport(Request $request)
      → Validate file (.xlsx, .csv)
      → Call ProductService->bulkImportFromExcel()
      → Return JSON: {success: X, errors: [...]}
      → Download error report if errors

    - bulkPriceUpdate(Request $request)
      → Validate filters, change_type, value
      → Preview affected products count
      → Call ProductService->bulkPriceUpdate()
      → Flash success
      → Redirect

    - export(Request $request)
      → Get products with filters
      → Export to Excel
      → Download

    - downloadTemplate()
      → Generate Excel template with headers
      → Download

    - priceHistory($id)
      → Get price_histories for product
      → Return view/JSON

    - overrideStorePrice(Request $request, $id)
      → Validate store_id, price
      → Call ProductService->overrideStorePrice()
      → Flash success
      → Redirect
    ```

- [ ] **Views**

  - [ ] **resources/views/products/index.blade.php**
    ```blade
    - Page title: "Products"
    - Search bar (name, SKU, barcode)
    - Filters:
      → Category (dropdown)
      → Status (Active/Inactive)
      → Stock Level (All/Low Stock/Normal/Overstock)
    - Buttons:
      → "Add Product"
      → "Bulk Import" (modal)
      → "Bulk Price Update" (modal)
      → "Export to Excel"
      → "Download Template"

    - Table:
      → Image (thumbnail)
      → Name
      → SKU
      → Barcode
      → Category
      → Unit
      → Purchase Price
      → Selling Price
      → Stock Status (badge: Low/Normal/Over)
      → Total Stock (all stores)
      → Status (Active/Inactive)
      → Actions (View, Edit, Delete, Adjust Stock)

    - Pagination
    ```

  - [ ] **resources/views/products/create.blade.php**
    ```blade
    - Form (2 columns):
      Left Column:
      → Name (input)
      → SKU (input - auto-generate button OR manual)
      → Barcode (input)
      → Category (select dropdown)
      → Unit (select: pcs, box, carton, kg, liter, dozen, custom)
      → Description (textarea)

      Right Column:
      → Purchase Price (input, number, Rp)
      → Selling Price (input, number, Rp)
      → Profit Margin (calculated, readonly: %)
      → Min Stock (input, number)
      → Max Stock (input, number)
      → Image (file upload with preview)
      → Status (radio: Active/Inactive)

    - Submit & Cancel
    ```

  - [ ] **resources/views/products/edit.blade.php**
    ```blade
    - Same as create but:
      → Pre-filled
      → Show current image with delete option
      → Button: "View Price History"
      → Button: "Adjust Stock" (quick link)
    ```

  - [ ] **resources/views/products/show.blade.php**
    ```blade
    - Product Info Card:
      → Image (large)
      → Name, SKU, Barcode
      → Category (link)
      → Unit
      → Purchase Price, Selling Price
      → Profit Margin (%)
      → Min/Max Stock
      → Status
      → Created At, Updated At

    - Stock Per Store Table:
      → Store Name
      → Current Quantity
      → Status (Low/Normal/Over - color coded)
      → Store-Specific Price (if overridden)
      → Actions (Adjust Stock, Override Price)

    - Price History Table (last 10):
      → Date
      → Store (if store-specific)
      → Old Price
      → New Price
      → Changed By
      → Change %

    - Stock Movement Log (last 10):
      → Date
      → Store
      → Type (IN/OUT/ADJ/OPNAME)
      → Quantity (+/-)
      → Reference (link to PO/Transaction/etc)
      → Notes

    - Chart: Stock Movement Trend (Line chart - last 30 days)

    - Action Buttons:
      → Edit Product
      → Adjust Stock
      → View Full Price History
      → View Full Stock Movements
      → Delete Product
    ```

  - [ ] **resources/views/products/bulk-import-modal.blade.php**
    ```blade
    - Modal content:
      → Instructions: "Upload Excel file with columns..."
      → Link: "Download Template"
      → File upload (drag & drop area)
      → Accept: .xlsx, .csv
      → Preview table (first 10 rows after upload)
      → Validation errors highlighted in red
      → Button: "Import" (process)
      → Progress bar during import
      → Result: "Success: X products, Errors: Y"
      → Download error report link
    ```

  - [ ] **resources/views/products/bulk-price-update-modal.blade.php**
    ```blade
    - Modal content:
      → Apply to: (radio)
        - All products
        - Current filtered products
        - Selected category

      → Change Type: (radio)
        - Increase by %
        - Decrease by %
        - Increase by fixed amount
        - Decrease by fixed amount

      → Value: (input, number)

      → Preview:
        "X products will be affected"
        Example: Rp 100,000 → Rp 110,000

      → Confirm & Update button
    ```

  - [ ] **resources/views/products/price-history.blade.php**
    ```blade
    - Full price history table
    - Filters: Date range, Store
    - Export to Excel
    ```

- [ ] **Additional Features**

  - [ ] **SKU Auto-generation**
    ```php
    - Pattern: {CATEGORY_CODE}-{YYYYMMDD}-{SEQUENCE}
    - Example: BEV-20251129-001
    - Category codes: BEV (Beverages), SNK (Snacks), etc.
    - Sequence: 001, 002, ...
    - Button: "Generate SKU" on form
    ```

  - [ ] **Barcode Field**
    ```blade
    - Separate input from SKU
    - Used for POS scanning
    - Validation: numeric, unique optional
    ```

  - [ ] **Duplicate SKU Validation**
    ```javascript
    - Real-time check while typing (AJAX)
    - Display: "SKU available ✓" or "SKU already exists ✗"
    - Prevent form submission if duplicate
    ```

  - [ ] **Profit Margin Calculation**
    ```javascript
    - Auto-calculate on price change
    - Formula: ((Selling - Purchase) / Purchase) × 100
    - Display: "Profit Margin: 25%"
    ```

  - [ ] **Image Upload & Preview**
    ```blade
    - File input with drag & drop
    - Preview thumbnail before upload
    - Validation: JPG/PNG/WebP, max 5MB
    - Auto-resize to 800x800
    - Generate thumbnail 200x200
    - Delete old image on update
    ```

  - [ ] **Stock Status Color Coding**
    ```blade
    - Low Stock (quantity < min_stock): Red badge
    - Normal Stock: Green badge
    - Overstock (quantity > max_stock): Orange badge
    - Out of Stock (quantity = 0): Gray badge
    ```

  - [ ] **Bulk Import Logic**
    ```php
    - Excel columns: Name, SKU, Barcode, Category, Unit, Purchase Price, Selling Price, Min Stock, Max Stock
    - If SKU exists: Update
    - If SKU new: Create
    - Validation per row
    - Error report: Row number, Field, Error message
    - Download error report Excel
    ```

  - [ ] **Store-Specific Price Override**
    ```blade
    - Button on product detail: "Override Price for Store"
    - Modal:
      → Store (dropdown)
      → Override Price (input)
      → Preview: "Default: Rp 100,000 → Override: Rp 95,000"
      → Save button
    - Display overridden prices in stock table
    ```

  - [ ] **Price History Logging**
    ```php
    - Auto-log on every price change
    - Fields: product_id, store_id (null = tenant level), old_price, new_price, changed_by, changed_at
    - Display in product detail
    - Filter & export
    ```

**Output:**
- ✅ Full CRUD for Products
- ✅ SKU auto-generation & barcode
- ✅ Image upload with resize
- ✅ Stock per store display
- ✅ Price history tracking
- ✅ Store-specific pricing
- ✅ Bulk import from Excel
- ✅ Bulk price update
- ✅ Export to Excel
- ✅ Duplicate SKU validation
- ✅ Stock status indicators

**Validation:**
- Create product → SKU generated, image uploaded, stocks created for all stores
- Edit product → Price change logged
- Bulk import → Products created/updated, errors reported
- Bulk price update → Prices updated, history logged
- Override store price → Saved and displayed
- Stock status colors → Displayed correctly
- Export → Excel downloaded

---

### **PHASE 11: MODULE - SUPPLIERS MANAGEMENT** (Hari 12-13)
**Status:** 🟡 PENDING
**Estimasi:** 6-8 jam
**Priority:** HIGH

#### Checklist:

- [ ] **Database Migration**
  - [ ] **suppliers** table
    ```php
    - id, tenant_id (FK)
    - name, code (unique per tenant)
    - contact_person, address
    - city, province, postal_code
    - phone, email
    - payment_terms (Net 30, Net 60, COD, etc.)
    - tax_id (NPWP - Indonesia tax ID)
    - is_active
    - timestamps, soft deletes
    ```

  - [ ] **supplier_ratings** table (optional - for future)
    ```php
    - id, supplier_id (FK)
    - purchase_order_id (FK)
    - rating (1-5 stars)
    - review_notes
    - rated_by_user_id (FK)
    - rated_at
    ```

- [ ] **Routes (web.php)**
  ```php
  Tenant Owner & Admin Toko:
  - GET /suppliers → index
  - GET /suppliers/create → create
  - POST /suppliers → store
  - GET /suppliers/{id} → show
  - GET /suppliers/{id}/edit → edit
  - PUT /suppliers/{id} → update
  - DELETE /suppliers/{id} → destroy
  - GET /suppliers/{id}/history → purchaseHistory
  - GET /suppliers/export → export
  ```

- [ ] **Supplier Model**
  - [ ] **app/Models/Supplier.php**
    ```php
    - BelongsTo: tenant
    - HasMany: purchaseOrders, supplierRatings
    - SoftDeletes trait
    - Global scope: TenantScope
    - Accessors: fullAddress, averageRating
    ```

- [ ] **SupplierRepository**
  - [ ] **app/Repositories/SupplierRepository.php**
    ```php
    - getByTenant($tenantId, $perPage, $search)
    - getWithStatistics($id) // PO count, total purchases, avg rating
    - create($data)
    - update($id, $data)
    - delete($id)
    - getPurchaseHistory($supplierId)
    ```

- [ ] **SupplierService**
  - [ ] **app/Services/SupplierService.php**
    ```php
    - createSupplier($data)
      → Generate unique code
      → Validate NPWP format (XX.XXX.XXX.X-XXX.XXX)
      → Create supplier

    - updateSupplier($id, $data)
      → Update supplier

    - deleteSupplier($id)
      → Check if has active/pending POs
      → Soft delete
    ```

- [ ] **SupplierRequest**
  - [ ] **app/Http/Requests/SupplierRequest.php**
    ```php
    - name: required, string, max:255
    - code: required, unique per tenant
    - contact_person: required, string
    - address: required, string
    - city, province, postal_code: nullable
    - phone: required
    - email: nullable, email
    - payment_terms: nullable, string
    - tax_id: nullable, regex (NPWP format)
    - is_active: boolean
    ```

- [ ] **SupplierController**
  - [ ] **app/Http/Controllers/SupplierController.php**
    ```php
    - index()
      → Search: name, code, contact_person
      → Filter: status, city
      → Sort: name, code, created_at
      → Show PO count per supplier
      → Pagination

    - create()
      → Return form

    - store(SupplierRequest $request)
      → Validate
      → Call SupplierService->createSupplier()
      → Flash success
      → Redirect

    - show($id)
      → Get supplier with statistics
      → Show PO list (last 10)
      → Show total purchases
      → Show payment history
      → Show average rating

    - edit($id)
      → Get supplier
      → Return form

    - update($id, SupplierRequest $request)
      → Call SupplierService->updateSupplier()
      → Flash success
      → Redirect

    - destroy($id)
      → Check if has active POs
      → Call SupplierService->deleteSupplier()
      → Flash success
      → Redirect

    - purchaseHistory($id)
      → Get all POs for supplier
      → Return view

    - export(Request $request)
      → Export suppliers to Excel
      → Download
    ```

- [ ] **Views**

  - [ ] **resources/views/suppliers/index.blade.php**
    ```blade
    - Page title: "Suppliers"
    - Search bar
    - Filter: Status, City
    - Buttons:
      → "Add Supplier"
      → "Export to Excel"

    - Table:
      → Code
      → Name
      → Contact Person
      → Phone / Email
      → City
      → Payment Terms
      → PO Count
      → Status (badge)
      → Actions (View, Edit, Delete)

    - Pagination
    ```

  - [ ] **resources/views/suppliers/create.blade.php**
    ```blade
    - Form (2 columns):
      Left:
      → Name (input)
      → Code (input - auto-generate or manual)
      → Contact Person (input)
      → Phone (input)
      → Email (input)

      Right:
      → Address (textarea)
      → City (input)
      → Province (select)
      → Postal Code (input)
      → Payment Terms (select: Net 30, Net 60, COD, etc.)
      → Tax ID / NPWP (input with format: XX.XXX.XXX.X-XXX.XXX)
      → Status (radio: Active/Inactive)

    - Submit & Cancel
    ```

  - [ ] **resources/views/suppliers/edit.blade.php**
    ```blade
    - Same as create but pre-filled
    ```

  - [ ] **resources/views/suppliers/show.blade.php**
    ```blade
    - Supplier Info Card:
      → Code, Name
      → Contact Person
      → Phone, Email
      → Full Address
      → Payment Terms
      → Tax ID (NPWP)
      → Status
      → Created At

    - Statistics Cards:
      → Total Purchase Orders
      → Total Purchase Amount
      → Average Rating (if implemented)
      → Last Purchase Date

    - Recent Purchase Orders Table (last 10):
      → PO Number
      → Date
      → Status
      → Total Amount
      → Link to PO detail

    - Payment History (optional):
      → Date
      → Amount Paid
      → Payment Method
      → Status

    - Action Buttons:
      → Edit Supplier
      → Create Purchase Order
      → View Full History
      → Delete Supplier
    ```

  - [ ] **resources/views/suppliers/history.blade.php**
    ```blade
    - Full purchase history
    - Filters: Date range, Status
    - Table: All POs
    - Total purchase amount
    - Export to Excel
    ```

- [ ] **Additional Features**

  - [ ] **NPWP Validation**
    ```php
    - Format: XX.XXX.XXX.X-XXX.XXX
    - Regex validation
    - Real-time format checking
    - Display formatted (with dots and dash)
    ```

  - [ ] **Payment Terms Dropdown**
    ```blade
    - Options:
      - Net 7 (Payment due in 7 days)
      - Net 30 (Payment due in 30 days)
      - Net 60 (Payment due in 60 days)
      - COD (Cash on Delivery)
      - CIA (Cash in Advance)
      - Custom (text input)
    ```

  - [ ] **Supplier Code Auto-generation**
    ```php
    - Pattern: SUP-{YYYYMMDD}-{SEQUENCE}
    - Example: SUP-20251129-001
    ```

  - [ ] **Prevent Delete if Active POs**
    ```blade
    - Modal: "Cannot delete. Supplier has X active/pending POs."
    - Option: "Complete all POs first"
    ```

**Output:**
- ✅ Full CRUD for Suppliers
- ✅ NPWP validation
- ✅ Payment terms management
- ✅ Purchase history tracking
- ✅ Supplier statistics
- ✅ Export to Excel

**Validation:**
- Create supplier → Code generated, NPWP validated
- Edit supplier → Changes saved
- Delete supplier with active POs → Prevented
- Show supplier → Statistics displayed
- Export → Excel downloaded

---

## ═══════════════════════════════════════════════════════
## TIER 3: ADVANCED MODULES (Phase 12-17)
## ═══════════════════════════════════════════════════════

### **PHASE 12: MODULE - PURCHASE ORDERS** (Hari 13-15)
**Status:** 🟡 PENDING
**Estimasi:** 14-16 jam
**Priority:** HIGH

#### Checklist:

- [ ] **Database Migrations**
  - [ ] **purchase_orders** table
    ```php
    - id, tenant_id (FK), store_id (FK)
    - supplier_id (FK)
    - po_number (unique per tenant)
    - po_date, expected_delivery_date
    - status (draft, submitted, approved, received, cancelled)
    - subtotal, tax_amount, total_amount
    - notes (text)
    - submitted_by_user_id (FK, nullable)
    - submitted_at (nullable)
    - approved_by_user_id (FK, nullable)
    - approved_at (nullable)
    - received_by_user_id (FK, nullable)
    - received_at (nullable)
    - timestamps, soft deletes
    ```

  - [ ] **purchase_order_items** table
    ```php
    - id, purchase_order_id (FK)
    - product_id (FK)
    - quantity, unit_price
    - subtotal (quantity × unit_price)
    - timestamps
    ```

- [ ] **Models**
  - [ ] **PurchaseOrder.php**
    ```php
    - BelongsTo: tenant, store, supplier
    - BelongsTo: submittedBy, approvedBy, receivedBy (User)
    - HasMany: items (purchase_order_items)
    - SoftDeletes
    - Global scopes: TenantScope, StoreScope
    - Accessors: statusBadge, canEdit, canSubmit, canApprove, canReceive
    - Methods: calculateTotal(), submit(), approve(), reject(), receive()
    ```

  - [ ] **PurchaseOrderItem.php**
    ```php
    - BelongsTo: purchaseOrder, product
    - Mutators: subtotal (auto-calculate)
    ```

- [ ] **Routes**
  ```php
  Admin Toko:
  - GET /purchases → index (store POs)
  - GET /purchases/create → create
  - POST /purchases → store
  - GET /purchases/{id} → show
  - GET /purchases/{id}/edit → edit (draft only)
  - PUT /purchases/{id} → update
  - DELETE /purchases/{id} → destroy
  - POST /purchases/{id}/submit → submit (for approval)
  - POST /purchases/{id}/receive → receive (mark as received, update stock)
  - GET /purchases/{id}/print → print (PDF)

  Tenant Owner:
  - GET /purchases → index (all tenant POs)
  - GET /purchases/{id} → show
  - POST /purchases/{id}/approve → approve
  - POST /purchases/{id}/reject → reject
  ```

- [ ] **PurchaseOrderRepository**
  - [ ] **app/Repositories/PurchaseOrderRepository.php**
    ```php
    - getByStore($storeId, $perPage, $filters)
    - getByTenant($tenantId, $perPage, $filters)
    - getPending($storeId) // submitted, waiting approval
    - create($data, $items)
    - update($id, $data, $items)
    - delete($id)
    - submit($id, $userId)
    - approve($id, $userId)
    - reject($id, $userId, $reason)
    - receive($id, $userId)
    - generatePONumber($tenantId)
    ```

- [ ] **PurchaseOrderService**
  - [ ] **app/Services/PurchaseOrderService.php**
    ```php
    - createPO($data, $items)
      → Generate PO number
      → Create PO header
      → Create PO items
      → Calculate totals

    - updatePO($id, $data, $items)
      → Only if status = draft
      → Update header
      → Delete old items, create new items
      → Recalculate totals

    - deletePO($id)
      → Only if status = draft
      → Soft delete

    - submitPO($id, $userId)
      → Change status: draft → submitted
      → Set submitted_by, submitted_at
      → Send notification to Tenant Owner

    - approvePO($id, $userId)
      → Change status: submitted → approved
      → Set approved_by, approved_at
      → Send notification to requester

    - rejectPO($id, $userId, $reason)
      → Change status: submitted → rejected (or back to draft)
      → Set rejected_by, rejected_at, rejection_reason
      → Send notification to requester

    - receivePO($id, $userId)
      → Change status: approved → received
      → Set received_by, received_at
      → Update stock for each item:
        → Create stock movement (type: IN)
        → Increase stock.quantity
      → Send notification
    ```

- [ ] **PurchaseOrderRequest**
  - [ ] **app/Http/Requests/PurchaseOrderRequest.php**
    ```php
    - supplier_id: required, exists:suppliers
    - po_date: required, date
    - expected_delivery_date: required, date, after:po_date
    - notes: nullable, string
    - items: required, array, min:1
    - items.*.product_id: required, exists:products
    - items.*.quantity: required, integer, min:1
    - items.*.unit_price: required, numeric, min:0
    ```

- [ ] **PurchaseOrderController**
  - [ ] **app/Http/Controllers/PurchaseOrderController.php**
    ```php
    - index()
      → Based on role:
        Admin Toko: store POs
        Tenant Owner: all tenant POs
      → Search: PO number, supplier
      → Filter: status, date range
      → Sort: po_date desc
      → Pagination

    - create()
      → Get suppliers dropdown
      → Get products dropdown (with current stock)
      → Return form

    - store(PurchaseOrderRequest $request)
      → Validate
      → Call PurchaseOrderService->createPO()
      → Flash success
      → Redirect to purchases.index

    - show($id)
      → Get PO with items, supplier, creator
      → Show status timeline
      → Show approval info
      → Print button

    - edit($id)
      → Check status = draft
      → Get PO with items
      → Get suppliers, products
      → Return form

    - update($id, PurchaseOrderRequest $request)
      → Check status = draft
      → Call PurchaseOrderService->updatePO()
      → Flash success
      → Redirect

    - destroy($id)
      → Check status = draft
      → Call PurchaseOrderService->deletePO()
      → Flash success
      → Redirect

    - submit($id)
      → Check status = draft
      → Call PurchaseOrderService->submitPO()
      → Flash success: "PO submitted for approval"
      → Redirect

    - approve($id)
      → Check permission (Tenant Owner)
      → Check status = submitted
      → Call PurchaseOrderService->approvePO()
      → Flash success
      → Redirect

    - reject($id, Request $request)
      → Check permission
      → Validate rejection_reason
      → Call PurchaseOrderService->rejectPO()
      → Flash success
      → Redirect

    - receive($id)
      → Check permission (Admin Toko)
      → Check status = approved
      → Call PurchaseOrderService->receivePO()
      → Flash success: "PO received. Stock updated."
      → Redirect

    - print($id)
      → Get PO with items
      → Generate PDF (supplier copy)
      → Download/Display
    ```

- [ ] **Views**

  - [ ] **resources/views/purchases/index.blade.php**
    ```blade
    - Page title: "Purchase Orders"
    - Search bar (PO number, supplier)
    - Filters: Status, Date Range
    - Button: "Create PO" (Admin Toko)

    - Table:
      → PO Number
      → Date
      → Supplier
      → Expected Delivery
      → Status (badge: Draft/Submitted/Approved/Received/Cancelled)
      → Total Amount
      → Created By
      → Actions (View, Edit, Submit, Approve, Reject, Receive, Print, Delete)

    - Status badges:
      → Draft: Gray
      → Submitted: Yellow
      → Approved: Blue
      → Received: Green
      → Cancelled: Red

    - Pagination
    ```

  - [ ] **resources/views/purchases/create.blade.php**
    ```blade
    - Form:
      → Supplier (select dropdown with search)
      → PO Date (datepicker)
      → Expected Delivery Date (datepicker)
      → Notes (textarea)

      → Items Table (dynamic rows):
        | Product | Quantity | Unit Price | Subtotal | Actions |
        | Select  | Input    | Input      | Auto     | Remove  |

        Button: "Add Product" (add row)

      → Summary:
        Subtotal: Rp X,XXX,XXX
        Tax (optional): Rp X,XXX
        Total: Rp X,XXX,XXX

    - Buttons:
      → Save as Draft
      → Save & Submit for Approval
      → Cancel

    - Alpine.js for:
      → Dynamic rows
      → Auto-calculate subtotal per row
      → Auto-calculate total
      → Product search/select
    ```

  - [ ] **resources/views/purchases/edit.blade.php**
    ```blade
    - Same as create but:
      → Pre-filled data
      → Show PO number (readonly)
      → Only if status = Draft
      → Otherwise: "Cannot edit. Status: {status}"
    ```

  - [ ] **resources/views/purchases/show.blade.php**
    ```blade
    - PO Header Card:
      → PO Number
      → Status (large badge)
      → Supplier Name
      → PO Date, Expected Delivery Date
      → Notes

    - Status Timeline:
      → Created: {date} by {user}
      → Submitted: {date} by {user} (if submitted)
      → Approved: {date} by {user} (if approved)
      → Received: {date} by {user} (if received)
      → Rejected: {date} by {user} - Reason: {reason} (if rejected)

    - Items Table:
      → Product Name
      → SKU
      → Quantity
      → Unit Price
      → Subtotal
      → Total row

    - Summary:
      → Subtotal
      → Tax
      → Total Amount

    - Action Buttons (conditional):
      → Edit (if draft)
      → Submit for Approval (if draft, Admin Toko)
      → Approve (if submitted, Tenant Owner)
      → Reject (if submitted, Tenant Owner)
      → Receive (if approved, Admin Toko)
      → Print PDF
      → Delete (if draft)

    - Approval Modal (for Tenant Owner):
      → Confirm: "Approve this PO?"
      → Button: Approve

    - Reject Modal:
      → Reason (textarea, required)
      → Button: Reject

    - Receive Modal:
      → Confirmation: "Mark as received? Stock will be updated."
      → Items preview (what will be added to stock)
      → Button: Confirm Receipt
    ```

  - [ ] **resources/views/purchases/print.blade.php** (PDF layout)
    ```blade
    - Header: Company Logo, Name, Address
    - Title: "PURCHASE ORDER"
    - PO Number, Date
    - Supplier Details:
      → Name, Address, Phone, Email
    - Items Table:
      → No, Product, SKU, Qty, Unit Price, Subtotal
    - Total Amount
    - Payment Terms
    - Authorized Signature
    - Footer: Terms & Conditions
    ```

- [ ] **Additional Features**

  - [ ] **PO Number Auto-generation**
    ```php
    - Pattern: PO-{YYYYMMDD}-{SEQUENCE}
    - Example: PO-20251129-001
    - Unique per tenant
    - Auto-increment sequence daily
    ```

  - [ ] **Delivery Date Validation**
    ```javascript
    - Cannot be before PO date
    - Warning if > 30 days from PO date
    ```

  - [ ] **Dynamic Items Table**
    ```javascript
    - Alpine.js component
    - Add row button
    - Remove row button (X icon)
    - Product select with search (Select2 or Alpine.js)
    - Show current stock when product selected
    - Auto-calculate subtotal on qty/price change
    - Auto-calculate total
    ```

  - [ ] **Payment Terms Display**
    ```blade
    - Get from supplier.payment_terms
    - Display on PO form
    - Calculate due date: PO date + payment terms days
    - Example: Net 30 → Due: 2025-12-29
    ```

  - [ ] **Print PO PDF**
    ```php
    - Use Laravel DomPDF or Snappy
    - Supplier copy layout
    - Download or email to supplier
    ```

  - [ ] **Approval Workflow**
    ```php
    - Draft → Submit (Admin Toko)
    - Submitted → Approve/Reject (Tenant Owner)
    - Approved → Receive (Admin Toko) → Stock updated
    ```

  - [ ] **Stock Update on Receive**
    ```php
    - For each PO item:
      → Get stock record (product_id, store_id)
      → Increase quantity
      → Create stock_movement (type: IN, reference: PO)
      → Update product.last_updated_at
    ```

  - [ ] **Notification System**
    ```php
    - On submit: Email to Tenant Owner
    - On approve: Email to requester (Admin Toko)
    - On reject: Email to requester with reason
    - On receive: Email confirmation to Tenant Owner
    ```

**Output:**
- ✅ Full CRUD for Purchase Orders
- ✅ PO number auto-generation
- ✅ Multi-item PO with dynamic rows
- ✅ Approval workflow (Draft → Submit → Approve → Receive)
- ✅ Stock update on receive
- ✅ Print PO to PDF
- ✅ Payment terms from supplier
- ✅ Email notifications
- ✅ Status timeline

**Validation:**
- Create PO → Saved as draft
- Submit PO → Status changed, email sent
- Approve PO → Status changed, email sent
- Receive PO → Stock updated, movements logged
- Reject PO → Reason saved, email sent
- Print PO → PDF generated
- Edit PO → Only draft can be edited
- Delete PO → Only draft can be deleted

---

### **PHASE 13: MODULE - STOCK OPNAME** (Hari 15-16)
**Status:** 🟡 PENDING
**Estimasi:** 10-12 jam
**Priority:** HIGH

#### Checklist:

- [ ] **Database Migrations**
  - [ ] **stock_opnames** table
    ```php
    - id, tenant_id (FK), store_id (FK)
    - opname_number (unique per tenant)
    - opname_date
    - status (draft, submitted, approved, finalized)
    - total_variance_value (calculated)
    - notes (text)
    - created_by_user_id (FK)
    - submitted_by_user_id (FK, nullable)
    - submitted_at (nullable)
    - approved_by_user_id (FK, nullable)
    - approved_at (nullable)
    - finalized_at (nullable)
    - timestamps, soft deletes
    ```

  - [ ] **stock_opname_items** table
    ```php
    - id, stock_opname_id (FK)
    - product_id (FK)
    - system_quantity (from stocks table)
    - physical_quantity (counted)
    - variance (physical - system)
    - variance_percentage
    - variance_reason (if threshold exceeded)
    - timestamps
    ```

- [ ] **Models**
  - [ ] **StockOpname.php**
    ```php
    - BelongsTo: tenant, store, createdBy, submittedBy, approvedBy
    - HasMany: items
    - SoftDeletes
    - Global scopes: TenantScope, StoreScope
    - Accessors: totalVariance, variancePercentage, statusBadge
    - Methods: submit(), approve(), reject(), finalize()
    ```

  - [ ] **StockOpnameItem.php**
    ```php
    - BelongsTo: stockOpname, product
    - Mutators: variance (auto-calculate)
    - Accessors: needsReason (if variance > threshold)
    ```

- [ ] **Routes**
  ```php
  Admin Toko:
  - GET /inventory/opname → index
  - GET /inventory/opname/create → create
  - POST /inventory/opname → store
  - GET /inventory/opname/{id} → show
  - GET /inventory/opname/{id}/edit → edit (draft only)
  - PUT /inventory/opname/{id} → update
  - DELETE /inventory/opname/{id} → destroy (draft only)
  - POST /inventory/opname/{id}/submit → submit
  - POST /inventory/opname/{id}/finalize → finalize (approved only)
  - GET /inventory/opname/{id}/print → print

  Tenant Owner:
  - GET /inventory/opname → index (all stores)
  - GET /inventory/opname/{id} → show
  - POST /inventory/opname/{id}/approve → approve
  - POST /inventory/opname/{id}/reject → reject
  ```

- [ ] **StockOpnameRepository**
  - [ ] **app/Repositories/StockOpnameRepository.php**
    ```php
    - getByStore($storeId, $perPage, $filters)
    - getByTenant($tenantId, $perPage, $filters)
    - getPending($storeId)
    - create($data, $items)
    - update($id, $data, $items)
    - delete($id)
    - submit($id, $userId)
    - approve($id, $userId)
    - reject($id, $userId, $reason)
    - finalize($id)
    - generateOpnameNumber($tenantId)
    ```

- [ ] **StockOpnameService**
  - [ ] **app/Services/StockOpnameService.php**
    ```php
    - createOpname($data, $items)
      → Generate opname number
      → Get current system quantities
      → Create opname with items
      → Calculate variances

    - updateOpname($id, $data, $items)
      → Only if status = draft
      → Recalculate variances

    - submitOpname($id, $userId)
      → Validate: all items have physical count
      → If variance > threshold: require reason
      → Change status: draft → submitted
      → Notify Tenant Owner

    - approveOpname($id, $userId)
      → Change status: submitted → approved
      → Notify requester

    - rejectOpname($id, $userId, $reason)
      → Change status: submitted → rejected
      → Notify requester

    - finalizeOpname($id)
      → Change status: approved → finalized
      → Update stocks based on physical count
      → Create stock movements (type: OPNAME)
      → Update last_stock_opname_date
    ```

- [ ] **StockOpnameRequest**
  - [ ] **app/Http/Requests/StockOpnameRequest.php**
    ```php
    - opname_date: required, date
    - notes: nullable, string
    - items: required, array, min:1
    - items.*.product_id: required, exists:products
    - items.*.physical_quantity: required, integer, min:0
    - items.*.variance_reason: required_if (variance > threshold)
    ```

- [ ] **StockOpnameController**
  - [ ] **app/Http/Controllers/Inventory/StockOpnameController.php**
    ```php
    - index()
      → Search: opname number
      → Filter: status, date range
      → Pagination

    - create()
      → Auto-populate all products with current stock
      → Return form

    - store(StockOpnameRequest $request)
      → Call StockOpnameService->createOpname()
      → Flash success
      → Redirect

    - show($id)
      → Get opname with items
      → Show variance analysis
      → Timeline

    - edit($id)
      → Only if draft
      → Get opname with items
      → Return form

    - update($id, StockOpnameRequest $request)
      → Call StockOpnameService->updateOpname()
      → Flash success
      → Redirect

    - submit($id)
      → Call StockOpnameService->submitOpname()
      → Flash success
      → Redirect

    - approve($id)
      → Call StockOpnameService->approveOpname()
      → Flash success
      → Redirect

    - reject($id, Request $request)
      → Validate reason
      → Call StockOpnameService->rejectOpname()
      → Flash success
      → Redirect

    - finalize($id)
      → Call StockOpnameService->finalizeOpname()
      → Flash success: "Stock updated"
      → Redirect

    - print($id)
      → Generate PDF report
      → Download
    ```

- [ ] **Views**

  - [ ] **resources/views/inventory/opname/index.blade.php**
    ```blade
    - Page title: "Stock Opname"
    - Search, Filters (status, date range)
    - Button: "Create Stock Opname"
    - Table: Opname Number, Date, Status, Total Variance, Actions
    - Pagination
    ```

  - [ ] **resources/views/inventory/opname/create.blade.php**
    ```blade
    - Form:
      → Opname Date (datepicker)
      → Notes (textarea)
      → Button: "Generate from Current Stock" (auto-populate all products)

      → Items Table:
        | Product | SKU | System Qty | Physical Qty | Variance | Variance % | Reason | Actions |

        - System Qty: readonly (from database)
        - Physical Qty: editable input
        - Variance: auto-calculated (red if negative, green if positive)
        - Variance %: auto-calculated
        - Reason: shown if |variance| > 5% (threshold)

    - Summary:
      → Total Products: X
      → Items with Variance: X
      → Total Variance Value: Rp X,XXX

    - Buttons: Save as Draft, Save & Submit
    ```

  - [ ] **resources/views/inventory/opname/show.blade.php**
    ```blade
    - Opname Info Card:
      → Opname Number, Date, Status
      → Created By, Submitted By, Approved By
      → Notes

    - Variance Summary Cards:
      → Total Variance Value
      → Items with Shortage
      → Items with Surplus
      → Items Requiring Reason

    - Items Table (full details)

    - Action Buttons:
      → Edit (if draft)
      → Submit (if draft)
      → Approve (if submitted, Tenant Owner)
      → Reject (if submitted, Tenant Owner)
      → Finalize (if approved, Admin Toko)
      → Print Report
    ```

- [ ] **Additional Features**

  - [ ] **Auto-populate Products**
    ```javascript
    - Button: "Generate from Current Stock"
    - AJAX call to fetch all products with current qty
    - Pre-fill system_quantity
    - Physical_quantity = 0 (to be filled)
    ```

  - [ ] **Variance Threshold**
    ```php
    - Setting: 5% variance threshold
    - If |variance| > 5%: reason required
    - Color code: Red if > threshold
    ```

  - [ ] **Variance Reason Dropdown**
    ```blade
    - Options:
      - Damaged/Broken
      - Expired
      - Theft/Stolen
      - Count Error
      - Other (custom input)
    ```

  - [ ] **Finalize Stock Update**
    ```php
    - For each item:
      → Update stocks.quantity = physical_quantity
      → Create stock_movement (type: OPNAME, variance)
      → Update stocks.last_stock_opname_date = opname_date
    ```

**Output:**
- ✅ Full CRUD for Stock Opname
- ✅ Auto-populate from current stock
- ✅ Variance calculation & threshold
- ✅ Approval workflow
- ✅ Stock update on finalize
- ✅ Print opname report

**Validation:**
- Create opname → Products populated
- Submit opname → Variance validated, reason required if needed
- Approve opname → Status changed
- Finalize opname → Stock updated, movements logged
- Print → PDF generated

---

### **PHASE 14: MODULE - STOCK ADJUSTMENT & UNPACKING** (Hari 16-17)
**Status:** 🟡 PENDING
**Estimasi:** 10-12 jam
**Priority:** MEDIUM-HIGH

#### Checklist:

- [ ] **Database Migrations**

  - [ ] **stock_adjustments** table
    ```php
    - id, tenant_id (FK), store_id (FK)
    - adjustment_number (unique per tenant)
    - adjustment_date
    - product_id (FK)
    - adjustment_type (add/reduce)
    - quantity
    - reason (damaged, expired, lost, found, correction, other)
    - reason_notes (text)
    - status (draft, submitted, approved, applied)
    - created_by_user_id (FK)
    - approved_by_user_id (FK, nullable)
    - approved_at (nullable)
    - timestamps, soft deletes
    ```

  - [ ] **unpacking_transactions** table
    ```php
    - id, tenant_id (FK), store_id (FK)
    - unpacking_number (unique per tenant)
    - unpacking_date
    - source_product_id (FK - e.g., 1 box)
    - source_quantity (e.g., 1)
    - result_product_id (FK - e.g., units)
    - result_quantity (e.g., 12)
    - conversion_ratio (result_quantity / source_quantity)
    - status (draft, submitted, approved, processed)
    - notes (text)
    - created_by_user_id (FK)
    - approved_by_user_id (FK, nullable)
    - approved_at (nullable)
    - timestamps, soft deletes
    ```

- [ ] **Models**
  - [ ] **StockAdjustment.php**
  - [ ] **UnpackingTransaction.php**

- [ ] **Stock Adjustment Module**

  - [ ] **Routes**
    ```php
    - GET /inventory/adjustment → index
    - GET /inventory/adjustment/create → create
    - POST /inventory/adjustment → store
    - GET /inventory/adjustment/{id} → show
    - POST /inventory/adjustment/{id}/submit → submit
    - POST /inventory/adjustment/{id}/approve → approve (Tenant Owner)
    - POST /inventory/adjustment/{id}/reject → reject
    ```

  - [ ] **StockAdjustmentController**
    ```php
    - index() → List all adjustments
    - create() → Form (select product, type, quantity, reason)
    - store() → Create as draft
    - submit() → Submit for approval
    - approve() → Approve & apply stock adjustment
    - reject() → Reject with reason
    ```

  - [ ] **Views**
    - [ ] **inventory/adjustment/index.blade.php**
    - [ ] **inventory/adjustment/create.blade.php**
      ```blade
      - Product (select)
      - Current Stock (readonly, from database)
      - Adjustment Type (radio: Add / Reduce)
      - Quantity (input, number)
      - Reason (dropdown: Damaged, Expired, Lost, Found, Correction, Other)
      - Reason Notes (textarea, required if Other)
      - New Stock (calculated: current ± quantity)
      ```
    - [ ] **inventory/adjustment/show.blade.php**

  - [ ] **Apply Adjustment Logic**
    ```php
    - If approved:
      → If type = add: stock.quantity += adjustment.quantity
      → If type = reduce: stock.quantity -= adjustment.quantity
      → Create stock_movement (type: ADJ)
      → Change status: approved → applied
    ```

- [ ] **Unpacking Module**

  - [ ] **Routes**
    ```php
    - GET /inventory/unpacking → index
    - GET /inventory/unpacking/create → create
    - POST /inventory/unpacking → store
    - GET /inventory/unpacking/{id} → show
    - POST /inventory/unpacking/{id}/submit → submit
    - POST /inventory/unpacking/{id}/approve → approve
    - POST /inventory/unpacking/{id}/process → process (update stocks)
    ```

  - [ ] **UnpackingController**
    ```php
    - create() → Form
    - store() → Create draft
    - submit() → Submit for approval
    - approve() → Approve
    - process() → Execute unpacking (reduce source, add result)
    ```

  - [ ] **Views**
    - [ ] **inventory/unpacking/create.blade.php**
      ```blade
      - Source Product (select - e.g., "Coca Cola Box 24pcs")
      - Source Quantity (input - e.g., 1)
      - Current Source Stock (readonly)
      - Result Product (select - e.g., "Coca Cola Can 330ml")
      - Result Quantity (input - e.g., 24)
      - Conversion Ratio (calculated - e.g., 1:24)
      - Notes (textarea)
      - Preview:
        "1 box will be removed from stock"
        "24 cans will be added to stock"
      ```

  - [ ] **Process Unpacking Logic**
    ```php
    - If approved & processed:
      → Reduce source_product stock by source_quantity
      → Add result_product stock by result_quantity
      → Create 2 stock_movements:
        1. Type: OUT (source product, reference: unpacking)
        2. Type: IN (result product, reference: unpacking)
      → Change status: approved → processed
    ```

**Output:**
- ✅ Stock Adjustment module (add/reduce with approval)
- ✅ Unpacking module (box to units conversion)
- ✅ Approval workflows
- ✅ Stock movements logged

**Validation:**
- Adjustment approved → Stock updated
- Unpacking processed → Source reduced, result added
- Movements logged correctly

---

### **PHASE 15: MODULE - POS TRANSACTIONS & STORE SESSIONS** (Hari 17-19)
**Status:** 🟡 PENDING
**Estimasi:** 16-18 jam
**Priority:** CRITICAL

#### Checklist:

- [ ] **Database Migrations**

  - [ ] **store_sessions** table
    ```php
    - id, store_id (FK)
    - cashier_id (FK - user_id)
    - register_id (FK, nullable)
    - session_number (unique per store)
    - session_date
    - opening_cash
    - closing_cash (nullable)
    - expected_cash (calculated)
    - actual_cash (nullable)
    - variance (actual - expected)
    - variance_reason (text, nullable)
    - status (open, closed, pending_approval, approved)
    - opened_at
    - closed_at (nullable)
    - approved_by_user_id (FK, nullable)
    - approved_at (nullable)
    - timestamps
    ```

  - [ ] **cash_registers** table
    ```php
    - id, store_id (FK)
    - register_name (Register 1, Register 2, etc.)
    - register_code (unique per store)
    - is_active
    - timestamps
    ```

  - [ ] **transactions** table
    ```php
    - id, tenant_id (FK), store_id (FK)
    - session_id (FK - store_sessions)
    - cashier_id (FK - user_id)
    - transaction_number (unique per store)
    - transaction_date
    - customer_name (nullable)
    - customer_phone (nullable)
    - customer_id (FK, nullable)
    - subtotal
    - discount_amount
    - discount_percentage
    - tax_amount
    - total_amount
    - amount_paid
    - change_amount
    - payment_method (cash, card, transfer, ewallet, split)
    - status (completed, voided, pending, held)
    - voided_at (nullable)
    - voided_by_user_id (FK, nullable)
    - void_reason (nullable)
    - notes (nullable)
    - timestamps, soft deletes
    ```

  - [ ] **transaction_items** table
    ```php
    - id, transaction_id (FK)
    - product_id (FK)
    - quantity
    - unit_price (at time of sale)
    - discount_percentage (per item)
    - discount_amount (per item)
    - subtotal (quantity × unit_price - discount)
    - timestamps
    ```

  - [ ] **transaction_payments** table (for split payment)
    ```php
    - id, transaction_id (FK)
    - payment_method (cash, card, transfer, ewallet)
    - amount
    - reference_number (nullable - for card/transfer)
    - timestamps
    ```

  - [ ] **pending_transactions** table (for hold/resume)
    ```php
    - id, store_id (FK), cashier_id (FK)
    - hold_number
    - transaction_data (JSON - items, customer, etc.)
    - held_at
    - timestamps
    ```

- [ ] **Models**
  - [ ] **StoreSession.php**
  - [ ] **CashRegister.php**
  - [ ] **Transaction.php**
  - [ ] **TransactionItem.php**
  - [ ] **TransactionPayment.php**
  - [ ] **PendingTransaction.php**

- [ ] **Store Sessions Module**

  - [ ] **Routes**
    ```php
    Kasir:
    - GET /sessions → index (my sessions)
    - GET /sessions/open → openForm
    - POST /sessions/open → open (create new session)
    - GET /sessions/{id} → show
    - GET /sessions/{id}/close → closeForm
    - POST /sessions/{id}/close → close
    - GET /sessions/{id}/print → print

    Admin Toko:
    - GET /sessions/all → index (all store sessions)
    - POST /sessions/{id}/approve → approve (if variance)
    ```

  - [ ] **SessionController**
    ```php
    - openForm()
      → Check: no open session for this cashier
      → Select register
      → Enter opening cash
      → Return form

    - open(Request $request)
      → Validate opening_cash
      → Create session (status: open)
      → Redirect to POS

    - closeForm($id)
      → Get session
      → Calculate expected_cash:
        = opening_cash + (cash_sales - cash_refunds)
      → Return form

    - close($id, Request $request)
      → Validate actual_cash
      → Calculate variance
      → If variance ≠ 0: require reason
      → If variance > threshold: status = pending_approval
      → Else: status = closed
      → Notify Admin Toko if variance

    - approve($id)
      → Change status: pending_approval → approved
      → Notify cashier
    ```

  - [ ] **Views**
    - [ ] **sessions/open.blade.php**
      ```blade
      - Register (select dropdown)
      - Opening Cash (input, Rp)
      - Date & Time (auto)
      - Button: Open Session
      ```

    - [ ] **sessions/close.blade.php**
      ```blade
      - Session Info: Number, Opened At, Register
      - Opening Cash: Rp X,XXX (readonly)
      - Expected Cash: Rp X,XXX (calculated, readonly)
        = Opening + Cash Sales - Cash Refunds
      - Actual Cash: (input, Rp) - cashier counts
      - Variance: (auto-calculated, color: red/green)
      - Variance Reason: (textarea, required if variance ≠ 0)
      - Button: Close Session
      ```

    - [ ] **sessions/show.blade.php**
      ```blade
      - Session Details
      - Transactions List (all transactions in this session)
      - Summary: Total Sales, Cash, Card, Transfer, etc.
      - Print Report button
      ```

- [ ] **POS Transactions Module**

  - [ ] **Routes**
    ```php
    Kasir:
    - GET /pos → index (POS interface)
    - POST /pos/transaction → createTransaction
    - POST /pos/hold → holdTransaction
    - GET /pos/pending → viewPendingTransactions
    - POST /pos/resume/{id} → resumeTransaction
    - GET /pos/history → myTransactions
    - POST /pos/reprint/{id} → reprintReceipt
    ```

  - [ ] **POSController**
    ```php
    - index()
      → Check: cashier has open session
      → If no session: redirect to open session
      → Get products (active, with stock)
      → Return POS interface

    - createTransaction(Request $request)
      → Validate items, payment
      → Check stock availability
      → Calculate totals (subtotal, discount, tax, total)
      → Apply discount (check authorization if > limit)
      → Validate payment (amount_paid >= total)
      → Create transaction & items
      → Create stock movements (type: OUT)
      → Reduce stocks
      → If multi-payment: create transaction_payments
      → Generate receipt (print/email)
      → Return JSON: {transaction_id, receipt_url}

    - holdTransaction(Request $request)
      → Save current cart to pending_transactions
      → Return hold_number
      → Clear cart

    - resumeTransaction($id)
      → Get pending transaction
      → Load cart data
      → Delete pending record
      → Return to POS
    ```

  - [ ] **Views**

    - [ ] **pos/index.blade.php** (Main POS Interface)
      ```blade
      Layout: 2 columns

      LEFT SIDE (60%): Product Selection
      - Search bar (by name, SKU, barcode)
      - Category filter tabs
      - Product grid/list:
        → Product image (thumbnail)
        → Name
        → SKU / Barcode
        → Price
        → Stock qty
        → "Add to Cart" button
      - Barcode scanner input (auto-submit)

      RIGHT SIDE (40%): Cart & Checkout
      - Session info: Cashier, Register, Session Number
      - Customer info (optional):
        → Search by phone
        → Name, Phone inputs
        → Loyalty points (if exists)

      - Cart Items Table:
        | Product | Qty | Price | Disc% | Subtotal | Remove |
        - Editable Qty
        - Editable Discount% (with auth check)
        - Auto-calculate subtotal

      - Summary:
        Subtotal: Rp X,XXX
        Discount: Rp X,XXX
        Tax (11%): Rp X,XXX
        Total: Rp X,XXX,XXX

      - Payment Section:
        → Payment Method (tabs: Cash, Card, Transfer, E-wallet, Split)
        → Amount Paid (input)
        → Change (auto-calculate, large text)

      - Action Buttons:
        → Hold Transaction (save for later)
        → Clear Cart
        → Charge (submit payment)

      - Quick Actions:
        → View Pending Transactions
        → View Transaction History
        → Close Session
      ```

    - [ ] **pos/receipt.blade.php** (Receipt Layout)
      ```blade
      - Store Logo & Name
      - Store Address, Phone
      - Transaction Number, Date, Time
      - Cashier Name
      - Session Number
      - Customer Name (if provided)
      - Items Table:
        | Product | Qty | Price | Total |
      - Subtotal
      - Discount
      - Tax (PPN 11%)
      - Total
      - Payment Method
      - Amount Paid
      - Change
      - Footer: "Thank you for shopping!"
      - Barcode (transaction number)
      ```

    - [ ] **pos/pending.blade.php**
      ```blade
      - List of held transactions
      - Hold Number, Date, Time, Items Count, Total
      - Actions: Resume, Delete
      ```

- [ ] **Additional Features**

  - [ ] **Barcode Scanning**
    ```javascript
    - Auto-focus barcode input
    - On enter: search product by barcode
    - If found: add to cart
    - If not found: show error
    ```

  - [ ] **Discount Authorization**
    ```php
    - Setting: max_discount_per_item, max_discount_per_transaction
    - If discount > limit:
      → Show modal: "Manager PIN required"
      → Validate PIN
      → If valid: allow discount
      → If invalid: reject
    ```

  - [ ] **Split Payment**
    ```blade
    - Tab: Split Payment
    - Add payment method button
    - Multiple rows:
      | Method | Amount | Reference | Remove |
    - Total must equal transaction total
    - Validate before submit
    ```

  - [ ] **Auto-print Receipt**
    ```php
    - If store_settings.auto_print_receipt = true
    - Auto-open print dialog after transaction
    - Print to thermal printer (ESC/POS commands)
    ```

  - [ ] **Email Receipt**
    ```php
    - If customer email provided
    - Button: "Email Receipt"
    - Send receipt PDF via email
    ```

  - [ ] **Stock Reduction**
    ```php
    - For each item:
      → Check stock availability
      → If stock < qty: error "Insufficient stock"
      → Reduce stock.quantity
      → Create stock_movement (type: OUT, reference: transaction)
    ```

  - [ ] **Transaction Number Format**
    ```php
    - Pattern: TRX-{STORE_CODE}-{YYYYMMDD}-{SEQUENCE}
    - Example: TRX-001-20251129-0042
    - Auto-increment daily per store
    ```

**Output:**
- ✅ Store Sessions (open/close with cash reconciliation)
- ✅ Cash Registers management
- ✅ POS Interface (product selection, cart, checkout)
- ✅ Barcode scanning
- ✅ Multiple payment methods & split payment
- ✅ Hold/Resume transactions
- ✅ Receipt printing & email
- ✅ Discount authorization
- ✅ Stock reduction on sale
- ✅ Session variance tracking

**Validation:**
- Open session → Can access POS
- No open session → Cannot access POS
- Create transaction → Stock reduced, receipt generated
- Hold transaction → Saved, can resume
- Close session → Variance calculated
- Split payment → Total validated
- Barcode scan → Product added to cart

---

### **PHASE 16: MODULE - VOID MANAGEMENT & CUSTOMERS** (Hari 19-20)
**Status:** 🟡 PENDING
**Estimasi:** 8-10 jam
**Priority:** MEDIUM-HIGH

#### Checklist:

- [ ] **Database Migrations**

  - [ ] **transaction_voids** table
    ```php
    - id, transaction_id (FK)
    - requested_by_user_id (FK)
    - requested_at
    - void_reason (dropdown + notes)
    - void_notes (text)
    - status (pending, approved, rejected)
    - approved_by_user_id (FK, nullable)
    - approved_at (nullable)
    - rejection_reason (nullable)
    - timestamps
    ```

  - [ ] **customers** table
    ```php
    - id, tenant_id (FK)
    - name, phone (unique per tenant)
    - email (nullable)
    - address (nullable)
    - date_of_birth (nullable)
    - loyalty_points (integer, default: 0)
    - is_active
    - timestamps, soft deletes
    ```

  - [ ] **customer_transactions** (optional - or use transactions.customer_id)

- [ ] **Void Management Module**

  - [ ] **Routes**
    ```php
    Kasir:
    - POST /pos/void-request/{transactionId} → requestVoid

    Admin Toko & Tenant Owner:
    - GET /voids → index (pending void requests)
    - GET /voids/{id} → show
    - POST /voids/{id}/approve → approveVoid
    - POST /voids/{id}/reject → rejectVoid
    ```

  - [ ] **VoidController**
    ```php
    - requestVoid($transactionId, Request $request)
      → Validate void_reason
      → Create transaction_void (status: pending)
      → Send notification to Admin Toko
      → Return success

    - index()
      → Get pending void requests
      → Filter by status, date range
      → Pagination

    - approveVoid($id)
      → Check permission
      → Update transaction.status = voided
      → Update transaction_void.status = approved
      → Restore stock (reverse OUT movements)
      → Create stock_movements (type: IN, reference: void)
      → Refund to session cash (if cash payment)
      → Send notification to requester
      → Flash success

    - rejectVoid($id, Request $request)
      → Validate rejection_reason
      → Update transaction_void.status = rejected
      → Send notification to requester
      → Flash success
    ```

  - [ ] **Views**
    - [ ] **voids/index.blade.php**
      ```blade
      - Page title: "Void Requests"
      - Filter: Status (Pending/Approved/Rejected), Date Range
      - Table:
        → Transaction Number
        → Date & Time
        → Cashier
        → Amount
        → Void Reason
        → Requested By
        → Status (badge)
        → Actions (View, Approve, Reject)
      - Pagination
      ```

    - [ ] **voids/show.blade.php**
      ```blade
      - Void Request Info:
        → Transaction Number (link to transaction)
        → Requested By, Requested At
        → Void Reason
        → Void Notes

      - Transaction Details:
        → Items, Amounts, Payment Method
        → Customer Info

      - Action Buttons (if pending):
        → Approve Void
        → Reject Void (modal with reason)
      ```

  - [ ] **Restore Stock Logic**
    ```php
    - When void approved:
      → For each transaction_item:
        → Add quantity back to stock
        → Create stock_movement (type: IN, reference: void)
    ```

- [ ] **Customers Module**

  - [ ] **Routes**
    ```php
    Kasir & Admin Toko:
    - GET /customers → index
    - GET /customers/search → searchByPhone (AJAX)
    - POST /customers → store (quick create)
    - GET /customers/{id} → show
    - GET /customers/{id}/edit → edit
    - PUT /customers/{id} → update
    - GET /customers/{id}/history → transactionHistory
    ```

  - [ ] **CustomerController**
    ```php
    - index()
      → Search: name, phone
      → Pagination

    - searchByPhone(Request $request)
      → AJAX endpoint
      → Search by phone
      → Return JSON: {customer data}

    - store(Request $request)
      → Quick create (name, phone required)
      → Return JSON: {customer_id, name, phone}

    - show($id)
      → Get customer with stats
      → Total purchases, last purchase, loyalty points

    - transactionHistory($id)
      → Get all transactions for customer
      → Pagination
    ```

  - [ ] **Views**
    - [ ] **customers/index.blade.php**
      ```blade
      - Search bar (name, phone)
      - Button: "Add Customer"
      - Table:
        → Name
        → Phone
        → Email
        → Total Purchases
        → Loyalty Points
        → Last Purchase
        → Actions (View, Edit)
      ```

    - [ ] **customers/show.blade.php**
      ```blade
      - Customer Info Card
      - Statistics: Total Purchases, Average Transaction, Loyalty Points
      - Transaction History Table (last 10)
      - Button: View Full History
      ```

  - [ ] **POS Customer Lookup**
    ```blade
    - In POS interface:
      → Customer Phone input
      → On blur: AJAX search
      → If found: auto-fill name, show loyalty points
      → If not found: button "Add New Customer" (modal)
    ```

**Output:**
- ✅ Void Management (request, approve, reject)
- ✅ Stock restoration on void
- ✅ Notification system
- ✅ Customer Management (CRUD)
- ✅ Customer lookup in POS
- ✅ Transaction history per customer
- ✅ Loyalty points tracking (basic)

**Validation:**
- Request void → Notification sent
- Approve void → Stock restored, transaction voided
- Reject void → Notification sent
- Customer lookup → Found and populated
- Add customer from POS → Created successfully

---

### **PHASE 17: MODULE - REPORTS & DASHBOARDS** (Hari 20-22)
**Status:** 🟡 PENDING
**Estimasi:** 14-16 jam
**Priority:** MEDIUM

#### Checklist:

- [ ] **Reports Module**

  - [ ] **Routes**
    ```php
    All Roles (permission-based):
    - GET /reports → index (dashboard of all reports)
    - GET /reports/sales → salesReport
    - GET /reports/inventory → inventoryReport
    - GET /reports/financial → financialReport (Tenant Owner)
    - GET /reports/cashier → cashierReport
    - GET /reports/export → export (Excel/PDF/CSV)
    ```

  - [ ] **ReportController**
    - [ ] **app/Http/Controllers/ReportController.php**
      ```php
      - salesReport(Request $request)
        → Filters: date_range, store, product, category, cashier
        → Data:
          - Total Sales
          - Sales by Day/Week/Month
          - Sales by Product (top sellers)
          - Sales by Category
          - Sales by Payment Method
          - Sales by Cashier
        → Charts: Line (trend), Bar (comparison), Pie (breakdown)
        → Export options

      - inventoryReport(Request $request)
        → Filters: store, category, stock_level
        → Data:
          - Current Stock Levels
          - Low Stock Items
          - Overstock Items
          - Stock Movements (IN/OUT)
          - Stock Value (qty × price)
        → Export options

      - financialReport(Request $request)
        → Filters: date_range
        → Data:
          - Revenue Summary
          - Profit & Loss
          - Cash Flow
          - Outstanding Payments (POs)
          - Tax Summary
        → Charts: Financial trends
        → Export options

      - cashierReport(Request $request)
        → Filters: date_range, cashier
        → Data:
          - Sales per Cashier
          - Avg Transaction Value
          - Transaction Count
          - Void Transactions
          - Session Variance History
        → Export options

      - export(Request $request)
        → Validate: report_type, format (excel/pdf/csv)
        → Generate file
        → Download
      ```

  - [ ] **Views**
    - [ ] **reports/index.blade.php**
      ```blade
      - Report Categories (cards):
        → Sales Reports
        → Inventory Reports
        → Financial Reports
        → Cashier Reports
        → System Reports (Super Admin)
      ```

    - [ ] **reports/sales.blade.php**
      ```blade
      - Filters:
        → Date Range (presets + custom)
        → Store (multi-select)
        → Product/Category
        → Cashier

      - Summary Cards:
        → Total Sales
        → Total Transactions
        → Avg Transaction Value
        → Total Discount Given

      - Charts:
        → Line Chart: Sales Trend (daily)
        → Bar Chart: Sales by Store
        → Pie Chart: Sales by Category
        → Bar Chart: Top 10 Products

      - Data Table:
        → Date, Store, Cashier, Transactions, Amount
        → Sortable columns
        → Pagination

      - Export Buttons:
        → Export to Excel
        → Export to PDF
        → Export to CSV
      ```

    - [ ] **reports/inventory.blade.php**
    - [ ] **reports/financial.blade.php**
    - [ ] **reports/cashier.blade.php**

- [ ] **Chart.js Integration**
  - [ ] Include Chart.js via CDN
  - [ ] Create reusable chart components
  - [ ] **Line Chart** (sales trend)
    ```javascript
    - X-axis: Dates
    - Y-axis: Sales Amount
    - Multiple datasets (compare stores)
    ```
  - [ ] **Bar Chart** (store comparison, product comparison)
  - [ ] **Pie Chart** (category breakdown, payment method distribution)
  - [ ] **Donut Chart** (alternative to pie)

- [ ] **Export Functionality**
  - [ ] **Excel Export**
    ```php
    - Use Laravel Excel (maatwebsite/excel)
    - Generate .xlsx file
    - Include charts (optional)
    - Download
    ```
  - [ ] **PDF Export**
    ```php
    - Use Laravel DomPDF or Snappy
    - Format report layout
    - Include charts as images
    - Download
    ```
  - [ ] **CSV Export**
    ```php
    - Generate CSV file
    - Include data table only (no charts)
    - Download
    ```

- [ ] **Dashboard Module** (4 Roles)

  - [ ] **Administrator SaaS Dashboard**
    - [ ] **routes/web.php**: `GET /admin/dashboard`
    - [ ] **DashboardController@admin**
    - [ ] **admin/dashboard.blade.php**
      ```blade
      - Statistics Cards:
        → Total Tenants (active/trial/expired)
        → Total Stores (all tenants)
        → Total Users
        → System Resources (CPU, Memory, Disk)

      - Charts:
        → Line: New Tenants (monthly, last 12 months)
        → Bar: Tenants by Subscription Plan
        → Pie: Users by Role

      - System Health Widgets:
        → Server Status (green/red indicator)
        → Database Status
        → Queue Status (pending jobs)
        → Cache Status (hit rate)
        → Error Log (last 10 errors, link to full log)

      - Activity Timeline:
        → Recent critical activities
        → Tenant activations/deactivations
        → User creations
        → System changes

      - Quick Actions:
        → Create Tenant
        → View All Tenants
        → System Settings
        → Backups
      ```

  - [ ] **Tenant Owner Dashboard**
    - [ ] **routes/web.php**: `GET /dashboard`
    - [ ] **DashboardController@tenant**
    - [ ] **tenant/dashboard.blade.php**
      ```blade
      - Statistics Cards:
        → Total Sales (today, this week, this month, YTD)
        → Total Transactions
        → Total Products
        → Total Stores

      - Charts:
        → Line: Sales Trend (last 30 days)
        → Bar: Sales by Store (this month)
        → Pie: Sales by Category
        → Donut: Payment Method Distribution

      - Store Performance Table:
        → Rank, Store Name, Sales (this month), Growth %
        → Link to store detail

      - Inventory Alerts:
        → Low Stock Items (count per store)
        → Out of Stock Items
        → Pending Purchase Orders

      - Key Metrics:
        → Revenue MTD/YTD
        → Profit Margin %
        → Avg Transaction Value
        → Customer Count

      - Quick Actions:
        → View Reports
        → Manage Stores
        → Manage Users
      ```

  - [ ] **Admin Toko Dashboard**
    - [ ] **routes/web.php**: `GET /dashboard`
    - [ ] **DashboardController@store**
    - [ ] **store/dashboard.blade.php**
      ```blade
      - Statistics Cards:
        → Today's Sales (current store)
        → Today's Transactions
        → Active Cashiers
        → Current Stock Value

      - Charts:
        → Bar: Hourly Sales (today)
        → Line: Sales Comparison (this week vs last week)
        → Bar: Top 10 Products (this month)

      - Store Performance:
        → This Month's Sales
        → Sales vs Target
        → Growth % (vs last month)

      - Active Cashiers Table:
        → Cashier Name
        → Session Status (open/closed)
        → Today's Sales
        → Transactions Count

      - Inventory Alerts:
        → Low Stock Items (count + list)
        → Pending Approvals (POs, Opname, Adjustments)

      - Quick Actions:
        → Open POS
        → View Sessions
        → Manage Inventory
      ```

  - [ ] **Kasir Dashboard**
    - [ ] **routes/web.php**: `GET /dashboard`
    - [ ] **DashboardController@cashier**
    - [ ] **cashier/dashboard.blade.php**
      ```blade
      - Today's Performance Cards:
        → My Sales (today)
        → My Transactions (today)
        → Avg Transaction Value
        → Customers Served

      - Current Session Info:
        → Session Number
        → Status (open/closed)
        → Opening Cash
        → Current Cash Balance
        → Transactions in Session

      - Quick Stats:
        → Top Product Sold (today)
        → Most Used Payment Method

      - Quick Actions (large buttons):
        → Open POS (if session open)
        → Open Session (if no session)
        → View Pending Transactions
        → Close Session
      ```

**Output:**
- ✅ Sales Reports (filters, charts, export)
- ✅ Inventory Reports
- ✅ Financial Reports (Tenant Owner)
- ✅ Cashier Reports
- ✅ Chart.js integration (4 chart types)
- ✅ Export to Excel/PDF/CSV
- ✅ 4 Role-specific Dashboards
- ✅ Real-time statistics
- ✅ Quick actions per role

**Validation:**
- Sales report → Data accurate, charts display
- Export → Files downloaded
- Dashboard (each role) → Statistics correct, charts render
- Filters → Reports update correctly

---

### **PHASE 18: NAVIGATION, MENU & SETTINGS** (Hari 22-23)
**Status:** 🟡 PENDING
**Estimasi:** 8-10 jam
**Priority:** MEDIUM

#### Checklist:

- [ ] **Menu Configuration**
  - [ ] **config/menus.php**
    ```php
    - Define all 132 sub-menus for 4 roles
    - Structure: [
        'label' => 'Menu Name',
        'route' => 'route.name',
        'icon' => 'heroicon-name',
        'permission' => 'permission.name',
        'badge' => null, // or count function
        'children' => [...] // sub-menus
      ]
    ```

  - [ ] **Administrator SaaS Menu (32 items)**
    ```php
    - Dashboard
    - Tenants Management
    - Subscription Management
    - Users Management (all)
    - Roles & Permissions
    - System Settings
    - Backups
    - Activity Logs
    - Reports (System, Subscription)
    ```

  - [ ] **Tenant Owner Menu (42 items)**
    ```php
    - Dashboard
    - Stores Management
    - Users Management (tenant)
    - Products Management
    - Categories Management
    - Suppliers Management
    - Purchase Orders (view all)
    - Inventory (view all stores)
    - Stock Opname (approvals)
    - Stock Adjustment (approvals)
    - Unpacking (approvals)
    - Customers Management
    - Reports (Sales, Inventory, Financial, Cashier)
    - Settings (tenant settings)
    ```

  - [ ] **Admin Toko Menu (38 items)**
    ```php
    - Dashboard
    - POS
    - Store Sessions
    - Transactions History
    - Void Requests
    - Users Management (store staff)
    - Products Management
    - Categories Management
    - Suppliers Management
    - Purchase Orders (create, view store)
    - Inventory Management
    - Stock Opname
    - Stock Adjustment
    - Unpacking
    - Customers Management
    - Cash Management
    - Reports (Sales, Inventory, Cashier)
    - Store Settings
    ```

  - [ ] **Kasir Menu (14 items)**
    ```php
    - Dashboard
    - POS (primary)
    - My Sessions
    - My Transactions
    - Pending Transactions (held)
    - Void Requests (my requests)
    - Customers (search, add)
    - Products (view only)
    - My Profile
    - Change Password
    - My Activity Log
    ```

- [ ] **MenuHelper Class**
  - [ ] **app/Helpers/MenuHelper.php**
    ```php
    - static function getMenuByRole($role)
      → Get menu config for role
      → Filter by permissions (user has permission)
      → Return menu array

    - static function isActiveRoute($route)
      → Check if current route matches menu route
      → Support wildcards (e.g., 'products.*')
      → Return true/false for highlighting

    - static function getBadgeCount($badge_function)
      → Execute badge count function
      → Return count (for notifications, pending approvals, etc.)
    ```

- [ ] **Sidebar Component Update**
  - [ ] **resources/views/components/sidebar.blade.php**
    ```blade
    - Get menu from MenuHelper::getMenuByRole(auth()->user()->roles)
    - Render menu recursively
    - Support nested sub-menus (collapsible)
    - Active state highlighting
    - Icons per menu item (Heroicons)
    - Badge indicators (e.g., "5" pending approvals)
    - User info at bottom (avatar, name, role)
    - Logout button
    - Mobile responsive (hamburger menu)
    ```

- [ ] **Breadcrumb Component Update**
  - [ ] **resources/views/components/breadcrumb.blade.php**
    ```blade
    - Auto-generate from route
    - Support manual override (pass $breadcrumbs array)
    - Format: Home > Section > Sub-section > Current Page
    - Last item not clickable (current page)
    - Mobile responsive
    ```

- [ ] **Settings Module**

  - [ ] **System Settings (Super Admin)**
    - [ ] **routes/web.php**: `GET /admin/settings`
    - [ ] **SettingsController@system**
    - [ ] **admin/settings/index.blade.php**
      ```blade
      Tabs:
      1. General
        → App Name, Logo Upload
        → Default Timezone, Locale
        → Date/Time Format

      2. Email
        → SMTP Host, Port, Username, Password, Encryption
        → From Email, From Name
        → Test Email button

      3. Notifications
        → Enable/Disable per type (checkboxes)
        → Email notifications, In-app notifications

      4. Security
        → Session Timeout (minutes)
        → Password Policy:
          - Min Length
          - Require Uppercase
          - Require Numbers
          - Require Symbols
          - Password Expiry Days
        → Two-Factor Authentication (enable/disable system-wide)

      5. Backups
        → Manual Backup button
        → Schedule (frequency, time)
        → Retention Policy (keep last X backups)
        → Storage Location (local/S3/FTP)
        → Backup History table
        → Download/Restore buttons

      Save button
      ```

  - [ ] **Store Settings (Admin Toko)**
    - Already implemented in Phase 8
    - Additional: Integration with global settings

- [ ] **Profile Management (All Users)**
  - [ ] **routes/web.php**: `GET /profile`
  - [ ] **ProfileController**
  - [ ] **profile/index.blade.php**
    ```blade
    - View My Profile:
      → Avatar (upload/change)
      → Name, Email, Phone
      → Role, Tenant, Store
      → Account Status

    - Edit Profile:
      → Name, Email (limited), Phone
      → Avatar upload

    - Change Password:
      → Current Password (required)
      → New Password (with strength meter)
      → Confirm Password
      → Password Strength Indicator (weak/medium/strong)

    - Activity Log:
      → My login history (last 30 days)
      → IP addresses
      → Devices (browser, OS)
      → Last active

    - Sessions:
      → Active sessions list
      → Button: "Logout All Other Sessions"
    ```

**Output:**
- ✅ Complete menu structure (132 sub-menus for 4 roles)
- ✅ Dynamic sidebar with permissions
- ✅ Active menu highlighting
- ✅ Breadcrumb auto-generation
- ✅ Badge indicators (notifications)
- ✅ System Settings (full configuration)
- ✅ Profile Management (all users)
- ✅ Password strength indicator
- ✅ Activity log & sessions

**Validation:**
- Login as each role → Correct menus displayed
- Click menu → Route accessible (permission checked)
- Active menu → Highlighted correctly
- Breadcrumb → Generated correctly
- Settings → Saved successfully
- Profile → Updated successfully

---

### **PHASE 19: POLISH & REFINEMENT** (Hari 23-24)
**Status:** 🟡 PENDING
**Estimasi:** 10-12 jam
**Priority:** MEDIUM

#### Checklist:

- [ ] **UI/UX Improvements**
  - [ ] **Consistent Spacing & Typography**
    - [ ] Review all pages for consistent padding/margins
    - [ ] Standardize heading sizes (h1, h2, h3)
    - [ ] Consistent button sizes and styles
    - [ ] Uniform card layouts

  - [ ] **Loading States**
    - [ ] Add loading spinners for AJAX calls
    - [ ] Skeleton loaders for data tables
    - [ ] Disable buttons during submission (prevent double-click)
    - [ ] Alpine.js loading states (x-show, x-transition)

  - [ ] **Form Validation Feedback**
    - [ ] Real-time validation (Alpine.js)
    - [ ] Inline error messages (red text below input)
    - [ ] Success indicators (green checkmark)
    - [ ] Required field indicators (*)

  - [ ] **Better Error Messages**
    - [ ] User-friendly messages (not technical)
    - [ ] Actionable messages ("Try again" vs "Error 500")
    - [ ] Toast notifications (top-right corner)
    - [ ] Error summary at top of form

  - [ ] **Confirmation Modals**
    - [ ] Delete confirmations (all delete actions)
    - [ ] Submit confirmations (important actions)
    - [ ] Approval confirmations
    - [ ] Modal with "Are you sure?" message
    - [ ] Red "Delete" button, Gray "Cancel" button

  - [ ] **Empty States**
    - [ ] "No records found" messages
    - [ ] Icon + message + action button
    - [ ] Example: "No products yet. Create your first product!"
    - [ ] Friendly illustrations (optional)

  - [ ] **Responsive Design**
    - [ ] Test on mobile (320px, 375px, 414px)
    - [ ] Test on tablet (768px, 1024px)
    - [ ] Test on desktop (1280px, 1920px)
    - [ ] Hamburger menu on mobile
    - [ ] Collapsible sidebar on tablet
    - [ ] Responsive tables (horizontal scroll or card layout)

- [ ] **Performance Optimization**
  - [ ] **Database Optimization**
    - [ ] Add indexes:
      ```sql
      - tenants: (slug), (is_active)
      - stores: (tenant_id, is_active), (code)
      - users: (tenant_id, is_active), (store_id)
      - products: (tenant_id, is_active), (sku), (barcode)
      - stocks: (product_id, store_id)
      - transactions: (store_id, transaction_date), (transaction_number)
      - purchase_orders: (store_id, status), (po_number)
      ```
    - [ ] Composite indexes where needed
    - [ ] Index foreign keys

  - [ ] **Query Optimization**
    - [ ] Eager loading (avoid N+1 queries)
      ```php
      - $users->with('tenant', 'store', 'roles')
      - $products->with('category', 'stocks.store')
      - $transactions->with('items.product', 'cashier')
      ```
    - [ ] Select only needed columns
      ```php
      - ->select('id', 'name', 'email')
      ```
    - [ ] Use chunk for large datasets
    - [ ] Paginate results (15-30 per page)

  - [ ] **Caching**
    - [ ] Cache menu configuration
      ```php
      - Cache::remember('menus.role.' . $role, 3600, fn() => ...)
      ```
    - [ ] Cache dashboard statistics (5-15 minutes)
    - [ ] Cache settings (system, store)
    - [ ] Clear cache on updates

  - [ ] **Asset Optimization**
    - [ ] Minify CSS: `npm run build`
    - [ ] Minify JS: `npm run build`
    - [ ] Combine CSS/JS files (Vite)
    - [ ] Use CDN for libraries (Alpine.js, Chart.js)
    - [ ] Image optimization (compress, resize)
    - [ ] Lazy load images

- [ ] **Security Hardening**
  - [ ] **CSRF Protection**
    - [ ] Verify @csrf tokens in all forms
    - [ ] AJAX requests include CSRF token
    - [ ] Laravel default protection enabled

  - [ ] **XSS Prevention**
    - [ ] Blade auto-escaping: {{ $var }}
    - [ ] Use {!! $var !!} only for trusted HTML
    - [ ] Sanitize user input

  - [ ] **SQL Injection Prevention**
    - [ ] Use Eloquent (parameterized queries)
    - [ ] Avoid raw queries
    - [ ] If raw: use bindings

  - [ ] **Permission Checks**
    - [ ] Verify all routes have middleware
    - [ ] Check permissions in controllers
    - [ ] Hide UI elements based on permissions
      ```blade
      @can('users.create')
        <button>Add User</button>
      @endcan
      ```

  - [ ] **Input Validation**
    - [ ] Server-side validation (FormRequest)
    - [ ] Validate all inputs (never trust user input)
    - [ ] Sanitize file uploads
    - [ ] Limit file sizes

  - [ ] **Rate Limiting**
    - [ ] Apply to login route (prevent brute force)
    - [ ] Apply to API routes (if any)
    - [ ] Laravel throttle middleware

- [ ] **Testing**
  - [ ] **Manual Testing Checklist**
    - [ ] Test all CRUD operations per module
    - [ ] Test as each role (4 roles)
    - [ ] Test permissions (try to access unauthorized pages)
    - [ ] Test workflows (PO: draft → submit → approve → receive)
    - [ ] Test POS (create transaction, hold, resume, void)
    - [ ] Test sessions (open, close, variance)
    - [ ] Test reports (filters, charts, export)
    - [ ] Test responsive design (mobile, tablet, desktop)

  - [ ] **Error Scenarios**
    - [ ] Test validation errors (empty fields, invalid data)
    - [ ] Test insufficient stock (POS)
    - [ ] Test duplicate entries (SKU, email, slug)
    - [ ] Test delete with dependencies (category has products)
    - [ ] Test permission denied (403 error)
    - [ ] Test not found (404 error)

  - [ ] **Browser Compatibility**
    - [ ] Chrome (latest)
    - [ ] Firefox (latest)
    - [ ] Safari (latest)
    - [ ] Edge (latest)
    - [ ] Mobile Safari (iOS)
    - [ ] Mobile Chrome (Android)

- [ ] **Documentation Updates**
  - [ ] Update README.md
    - [ ] Project description
    - [ ] Features list
    - [ ] Installation steps
    - [ ] Environment setup
    - [ ] Seeder instructions
    - [ ] Default credentials

  - [ ] Create USER-GUIDE.md (optional)
    - [ ] Login instructions
    - [ ] How to use POS
    - [ ] How to manage products
    - [ ] How to approve POs
    - [ ] FAQ

**Output:**
- ✅ Polished UI/UX (consistent, responsive, loading states)
- ✅ Optimized performance (queries, caching, assets)
- ✅ Hardened security (CSRF, XSS, SQL injection, permissions)
- ✅ Comprehensive manual testing
- ✅ Browser compatibility verified
- ✅ Documentation updated

**Validation:**
- All pages load smoothly (no lag)
- No N+1 query issues (check debug bar)
- All security checks pass
- Manual testing checklist completed
- Documentation clear and helpful

---

### **PHASE 20: DEPLOYMENT PREPARATION** (Hari 24-25)
**Status:** 🟡 PENDING
**Estimasi:** 6-8 jam
**Priority:** HIGH

#### Checklist:

- [ ] **Environment Configuration**
  - [ ] Create `.env.production` template
    ```env
    APP_NAME="KASIR-5 POS"
    APP_ENV=production
    APP_DEBUG=false
    APP_URL=https://yourdomain.com

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=kasir5_production
    DB_USERNAME=kasir5_user
    DB_PASSWORD=STRONG_PASSWORD_HERE

    MAIL_MAILER=smtp
    MAIL_HOST=smtp.gmail.com
    MAIL_PORT=587
    MAIL_USERNAME=your-email@gmail.com
    MAIL_PASSWORD=your-app-password
    MAIL_ENCRYPTION=tls
    MAIL_FROM_ADDRESS=noreply@yourdomain.com
    MAIL_FROM_NAME="${APP_NAME}"

    CACHE_DRIVER=file
    SESSION_DRIVER=file
    QUEUE_CONNECTION=database

    FILESYSTEM_DISK=public
    ```

  - [ ] Security settings:
    - [ ] Generate new APP_KEY: `php artisan key:generate`
    - [ ] Set APP_DEBUG=false
    - [ ] Set APP_ENV=production
    - [ ] Strong database password
    - [ ] Secure SMTP credentials

- [ ] **Production Database Setup**
  - [ ] Create production database
  - [ ] Import database schema: `php artisan migrate --force`
  - [ ] Run production seeders:
    ```bash
    php artisan db:seed --class=RoleSeeder
    php artisan db:seed --class=PermissionSeeder
    php artisan db:seed --class=RolePermissionSeeder
    # DO NOT run DummyDataSeeder in production
    ```
  - [ ] Create Super Admin manually:
    ```bash
    php artisan tinker
    >>> $user = User::create([
        'name' => 'Super Admin',
        'email' => 'admin@yourdomain.com',
        'password' => bcrypt('STRONG_PASSWORD'),
        'is_active' => true
    ]);
    >>> $user->assignRole('Administrator SaaS');
    ```

- [ ] **File Permissions**
  - [ ] Set correct permissions:
    ```bash
    chmod -R 755 storage/
    chmod -R 755 bootstrap/cache/
    chown -R www-data:www-data storage/
    chown -R www-data:www-data bootstrap/cache/
    ```

- [ ] **Storage Link**
  - [ ] Create symlink: `php artisan storage:link`
  - [ ] Verify public/storage exists and points to storage/app/public

- [ ] **Asset Compilation**
  - [ ] Build for production: `npm run build`
  - [ ] Verify compiled assets in public/build/

- [ ] **Optimization Commands**
  - [ ] Cache configuration: `php artisan config:cache`
  - [ ] Cache routes: `php artisan route:cache`
  - [ ] Cache views: `php artisan view:cache`
  - [ ] Optimize autoload: `composer install --optimize-autoloader --no-dev`

- [ ] **Hostinger Deployment**
  - [ ] **Upload Files**
    - [ ] Compress project (exclude: node_modules, .git, .env, storage/logs/*)
    - [ ] Upload via FTP/SFTP or Git
    - [ ] Extract in public_html or subdirectory

  - [ ] **Configure Hostinger**
    - [ ] Set PHP version: 8.2 or higher
    - [ ] Create MySQL database via Hostinger panel
    - [ ] Update .env with Hostinger database credentials
    - [ ] Point domain to public/ folder (or create symlink)

  - [ ] **Public Folder Setup**
    - [ ] If domain points to public_html:
      ```
      - Move contents of public/ to public_html/
      - Update index.php paths to point to ../bootstrap/
      ```
    - [ ] Or create symlink:
      ```bash
      ln -s /path/to/project/public /home/user/public_html
      ```

  - [ ] **Verify Installation**
    - [ ] Visit domain URL → Laravel welcome page or login
    - [ ] Test login with Super Admin credentials
    - [ ] Test creating tenant, store, user
    - [ ] Test POS workflow
    - [ ] Test permissions

- [ ] **SSL Certificate**
  - [ ] Enable SSL via Hostinger panel (Let's Encrypt - free)
  - [ ] Update APP_URL in .env to https://
  - [ ] Force HTTPS:
    ```php
    // app/Providers/AppServiceProvider.php
    public function boot()
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
    ```

- [ ] **Backup Setup**
  - [ ] Setup automatic backups (Hostinger panel)
  - [ ] Or create cron job:
    ```bash
    # Daily database backup at 2 AM
    0 2 * * * /usr/bin/mysqldump -u username -p'password' database > /backup/db_$(date +\%Y\%m\%d).sql
    ```
  - [ ] Implement backup download in admin panel

- [ ] **Monitoring & Logging**
  - [ ] Configure error logging:
    ```env
    LOG_CHANNEL=daily
    LOG_LEVEL=error
    ```
  - [ ] Setup log rotation (keep last 14 days)
  - [ ] Monitor error logs: storage/logs/laravel.log
  - [ ] Setup email notifications for critical errors (optional)

- [ ] **Cron Jobs (Task Scheduler)**
  - [ ] Add cron job via Hostinger cPanel:
    ```bash
    * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
    ```
  - [ ] Define scheduled tasks in app/Console/Kernel.php:
    ```php
    protected function schedule(Schedule $schedule)
    {
        // Backup database daily at 2 AM
        $schedule->command('backup:database')->daily()->at('02:00');

        // Clean old logs weekly
        $schedule->command('log:clear')->weekly();

        // Check subscription expiry daily
        $schedule->command('subscription:check-expiry')->daily();
    }
    ```

- [ ] **Final Checklist**
  - [ ] All environment variables set correctly
  - [ ] Database migrations run successfully
  - [ ] Super Admin account created
  - [ ] Storage symlink created
  - [ ] Assets compiled and accessible
  - [ ] Caches optimized
  - [ ] SSL enabled (HTTPS)
  - [ ] Backups configured
  - [ ] Cron jobs setup
  - [ ] Error logging configured
  - [ ] Test full application workflow
  - [ ] Monitor for errors in first 24 hours

- [ ] **Post-Deployment**
  - [ ] Test all critical features in production
  - [ ] Monitor server resources (CPU, memory, disk)
  - [ ] Check error logs daily (first week)
  - [ ] Gather user feedback
  - [ ] Plan for future enhancements

**Output:**
- ✅ Application deployed to Hostinger
- ✅ Production database setup
- ✅ SSL enabled (HTTPS)
- ✅ Backups configured
- ✅ Monitoring & logging active
- ✅ All optimizations applied
- ✅ Ready for production use

**Validation:**
- Visit production URL → Application loads
- Login as Super Admin → Successful
- Create tenant → Successful
- Create store → Successful
- Create product → Successful
- Access POS → Works correctly
- Generate report → Works correctly
- SSL certificate → Valid
- Backups → Running

---

## 🎉 COMPLETION SUMMARY

**Total Phases:** 20 (Phase 0-20)
**Estimated Timeline:** 24-25 hari kerja (5-6 minggu dengan buffer)
**Total Features Implemented:** 225+ features

### Coverage Breakdown:
- ✅ **Phase 0-5:** Foundation (Database, Auth, UI Components)
- ✅ **Phase 6-11:** Core CRUD Modules (Users, Tenants, Stores, Categories, Products, Suppliers)
- ✅ **Phase 12-14:** Advanced Inventory (PO, Stock Opname, Adjustments, Unpacking)
- ✅ **Phase 15-16:** POS & Operations (Transactions, Sessions, Void, Customers)
- ✅ **Phase 17:** Reporting & Dashboards
- ✅ **Phase 18:** Navigation & Settings
- ✅ **Phase 19:** Polish & Testing
- ✅ **Phase 20:** Deployment

### All Gap Analysis Items Covered:
- ✅ 25 Major Modules
- ✅ 40 Advanced Features
- ✅ 60 UI/UX Components
- ✅ 30 Technical Infrastructure
- ✅ 20 Database Details
- ✅ 30 Business Rules
- ✅ 20 Integration Points

### Ready for:
- ✅ Production deployment
- ✅ Multi-tenant usage
- ✅ Real POS operations
- ✅ Inventory management
- ✅ Comprehensive reporting
- ✅ Role-based access control

---

## 📌 NEXT STEPS

**Setelah Plan Ini Selesai:**

1. **START IMPLEMENTATION**
   - Begin with Phase 0: Setup Laravel
   - Follow checklist systematically
   - Mark items as complete
   - Test after each phase

2. **TRACK PROGRESS**
   - Update phase status (PENDING → IN PROGRESS → COMPLETED)
   - Update progress bars
   - Document any issues encountered

3. **COMMUNICATION**
   - Report after each phase completion
   - Ask for clarification if needed
   - Show demo/screenshots when ready

---

**🚀 Ready to Start Development?**

**Document Version:** 2.0 (Complete)
**Created:** 2025-11-29
**Last Updated:** 2025-11-29
**Author:** Claude Code (Anthropic AI)
**Total Pages:** ~3500+ lines
**Status:** ✅ COMPLETE & COMPREHENSIVE
