<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class FilamentAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If the user is not authenticated, let Filament's authentication middleware handle it
        if (! Auth::check()) {
            return $next($request);
        }

        // If the user is authenticated but not an admin, redirect to dashboard
        if (! Auth::user()->isAdmin()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
            }

            return redirect()->route('admin.instructions')->with('error', 'You do not have permission to access the admin panel.');
        }

        return $next($request);
    }
}
