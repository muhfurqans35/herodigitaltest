<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
class Booking extends Model implements AuditableContract
{
    use HasUuids, HasFactory, Auditable, SoftDeletes;

    protected $fillable = [
        'user_id',
        'service_id',
        'service_name',
        'price_at_booking',
        'date',
        'session',
        'units',
        'total_price',
        'status',
        'start_time',
        'end_time',
        'snap_token',
        'snap_token_expires_at'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function review()
    {
        return $this->hasOne(Review::class);
    }
}
