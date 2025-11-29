<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequest;
use App\Repositories\StoreRepository;
use App\Services\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Exception;

class StoreController extends Controller
{
    protected StoreRepository $storeRepository;
    protected StoreService $storeService;

    public function __construct(
        StoreRepository $storeRepository,
        StoreService $storeService
    ) {
        $this->storeRepository = $storeRepository;
        $this->storeService = $storeService;
    }

    /**
     * Display a listing of stores
     */
    public function index(Request $request): View
    {
        $search = $request->get('search');
        $filters = [
            'tenant_id' => auth()->user()->tenant_id,
            'is_active' => $request->get('is_active'),
            'city' => $request->get('city'),
            'province' => $request->get('province'),
        ];

        $stores = $this->storeRepository->getAllPaginated(15, $search, $filters);

        return view('stores.index', compact('stores', 'search', 'filters'));
    }

    /**
     * Show the form for creating a new store
     */
    public function create(): View
    {
        $timezones = timezone_identifiers_list();
        $currencies = $this->getCurrencyList();
        $roundingMethods = $this->getRoundingMethods();

        return view('stores.create', compact('timezones', 'currencies', 'roundingMethods'));
    }

    /**
     * Store a newly created store in storage
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['tenant_id'] = auth()->user()->tenant_id;

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('stores/logos', 'public');
                $data['logo'] = $logoPath;
            }

            $store = $this->storeService->createStore($data);

            return redirect()
                ->route('stores.show', $store->id)
                ->with('success', 'Toko berhasil ditambahkan.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan toko: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified store
     */
    public function show(int $id): View
    {
        $store = $this->storeRepository->getWithStatistics($id);

        if (!$store || $store->tenant_id !== auth()->user()->tenant_id) {
            abort(404);
        }

        $statistics = $this->storeService->getStoreStatistics($id);

        // Get users breakdown by role
        $usersBreakdown = $store->users()
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->selectRaw('roles.name as role_name, COUNT(*) as count')
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->groupBy('roles.name')
            ->get();

        return view('stores.show', compact('store', 'statistics', 'usersBreakdown'));
    }

    /**
     * Show the form for editing the specified store
     */
    public function edit(int $id): View
    {
        $store = $this->storeRepository->find($id);

        if (!$store || $store->tenant_id !== auth()->user()->tenant_id) {
            abort(404);
        }

        $timezones = timezone_identifiers_list();
        $currencies = $this->getCurrencyList();
        $roundingMethods = $this->getRoundingMethods();

        return view('stores.edit', compact('store', 'timezones', 'currencies', 'roundingMethods'));
    }

    /**
     * Update the specified store in storage
     */
    public function update(StoreRequest $request, int $id): RedirectResponse
    {
        try {
            $store = $this->storeRepository->find($id);

            if (!$store || $store->tenant_id !== auth()->user()->tenant_id) {
                abort(404);
            }

            $data = $request->validated();

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $logoPath = $this->storeService->uploadLogo($id, $request->file('logo'));
                $data['logo'] = $logoPath;
            }

            $this->storeService->updateStore($id, $data);

            return redirect()
                ->route('stores.show', $id)
                ->with('success', 'Toko berhasil diperbarui.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui toko: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified store from storage
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $store = $this->storeRepository->find($id);

            if (!$store || $store->tenant_id !== auth()->user()->tenant_id) {
                abort(404);
            }

            $this->storeService->deleteStore($id);

            return redirect()
                ->route('stores.index')
                ->with('success', 'Toko berhasil dihapus.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus toko: ' . $e->getMessage());
        }
    }

    /**
     * Show store settings page
     */
    public function settings(int $id): View
    {
        $store = $this->storeRepository->find($id);

        if (!$store || $store->tenant_id !== auth()->user()->tenant_id) {
            abort(404);
        }

        // Check if user has Admin Toko role
        if (!auth()->user()->hasRole('Admin Toko')) {
            abort(403, 'Unauthorized action.');
        }

        $timezones = timezone_identifiers_list();
        $currencies = $this->getCurrencyList();
        $roundingMethods = $this->getRoundingMethods();

        // Parse operating hours if JSON
        $operatingHours = $store->operating_hours;
        if (is_string($operatingHours)) {
            $operatingHours = json_decode($operatingHours, true);
        }

        // Default operating hours if not set
        if (!$operatingHours) {
            $operatingHours = $this->getDefaultOperatingHours();
        }

        return view('stores.settings', compact('store', 'timezones', 'currencies', 'roundingMethods', 'operatingHours'));
    }

