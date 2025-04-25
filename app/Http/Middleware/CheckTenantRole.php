<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckTenantRole
{
    public function handle(Request $request, Closure $next, string $roleName): Response
    {
        $user = Auth::user();
        $tenantId = $request->route('tenant')?->id ?? $request->tenant_id;

        if (!$tenantId) {
            abort(403, 'Tenant tidak ditemukan.');
        }

        // Cek apakah user memiliki role yang sesuai di tenant
        $hasRole = $user->tenants()
            ->where('tenant_id', $tenantId)
            ->whereHas('roles', function ($q) use ($roleName) {
                $q->where('name', $roleName);
            })
            ->exists();

        if (!$hasRole) {
            abort(403, 'Kamu tidak memiliki akses sebagai ' . $roleName . ' di tenant ini.');
        }

        return $next($request);
    }
}