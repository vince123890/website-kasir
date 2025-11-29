# MENU & SUB-MENU STRUCTURE - KASIR-5 POS SYSTEM

> **Dokumen Breakdown Menu-Submenu Per Role**
> Versi: 1.0
> Update: 2025-11-29
> Project: Kasir-5 Multi-Tenant POS SaaS Boilerplate

---

## DAFTAR ISI

1. [Overview Struktur Menu](#overview-struktur-menu)
2. [Role Definitions](#role-definitions)
3. [Menu Per Role](#menu-per-role)
   - [Administrator SaaS](#1-administrator-saas-super-admin)
   - [Tenant Owner](#2-tenant-owner-business-owner)
   - [Admin Toko](#3-admin-toko-store-manager)
   - [Kasir](#4-kasir-cashier)
4. [Permission Matrix](#permission-matrix)
5. [Fitur CRUD Per Module](#fitur-crud-per-module)
6. [Implementation Checklist](#implementation-checklist)

---

## OVERVIEW STRUKTUR MENU

### Hirarki Role & Access Level

```
┌─────────────────────────────────────────────────────────────┐
│  ADMINISTRATOR SaaS (Super Admin)                           │
│  ├─ Full access ke semua tenant & stores                    │
│  └─ Manage system, subscriptions, global settings           │
└─────────────────────────────────────────────────────────────┘
          │
          ├─────────────────────────────────────────────────┐
          │  TENANT OWNER (Business Owner)                  │
          │  ├─ Full access ke semua stores dalam tenant   │
          │  └─ Manage users, products, inventory          │
          └─────────────────────────────────────────────────┘
                    │
                    ├───────────────────────────────────────┐
                    │  ADMIN TOKO (Store Manager)           │
                    │  ├─ Access ke specific store          │
                    │  └─ Manage staff, inventory, POS      │
                    └───────────────────────────────────────┘
                              │
                              ├─────────────────────────────┐
                              │  KASIR (Cashier)            │
                              │  ├─ Access ke POS only      │
                              │  └─ Create transactions     │
                              └─────────────────────────────┘
```

---

## ROLE DEFINITIONS

### 1. Administrator SaaS (Super Admin)
- **Scope:** All tenants, all stores
- **Responsibilities:**
  - System management & configuration
  - Tenant & subscription management
  - Global user & role management
  - System monitoring & health checks
  - Backup & recovery
- **Access Level:** `all`

### 2. Tenant Owner (Business Owner)
- **Scope:** Specific tenant, all stores within tenant
- **Responsibilities:**
  - Store creation & management
  - Staff management (Admin Toko & Kasir)
  - Product & pricing management
  - Financial oversight
  - Business analytics
- **Access Level:** `tenant`

### 3. Admin Toko (Store Manager)
- **Scope:** Specific store
- **Responsibilities:**
  - Store operations management
  - Kasir management
  - Inventory control
  - POS transaction oversight
  - Store-level reporting
- **Access Level:** `store`

### 4. Kasir (Cashier)
- **Scope:** Specific store, own transactions
- **Responsibilities:**
  - POS transaction processing
  - Customer service
  - Cash handling
  - Personal sales reporting
- **Access Level:** `own`

---

## MENU PER ROLE

---

## 1. ADMINISTRATOR SaaS (Super Admin)

### Menu Structure

#### 📊 Dashboard
**Route:** `/admin/dashboard`
**Permission:** `dashboard.view.all`
**Description:** System-wide overview & monitoring

- **Overview Statistics**
  - Total tenants (active/inactive)
  - Total stores across all tenants
  - Total users & active sessions
  - System resource usage (CPU, Memory, Disk)
  - Recent activity logs

- **System Health**
  - Server status monitoring
  - Database performance
  - Queue status
  - Cache status
  - Error log summary

- **Activity Logs**
  - Recent system activities
  - Login attempts (successful/failed)
  - Critical operations log
  - Audit trail

**CRUD Operations:** View only
**Filters:** Date range, activity type

---

#### 👥 System Management

##### 👤 Users
**Route:** `/admin/users`
**Permission:** `users.view.all`, `users.create.all`, `users.update.all`, `users.delete.all`

- **List Users**
  - View all users across all tenants
  - Filter by: tenant, role, status, store
  - Search by: name, email, activation code
  - Pagination
  - Bulk actions: activate/deactivate, delete

- **Create User**
  - Form fields:
    - Name, Email, Password
    - Tenant selection (dropdown)
    - Store selection (filtered by tenant)
    - Role assignment
    - Is Active toggle
    - Generate activation code (optional)
  - Validation rules
  - Send activation email (optional)

- **Edit User**
  - Update user information
  - Change password
  - Reassign tenant/store
  - Change role
  - Activate/deactivate

- **Delete User**
  - Soft delete
  - Confirmation dialog
  - Cascade handling (reassign data)

- **Reset Password**
  - Generate temporary password
  - Send reset email
  - Force password change on next login

**CRUD Operations:** Full CRUD
**Related Modules:** Tenants, Stores, Roles
**Validation:** UserRequest
**Service:** UserService
**Repository:** UserRepository

---

##### 🔐 Roles & Permissions
**Route:** `/admin/roles`
**Permission:** `roles.view.all`, `roles.manage.all`

- **Manage Roles**
  - List all roles
  - Create new role
  - Edit role name & guard
  - Delete role (if not assigned)
  - View users count per role

- **Assign Permissions**
  - Permission tree view (grouped by module)
  - Checkbox selection
  - Bulk assign/revoke
  - Permission inheritance visualization
  - Preview role capabilities

- **View Permission Tree**
  - Hierarchical permission display
  - Filter by module
  - Search permissions
  - Color-coded by scope (all, tenant, store, own)

**CRUD Operations:** Full CRUD for roles, Assign permissions
**Related Modules:** Users, Permissions
**Package:** Spatie Laravel-Permission

---

##### ⚙️ Settings
**Route:** `/admin/settings`
**Permission:** `settings.manage.all`

- **System Settings**
  - Application name & logo
  - Default timezone & locale
  - Email configuration (SMTP)
  - Notification settings
  - Session timeout
  - Password policy
  - Two-factor authentication toggle

- **Backup & Recovery**
  - Create manual backup
  - Schedule automatic backups
  - Download backup files
  - Restore from backup
  - Backup history

**CRUD Operations:** Update settings
**Related Modules:** System, Email

---

#### 🏢 Tenant Management
**Route:** `/admin/tenants`
**Permission:** `tenants.view.all`, `tenants.create.all`, `tenants.update.all`, `tenants.delete.all`

- **List Tenants**
  - View all tenants
  - Display: ID, Name, Slug, Email, Phone, Status, Stores Count, Users Count
  - Filter by: status (active/inactive)
  - Search by: name, slug, email
  - Sort by: created date, name
  - Pagination

- **Create Tenant**
  - Form fields:
    - Name (auto-generate slug)
    - Email
    - Phone
    - Is Active (default: true)
  - Validation
  - Auto-create owner account (optional)

- **Edit Tenant**
  - Update tenant information
  - Slug modification (with warning)
  - Activate/deactivate
  - View associated stores
  - View users count

- **View Tenant Details**
  - Tenant information
  - List of stores
  - List of users
  - Subscription status
  - Activity history
  - Statistics (sales, transactions, products)

- **Activate/Deactivate**
  - Toggle tenant status
  - Cascade to stores & users
  - Confirmation dialog
  - Notification to owner

**CRUD Operations:** Full CRUD
**Related Modules:** Stores, Users, Subscriptions
**Validation:** TenantRequest
**Service:** TenantService
**Repository:** TenantRepository

---

#### 💳 Subscription Management
**Route:** `/admin/subscriptions`
**Permission:** `subscriptions.view.all`, `subscriptions.manage.all`, `subscriptions.approve.all`

- **View Subscriptions**
  - List all subscriptions
  - Display: Tenant, Plan, Status, Start Date, End Date, Amount
  - Filter by: status (active, pending, expired, cancelled)
  - Search by: tenant name
  - Sort by: expiry date, created date

- **Manage Plans**
  - List subscription plans
  - Create new plan
  - Edit plan details (name, price, features, duration)
  - Activate/deactivate plan
  - Set plan limits (stores, users, transactions)

- **Approve Subscriptions**
  - Approve pending subscriptions
  - Reject with reason
  - Extend subscription period
  - Manual upgrade/downgrade
  - Refund processing

- **Billing Management**
  - View invoices
  - Generate invoices
  - Payment history
  - Outstanding payments
  - Revenue reports

**CRUD Operations:** Full CRUD for plans, Approve/Manage subscriptions
**Related Modules:** Tenants, Payments, Invoices
**Service:** SubscriptionService

---

#### 📈 Reports (Admin Level)
**Route:** `/admin/reports`
**Permission:** `reports.subscription.view.all`, `reports.system.view.all`, `reports.financial.view.all`, `reports.export.all`

- **Subscription Reports**
  - Active subscriptions summary
  - New subscriptions (monthly)
  - Expired/cancelled subscriptions
  - Revenue by plan
  - Churn rate analysis

- **System Usage Reports**
  - Total transactions across all tenants
  - Peak usage times
  - Average transactions per tenant
  - Storage usage per tenant
  - API usage statistics

- **Financial Reports (All Tenants)**
  - Total revenue (all tenants)
  - Revenue by tenant
  - Payment method breakdown
  - Outstanding payments
  - Tax summaries

- **Export Data**
  - Export to Excel/CSV/PDF
  - Custom date range selection
  - Filter by tenant
  - Email reports to recipients
  - Schedule automated reports

**CRUD Operations:** View, Export
**Related Modules:** Tenants, Subscriptions, Transactions
**Export Formats:** Excel, CSV, PDF

---

### Administrator SaaS - Menu Summary

```
Dashboard
├── Overview Statistics
├── System Health
└── Activity Logs

System Management
├── Users
│   ├── List Users
│   ├── Create User
│   ├── Edit User
│   ├── Delete User
│   └── Reset Password
├── Roles & Permissions
│   ├── Manage Roles
│   ├── Assign Permissions
│   └── View Permission Tree
└── Settings
    ├── System Settings
    └── Backup & Recovery

Tenant Management
├── List Tenants
├── Create Tenant
├── Edit Tenant
├── View Tenant Details
└── Activate/Deactivate

Subscription Management
├── View Subscriptions
├── Manage Plans
├── Approve Subscriptions
└── Billing Management

Reports (Admin Level)
├── Subscription Reports
├── System Usage Reports
├── Financial Reports (All Tenants)
└── Export Data
```

**Total Menu Items:** 5 main menus, 32 sub-menus
**Permissions Required:** 25 unique permissions
**CRUD Modules:** Users, Tenants, Roles, Plans, Subscriptions

---

## 2. TENANT OWNER (Business Owner)

### Menu Structure

#### 📊 Dashboard
**Route:** `/dashboard`
**Permission:** `dashboard.view.tenant`
**Description:** Business overview for tenant owner

- **Sales Overview**
  - Today's sales (all stores)
  - This week sales
  - This month sales
  - Sales comparison (vs last period)
  - Top selling products (tenant-wide)
  - Sales by store (breakdown)

- **Store Performance**
  - Active stores count
  - Total transactions (all stores)
  - Average transaction value
  - Store rankings (by revenue)
  - Store status indicators

- **Inventory Status**
  - Low stock alerts (all stores)
  - Out of stock items
  - Total products count
  - Stock value (all stores)
  - Recent stock movements

- **Key Metrics**
  - Total revenue (MTD, YTD)
  - Active users (staff) count
  - Active cashiers today
  - Customer count
  - Gross profit margin

**CRUD Operations:** View only
**Filters:** Date range, store selection
**Charts:** Line charts, bar charts, pie charts

---

#### 🏪 Store Management
**Route:** `/stores`
**Permission:** `stores.view.tenant`, `stores.create.tenant`, `stores.update.tenant`, `stores.delete.tenant`

- **List Stores**
  - View all stores within tenant
  - Display: ID, Name, Slug, Address, Phone, Status, Users Count
  - Filter by: status (active/inactive)
  - Search by: name, address, phone
  - Sort by: created date, name
  - Pagination

- **Create Store**
  - Form fields:
    - Name (auto-generate slug)
    - Address (text area)
    - Phone
    - Is Active (default: true)
  - Validation
  - Auto-assign tenant_id (current tenant)

- **Edit Store**
  - Update store information
  - Slug modification (with warning)
  - Activate/deactivate
  - View assigned users
  - Operating hours management

- **Store Settings**

  - **Basic Info**
    - Name, address, phone
    - Logo upload
    - Operating hours (open/close time)
    - Time zone

  - **Tax Settings**
    - Enable/disable tax
    - Tax rate (%)
    - Tax name (e.g., "PPN", "VAT")
    - Tax calculation method (inclusive/exclusive)

  - **Pricing Rules**
    - Markup percentage (default)
    - Rounding rules
    - Discount rules
    - Promotion settings

- **Activate/Deactivate**
  - Toggle store status
  - Confirmation dialog
  - Cascade to users (optional)
  - Notification to store manager

**CRUD Operations:** Full CRUD
**Related Modules:** Users, Products, Inventory
**Validation:** StoreRequest
**Service:** StoreService
**Repository:** StoreRepository

---

#### 👥 Users & Staff
**Route:** `/users`
**Permission:** `users.view.tenant`, `users.create.tenant`, `users.update.tenant`, `users.delete.tenant`

- **List Users**
  - View all users within tenant
  - Display: Name, Email, Role, Store, Status, Last Login
  - Filter by: role (Admin Toko, Kasir), store, status
  - Search by: name, email
  - Sort by: created date, last login
  - Pagination

- **Create User (Invite Staff)**
  - Form fields:
    - Name, Email
    - Password (auto-generate or manual)
    - Store assignment (dropdown)
    - Role selection (Admin Toko or Kasir)
    - Is Active (default: true)
    - Send invitation email (checkbox)
  - Validation
  - Auto-assign tenant_id (current tenant)
  - Generate activation code

- **Edit User**
  - Update user information
  - Change password
  - Reassign store
  - Change role (within tenant roles)
  - Activate/deactivate
  - View activity log

- **Assign Roles**
  - Role: Admin Toko
  - Role: Kasir
  - Preview role permissions
  - Bulk role assignment

- **Reset Password**
  - Generate temporary password
  - Send reset email to user
  - Force password change on next login
  - Password history (optional)

- **Deactivate User**
  - Soft deactivate (toggle is_active)
  - Confirmation dialog
  - Reassign data (if Admin Toko)
  - Logout active sessions

**CRUD Operations:** Full CRUD
**Related Modules:** Stores, Roles
**Validation:** UserRequest
**Service:** UserService
**Repository:** UserRepository

---

#### 📦 Product Management

##### 🏷️ Categories
**Route:** `/categories`
**Permission:** `categories.view.tenant`, `categories.manage.tenant`

- **List Categories**
  - View all product categories
  - Display: ID, Name, Slug, Description, Status, Products Count
  - Filter by: status (active/inactive)
  - Search by: name, slug
  - Sort by: name, created date
  - Pagination

- **Create Category**
  - Form fields:
    - Name (auto-generate slug)
    - Description (optional)
    - Is Active (default: true)
  - Validation
  - Duplicate check

- **Edit/Delete Category**
  - Update category information
  - Slug modification (with warning)
  - Activate/deactivate
  - Delete (soft delete)
  - Check for associated products before delete

**CRUD Operations:** Full CRUD
**Related Modules:** Products
**Validation:** CategoryRequest
**Service:** CategoryService
**Repository:** CategoryRepository

---

##### 🛍️ Products
**Route:** `/products`
**Permission:** `products.view.tenant`, `products.create.tenant`, `products.update.tenant`, `products.delete.tenant`

- **List Products**
  - View all products (tenant-wide)
  - Display: SKU, Name, Category, Price (Tenant), Stock (all stores), Status
  - Filter by: category, status, stock level
  - Search by: SKU, name, barcode
  - Sort by: name, price, stock, created date
  - Pagination
  - Bulk actions: activate/deactivate, delete

- **Create Product**
  - Form fields:
    - SKU (auto-generate or manual)
    - Barcode (optional)
    - Name
    - Category (dropdown)
    - Description
    - Unit (pcs, box, kg, etc.)
    - Purchase Price (cost)
    - Selling Price (tenant default)
    - Min Stock (alert threshold)
    - Max Stock (optional)
    - Is Active (default: true)
    - Image upload (optional)
  - Validation
  - Duplicate SKU check

- **Edit Product**
  - Update product information
  - Price modification (affects all stores by default)
  - Stock adjustment link
  - View stock per store
  - Product history log

- **Upload Bulk Products**
  - Download Excel template
  - Upload Excel file (.xlsx, .csv)
  - Validation & preview
  - Bulk import (create/update)
  - Error report (invalid rows)

- **Set Prices (Tenant-wide)**
  - Tenant default price (master)
  - Bulk price update (by category, by filter)
  - Price increase/decrease (percentage or amount)
  - Price history log
  - Note: Store-specific pricing managed by Admin Toko

**CRUD Operations:** Full CRUD, Bulk import
**Related Modules:** Categories, Inventory, Stores
**Validation:** ProductRequest
**Service:** ProductService
**Repository:** ProductRepository

---

#### 📊 Inventory
**Route:** `/inventory`
**Permission:** `inventory.view.tenant`

- **Stock Overview**
  - View stock levels (all stores)
  - Display: Product, SKU, Store, Current Stock, Min Stock, Status
  - Filter by: store, category, stock status (low, normal, overstock)
  - Search by: product name, SKU
  - Sort by: stock level, value
  - Pagination
  - Low stock alerts (highlighted)
  - Export to Excel

- **Stock Opname**
  - Initiate stock opname (per store)
  - View stock opname history
  - Approve/reject stock opname (submitted by Admin Toko)
  - Variance report
  - Adjustment approval

- **Stock Adjustment**
  - View adjustment history (all stores)
  - Approve/reject adjustments (submitted by Admin Toko)
  - Adjustment reasons
  - Audit trail

- **Purchase Orders**
  - View all purchase orders (all stores)
  - PO status: Draft, Submitted, Approved, Received, Cancelled
  - Filter by: store, status, date
  - Approve/reject PO (submitted by Admin Toko)
  - PO details & items

- **Supplier Management**
  - List suppliers (tenant-wide)
  - Create/edit supplier
  - Supplier info: Name, Contact, Address, Phone, Email
  - View supplier history (POs, payments)
  - Supplier performance rating

- **Unpacking**
  - View unpacking transactions (all stores)
  - Approve/reject unpacking (submitted by Admin Toko)
  - Unpacking details: Source product (box), Result products (units)
  - Conversion rules

**CRUD Operations:** View, Approve/Reject
**Related Modules:** Products, Stores, Suppliers
**Service:** InventoryService

---

#### 💰 POS Operations
**Route:** `/pos`
**Permission:** `pos.view.tenant` (restricted, view-only)

- **Store Sessions (View)**
  - View all store sessions (all stores)
  - Display: Store, Cashier, Open Time, Close Time, Status
  - Filter by: store, date, cashier
  - Session details: Opening cash, closing cash, variance

- **Transaction Management**
  - View all transactions (all stores)
  - Display: Transaction ID, Store, Cashier, Date, Total, Payment Method, Status
  - Filter by: store, date, cashier, payment method, status
  - Search by: transaction ID, customer
  - Transaction details view
  - Void transactions (restricted, approval required)

- **Void Management (Restricted)**
  - View void requests (submitted by Admin Toko or Kasir)
  - Approve/reject void requests
  - Void reason required
  - Audit trail
  - Notification to requester

**CRUD Operations:** View only, Approve voids
**Related Modules:** Stores, Users, Transactions
**Service:** POSService

---

#### 📈 Reports (Tenant Level)
**Route:** `/reports`
**Permission:** `reports.sales.view.tenant`, `reports.inventory.view.tenant`, `reports.financial.view.tenant`, `reports.cashier.view.tenant`, `reports.export.tenant`

- **Sales Reports**
  - Sales summary (all stores)
  - Sales by store (comparison)
  - Sales by product (top sellers)
  - Sales by category
  - Sales by cashier
  - Sales by payment method
  - Sales trends (daily, weekly, monthly)
  - Date range filter
  - Export to Excel/PDF

- **Inventory Reports**
  - Stock level report (all stores)
  - Stock movement report
  - Stock opname variance report
  - Purchase order report
  - Unpacking report
  - Supplier report
  - Low stock alert report
  - Export to Excel/PDF

- **Financial Reports**
  - Revenue summary (all stores)
  - Profit & loss statement
  - Cash flow report
  - Payment method summary
  - Tax report
  - Outstanding payments
  - Financial trends
  - Export to Excel/PDF

- **Cashier Reports**
  - Cashier performance (all stores)
  - Sales per cashier
  - Average transaction value
  - Transaction count
  - Void transactions per cashier
  - Session summary per cashier
  - Export to Excel/PDF

- **Export Data**
  - Export to Excel (.xlsx)
  - Export to CSV
  - Export to PDF
  - Email report to recipients
  - Schedule automated reports (daily, weekly, monthly)

**CRUD Operations:** View, Export
**Related Modules:** Transactions, Products, Inventory, Users
**Export Formats:** Excel, CSV, PDF

---

### Tenant Owner - Menu Summary

```
Dashboard
├── Sales Overview
├── Store Performance
├── Inventory Status
└── Key Metrics

Store Management
├── List Stores
├── Create Store
├── Edit Store
├── Store Settings
│   ├── Basic Info
│   ├── Tax Settings
│   └── Pricing Rules
└── Activate/Deactivate

Users & Staff
├── List Users
├── Create User (Invite Staff)
├── Edit User
├── Assign Roles (Admin Toko, Kasir)
├── Reset Password
└── Deactivate User

Product Management
├── Categories
│   ├── List Categories
│   ├── Create Category
│   └── Edit/Delete Category
└── Products
    ├── List Products
    ├── Create Product
    ├── Edit Product
    ├── Upload Bulk Products
    └── Set Prices (Tenant-wide)

Inventory
├── Stock Overview
├── Stock Opname
├── Stock Adjustment
├── Purchase Orders
├── Supplier Management
└── Unpacking

POS Operations
├── Store Sessions (View)
├── Transaction Management
└── Void Management (Restricted)

Reports (Tenant Level)
├── Sales Reports
├── Inventory Reports
├── Financial Reports
├── Cashier Reports
└── Export Data
```

**Total Menu Items:** 7 main menus, 42 sub-menus
**Permissions Required:** 28 unique permissions
**CRUD Modules:** Stores, Users, Categories, Products, Suppliers

---

## 3. ADMIN TOKO (Store Manager)

### Menu Structure

#### 📊 Dashboard
**Route:** `/dashboard`
**Permission:** `dashboard.view.store`
**Description:** Store-specific overview

- **Today's Sales**
  - Total sales today (current store)
  - Transaction count today
  - Average transaction value
  - Sales vs target (if set)
  - Hourly sales trend (chart)

- **Store Performance**
  - Top 5 selling products today
  - Revenue vs yesterday
  - Revenue vs last week
  - Customer count today
  - New customers today

- **Active Cashiers**
  - List of cashiers on duty
  - Sales per cashier today
  - Active session count
  - Cashier performance indicators

- **Inventory Alerts**
  - Low stock products (current store)
  - Out of stock products
  - Stock value (current store)
  - Recent stock movements (last 7 days)
  - Pending purchase orders

**CRUD Operations:** View only
**Filters:** Date (today, yesterday, this week)
**Charts:** Line charts, bar charts, donut charts

---

#### 👥 Staff Management
**Route:** `/staff`
**Permission:** `users.view.store`, `users.create.store`, `users.update.store`, `users.delete.store`

- **List Staff**
  - View all staff in current store
  - Display: Name, Email, Role (Kasir), Status, Last Login
  - Filter by: status (active/inactive)
  - Search by: name, email
  - Sort by: name, last login
  - Pagination

- **Add Kasir**
  - Form fields:
    - Name, Email
    - Password (auto-generate or manual)
    - Is Active (default: true)
    - Send invitation email (checkbox)
  - Validation
  - Auto-assign: current store_id, tenant_id, role: Kasir
  - Generate activation code

- **Edit Staff Info**
  - Update staff information (name, email)
  - Change password
  - Activate/deactivate
  - View activity log
  - View sales history

- **Manage Permissions** (Limited)
  - View assigned permissions (Kasir)
  - No permission modification (managed by Tenant Owner)
  - Permission preview only

- **Reset Password**
  - Generate temporary password
  - Send reset email
  - Force password change on next login

- **Activate/Deactivate**
  - Toggle staff status
  - Confirmation dialog
  - Logout active sessions
  - Notification to staff

**CRUD Operations:** Full CRUD (limited to Kasir role, current store)
**Related Modules:** Roles (view only)
**Validation:** UserRequest
**Service:** UserService
**Repository:** UserRepository

---

#### 📦 Product Management

##### 🏷️ Categories
**Route:** `/categories`
**Permission:** `categories.view.store`, `categories.manage.store`

- **View Categories**
  - View all categories (tenant-wide, read-only)
  - Display: ID, Name, Description, Status, Products Count
  - Filter by: status
  - Search by: name
  - Sort by: name

- **Create/Edit Category**
  - Same as Tenant Owner
  - Available only if permission granted by Tenant Owner
  - Form fields: Name, Description, Is Active
  - Validation

- **Delete Category**
  - Soft delete
  - Check for associated products
  - Confirmation dialog

**CRUD Operations:** Full CRUD (if granted permission)
**Related Modules:** Products
**Validation:** CategoryRequest
**Service:** CategoryService

---

##### 🛍️ Products
**Route:** `/products`
**Permission:** `products.view.store`, `products.create.store`, `products.update.store`, `products.delete.store`

- **List Products**
  - View all products (current store perspective)
  - Display: SKU, Name, Category, Price (Store), Stock (Store), Status
  - Filter by: category, status, stock level
  - Search by: SKU, name, barcode
  - Sort by: name, price, stock
  - Pagination

- **Create/Edit Product**
  - Same as Tenant Owner (if permission granted)
  - Form fields: SKU, Name, Category, Description, Unit, Price, Min/Max Stock
  - Validation
  - Image upload

- **Set Store-specific Prices**
  - Override tenant default price
  - Store price management
  - Price history (store-specific)
  - Bulk price update (current store only)
  - Price comparison: Tenant Price vs Store Price

- **Manage Stock**
  - View stock level (current store only)
  - Stock adjustment link
  - Stock movement history (current store)
  - Transfer stock to other stores (if multi-store)

**CRUD Operations:** Full CRUD (current store), Price override
**Related Modules:** Categories, Inventory
**Validation:** ProductRequest
**Service:** ProductService

---

#### 📊 Inventory
**Route:** `/inventory`
**Permission:** `inventory.view.store`, `purchases.view.store`, `purchases.create.store`, `stock-opname.perform.store`, `stock-adjustment.perform.store`, `unpacking.perform.store`

- **Stock Level**
  - View stock (current store only)
  - Display: Product, SKU, Current Stock, Min Stock, Max Stock, Status
  - Filter by: category, stock status
  - Search by: product name, SKU
  - Sort by: stock level, value
  - Low stock alerts (highlighted)
  - Export to Excel

- **Stock Opname (Perform)**
  - Initiate stock opname
  - Enter physical count per product
  - System count vs Physical count
  - Variance calculation
  - Reason for variance (if significant)
  - Submit for approval (Tenant Owner)
  - Stock opname history

- **Stock Adjustment**
  - Create stock adjustment
  - Adjustment type: Add, Reduce
  - Adjustment reason: Damaged, Expired, Lost, Found, etc.
  - Quantity adjustment
  - Notes required
  - Submit for approval (Tenant Owner)
  - Adjustment history

- **Purchase Orders**
  - List purchase orders (current store)
  - Create new PO
    - Select supplier
    - Add products & quantities
    - Purchase price per product
    - PO total
    - Expected delivery date
    - Notes
  - Edit PO (if status: Draft)
  - Submit PO for approval (Tenant Owner)
  - Receive PO (update stock when goods arrive)
  - PO status tracking
  - PO history

- **Manage Suppliers**
  - View suppliers (tenant-wide)
  - Create supplier (if permission granted)
    - Supplier name, contact person
    - Address, phone, email
    - Payment terms
    - Tax ID (NPWP)
  - Edit supplier
  - View supplier history (POs, payments)

- **Unpacking Management**
  - Create unpacking transaction
    - Select source product (e.g., 1 box)
    - Define result products (e.g., 12 units)
    - Conversion ratio
    - Stock update (reduce box, add units)
  - Submit for approval (Tenant Owner)
  - Unpacking history

**CRUD Operations:** Full CRUD, Approval workflow
**Related Modules:** Products, Suppliers
**Service:** InventoryService, PurchaseService

---

#### 💰 POS Operations
**Route:** `/pos`
**Permission:** `pos.access.store`, `pos.transactions.create.store`, `pos.transactions.void.store`, `store-sessions.open.store`, `store-sessions.close.store`, `cash-reconciliation.view.store`

- **Open/Close Store Session**
  - **Open Session**
    - Cashier selects register/counter
    - Enter opening cash balance
    - Session start time (auto)
    - Confirmation
  - **Close Session**
    - Enter closing cash balance
    - Calculate: Expected cash, Actual cash, Variance
    - Variance reason (if exists)
    - Session end time (auto)
    - Print session report
    - Submit for review

- **View Transactions**
  - List all transactions (current store)
  - Display: Transaction ID, Cashier, Date/Time, Total, Payment Method, Status
  - Filter by: date, cashier, payment method, status
  - Search by: transaction ID
  - Transaction details view
  - Print receipt
  - Void transaction (with approval)

- **Manage Voids (Restrict/Approve)**
  - View void requests (current store)
  - Approve/reject void (submitted by Kasir)
  - Void reason required
  - Void approval workflow
  - Audit trail
  - Notification to Kasir

- **Void Reason Audit**
  - List all voids (current store)
  - Display: Transaction ID, Cashier, Void Reason, Approved By, Date
  - Filter by: date, cashier
  - Export void report

**CRUD Operations:** View, Create transaction, Void (with approval)
**Related Modules:** Users (Cashier), Products
**Service:** POSService, SessionService

---

#### 💵 Cash Management
**Route:** `/cash`
**Permission:** `cash-reconciliation.view.store`

- **Cash Reconciliation**
  - Daily cash reconciliation
  - Display: Opening Cash, Sales Cash, Expected Cash, Actual Cash, Variance
  - Variance analysis
  - Cashier-wise breakdown
  - Payment method breakdown (Cash, Card, Transfer)
  - Deposit slip management

- **Variance Reports**
  - Daily variance report
  - Variance trends (weekly, monthly)
  - Cashier variance analysis
  - Root cause tracking
  - Corrective actions

- **Register Management**
  - Register/Counter setup
  - Assign cashiers to registers
  - Register status (active/inactive)
  - Register transaction history

**CRUD Operations:** View, Reconcile
**Related Modules:** POS, Users
**Service:** CashService

---

#### 📈 Reports (Store Level)
**Route:** `/reports`
**Permission:** `reports.sales.view.store`, `reports.inventory.view.store`, `reports.cashier.view.store`, `reports.financial.view.store`, `reports.export.store`

- **Sales Reports**
  - Sales summary (current store)
  - Sales by product
  - Sales by category
  - Sales by cashier
  - Sales by payment method
  - Hourly sales report
  - Sales trends (daily, weekly, monthly)
  - Date range filter
  - Export to Excel/PDF

- **Inventory Reports**
  - Stock level report (current store)
  - Stock movement report
  - Stock opname report
  - Purchase order report
  - Stock adjustment report
  - Unpacking report
  - Low stock report
  - Export to Excel/PDF

- **Cashier Performance**
  - Sales per cashier
  - Transaction count per cashier
  - Average transaction value per cashier
  - Void transactions per cashier
  - Cashier session summary
  - Cashier variance report
  - Export to Excel/PDF

- **Financial Summary**
  - Revenue summary (current store)
  - Payment method breakdown
  - Cash vs non-cash sales
  - Tax collected
  - Discounts given
  - Net revenue
  - Export to Excel/PDF

- **Export Data**
  - Export to Excel (.xlsx)
  - Export to CSV
  - Export to PDF
  - Date range selection
  - Email report

**CRUD Operations:** View, Export
**Related Modules:** Transactions, Products, Inventory, Users
**Export Formats:** Excel, CSV, PDF

---

### Admin Toko - Menu Summary

```
Dashboard
├── Today's Sales
├── Store Performance
├── Active Cashiers
└── Inventory Alerts

Staff Management
├── List Staff
├── Add Kasir
├── Edit Staff Info
├── Manage Permissions (view only)
├── Reset Password
└── Activate/Deactivate

Product Management
├── Categories
│   ├── View Categories
│   ├── Create/Edit Category
│   └── Delete Category
└── Products
    ├── List Products
    ├── Create/Edit Product
    ├── Set Store-specific Prices
    └── Manage Stock

Inventory
├── Stock Level
├── Stock Opname (Perform)
├── Stock Adjustment
├── Purchase Orders
├── Manage Suppliers
└── Unpacking Management

POS Operations
├── Open/Close Store Session
├── View Transactions
├── Manage Voids (Restrict/Approve)
└── Void Reason Audit

Cash Management
├── Cash Reconciliation
├── Variance Reports
└── Register Management

Reports (Store Level)
├── Sales Reports
├── Inventory Reports
├── Cashier Performance
├── Financial Summary
└── Export Data
```

**Total Menu Items:** 7 main menus, 38 sub-menus
**Permissions Required:** 24 unique permissions
**CRUD Modules:** Users (Kasir), Products, Inventory, PO, Suppliers

---

## 4. KASIR (Cashier)

### Menu Structure

#### 📊 Dashboard
**Route:** `/dashboard`
**Permission:** `dashboard.view.store` (limited to own data)
**Description:** Personal sales dashboard

- **Today's Sales (Personal)**
  - My total sales today
  - My transaction count today
  - My average transaction value
  - Sales vs target (if set)
  - Hourly sales trend (personal)

- **Current Session**
  - Session status (Open/Closed)
  - Opening cash
  - Current sales (cash)
  - Expected cash balance
  - Session start time
  - Session duration

- **Quick Stats**
  - Total customers served today
  - Top selling products (my sales)
  - Payment method breakdown (my sales)
  - Last transaction time

**CRUD Operations:** View only (own data)
**Filters:** None (always today)

---

#### 💰 POS Operations
**Route:** `/pos`
**Permission:** `pos.access.store`, `pos.transactions.create.store`

- **New Transaction**
  - **Product Selection**
    - Search product (by name, SKU, barcode)
    - Scan barcode (if barcode scanner available)
    - Category filter
    - Product list/grid view
    - Add to cart

  - **Cart Management**
    - View cart items
    - Quantity adjustment (+/-)
    - Remove item
    - Apply discount (per item or total)
    - Discount authorization (if needed)
    - Cart total calculation

  - **Customer Information** (Optional)
    - Customer name
    - Customer phone
    - Customer loyalty ID (if implemented)

  - **Payment Processing**
    - Select payment method (Cash, Card, Transfer, E-Wallet)
    - Enter amount tendered (for cash)
    - Calculate change
    - Split payment (if allowed)
    - Process payment

  - **Transaction Complete**
    - Generate transaction ID
    - Print receipt (auto or manual)
    - Email receipt (if customer email provided)
    - Transaction success message
    - Clear cart

- **Transaction History (Own)**
  - View my transactions (today, this week, this month)
  - Display: Transaction ID, Date/Time, Total, Payment Method, Status
  - Filter by: date, payment method
  - Search by: transaction ID
  - Transaction details view
  - Reprint receipt
  - Request void (with reason, approval required)

- **Pending Orders**
  - View pending transactions (on-hold)
  - Resume transaction
  - Cancel transaction
  - Transaction notes

- **Customer Management** (Basic)
  - Search existing customer
  - Add new customer (name, phone, email)
  - View customer transaction history
  - Customer loyalty points (if implemented)

**CRUD Operations:** Create transaction, View own transactions
**Related Modules:** Products, Customers
**Service:** POSService

---

#### 📈 My Reports
**Route:** `/my-reports`
**Permission:** `reports.cashier.view.store` (own data only)

- **My Sales**
  - My daily sales summary
  - My weekly sales summary
  - My monthly sales summary
  - Sales by payment method
  - Sales by product (top sellers - my sales)
  - Transaction count
  - Average transaction value
  - Date range filter
  - Export to PDF

- **Cash Register Status**
  - Current session status
  - Opening cash balance
  - Current sales (cash)
  - Expected cash balance
  - Payment method breakdown
  - Session history (my sessions)

- **Transaction Details**
  - List of my transactions
  - Filter by: date, payment method, status
  - Search by: transaction ID
  - Transaction details view
  - Void transactions (my voids)
  - Void reason & approval status

**CRUD Operations:** View only (own data)
**Related Modules:** POS, Transactions
**Export Formats:** PDF only

---

#### 👤 Profile
**Route:** `/profile`
**Permission:** `users.view.own`, `users.update.own`

- **My Info**
  - View my profile
  - Display: Name, Email, Role, Store, Status, Last Login
  - Edit name, email (limited)
  - Profile photo upload (optional)

- **Change Password**
  - Current password verification
  - New password
  - Confirm new password
  - Password strength indicator
  - Password policy enforcement

- **Activity Log**
  - My login history
  - My activity log (last 30 days)
  - Device information
  - IP address
  - Logout all sessions (except current)

**CRUD Operations:** View, Update (own data only)
**Related Modules:** Users
**Service:** ProfileService

---

### Kasir - Menu Summary

```
Dashboard
├── Today's Sales (Personal)
├── Current Session
└── Quick Stats

POS Operations
├── New Transaction
│   ├── Product Selection
│   ├── Cart Management
│   ├── Customer Information
│   ├── Payment Processing
│   └── Transaction Complete
├── Transaction History (Own)
├── Pending Orders
└── Customer Management

My Reports
├── My Sales
├── Cash Register Status
└── Transaction Details

Profile
├── My Info
├── Change Password
└── Activity Log
```

**Total Menu Items:** 4 main menus, 14 sub-menus
**Permissions Required:** 7 unique permissions
**CRUD Modules:** Transactions (create), Profile (update)

---

## PERMISSION MATRIX

### Permission Naming Convention

**Format:** `module.action.scope`

- **Module:** users, stores, products, inventory, pos, reports, etc.
- **Action:** view, create, update, delete, manage, approve, export, etc.
- **Scope:** all, tenant, store, own

### Permission List by Module

#### System Management
```
users.view.all
users.view.tenant
users.view.store
users.view.own

users.create.all
users.create.tenant
users.create.store

users.update.all
users.update.tenant
users.update.store
users.update.own

users.delete.all
users.delete.tenant
users.delete.store

roles.view.all
roles.manage.all

settings.view.all
settings.manage.all
```

#### Tenant & Store Management
```
tenants.view.all
tenants.view.own
tenants.create.all
tenants.update.all
tenants.update.own
tenants.delete.all

stores.view.all
stores.view.tenant
stores.view.store
stores.create.all
stores.create.tenant
stores.update.all
stores.update.tenant
stores.update.store
stores.delete.all
stores.delete.tenant

store-settings.manage.own
store-settings.manage.tenant
```

#### Subscription Management
```
subscriptions.view.all
subscriptions.view.own
subscriptions.manage.all
subscriptions.manage.own
subscriptions.approve.all
```

#### Product Management
```
products.view.all
products.view.tenant
products.view.store

products.create.tenant
products.create.store

products.update.tenant
products.update.store

products.delete.tenant
products.delete.store

categories.view.all
categories.view.tenant
categories.view.store

categories.manage.all
categories.manage.tenant
categories.manage.store
```

#### Inventory Management
```
inventory.view.tenant
inventory.view.store

purchases.view.tenant
purchases.view.store
purchases.create.tenant
purchases.create.store

stock-opname.perform.tenant
stock-opname.perform.store

stock-adjustment.perform.tenant
stock-adjustment.perform.store

unpacking.perform.tenant
unpacking.perform.store

suppliers.view.all
suppliers.view.tenant
suppliers.manage.tenant
```

#### POS Operations
```
pos.access.store
pos.transactions.create.store
pos.transactions.view.store
pos.transactions.view.own
pos.transactions.void.store

store-sessions.open.store
store-sessions.close.store

cash-reconciliation.view.store
```

#### Reports & Dashboard
```
dashboard.view.all
dashboard.view.tenant
dashboard.view.store

reports.sales.view.all
reports.sales.view.tenant
reports.sales.view.store

reports.inventory.view.tenant
reports.inventory.view.store

reports.financial.view.all
reports.financial.view.tenant
reports.financial.view.store

reports.cashier.view.tenant
reports.cashier.view.store
reports.cashier.view.own

reports.subscription.view.all

reports.export.all
reports.export.tenant
reports.export.store
```

---

### Permission Assignment by Role

#### Administrator SaaS
```
users.view.all, users.create.all, users.update.all, users.delete.all
roles.view.all, roles.manage.all
settings.view.all, settings.manage.all

tenants.view.all, tenants.create.all, tenants.update.all, tenants.delete.all

stores.view.all, stores.create.all, stores.update.all, stores.delete.all

subscriptions.view.all, subscriptions.manage.all, subscriptions.approve.all

dashboard.view.all
reports.*.view.all
reports.export.all
```

**Total Permissions:** 25 permissions

---

#### Tenant Owner
```
users.view.tenant, users.create.tenant, users.update.tenant, users.delete.tenant

stores.view.tenant, stores.create.tenant, stores.update.tenant, stores.delete.tenant
store-settings.manage.tenant

products.view.tenant, products.create.tenant, products.update.tenant, products.delete.tenant
categories.view.tenant, categories.manage.tenant

inventory.view.tenant
purchases.view.tenant, purchases.create.tenant
stock-opname.perform.tenant
stock-adjustment.perform.tenant
unpacking.perform.tenant
suppliers.view.tenant, suppliers.manage.tenant

pos.transactions.view.tenant
pos.transactions.void.tenant (approve)

dashboard.view.tenant
reports.sales.view.tenant
reports.inventory.view.tenant
reports.financial.view.tenant
reports.cashier.view.tenant
reports.export.tenant
```

**Total Permissions:** 28 permissions

---

#### Admin Toko
```
users.view.store, users.create.store, users.update.store, users.delete.store

stores.view.store, stores.update.store
store-settings.manage.own

products.view.store, products.create.store, products.update.store, products.delete.store
categories.view.store, categories.manage.store

inventory.view.store
purchases.view.store, purchases.create.store
stock-opname.perform.store
stock-adjustment.perform.store
unpacking.perform.store
suppliers.view.tenant

pos.access.store
pos.transactions.create.store
pos.transactions.view.store
pos.transactions.void.store (approve)

store-sessions.open.store, store-sessions.close.store
cash-reconciliation.view.store

dashboard.view.store
reports.sales.view.store
reports.inventory.view.store
reports.cashier.view.store
reports.financial.view.store
reports.export.store
```

**Total Permissions:** 24 permissions

---

#### Kasir
```
users.view.own
users.update.own

pos.access.store
pos.transactions.create.store
pos.transactions.view.own

dashboard.view.store (limited to own data)
reports.cashier.view.own
```

**Total Permissions:** 7 permissions

---

## FITUR CRUD PER MODULE

### Standard CRUD Operations

Setiap module yang implement BaseController memiliki standard CRUD operations:

#### INDEX (List/View)
- Pagination (default: 15 per page)
- Search functionality
- Filter by status (active/inactive)
- Sort by multiple columns
- Bulk actions (activate, deactivate, delete)
- Export to Excel/CSV/PDF
- Soft-deleted recovery option

#### CREATE (Add New)
- Form validation (via Request class)
- Required field indicators
- Input error display (inline)
- Success/error messages
- Redirect after success
- Transaction management

#### STORE (Save New)
- Data validation
- Duplicate check (where applicable)
- DB transaction
- Error handling & logging
- Success message
- Redirect to index or edit

#### SHOW (View Details)
- Full record details
- Related data display
- Action buttons (Edit, Delete, Restore)
- Activity log (if applicable)
- Back button

#### EDIT (Modify)
- Pre-filled form with existing data
- Data validation
- Optimistic locking (prepared)
- Success/error messages
- Redirect after success

#### UPDATE (Save Changes)
- Full update capability
- Partial update support
- DB transaction
- Error handling & logging
- Success message
- Redirect to index or edit

#### DESTROY (Delete)
- Soft delete (default)
- Confirmation dialog
- Check for associated data
- Cascade handling
- Transaction management
- Success message

#### RESTORE (Recover)
- Restore soft-deleted records
- Available for soft-delete models
- Transaction management
- Success message

---

### Modules dengan CRUD Lengkap

#### 1. Users Module
**Controller:** UserController
**Model:** User
**Service:** UserService
**Repository:** UserRepository
**Request:** UserRequest
**Routes:** `/users`

**Fields:**
- name, email, password
- tenant_id, store_id
- role assignment (via Spatie)
- is_active, activation_code
- last_login_at, password_expires_at

**CRUD Features:**
- ✅ Index (list dengan pagination, filter by role/tenant/store)
- ✅ Create (with tenant/store/role selection)
- ✅ Store (with validation & password hashing)
- ✅ Show (user details + role + permissions + activity log)
- ✅ Edit (update info, change role, change store)
- ✅ Update (with validation)
- ✅ Destroy (soft delete)
- ✅ Restore
- ➕ Reset Password
- ➕ Activate/Deactivate
- ➕ Send Activation Email

**Permissions:** users.view.{scope}, users.create.{scope}, users.update.{scope}, users.delete.{scope}

---

#### 2. Tenants Module
**Controller:** TenantController
**Model:** Tenant
**Service:** TenantService
**Repository:** TenantRepository
**Request:** TenantRequest
**Routes:** `/admin/tenants`

**Fields:**
- name, slug, email, phone
- is_active

**CRUD Features:**
- ✅ Index (list dengan pagination, filter by status)
- ✅ Create
- ✅ Store (with slug auto-generation)
- ✅ Show (tenant details + stores + users count)
- ✅ Edit
- ✅ Update
- ✅ Destroy (soft delete)
- ✅ Restore
- ➕ Activate/Deactivate

**Permissions:** tenants.view.all, tenants.create.all, tenants.update.all, tenants.delete.all

---

#### 3. Stores Module
**Controller:** StoreController
**Model:** Store
**Service:** StoreService
**Repository:** StoreRepository
**Request:** StoreRequest
**Routes:** `/stores`

**Fields:**
- tenant_id, name, slug, address, phone
- is_active

**CRUD Features:**
- ✅ Index (list dengan pagination, filter by tenant/status)
- ✅ Create (with tenant selection)
- ✅ Store (with slug auto-generation)
- ✅ Show (store details + users + products count)
- ✅ Edit
- ✅ Update
- ✅ Destroy (soft delete)
- ✅ Restore
- ➕ Activate/Deactivate
- ➕ Store Settings (Tax, Pricing Rules, Operating Hours)

**Permissions:** stores.view.{scope}, stores.create.{scope}, stores.update.{scope}, stores.delete.{scope}

---

#### 4. Categories Module
**Controller:** CategoryController
**Model:** Category
**Service:** CategoryService
**Repository:** CategoryRepository
**Request:** CategoryRequest
**Routes:** `/categories`

**Fields:**
- name, slug, description
- is_active

**CRUD Features:**
- ✅ Index (list dengan pagination, filter by status) - **SUDAH IMPLEMENTED**
- ✅ Create - **SUDAH IMPLEMENTED**
- ✅ Store (with slug auto-generation) - **SUDAH IMPLEMENTED**
- ✅ Show - **SUDAH IMPLEMENTED**
- ✅ Edit - **SUDAH IMPLEMENTED**
- ✅ Update - **SUDAH IMPLEMENTED**
- ✅ Destroy (soft delete) - **SUDAH IMPLEMENTED**
- ✅ Restore - **SUDAH IMPLEMENTED**

**Permissions:** categories.view.{scope}, categories.manage.{scope}

---

#### 5. Products Module
**Controller:** ProductController
**Model:** Product
**Service:** ProductService
**Repository:** ProductRepository
**Request:** ProductRequest
**Routes:** `/products`

**Fields:**
- sku, barcode, name, category_id
- description, unit
- purchase_price, selling_price (tenant default)
- min_stock, max_stock
- is_active
- image (optional)

**CRUD Features:**
- ✅ Index (list dengan pagination, filter by category/status/stock)
- ✅ Create
- ✅ Store (with SKU validation)
- ✅ Show (product details + stock per store)
- ✅ Edit
- ✅ Update
- ✅ Destroy (soft delete)
- ✅ Restore
- ➕ Upload Bulk Products (Excel import)
- ➕ Set Tenant-wide Prices
- ➕ Set Store-specific Prices (Admin Toko)

**Permissions:** products.view.{scope}, products.create.{scope}, products.update.{scope}, products.delete.{scope}

---

#### 6. Suppliers Module
**Controller:** SupplierController
**Model:** Supplier
**Service:** SupplierService
**Repository:** SupplierRepository
**Request:** SupplierRequest
**Routes:** `/suppliers`

**Fields:**
- tenant_id, name, contact_person
- address, phone, email
- payment_terms, tax_id (NPWP)
- is_active

**CRUD Features:**
- ✅ Index (list dengan pagination, filter by status)
- ✅ Create
- ✅ Store
- ✅ Show (supplier details + PO history)
- ✅ Edit
- ✅ Update
- ✅ Destroy (soft delete)
- ✅ Restore
- ➕ Supplier Performance Report

**Permissions:** suppliers.view.tenant, suppliers.manage.tenant

---

#### 7. Purchase Orders Module
**Controller:** PurchaseOrderController
**Model:** PurchaseOrder, PurchaseOrderItem
**Service:** PurchaseOrderService
**Repository:** PurchaseOrderRepository
**Request:** PurchaseOrderRequest
**Routes:** `/purchases`

**Fields:**
- store_id, supplier_id, po_number
- po_date, expected_delivery_date
- status (draft, submitted, approved, received, cancelled)
- total_amount, notes

**CRUD Features:**
- ✅ Index (list dengan pagination, filter by status/store/supplier)
- ✅ Create (with product selection)
- ✅ Store (with items)
- ✅ Show (PO details + items)
- ✅ Edit (if status: draft)
- ✅ Update
- ✅ Destroy (if status: draft)
- ➕ Submit for Approval
- ➕ Approve/Reject (Tenant Owner)
- ➕ Receive PO (update stock)
- ➕ Print PO

**Permissions:** purchases.view.{scope}, purchases.create.{scope}

---

#### 8. Stock Opname Module
**Controller:** StockOpnameController
**Model:** StockOpname, StockOpnameItem
**Service:** StockOpnameService
**Repository:** StockOpnameRepository
**Request:** StockOpnameRequest
**Routes:** `/stock-opname`

**Fields:**
- store_id, opname_date, opname_number
- status (draft, submitted, approved)
- total_variance, notes

**CRUD Features:**
- ✅ Index (list dengan pagination, filter by status/store)
- ✅ Create (generate opname items from current stock)
- ✅ Store (with physical count)
- ✅ Show (opname details + variance)
- ✅ Edit (if status: draft)
- ✅ Update
- ➕ Submit for Approval
- ➕ Approve/Reject (Tenant Owner)
- ➕ Finalize (update stock)
- ➕ Print Opname Report

**Permissions:** stock-opname.perform.{scope}

---

#### 9. Stock Adjustment Module
**Controller:** StockAdjustmentController
**Model:** StockAdjustment
**Service:** StockAdjustmentService
**Repository:** StockAdjustmentRepository
**Request:** StockAdjustmentRequest
**Routes:** `/stock-adjustment`

**Fields:**
- store_id, product_id, adjustment_type (add/reduce)
- quantity, reason
- status (draft, submitted, approved)
- notes

**CRUD Features:**
- ✅ Index (list dengan pagination, filter by status/store/type)
- ✅ Create
- ✅ Store
- ✅ Show (adjustment details)
- ✅ Edit (if status: draft)
- ✅ Update
- ➕ Submit for Approval
- ➕ Approve/Reject (Tenant Owner)
- ➕ Finalize (update stock)

**Permissions:** stock-adjustment.perform.{scope}

---

#### 10. POS Transactions Module
**Controller:** POSController
**Model:** Transaction, TransactionItem
**Service:** POSService
**Repository:** TransactionRepository
**Request:** TransactionRequest
**Routes:** `/pos`

**Fields:**
- store_id, cashier_id, transaction_number
- transaction_date, customer_name, customer_phone
- subtotal, discount, tax, total
- payment_method, amount_paid, change
- status (completed, voided)

**CRUD Features:**
- ✅ Index (list dengan pagination, filter by date/cashier/status)
- ✅ Create (POS interface)
- ✅ Store (with items + payment + stock reduction)
- ✅ Show (transaction details + items)
- ➕ Print Receipt
- ➕ Email Receipt
- ➕ Void Transaction (with approval)
- ➕ Reprint Receipt

**Permissions:** pos.access.store, pos.transactions.create.store, pos.transactions.view.{scope}, pos.transactions.void.store

---

## IMPLEMENTATION CHECKLIST

### Phase 1: Foundation ✅ COMPLETED
- [x] SOLID Architecture
- [x] Repository Pattern
- [x] Service Pattern
- [x] Base Controller
- [x] Category Module (Example)
- [x] Auth (Laravel Breeze)
- [x] Multi-Tenancy Foundation
- [x] Documentation

---

### Phase 2: User Management (In Progress)
- [ ] User CRUD Module
  - [ ] Routes: `/admin/users`, `/users`
  - [ ] UserController extends BaseController
  - [ ] UserService & UserRepository (SUDAH ADA)
  - [ ] UserRequest validation
  - [ ] Views: index, create, edit, show
  - [ ] Permission middleware integration
  - [ ] Activation code generation
  - [ ] Password reset functionality

- [ ] Role & Permission Seeder
  - [ ] Seed 4 default roles (Administrator SaaS, Tenant Owner, Admin Toko, Kasir)
  - [ ] Seed all permissions (84+ permissions)
  - [ ] Assign permissions to roles

- [ ] Permission Management UI
  - [ ] Routes: `/admin/roles`
  - [ ] RoleController
  - [ ] Views: role index, role form, permission tree
  - [ ] Assign/revoke permissions

- [ ] Activity Logging
  - [ ] ActivityLog model
  - [ ] Log user activities (create, update, delete)
  - [ ] View activity log per user

---

### Phase 3: Tenant & Store Management
- [ ] Tenant CRUD Module
  - [ ] Routes: `/admin/tenants`
  - [ ] TenantController
  - [ ] TenantService & TenantRepository
  - [ ] TenantRequest validation
  - [ ] Views: index, create, edit, show
  - [ ] Activate/Deactivate functionality

- [ ] Store CRUD Module
  - [ ] Routes: `/stores`
  - [ ] StoreController
  - [ ] StoreService & StoreRepository
  - [ ] StoreRequest validation
  - [ ] Views: index, create, edit, show
  - [ ] Store Settings (Tax, Pricing Rules, Operating Hours)

---

### Phase 4: Product Management
- [ ] Product CRUD Module
  - [ ] Routes: `/products`
  - [ ] ProductController
  - [ ] ProductService & ProductRepository
  - [ ] ProductRequest validation
  - [ ] Views: index, create, edit, show
  - [ ] SKU auto-generation
  - [ ] Image upload
  - [ ] Bulk import (Excel)

- [ ] Store-specific Pricing
  - [ ] ProductStorePrice model
  - [ ] Price override functionality
  - [ ] Price history

---

### Phase 5: Inventory Management
- [ ] Supplier CRUD Module
  - [ ] Routes: `/suppliers`
  - [ ] SupplierController
  - [ ] SupplierService & SupplierRepository
  - [ ] Views: index, create, edit, show

- [ ] Purchase Order Module
  - [ ] Routes: `/purchases`
  - [ ] PurchaseOrderController
  - [ ] PO creation with items
  - [ ] Approval workflow
  - [ ] Receive PO (update stock)

- [ ] Stock Management
  - [ ] Stock model
  - [ ] Stock Opname module
  - [ ] Stock Adjustment module
  - [ ] Unpacking module

---

### Phase 6: POS System
- [ ] POS Transaction Module
  - [ ] Routes: `/pos`
  - [ ] POSController
  - [ ] POS interface (Vue.js or Livewire)
  - [ ] Product search & selection
  - [ ] Cart management
  - [ ] Payment processing
  - [ ] Receipt printing

- [ ] Store Session Management
  - [ ] Routes: `/sessions`
  - [ ] SessionController
  - [ ] Open/Close session
  - [ ] Cash reconciliation

- [ ] Void Management
  - [ ] Void request workflow
  - [ ] Approval by Admin Toko/Tenant Owner
  - [ ] Audit trail

---

### Phase 7: Cash Management
- [ ] Cash Reconciliation Module
  - [ ] Routes: `/cash`
  - [ ] CashController
  - [ ] Daily reconciliation
  - [ ] Variance analysis

- [ ] Register Management
  - [ ] Register/Counter setup
  - [ ] Assign cashiers to registers

---

### Phase 8: Reports & Dashboard
- [ ] Dashboard Module
  - [ ] Routes: `/dashboard`, `/admin/dashboard`
  - [ ] DashboardController
  - [ ] Role-based dashboard views
  - [ ] Charts & statistics

- [ ] Reports Module
  - [ ] Routes: `/reports`
  - [ ] ReportController
  - [ ] Sales reports
  - [ ] Inventory reports
  - [ ] Financial reports
  - [ ] Cashier reports
  - [ ] Export to Excel/CSV/PDF

---

### Phase 9: Subscription & Billing
- [ ] Subscription Module
  - [ ] Routes: `/admin/subscriptions`
  - [ ] SubscriptionController
  - [ ] Subscription plans CRUD
  - [ ] Approve/Reject subscriptions
  - [ ] Billing management

- [ ] Invoice Module
  - [ ] Invoice generation
  - [ ] Payment tracking

---

### Phase 10: Testing & Deployment
- [ ] Unit Tests
- [ ] Feature Tests
- [ ] UI/UX Testing
- [ ] Performance Testing
- [ ] Security Audit
- [ ] Deployment Documentation

---

## CATATAN IMPLEMENTASI

### Prioritas Development

**High Priority (Must Have):**
1. User Management (Phase 2)
2. Tenant & Store Management (Phase 3)
3. Product Management (Phase 4)
4. POS System (Phase 6)
5. Reports & Dashboard (Phase 8)

**Medium Priority (Should Have):**
1. Inventory Management (Phase 5)
2. Cash Management (Phase 7)
3. Subscription & Billing (Phase 9)

**Low Priority (Nice to Have):**
1. Advanced Analytics
2. Customer Loyalty Program
3. Multi-language Support
4. Mobile App

---

### Technology Stack

**Backend:**
- Laravel 11.46.1
- PHP 8.2+
- MySQL 8.0+
- Spatie Laravel-Permission 6.23

**Frontend:**
- Blade Templates
- Tailwind CSS
- Alpine.js (for interactivity)
- Vue.js (for POS interface - optional)
- Chart.js (for charts)

**Export:**
- Maatwebsite/Laravel-Excel (Excel export)
- DomPDF or Snappy (PDF export)

**Others:**
- Laravel Queue (for background jobs)
- Laravel Cache (for performance)
- Laravel Sanctum (for API - optional)

---

## KESIMPULAN

Dokumen ini merupakan **breakdown lengkap menu-submenu** untuk aplikasi Kasir-5 POS Multi-Tenant SaaS, dengan:

- ✅ **4 Role definitions** dengan hierarchy yang jelas
- ✅ **132 Sub-menus** tersebar di 4 roles
- ✅ **84+ Permissions** dengan naming convention yang konsisten
- ✅ **10 CRUD Modules** dengan detail implementasi
- ✅ **Implementation Checklist** untuk tracking development
- ✅ **Permission Matrix** untuk role-based access control

**Dokumen ini dapat digunakan sebagai:**
1. Reference untuk development
2. Checklist untuk tracking progress
3. Documentation untuk client/stakeholder
4. Guide untuk UI/UX design
5. Basis untuk user manual/training

**Next Steps:**
1. Review dengan stakeholder
2. Prioritize features
3. Start Phase 2 implementation
4. Create UI mockups based on menu structure
5. Develop views & routes step by step

---

**End of Document**
**Last Updated:** 2025-11-29
**Version:** 1.0
**Author:** Claude (Anthropic AI)
