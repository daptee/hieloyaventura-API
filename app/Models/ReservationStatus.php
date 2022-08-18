<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationStatus extends Model
{
    const RECHAZADA = 1;
    const INICIADA = 2;

    protected $fillable = [
        'name',
    ];

    use HasFactory;
}
