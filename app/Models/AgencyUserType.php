<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgencyUserType extends Model
{
    use HasFactory;

    protected $table = 'agency_user_types';

    const ADMIN = 1;
    const VENDEDOR = 2;

    protected $hidden = ['created_at', 'updated_at'];
}
