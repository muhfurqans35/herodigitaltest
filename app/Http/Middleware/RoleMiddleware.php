<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $roles)
    {
        $allowedRoles = explode('|', $roles);

        $user = auth()->user();

        if (!$user) {
            return redirect('/')->with('error', 'Silakan login terlebih dahulu.');
        }

        if ($user->roles()->whereIn('name', $allowedRoles)->exists()) {
            return $next($request);
        }

        return redirect('/')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
    }
}
