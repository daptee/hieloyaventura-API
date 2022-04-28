<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lenguage extends Model
{
    use HasFactory;

    const SHOW = [];
    const INDEX = [];

    const SPA = 1;
    const EN = 2;
    const POR = 3;

    protected $fillable = [
        'name',
        'abreviation',
    ];
    public $timestamps = false;

}
