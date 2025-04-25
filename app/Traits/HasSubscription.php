<?php

namespace App\Traits;

use Carbon\Carbon;

trait HasSubscription
{
    /**
     * Get the active subscription for the user
     */
    public function getActiveSubscriptionAttribute()
    {
        return $this->subscription()
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->with('package')
            ->first();
    }

    /**
     * Get the name of the current subscription package
     * 
     * @return string
     */
    public function getSubscriptionPackageNameAttribute()
    {
        return $this->activeSubscription ?
            $this->activeSubscription->package->name :
            'Free';
    }

    /**
     * Check if user is on a free plan
     * 
     * @return bool
     */
    public function isOnFreePlan()
    {
        return !$this->activeSubscription;
    }

    /**
     * Check if user can access transactions from given date
     * 
     * @param Carbon $date
     * @return bool
     */
    public function canAccessTransactionHistory(Carbon $date)
    {
        if (!$this->activeSubscription) {
            // Free plan - 1 month history
            return $date->greaterThanOrEqualTo(now()->subMonth());
        }

        $packageName = $this->activeSubscription->package->name;

        if ($packageName === 'Basic') {
            // Basic plan - 3 months history
            return $date->greaterThanOrEqualTo(now()->subMonths(3));
        }

        if ($packageName === 'Pro') {
            // Pro plan - unlimited history
            return true;
        }

        // Default to 1 month for unrecognized packages
        return $date->greaterThanOrEqualTo(now()->subMonth());
    }

    /**
     * Check if the user has permission to use advanced service features
     * 
     * @return bool
     */
    public function hasAdvancedServiceFeatures()
    {
        if (!$this->activeSubscription) {
            return false; // Free plan has basic service features only
        }

        // Basic and Pro plans have advanced service features
        return in_array($this->activeSubscription->package->name, ['Basic', 'Pro']);
    }

    /**
     * Check if user has access to advanced reporting features
     * 
     * @return bool
     */
    public function hasAdvancedReportingFeatures()
    {
        if (!$this->activeSubscription) {
            return false; // Free plan has no advanced reporting
        }

        return $this->activeSubscription->package->name === 'Pro';
    }

    /**
     * Check if user can create a new tenant
     * 
     * @return bool
     */
    public function canCreateTenant()
    {
        $maxTenants = $this->getMaxTenants();
        $currentTenants = $this->tenants()->count();

        return $currentTenants < $maxTenants;
    }

    /**
     * Get maximum allowed tenants based on subscription
     * 
     * @return int
     */
    public function getMaxTenants()
    {
        if (!$this->activeSubscription) {
            return 1; // Free plan: 1 tenant
        }

        switch ($this->activeSubscription->package->name) {
            case 'Basic':
                return 1;
            case 'Pro':
                return 2;
            default:
                return 1;
        }
    }

    /**
     * Get maximum allowed users per tenant based on subscription
     * 
     * @return int
     */
    public function getMaxUsersPerTenant()
    {
        if (!$this->activeSubscription) {
            return 1; // Free plan: 1 user
        }

        switch ($this->activeSubscription->package->name) {
            case 'Basic':
                return 3;
            case 'Pro':
                return 5;
            default:
                return 1;
        }
    }
}