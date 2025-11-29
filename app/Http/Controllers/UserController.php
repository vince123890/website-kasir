<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Store;
use App\Repositories\UserRepository;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    protected UserRepository $userRepository;
    protected UserService $userService;

    public function __construct(UserRepository $userRepository, UserService $userService)
    {
        $this->userRepository = $userRepository;
        $this->userService = $userService;
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $filters = $request->only(['role', 'status', 'store_id', 'tenant_id']);

        // Determine scope based on role
        $user = auth()->user();
        if ($user->hasRole('Administrator SaaS')) {
            $users = $this->userRepository->getAllPaginated(15, $search, $filters);
        } elseif ($user->hasRole('Tenant Owner')) {
            $filters['tenant_id'] = $user->tenant_id;
            $users = $this->userRepository->getAllPaginated(15, $search, $filters);
        } else {
            // Admin Toko - only see store users
            $users = $this->userRepository->getByStore($user->store_id, 15);
        }

        // Get filter options
        $roles = Role::all();
        $stores = $user->hasRole('Administrator SaaS')
            ? Store::all()
            : Store::where('tenant_id', $user->tenant_id)->get();

        return view('users.index', compact('users', 'roles', 'stores', 'search', 'filters'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $user = auth()->user();

        // Get available roles based on permission
        if ($user->hasRole('Administrator SaaS')) {
            $roles = Role::all();
            $tenants = Tenant::all();
            $stores = Store::all();
        } elseif ($user->hasRole('Tenant Owner')) {
            $roles = Role::whereIn('name', ['Tenant Owner', 'Admin Toko', 'Kasir'])->get();
            $tenants = Tenant::where('id', $user->tenant_id)->get();
            $stores = Store::where('tenant_id', $user->tenant_id)->get();
        } else {
            // Admin Toko
            $roles = Role::whereIn('name', ['Admin Toko', 'Kasir'])->get();
            $tenants = Tenant::where('id', $user->tenant_id)->get();
            $stores = Store::where('id', $user->store_id)->get();
        }

        return view('users.create', compact('roles', 'tenants', 'stores'));
    }

    /**
     * Store a newly created user
     */
    public function store(UserRequest $request)
    {
        try {
            $data = $request->validated();

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
            }

            $user = $this->userService->createUser($data);

            return redirect()
                ->route('users.index')
                ->with('success', 'User berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan user: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified user
     */
    public function show(int $id)
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return redirect()
                ->route('users.index')
                ->with('error', 'User tidak ditemukan.');
        }

        // Check permission
        $authUser = auth()->user();
        if (!$authUser->hasRole('Administrator SaaS')) {
            if ($authUser->hasRole('Tenant Owner') && $user->tenant_id !== $authUser->tenant_id) {
                abort(403, 'Unauthorized action.');
            }
            if ($authUser->hasRole('Admin Toko') && $user->store_id !== $authUser->store_id) {
                abort(403, 'Unauthorized action.');
            }
        }

        // TODO: Get activity log and login history
        // For now, we'll pass empty arrays
        $activities = [];
        $loginHistory = [];

        return view('users.show', compact('user', 'activities', 'loginHistory'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(int $id)
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return redirect()
                ->route('users.index')
                ->with('error', 'User tidak ditemukan.');
        }

        // Check permission
        $authUser = auth()->user();
        if (!$authUser->hasRole('Administrator SaaS')) {
            if ($authUser->hasRole('Tenant Owner') && $user->tenant_id !== $authUser->tenant_id) {
                abort(403, 'Unauthorized action.');
            }
            if ($authUser->hasRole('Admin Toko') && $user->store_id !== $authUser->store_id) {
                abort(403, 'Unauthorized action.');
            }
        }

        // Get available roles and stores
        if ($authUser->hasRole('Administrator SaaS')) {
            $roles = Role::all();
            $tenants = Tenant::all();
            $stores = Store::all();
        } elseif ($authUser->hasRole('Tenant Owner')) {
            $roles = Role::whereIn('name', ['Tenant Owner', 'Admin Toko', 'Kasir'])->get();
            $tenants = Tenant::where('id', $authUser->tenant_id)->get();
            $stores = Store::where('tenant_id', $authUser->tenant_id)->get();
        } else {
            $roles = Role::whereIn('name', ['Admin Toko', 'Kasir'])->get();
            $tenants = Tenant::where('id', $authUser->tenant_id)->get();
            $stores = Store::where('id', $authUser->store_id)->get();
        }

        return view('users.edit', compact('user', 'roles', 'tenants', 'stores'));
    }

    /**
     * Update the specified user
     */
    public function update(UserRequest $request, int $id)
    {
        try {
            $data = $request->validated();

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                $user = User::findOrFail($id);

                // Delete old avatar
                if ($user->avatar_path) {
                    Storage::disk('public')->delete($user->avatar_path);
                }

                $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
            }

            $this->userService->updateUser($id, $data);

            return redirect()
                ->route('users.index')
                ->with('success', 'User berhasil diupdate.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal mengupdate user: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy(int $id)
    {
        try {
            $this->userService->deleteUser($id);

            return redirect()
                ->route('users.index')
                ->with('success', 'User berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }

    /**
     * Restore soft deleted user
     */
    public function restore(int $id)
    {
        try {
            $this->userService->restoreUser($id);

            return redirect()
                ->route('users.index')
                ->with('success', 'User berhasil direstore.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal merestore user: ' . $e->getMessage());
        }
    }

    /**
     * Force logout all sessions
     */
    public function logoutAllSessions(int $id)
    {
        try {
            $this->userService->logoutAllSessions($id);

            return redirect()
                ->back()
                ->with('success', 'Semua sesi user berhasil dilogout.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal logout sesi: ' . $e->getMessage());
        }
    }

    /**
     * Send activation email
     */
    public function sendActivationEmail(int $id)
    {
        try {
            $this->userService->sendActivationEmail($id);

            return redirect()
                ->back()
                ->with('success', 'Email aktivasi berhasil dikirim.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal mengirim email: ' . $e->getMessage());
        }
    }

    /**
     * Force password change
     */
    public function forcePasswordChange(int $id)
    {
        try {
            $this->userService->forcePasswordChange($id);

            return redirect()
                ->back()
                ->with('success', 'User akan diminta mengganti password pada login berikutnya.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        try {
            $count = 0;
            switch ($request->action) {
                case 'activate':
                    $count = $this->userService->bulkActivate($request->user_ids);
                    break;
                case 'deactivate':
                    $count = $this->userService->bulkDeactivate($request->user_ids);
                    break;
                case 'delete':
                    $count = $this->userService->bulkDelete($request->user_ids);
                    break;
            }

            return redirect()
                ->route('users.index')
                ->with('success', "{$count} user berhasil diproses.");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }
}
