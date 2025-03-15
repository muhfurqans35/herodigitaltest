<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'service', 'session', 'total_price', 'status', 'user_id', 'snap_token', 'snap_token_expires_at'];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
