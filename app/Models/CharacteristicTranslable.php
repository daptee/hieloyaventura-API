<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CharacteristicTranslable extends Model
{
    use HasFactory;

    protected $fillable = [
        'lenguage_id',
        'characteristic_id',
        'name',
        'description',
    ];

    protected $casts = [
        'description' => 'array'
    ];
}
