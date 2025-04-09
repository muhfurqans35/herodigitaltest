<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model implements AuditableContract
{
    use HasFactory, HasUuids, Auditable, SoftDeletes;

    protected $fillable = ['name', 'price', 'total_units', 'manual_book_path', 'manual_book_name', 'extra_info'];

    protected $casts = [
        'extra_info' => 'array',
    ];
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
