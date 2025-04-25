<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantRole extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'name', 'label'];

    // Relasi ke user lewat tenant_user pivot (banyak ke banyak)
    public function users()
    {
        return $this->belongsToMany(User::class, 'tenant_user', 'role_id', 'user_id');
    }

}