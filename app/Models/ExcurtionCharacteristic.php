<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExcurtionCharacteristic extends Model
{
    use HasFactory;

    protected $fillable = [
        'characteristic_id',
        'excurtion_id',
    ];
}
