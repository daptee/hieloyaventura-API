<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CharacteristicType extends Model
{
    use HasFactory;
    const SHOW = [];
    const INDEX = [];
    protected $hidden = ['created_at', 'updated_at'];
    public $timestamps = false;
    protected $fillable = [
        'name',
    ];
}
