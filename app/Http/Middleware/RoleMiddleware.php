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
                            abort(403, 'User is not authenticated.');
                        }

                        $user = Auth::user();

                        // Check the user's role
                        if ($user->role !== $role) {
                            abort(403, 'This action is unauthorized.');
                        }

                        // Log::info('Middleware Role:', ['role' => $role]);

                        // Log::info('Authenticated User Role:', ['role' => $user->role]);

                        return $next($request);
    }
}
