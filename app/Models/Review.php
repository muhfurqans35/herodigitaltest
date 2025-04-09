<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model implements AuditableContract
{
    use HasUuids, Auditable, SoftDeletes;

    protected $fillable = [
        'booking_id',
        'rating',
        'comment'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}

