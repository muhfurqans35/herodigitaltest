<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;



class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['name', 'email', 'password', 'global_role_id', 'image', 'phone'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }


    public function getActiveSubscriptionAttribute()
    {
        return $this->subscriptions()
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->where('status', 'active')
            ->orderByDesc('starts_at')
            ->with('package') // opsional, kalau kamu ada relasi ke package
            ->first();
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->orderByDesc('starts_at'); // gunakan kolom yang relevan
    }

    public function globalRole()
    {
        return $this->belongsTo(GlobalRole::class);
    }

    public function tenants()
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user')
            ->withPivot('role_id')->withTimestamps();
    }

    public function tenantRoles()
    {
        return $this->belongsToMany(TenantRole::class, 'tenant_user', 'user_id', 'role_id')
            ->withPivot('tenant_id', 'invited_by')
            ->withTimestamps();
    }

    public function roles()
    {
        return $this->belongsToMany(TenantRole::class, 'tenant_user', 'user_id', 'role_id')
            ->withPivot('tenant_id')
            ->withTimestamps();
    }

    public function isAdmin()
    {
        $adminRole = GlobalRole::where('name', 'admin')->first();
        return $adminRole && $this->global_role_id === $adminRole->id;
    }

    public function isSuperAdmin()
    {
        $superadminRole = GlobalRole::where('name', 'superadmin')->first();
        return $superadminRole && $this->global_role_id === $superadminRole->id;
    }

    public function isAdminOrSuperadmin()
    {
        return $this->isSuperAdmin() || $this->isAdmin();
    }

    /**
     * Get tenant IDs for this user
     * 
     * @return array
     */
    public function getTenantIds()
    {
        return $this->tenants->pluck('id')->toArray();
    }


}
