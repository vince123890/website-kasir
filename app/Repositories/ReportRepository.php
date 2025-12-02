<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use App\Models\Store;
use App\Models\StoreSession;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportRepository
{
    /**
     * Get sales report data
     */
    public function getSalesReport(array $filters): array
    {
        $query = Transaction::query()
            ->with(['store', 'cashier', 'items.product'])
            ->where('status', 'completed');

        // Apply filters
        if (!empty($filters['start_date'])) {
            $query->whereDate('transaction_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('transaction_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['store_id'])) {
            $query->where('store_id', $filters['store_id']);
        }

        if (!empty($filters['cashier_id'])) {
            $query->where('cashier_id', $filters['cashier_id']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        $transactions = $query->get();

        // Calculate summary
        $totalSales = $transactions->sum('total_amount');
        $totalTransactions = $transactions->count();
        $avgTransactionValue = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;
        $totalDiscount = $transactions->sum('discount_amount');
        $totalTax = $transactions->sum('tax_amount');

        // Sales by day
        $salesByDay = $transactions->groupBy(function ($transaction) {
            return $transaction->transaction_date->format('Y-m-d');
        })->map(function ($dayTransactions) {
            return [
                'total' => $dayTransactions->sum('total_amount'),
                'count' => $dayTransactions->count(),
            ];
        });

        // Sales by store
        $salesByStore = $transactions->groupBy('store_id')->map(function ($storeTransactions) {
            return [
                'store_name' => $storeTransactions->first()->store->name ?? 'Unknown',
                'total' => $storeTransactions->sum('total_amount'),
                'count' => $storeTransactions->count(),
            ];
        });

        // Sales by payment method
        $salesByPaymentMethod = $transactions->groupBy('payment_method')->map(function ($methodTransactions) {
            return [
                'total' => $methodTransactions->sum('total_amount'),
                'count' => $methodTransactions->count(),
            ];
        });

        // Top products
        $topProducts = $this->getTopProducts($filters, 10);

        // Sales by category
        $salesByCategory = $this->getSalesByCategory($filters);

        return [
            'summary' => [
                'total_sales' => $totalSales,
                'total_transactions' => $totalTransactions,
                'avg_transaction_value' => $avgTransactionValue,
                'total_discount' => $totalDiscount,
                'total_tax' => $totalTax,
            ],
            'sales_by_day' => $salesByDay,
            'sales_by_store' => $salesByStore,
            'sales_by_payment_method' => $salesByPaymentMethod,
            'top_products' => $topProducts,
            'sales_by_category' => $salesByCategory,
            'transactions' => $transactions,
        ];
    }

    /**
     * Get top selling products
     */
    public function getTopProducts(array $filters, int $limit = 10): array
    {
        $query = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->where('transactions.status', 'completed')
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                DB::raw('SUM(transaction_items.quantity) as total_quantity'),
                DB::raw('SUM(transaction_items.total_price) as total_sales')
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_sales')
            ->limit($limit);

        // Apply filters
        if (!empty($filters['start_date'])) {
            $query->whereDate('transactions.transaction_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('transactions.transaction_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['store_id'])) {
            $query->where('transactions.store_id', $filters['store_id']);
        }

        return $query->get()->toArray();
    }

    /**
     * Get sales by category
     */
    public function getSalesByCategory(array $filters): array
    {
        $query = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('transactions.status', 'completed')
            ->select(
                'categories.id',
                'categories.name as category_name',
                DB::raw('SUM(transaction_items.total_price) as total_sales'),
                DB::raw('COUNT(DISTINCT transactions.id) as transaction_count')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_sales');

        // Apply filters
        if (!empty($filters['start_date'])) {
            $query->whereDate('transactions.transaction_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('transactions.transaction_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['store_id'])) {
            $query->where('transactions.store_id', $filters['store_id']);
        }

        return $query->get()->toArray();
    }

    /**
     * Get inventory report data
     */
    public function getInventoryReport(array $filters): array
    {
        $query = ProductStock::query()
            ->with(['product.category', 'store']);

        // Apply filters
        if (!empty($filters['store_id'])) {
            $query->where('store_id', $filters['store_id']);
        }

        if (!empty($filters['category_id'])) {
            $query->whereHas('product', function ($q) use ($filters) {
                $q->where('category_id', $filters['category_id']);
            });
        }

        if (!empty($filters['stock_level'])) {
            $stockLevel = $filters['stock_level'];
            if ($stockLevel === 'low') {
                $query->whereRaw('quantity < min_stock');
            } elseif ($stockLevel === 'out') {
                $query->where('quantity', 0);
            } elseif ($stockLevel === 'over') {
                $query->whereRaw('quantity > max_stock');
            }
        }

        $stocks = $query->get();

        // Calculate summary
        $totalProducts = $stocks->groupBy('product_id')->count();
        $totalStockValue = $stocks->sum(function ($stock) {
            return $stock->quantity * $stock->product->selling_price;
        });
        $lowStockCount = $stocks->filter(function ($stock) {
            return $stock->quantity < $stock->min_stock;
        })->count();
        $outOfStockCount = $stocks->filter(function ($stock) {
            return $stock->quantity == 0;
        })->count();
        $overstockCount = $stocks->filter(function ($stock) {
            return $stock->quantity > $stock->max_stock;
        })->count();

        // Stock by store
        $stockByStore = $stocks->groupBy('store_id')->map(function ($storeStocks) {
            $store = $storeStocks->first()->store;
            return [
                'store_name' => $store->name ?? 'Unknown',
                'total_value' => $storeStocks->sum(function ($stock) {
                    return $stock->quantity * $stock->product->selling_price;
                }),
                'product_count' => $storeStocks->groupBy('product_id')->count(),
                'low_stock_count' => $storeStocks->filter(fn($s) => $s->quantity < $s->min_stock)->count(),
            ];
        });

        // Stock by category
        $stockByCategory = $stocks->groupBy('product.category_id')->map(function ($categoryStocks) {
            $category = $categoryStocks->first()->product->category;
            return [
                'category_name' => $category->name ?? 'Uncategorized',
                'total_value' => $categoryStocks->sum(function ($stock) {
                    return $stock->quantity * $stock->product->selling_price;
                }),
                'product_count' => $categoryStocks->groupBy('product_id')->count(),
            ];
        });

        return [
            'summary' => [
                'total_products' => $totalProducts,
                'total_stock_value' => $totalStockValue,
                'low_stock_count' => $lowStockCount,
                'out_of_stock_count' => $outOfStockCount,
                'overstock_count' => $overstockCount,
            ],
            'stock_by_store' => $stockByStore,
            'stock_by_category' => $stockByCategory,
            'stocks' => $stocks,
        ];
    }

    /**
     * Get financial report data
     */
    public function getFinancialReport(array $filters): array
    {
        $startDate = $filters['start_date'] ?? Carbon::now()->startOfMonth();
        $endDate = $filters['end_date'] ?? Carbon::now()->endOfMonth();

        // Revenue from completed transactions
        $revenue = Transaction::where('status', 'completed')
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->sum('total_amount');

        // COGS (Cost of Goods Sold)
        $cogs = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->where('transactions.status', 'completed')
            ->whereDate('transactions.transaction_date', '>=', $startDate)
            ->whereDate('transactions.transaction_date', '<=', $endDate)
            ->sum(DB::raw('transaction_items.quantity * products.purchase_price'));

        // Gross profit
        $grossProfit = $revenue - $cogs;
        $profitMargin = $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0;

        // Total discount given
        $totalDiscount = Transaction::where('status', 'completed')
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->sum('discount_amount');

        // Total tax collected
        $totalTax = Transaction::where('status', 'completed')
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->sum('tax_amount');

        // Revenue by payment method
        $revenueByPaymentMethod = Transaction::where('status', 'completed')
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->select('payment_method', DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get()
            ->pluck('total', 'payment_method')
            ->toArray();

        // Daily revenue trend
        $dailyRevenue = Transaction::where('status', 'completed')
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->select(
                DB::raw('DATE(transaction_date) as date'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        return [
            'summary' => [
                'revenue' => $revenue,
                'cogs' => $cogs,
                'gross_profit' => $grossProfit,
                'profit_margin' => $profitMargin,
                'total_discount' => $totalDiscount,
                'total_tax' => $totalTax,
            ],
            'revenue_by_payment_method' => $revenueByPaymentMethod,
            'daily_revenue' => $dailyRevenue,
        ];
    }

    /**
     * Get cashier performance report
     */
    public function getCashierReport(array $filters): array
    {
        $query = Transaction::query()
            ->with(['cashier', 'store'])
            ->where('status', 'completed');

        // Apply filters
        if (!empty($filters['start_date'])) {
            $query->whereDate('transaction_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('transaction_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['store_id'])) {
            $query->where('store_id', $filters['store_id']);
        }

        if (!empty($filters['cashier_id'])) {
            $query->where('cashier_id', $filters['cashier_id']);
        }

        $transactions = $query->get();

        // Cashier performance
        $cashierPerformance = $transactions->groupBy('cashier_id')->map(function ($cashierTransactions) {
            $cashier = $cashierTransactions->first()->cashier;
            $totalSales = $cashierTransactions->sum('total_amount');
            $transactionCount = $cashierTransactions->count();

            return [
                'cashier_id' => $cashier->id,
                'cashier_name' => $cashier->name,
                'total_sales' => $totalSales,
                'transaction_count' => $transactionCount,
                'avg_transaction_value' => $transactionCount > 0 ? $totalSales / $transactionCount : 0,
                'total_discount_given' => $cashierTransactions->sum('discount_amount'),
            ];
        })->sortByDesc('total_sales')->values();

        // Voided transactions by cashier
        $voidedTransactions = Transaction::where('status', 'voided')
            ->whereDate('voided_at', '>=', $filters['start_date'] ?? Carbon::now()->startOfMonth())
            ->whereDate('voided_at', '<=', $filters['end_date'] ?? Carbon::now()->endOfMonth())
            ->with('cashier')
            ->get()
            ->groupBy('cashier_id')
            ->map(function ($voidedTrans) {
                return [
                    'cashier_name' => $voidedTrans->first()->cashier->name ?? 'Unknown',
                    'void_count' => $voidedTrans->count(),
                    'void_amount' => $voidedTrans->sum('total_amount'),
                ];
            });

        // Session variance
        $sessionVariance = StoreSession::query()
            ->where('status', 'approved')
            ->whereDate('opened_at', '>=', $filters['start_date'] ?? Carbon::now()->startOfMonth())
            ->whereDate('closed_at', '<=', $filters['end_date'] ?? Carbon::now()->endOfMonth())
            ->with('cashier')
            ->get()
            ->map(function ($session) {
                return [
                    'cashier_name' => $session->cashier->name ?? 'Unknown',
                    'date' => $session->opened_at->format('Y-m-d'),
                    'variance' => $session->actual_cash - $session->expected_cash,
                ];
            });

        return [
            'cashier_performance' => $cashierPerformance,
            'voided_transactions' => $voidedTransactions,
            'session_variance' => $sessionVariance,
            'transactions' => $transactions,
        ];
    }

    /**
     * Get dashboard statistics for Super Admin
     */
    public function getAdminDashboardStats(): array
    {
        $totalTenants = DB::table('tenants')->count();
        $activeTenants = DB::table('tenants')->where('is_active', true)->count();
        $trialTenants = DB::table('tenants')->where('subscription_status', 'trial')->count();
        $totalStores = DB::table('stores')->count();
        $totalUsers = DB::table('users')->count();

        // New tenants by month (last 12 months)
        $newTenantsByMonth = DB::table('tenants')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        // Users by role
        $usersByRole = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('roles.name', DB::raw('COUNT(*) as count'))
            ->groupBy('roles.name')
            ->get()
            ->pluck('count', 'name')
            ->toArray();

        return [
            'total_tenants' => $totalTenants,
            'active_tenants' => $activeTenants,
            'trial_tenants' => $trialTenants,
            'total_stores' => $totalStores,
            'total_users' => $totalUsers,
            'new_tenants_by_month' => $newTenantsByMonth,
            'users_by_role' => $usersByRole,
        ];
    }

    /**
     * Get dashboard statistics for Tenant Owner
     */
    public function getTenantDashboardStats(int $tenantId): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $thisYear = Carbon::now()->startOfYear();

        // Sales statistics
        $todaySales = Transaction::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereDate('transaction_date', $today)
            ->sum('total_amount');

        $thisMonthSales = Transaction::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereDate('transaction_date', '>=', $thisMonth)
            ->sum('total_amount');

        $thisYearSales = Transaction::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereDate('transaction_date', '>=', $thisYear)
            ->sum('total_amount');

        // Other statistics
        $totalTransactions = Transaction::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereDate('transaction_date', '>=', $thisMonth)
            ->count();

        $totalProducts = Product::where('tenant_id', $tenantId)->count();
        $totalStores = Store::where('tenant_id', $tenantId)->count();

        // Sales trend (last 30 days)
        $salesTrend = Transaction::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereDate('transaction_date', '>=', Carbon::now()->subDays(30))
            ->select(
                DB::raw('DATE(transaction_date) as date'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        // Sales by store
        $salesByStore = Transaction::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereDate('transaction_date', '>=', $thisMonth)
            ->with('store')
            ->get()
            ->groupBy('store_id')
            ->map(function ($storeTransactions) {
                return [
                    'store_name' => $storeTransactions->first()->store->name ?? 'Unknown',
                    'total' => $storeTransactions->sum('total_amount'),
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->toArray();

        // Inventory alerts
        $lowStockCount = ProductStock::whereHas('product', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId');
        })
            ->whereRaw('quantity < min_stock')
            ->count();

        $outOfStockCount = ProductStock::whereHas('product', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })
            ->where('quantity', 0)
            ->count();

        return [
            'today_sales' => $todaySales,
            'this_month_sales' => $thisMonthSales,
            'this_year_sales' => $thisYearSales,
            'total_transactions' => $totalTransactions,
            'total_products' => $totalProducts,
            'total_stores' => $totalStores,
            'sales_trend' => $salesTrend,
            'sales_by_store' => $salesByStore,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
        ];
    }

    /**
     * Get dashboard statistics for Admin Toko
     */
    public function getStoreDashboardStats(int $storeId): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // Today's sales
        $todaySales = Transaction::where('store_id', $storeId)
            ->where('status', 'completed')
            ->whereDate('transaction_date', $today)
            ->sum('total_amount');

        $todayTransactions = Transaction::where('store_id', $storeId)
            ->where('status', 'completed')
            ->whereDate('transaction_date', $today)
            ->count();

        // Active cashiers (open sessions)
        $activeCashiers = StoreSession::where('store_id', $storeId)
            ->where('status', 'open')
            ->count();

        // Stock value
        $stockValue = ProductStock::where('store_id', $storeId)
            ->with('product')
            ->get()
            ->sum(function ($stock) {
                return $stock->quantity * $stock->product->selling_price;
            });

        // This month's sales
        $thisMonthSales = Transaction::where('store_id', $storeId)
            ->where('status', 'completed')
            ->whereDate('transaction_date', '>=', $thisMonth)
            ->sum('total_amount');

        // Hourly sales (today)
        $hourlySales = Transaction::where('store_id', $storeId)
            ->where('status', 'completed')
            ->whereDate('transaction_date', $today)
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->pluck('total', 'hour')
            ->toArray();

        // Top products (this month)
        $topProducts = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->where('transactions.store_id', $storeId)
            ->where('transactions.status', 'completed')
            ->whereDate('transactions.transaction_date', '>=', $thisMonth)
            ->select(
                'products.name',
                DB::raw('SUM(transaction_items.quantity) as total_quantity'),
                DB::raw('SUM(transaction_items.total_price) as total_sales')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get()
            ->toArray();

        // Inventory alerts
        $lowStockCount = ProductStock::where('store_id', $storeId)
            ->whereRaw('quantity < min_stock')
            ->count();

        $outOfStockCount = ProductStock::where('store_id', $storeId)
            ->where('quantity', 0)
            ->count();

        return [
            'today_sales' => $todaySales,
            'today_transactions' => $todayTransactions,
            'active_cashiers' => $activeCashiers,
            'stock_value' => $stockValue,
            'this_month_sales' => $thisMonthSales,
            'hourly_sales' => $hourlySales,
            'top_products' => $topProducts,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
        ];
    }

    /**
     * Get dashboard statistics for Kasir
     */
    public function getCashierDashboardStats(int $cashierId): array
    {
        $today = Carbon::today();

        // Today's performance
        $todaySales = Transaction::where('cashier_id', $cashierId)
            ->where('status', 'completed')
            ->whereDate('transaction_date', $today)
            ->sum('total_amount');

        $todayTransactions = Transaction::where('cashier_id', $cashierId)
            ->where('status', 'completed')
            ->whereDate('transaction_date', $today)
            ->count();

        $avgTransactionValue = $todayTransactions > 0 ? $todaySales / $todayTransactions : 0;

        $customersServed = Transaction::where('cashier_id', $cashierId)
            ->where('status', 'completed')
            ->whereDate('transaction_date', $today)
            ->whereNotNull('customer_id')
            ->distinct('customer_id')
            ->count();

        // Current session
        $currentSession = StoreSession::where('cashier_id', $cashierId)
            ->where('status', 'open')
            ->latest()
            ->first();

        $sessionInfo = null;
        if ($currentSession) {
            $sessionSales = Transaction::where('store_session_id', $currentSession->id)
                ->where('status', 'completed')
                ->sum('total_amount');

            $sessionTransactions = Transaction::where('store_session_id', $currentSession->id)
                ->where('status', 'completed')
                ->count();

            $sessionInfo = [
                'session_number' => $currentSession->session_number,
                'opening_cash' => $currentSession->opening_cash,
                'current_balance' => $currentSession->opening_cash + $sessionSales,
                'transactions_count' => $sessionTransactions,
                'sales' => $sessionSales,
            ];
        }

        // Top product sold today
        $topProduct = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->where('transactions.cashier_id', $cashierId)
            ->where('transactions.status', 'completed')
            ->whereDate('transactions.transaction_date', $today)
            ->select(
                'products.name',
                DB::raw('SUM(transaction_items.quantity) as total_quantity')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->first();

        // Most used payment method
        $topPaymentMethod = Transaction::where('cashier_id', $cashierId)
            ->where('status', 'completed')
            ->whereDate('transaction_date', $today)
            ->select('payment_method', DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->orderByDesc('count')
            ->first();

        return [
            'today_sales' => $todaySales,
            'today_transactions' => $todayTransactions,
            'avg_transaction_value' => $avgTransactionValue,
            'customers_served' => $customersServed,
            'current_session' => $sessionInfo,
            'top_product' => $topProduct,
            'top_payment_method' => $topPaymentMethod?->payment_method,
        ];
    }
}
