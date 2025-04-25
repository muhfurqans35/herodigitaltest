<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckGlobalRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user || !$user->globalRole || !in_array($user->globalRole->name, $roles)) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}

