<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationPax extends Model
{
    use HasFactory;
    protected $fillable = [
        'pax_type_id',
        'user_reservation_id',
        'price',
        'quantity',
    ];
}
