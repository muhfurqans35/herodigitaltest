<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use \OwenIt\Auditing\Auditable;

class Transaction extends Model implements AuditableContract
{
    use Auditable, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['transaction_date', 'total_price', 'customer_name', 'meta'];

    protected $casts = [
        'transaction_date' => 'datetime',
        'meta' => 'array',
    ];

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }
}