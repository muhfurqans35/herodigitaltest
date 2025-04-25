<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckTenantOrGlobalRole
{
    public function handle(Request $request, Closure $next, string $tenantRoleName, string ...$globalRoles): Response
    {
        $user = Auth::user();

        // Jika user tidak ditemukan, batalkan dan beri pesan error
        if (!$user) {
            abort(403, 'User tidak ditemukan.');
        }

        // 1. Cek tenantId dari route atau parameter tenant
        $tenantId = $request->route('tenant')?->id ?? $request->tenant_id;

        // 2. Jika tenantId ditemukan, cek tenant role terlebih dahulu
        if ($tenantId) {
            // Cek apakah user memiliki role yang sesuai di tenant
            $hasTenantRole = $user->tenants()
                ->where('tenant_id', $tenantId)
                ->whereHas('roles', function ($q) use ($tenantRoleName) {
                    $q->where('name', $tenantRoleName);
                })
                ->exists();

            if ($hasTenantRole) {
                // Jika user memiliki tenant role yang valid, lanjutkan
                return $next($request);
            }
        }

        // 3. Jika tidak ada tenantId atau tidak memiliki tenant role, cek global role
        if (!empty($globalRoles) && $user->globalRole && in_array($user->globalRole->name, $globalRoles)) {
            return $next($request);
        }
        // 4. Cek subscription user - HANYA subscription berbayar yang diizinkan
        if (
            $user->subscription &&
            $user->subscription->package &&
            $user->subscription->package->name !== 'Free'
        ) {
            return $next($request);
        }

        // 5. Jika tidak ada izin apapun, beri pesan error yang sesuai
        if ($tenantId) {
            abort(403, 'Kamu tidak memiliki akses di tenant ini.');
        } else {
            abort(403, 'Kamu tidak memiliki izin yang diperlukan untuk mengakses fitur ini.');
        }
    }
}