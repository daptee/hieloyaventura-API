<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaxType extends Model
{
    const ADULTO = 1;
    const NINO = 2;
    protected $fillable = [
        'name',
    ];

    use HasFactory;
}
