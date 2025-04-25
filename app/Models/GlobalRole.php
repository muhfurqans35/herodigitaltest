<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalRole extends Model
{
    protected $fillable = ['name', 'label'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}

