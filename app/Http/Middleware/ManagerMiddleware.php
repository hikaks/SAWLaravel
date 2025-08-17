<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManagerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * This middleware allows access to admin and manager roles.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.'
                ], 401);
            }
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        // Check if user has manager or admin role
        if (!auth()->user()->canManage()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Manager or admin access required. You do not have permission to perform this action.'
                ], 403);
            }

            abort(403, 'Manager or admin access required. You do not have permission to access this page.');
        }

        // Check if user is active
        if (!auth()->user()->is_active) {
            auth()->logout();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated. Please contact administrator.'
                ], 403);
            }

            return redirect()->route('login')->with('error', 'Your account has been deactivated. Please contact administrator.');
        }

        return $next($request);
    }
}



