<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() ||
            ($request->user() instanceof MustVerifyEmail &&
            !$request->user()->hasVerifiedEmail())) {

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your email address is not verified.',
                    'verification_required' => true
                ], 409);
            }

            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}

