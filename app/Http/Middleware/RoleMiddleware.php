<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return response()->json(['error' => 'User is not authenticated.'], 403);
        }

        $user = Auth::user();

        // Check the user's role
        if ($user->role !== $role) {
            return response()->json(['error' => 'This action is unauthorized.'], 403);
        }

        return $next($request);
    }
}
