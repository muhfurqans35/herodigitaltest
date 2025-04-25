<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Models\Service;
use App\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // If no user is logged in or user is admin/superadmin, skip checks
        if (!$user || $user->isAdmin() || $user->isSuperAdmin()) {
            return $next($request);
        }

        // Get active subscription
        $subscription = $user->activeSubscription;

        // If no active subscription, default to Free package limits
        $packageLimits = $this->getPackageLimits($subscription ? $subscription->package->name : 'Free');

        // Check limits based on the requested route/action
        $routeName = $request->route()->getName();

        if ($request->isMethod('POST') && $request->route()->uri == 'tenants') {
            if (!$this->checkTenantLimit($user, $packageLimits)) {
                return $this->limitExceededResponse('tenant');
            }
        }

        // For service creation, regardless of the route used
        if ($request->isMethod('POST') && $request->route()->uri == 'services') {
            if (!$this->checkServiceLimit($user, $packageLimits)) {
                return $this->limitExceededResponse('service');
            }
        }
        // For service creation, regardless of the route used
        if ($request->isMethod('POST') && $request->route()->uri == 'transactions') {
            if (!$this->checkServiceLimit($user, $packageLimits)) {
                return $this->limitExceededResponse('service');
            }
        }

        // Handle user invitation to tenant
        if ($routeName === 'tenant.invite') {
            if (!$this->checkUserPerTenantLimit($request->tenant_id, $packageLimits)) {
                return $this->limitExceededResponse('user');
            }
        }

        // // Handle transaction creation
        // if (in_array($routeName, ['transaction.store', 'transaction.create'])) {
        //     if (!$this->checkTransactionLimit($user, $packageLimits)) {
        //         return $this->limitExceededResponse('transaction');
        //     }
        // }


        return $next($request);
    }

    /**
     * Get package limits based on subscription package name
     *
     * @param string $packageName
     * @return array
     */
    protected function getPackageLimits($packageName)
    {
        $packages = [
            'Free' => [
                'max_tenants' => 1,
                'max_users_per_tenant' => 1,
                'max_transactions' => 5,
                'max_services' => 2,
                'transaction_history_months' => 1,
            ],
            'Basic' => [
                'max_tenants' => 1,
                'max_users_per_tenant' => 3,
                'max_transactions' => PHP_INT_MAX, // unlimited
                'max_services' => PHP_INT_MAX, // unlimited
                'transaction_history_months' => 3,
            ],
            'Pro' => [
                'max_tenants' => 2,
                'max_users_per_tenant' => 5,
                'max_transactions' => PHP_INT_MAX, // unlimited
                'max_services' => PHP_INT_MAX, // unlimited
                'transaction_history_months' => PHP_INT_MAX, // unlimited
            ],
        ];

        return $packages[$packageName] ?? $packages['Free'];
    }

    /**
     * Check if user has reached tenant limit
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param array $limits
     * @return bool
     */
    protected function checkTenantLimit($user, $limits)
    {
        $tenantCount = $user->tenants()->count();
        return $tenantCount < $limits['max_tenants'];
    }

    /**
     * Check if tenant has reached user limit
     *
     * @param string $tenantId
     * @param array $limits
     * @return bool
     */
    protected function checkUserPerTenantLimit($tenantId, $limits)
    {
        $tenant = Tenant::findOrFail($tenantId);
        $userCount = $tenant->users()->count();
        return $userCount < $limits['max_users_per_tenant'];
    }

    /**
     * Check if user has reached transaction limit
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param array $limits
     * @return bool
     */
    protected function checkTransactionLimit($user, $limits)
    {
        // If unlimited transactions
        if ($limits['max_transactions'] === PHP_INT_MAX) {
            return true;
        }

        // Get transaction count from all user's tenants
        $transactionCount = 0;
        foreach ($user->tenants as $tenant) {
            $transactionCount += Transaction::where('tenant_id', $tenant->id)->count();
        }

        return $transactionCount < $limits['max_transactions'];
    }

    /**
     * Check if user has reached service limit
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param array $limits
     * @return bool
     */
    protected function checkServiceLimit($user, $limits)
    {
        // If unlimited services
        if ($limits['max_services'] === PHP_INT_MAX) {
            return true;
        }

        // Get service count from all user's tenants
        $serviceCount = 0;
        foreach ($user->tenants as $tenant) {
            $serviceCount += Service::where('tenant_id', $tenant->id)->count();
        }

        return $serviceCount < $limits['max_services'];
    }

    /**
     * Generate response for limit exceeded
     *
     * @param string $limitType
     * @return \Illuminate\Http\Response
     */
    protected function limitExceededResponse($limitType): Response
    {
        $messages = [
            'tenant' => 'Batas jumlah tenant untuk paket langganan Anda telah tercapai.',
            'user' => 'Batas jumlah pengguna per tenant untuk paket langganan Anda telah tercapai.',
            'transaction' => 'Batas jumlah transaksi untuk paket langganan Anda telah tercapai.',
            'service' => 'Batas jumlah layanan untuk paket langganan Anda telah tercapai.',
        ];

        $message = $messages[$limitType] ?? 'Batas paket langganan Anda telah tercapai.';
        $message .= ' Silahkan upgrade paket langganan Anda untuk mendapatkan fitur lebih.';

        if (request()->expectsJson()) {
            return response()->json(['error' => $message], Response::HTTP_FORBIDDEN);
        }

        return redirect()->back()->with('error', $message);
    }
}