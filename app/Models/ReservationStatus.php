<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationStatus extends Model
{
    const REJECTED = 1;
    const STARTED = 2;
    const COMPLETED = 3;
    const AUTOMATIC_CANCELED = 4;
    const PAX_PENDING = 5;


    protected $fillable = [
        'name',
    ];

    use HasFactory;
}
