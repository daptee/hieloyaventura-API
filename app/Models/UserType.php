<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
    use HasFactory;

    protected $table = 'user_types';

    const CLIENTE = 1;
    const ADMIN = 2;

    protected $hidden = ['created_at', 'updated_at'];
}
