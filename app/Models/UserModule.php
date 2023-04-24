<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserModule extends Model
{
    use HasFactory;

    protected $table = 'user_modules';

    public function module()
    {
        return $this->hasOne(Module::class, 'id', 'module_id');
    }
}
