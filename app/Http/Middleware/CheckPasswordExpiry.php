<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPasswordExpiry
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            // Check if user must change password
            if ($user->must_change_password && !$request->routeIs('password.*')) {
                return redirect()->route('password.request')
                    ->with('warning', 'You must change your password before continuing.');
            }

            // Check if password has expired
            if ($user->password_expires_at && $user->password_expires_at->isPast() && !$request->routeIs('password.*')) {
                return redirect()->route('password.request')
                    ->with('warning', 'Your password has expired. Please reset your password to continue.');
            }

            // Check if password is expiring soon (within 7 days)
            if ($user->password_expires_at
                && $user->password_expires_at->isFuture()
                && $user->password_expires_at->diffInDays(now()) <= 7
                && !session()->has('password_expiry_warning_shown')
            ) {
                session()->flash('warning', 'Your password will expire in ' . $user->password_expires_at->diffInDays(now()) . ' days. Please consider changing it soon.');
                session()->put('password_expiry_warning_shown', true);
            }
        }

        return $next($request);
    }
}
