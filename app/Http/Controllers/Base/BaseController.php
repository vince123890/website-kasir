<?php

namespace App\Http\Controllers\Base;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

abstract class BaseController extends Controller
{
    /**
     * Flash a success message
     */
    protected function flashSuccess(string $message): void
    {
        session()->flash('success', $message);
    }

    /**
     * Flash an error message
     */
    protected function flashError(string $message): void
    {
        session()->flash('error', $message);
    }

    /**
     * Flash a warning message
     */
    protected function flashWarning(string $message): void
    {
        session()->flash('warning', $message);
    }

    /**
     * Flash an info message
     */
    protected function flashInfo(string $message): void
    {
        session()->flash('info', $message);
    }

    /**
     * Check if user has permission
     */
    protected function checkPermission(string $permission): bool
    {
        return auth()->user()->can($permission);
    }

    /**
     * Abort if user doesn't have permission
     */
    protected function authorizePermission(string $permission): void
    {
        if (!$this->checkPermission($permission)) {
            abort(403, 'Unauthorized action.');
        }
    }

    /**
     * Redirect back with success message
     */
    protected function redirectWithSuccess(string $message, string $route = null): RedirectResponse
    {
        $this->flashSuccess($message);
        return $route ? redirect()->route($route) : back();
    }

    /**
     * Redirect back with error message
     */
    protected function redirectWithError(string $message, string $route = null): RedirectResponse
    {
        $this->flashError($message);
        return $route ? redirect()->route($route) : back();
    }
}
