<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Route;

class MenuHelper
{
    /**
     * Get menu structure for a specific role
     *
     * @param string $role
     * @return array
     */
    public static function getMenuByRole(string $role): array
    {
        $menus = config('menus.' . $role, []);

        return self::filterMenusByPermission($menus);
    }

    /**
     * Filter menu items by user permissions
     *
     * @param array $menus
     * @return array
     */
    protected static function filterMenusByPermission(array $menus): array
    {
        $filteredMenus = [];

        foreach ($menus as $menu) {
            // Check if user has permission
            if (isset($menu['permission']) && $menu['permission'] !== null) {
                if (!auth()->user()->can($menu['permission'])) {
                    continue;
                }
            }

            // Filter children recursively
            if (isset($menu['children'])) {
                $menu['children'] = self::filterMenusByPermission($menu['children']);

                // Remove menu if all children are filtered out
                if (empty($menu['children'])) {
                    continue;
                }
            }

            $filteredMenus[] = $menu;
        }

        return $filteredMenus;
    }

    /**
     * Check if current route matches menu route
     * Supports wildcards (e.g., 'products.*')
     *
     * @param string|null $route
     * @param array|null $query
     * @return bool
     */
    public static function isActiveRoute(?string $route, ?array $query = null): bool
    {
        if (!$route) {
            return false;
        }

        $currentRouteName = Route::currentRouteName();

        // Exact match
        if ($currentRouteName === $route) {
            // Check query parameters if specified
            if ($query) {
                foreach ($query as $key => $value) {
                    if (request()->get($key) != $value) {
                        return false;
                    }
                }
            }
            return true;
        }

        // Wildcard match (e.g., 'products.*' matches 'products.index', 'products.create', etc.)
        if (str_contains($route, '*')) {
            $pattern = str_replace('*', '.*', $route);
            if (preg_match('/^' . $pattern . '$/', $currentRouteName)) {
                return true;
            }
        }

        // Prefix match (e.g., 'products' matches 'products.index', 'products.create', etc.)
        if (str_starts_with($currentRouteName, $route . '.')) {
            return true;
        }

        return false;
    }

    /**
     * Get badge count by calling specified function
     *
     * @param string|null $badgeFunction
     * @return int|null
     */
    public static function getBadgeCount(?string $badgeFunction): ?int
    {
        if (!$badgeFunction) {
            return null;
        }

        // Call the badge function if it exists
        if (method_exists(self::class, $badgeFunction)) {
            return self::$badgeFunction();
        }

        return null;
    }

    /**
     * Get pending purchase orders count
     *
     * @return int
     */
    protected static function getPendingPurchaseOrdersCount(): int
    {
        return \App\Models\PurchaseOrder::where('status', 'pending')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->count();
    }

    /**
     * Get pending stock opname count
     *
     * @return int
     */
    protected static function getPendingStockOpnameCount(): int
    {
        return \App\Models\StockOpname::where('status', 'pending')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->count();
    }

    /**
     * Get pending stock adjustment count
     *
     * @return int
     */
    protected static function getPendingStockAdjustmentCount(): int
    {
        return \App\Models\StockAdjustment::where('status', 'pending')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->count();
    }

    /**
     * Get pending unpacking count
     *
     * @return int
     */
    protected static function getPendingUnpackingCount(): int
    {
        return \App\Models\Unpacking::where('status', 'pending')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->count();
    }

    /**
     * Get pending void requests count
     *
     * @return int
     */
    protected static function getPendingVoidRequestsCount(): int
    {
        $storeId = auth()->user()->store_id;

        if (!$storeId) {
            return 0;
        }

        return \App\Models\Transaction::where('store_id', $storeId)
            ->where('status', 'void_requested')
            ->count();
    }

    /**
     * Get pending session approvals count
     *
     * @return int
     */
    protected static function getPendingSessionApprovalsCount(): int
    {
        $storeId = auth()->user()->store_id;

        if (!$storeId) {
            return 0;
        }

        return \App\Models\StoreSession::where('store_id', $storeId)
            ->where('status', 'pending_approval')
            ->count();
    }

    /**
     * Get low stock count
     *
     * @return int
     */
    protected static function getLowStockCount(): int
    {
        $storeId = auth()->user()->store_id;

        if (!$storeId) {
            return 0;
        }

        return \App\Models\Stock::where('store_id', $storeId)
            ->whereColumn('quantity', '<', 'min_stock')
            ->count();
    }

    /**
     * Get my pending transactions count (for cashier)
     *
     * @return int
     */
    protected static function getMyPendingTransactionsCount(): int
    {
        return \App\Models\PendingTransaction::where('cashier_id', auth()->id())
            ->count();
    }

    /**
     * Generate route URL with query parameters
     *
     * @param string $route
     * @param array|null $query
     * @return string
     */
    public static function getMenuUrl(string $route, ?array $query = null): string
    {
        $url = route($route);

        if ($query) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }

    /**
     * Get active menu item for breadcrumb generation
     *
     * @return array|null
     */
    public static function getActiveMenuItem(): ?array
    {
        $user = auth()->user();
        $role = $user->roles->first()?->name;

        if (!$role) {
            return null;
        }

        $menus = self::getMenuByRole($role);
        $currentRouteName = Route::currentRouteName();

        return self::findActiveMenuItem($menus, $currentRouteName);
    }

    /**
     * Find active menu item recursively
     *
     * @param array $menus
     * @param string $currentRouteName
     * @param array $breadcrumb
     * @return array|null
     */
    protected static function findActiveMenuItem(array $menus, string $currentRouteName, array $breadcrumb = []): ?array
    {
        foreach ($menus as $menu) {
            $currentBreadcrumb = array_merge($breadcrumb, [$menu]);

            if (self::isActiveRoute($menu['route'], $menu['query'] ?? null)) {
                return [
                    'menu' => $menu,
                    'breadcrumb' => $currentBreadcrumb,
                ];
            }

            if (isset($menu['children'])) {
                $result = self::findActiveMenuItem($menu['children'], $currentRouteName, $currentBreadcrumb);
                if ($result) {
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * Generate breadcrumb array from current route
     *
     * @return array
     */
    public static function generateBreadcrumb(): array
    {
        $activeMenuItem = self::getActiveMenuItem();

        if (!$activeMenuItem) {
            return [
                ['label' => 'Home', 'url' => route('dashboard')],
            ];
        }

        $breadcrumb = [
            ['label' => 'Home', 'url' => route('dashboard')],
        ];

        foreach ($activeMenuItem['breadcrumb'] as $index => $item) {
            $isLast = $index === count($activeMenuItem['breadcrumb']) - 1;

            $breadcrumb[] = [
                'label' => $item['label'],
                'url' => $isLast ? null : self::getMenuUrl($item['route'], $item['query'] ?? null),
            ];
        }

        return $breadcrumb;
    }
}
