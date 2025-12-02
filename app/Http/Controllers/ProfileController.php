<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\ActivityLog;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Exception;

class ProfileController extends Controller
{
    public function __construct(
        protected SettingService $settingService
    ) {
        $this->middleware('auth');
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $activeTab = $request->get('tab', 'profile');

        // Get activity logs
        $activityLogs = ActivityLog::getRecentActivities($user->id, 30, 50);

        // Get login history
        $loginHistory = ActivityLog::getLoginHistory($user->id, 30);

        // Get active sessions
        $activeSessions = $this->getActiveSessions($user->id);

        return view('profile.edit', [
            'user' => $user,
            'activeTab' => $activeTab,
            'activityLogs' => $activityLogs,
            'loginHistory' => $loginHistory,
            'activeSessions' => $activeSessions,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $oldData = $user->toArray();

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Log activity
        ActivityLog::log(
            'profile_update',
            'Updated profile information',
            $user,
            [
                'old_data' => $oldData,
                'new_data' => $user->toArray(),
            ]
        );

        return Redirect::route('profile.edit')->with('success', 'Profile updated successfully');
    }

    /**
     * Update the user's password
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'current_password' => ['required', 'current_password'],
                'password' => ['required', 'confirmed', 'min:8'],
            ]);

            // Validate password against policy
            $validation = $this->settingService->validatePassword($request->password);

            if (!$validation['valid']) {
                return back()->withErrors(['password' => $validation['errors']])->withInput();
            }

            $user = $request->user();
            $user->password = Hash::make($request->password);
            $user->save();

            // Log activity
            ActivityLog::log(
                'password_change',
                'Changed password'
            );

            return Redirect::route('profile.edit', ['tab' => 'password'])
                ->with('success', 'Password updated successfully');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Update the user's avatar
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'avatar' => ['required', 'image', 'max:2048'], // Max 2MB
            ]);

            $user = $request->user();

            // Delete old avatar if exists
            if ($user->avatar && file_exists(storage_path('app/public/' . $user->avatar))) {
                unlink(storage_path('app/public/' . $user->avatar));
            }

            // Store new avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
            $user->save();

            // Log activity
            ActivityLog::log(
                'avatar_update',
                'Updated profile avatar'
            );

            return back()->with('success', 'Avatar updated successfully');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get activity log
     */
    public function activityLog(Request $request)
    {
        $user = $request->user();
        $days = $request->get('days', 30);

        $activityLogs = ActivityLog::getRecentActivities($user->id, $days, 100);

        return response()->json([
            'success' => true,
            'data' => $activityLogs,
        ]);
    }

    /**
     * Get login history
     */
    public function loginHistory(Request $request)
    {
        $user = $request->user();
        $days = $request->get('days', 30);

        $loginHistory = ActivityLog::getLoginHistory($user->id, $days);

        return response()->json([
            'success' => true,
            'data' => $loginHistory,
        ]);
    }

    /**
     * Logout all other sessions
     */
    public function logoutAllSessions(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'password' => ['required', 'current_password'],
            ]);

            // Logout all other sessions
            Auth::logoutOtherDevices($request->password);

            // Log activity
            ActivityLog::log(
                'logout_all_sessions',
                'Logged out all other sessions'
            );

            return back()->with('success', 'All other sessions have been logged out successfully');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get active sessions for a user
     */
    protected function getActiveSessions(int $userId): array
    {
        // Get sessions from database
        $sessions = \DB::table('sessions')
            ->where('user_id', $userId)
            ->orderBy('last_activity', 'desc')
            ->get();

        $activeSessions = [];

        foreach ($sessions as $session) {
            $agent = $this->parseUserAgent($session->user_agent);

            $activeSessions[] = [
                'id' => $session->id,
                'ip_address' => $session->ip_address,
                'user_agent' => $session->user_agent,
                'browser' => $agent['browser'],
                'os' => $agent['os'],
                'device' => $agent['device'],
                'last_activity' => date('Y-m-d H:i:s', $session->last_activity),
                'is_current' => $session->id === Session::getId(),
            ];
        }

        return $activeSessions;
    }

    /**
     * Parse user agent string
     */
    protected function parseUserAgent(?string $userAgent): array
    {
        if (!$userAgent) {
            return [
                'browser' => 'Unknown',
                'os' => 'Unknown',
                'device' => 'Unknown on Unknown',
            ];
        }

        $browser = 'Unknown';
        $os = 'Unknown';

        // Detect browser
        if (str_contains($userAgent, 'Firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($userAgent, 'Chrome')) {
            $browser = 'Chrome';
        } elseif (str_contains($userAgent, 'Safari')) {
            $browser = 'Safari';
        } elseif (str_contains($userAgent, 'Edge')) {
            $browser = 'Edge';
        } elseif (str_contains($userAgent, 'Opera') || str_contains($userAgent, 'OPR')) {
            $browser = 'Opera';
        }

        // Detect OS
        if (str_contains($userAgent, 'Windows')) {
            $os = 'Windows';
        } elseif (str_contains($userAgent, 'Mac')) {
            $os = 'MacOS';
        } elseif (str_contains($userAgent, 'Linux')) {
            $os = 'Linux';
        } elseif (str_contains($userAgent, 'Android')) {
            $os = 'Android';
        } elseif (str_contains($userAgent, 'iOS') || str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) {
            $os = 'iOS';
        }

        return [
            'browser' => $browser,
            'os' => $os,
            'device' => "$browser on $os",
        ];
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Log activity before deletion
        ActivityLog::log(
            'account_delete',
            'Deleted own account'
        );

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
