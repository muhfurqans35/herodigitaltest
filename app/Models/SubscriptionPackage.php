<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPackage extends Model
{
    protected $fillable = ['name', 'max_tenants', 'max_users_per_tenant', 'price_per_month', 'feature_json'];

    protected $casts = [
        'features_json' => 'array',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
