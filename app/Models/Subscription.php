<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Subscription extends Model
{
    use HasUuids;

    protected $fillable = ['user_id', 'subscription_package_id', 'status', 'starts_at', 'ends_at'];

    public function package()
    {
        return $this->belongsTo(SubscriptionPackage::class, 'subscription_package_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
