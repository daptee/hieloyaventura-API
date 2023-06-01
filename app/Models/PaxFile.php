<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaxFile extends Model
{
    protected $fillable = [
        'pax_id',
        'url'
    ];

    use HasFactory;
}
