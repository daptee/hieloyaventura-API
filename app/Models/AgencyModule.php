<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgencyModule extends Model
{
    use HasFactory;

    protected $table = 'agency_modules';

    protected $fillable = [
        'name',
    ];
}