    /**
     * Update store settings
     */
    public function updateSettings(Request $request, int $id): RedirectResponse
    {
        try {
            $store = $this->storeRepository->find($id);

            if (!$store || $store->tenant_id !== auth()->user()->tenant_id) {
                abort(404);
            }

            // Check if user has Admin Toko role
            if (!auth()->user()->hasRole('Admin Toko')) {
                abort(403, 'Unauthorized action.');
            }

            $settings = $request->only([
                'timezone',
                'currency',
                'tax_rate',
                'tax_included',
                'rounding_method',
                'receipt_header',
                'receipt_footer',
                'operating_hours',
            ]);

            // Convert tax_included to boolean
            if (isset($settings['tax_included'])) {
                $settings['tax_included'] = filter_var($settings['tax_included'], FILTER_VALIDATE_BOOLEAN);
            }

            $this->storeService->updateStoreSettings($id, $settings);

            return redirect()
                ->route('stores.settings', $id)
                ->with('success', 'Pengaturan toko berhasil diperbarui.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui pengaturan: ' . $e->getMessage());
        }
    }

    /**
     * Activate a store
     */
    public function activate(Request $request, int $id): RedirectResponse
    {
        try {
            $activateUsers = $request->boolean('activate_users', false);

            $this->storeService->activateStore($id, $activateUsers);

            return redirect()
                ->back()
                ->with('success', 'Toko berhasil diaktifkan.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal mengaktifkan toko: ' . $e->getMessage());
        }
    }

    /**
     * Deactivate a store
     */
    public function deactivate(Request $request, int $id): RedirectResponse
    {
        try {
            $deactivateUsers = $request->boolean('deactivate_users', false);

            $this->storeService->deactivateStore($id, $deactivateUsers);

            return redirect()
                ->back()
                ->with('success', 'Toko berhasil dinonaktifkan.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal menonaktifkan toko: ' . $e->getMessage());
        }
    }

    /**
     * Get list of currencies
     */
    protected function getCurrencyList(): array
    {
        return [
            'IDR' => 'Indonesian Rupiah (IDR)',
            'USD' => 'US Dollar (USD)',
            'EUR' => 'Euro (EUR)',
            'GBP' => 'British Pound (GBP)',
            'JPY' => 'Japanese Yen (JPY)',
            'SGD' => 'Singapore Dollar (SGD)',
            'MYR' => 'Malaysian Ringgit (MYR)',
        ];
    }

    /**
     * Get list of rounding methods
     */
    protected function getRoundingMethods(): array
    {
        return [
            'none' => 'Tidak Ada Pembulatan',
            'round_up' => 'Bulatkan Ke Atas',
            'round_down' => 'Bulatkan Ke Bawah',
            'round_nearest' => 'Bulatkan Ke Terdekat',
            'round_nearest_5' => 'Bulatkan Ke Kelipatan 5',
            'round_nearest_10' => 'Bulatkan Ke Kelipatan 10',
            'round_nearest_100' => 'Bulatkan Ke Kelipatan 100',
            'round_nearest_1000' => 'Bulatkan Ke Kelipatan 1000',
        ];
    }

    /**
     * Get default operating hours
     */
    protected function getDefaultOperatingHours(): array
    {
        return [
            'monday' => ['open' => '08:00', 'close' => '17:00', 'is_open' => true],
            'tuesday' => ['open' => '08:00', 'close' => '17:00', 'is_open' => true],
            'wednesday' => ['open' => '08:00', 'close' => '17:00', 'is_open' => true],
            'thursday' => ['open' => '08:00', 'close' => '17:00', 'is_open' => true],
            'friday' => ['open' => '08:00', 'close' => '17:00', 'is_open' => true],
            'saturday' => ['open' => '08:00', 'close' => '14:00', 'is_open' => true],
            'sunday' => ['open' => '00:00', 'close' => '00:00', 'is_open' => false],
        ];
    }
}
