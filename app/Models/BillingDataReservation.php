<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingDataReservation extends Model
{
    use HasFactory;

    protected $table = "billing_data_reservations";

    
    protected $fillable = [
        'user_reservation_id',
        'name',
        'phone',
        'lastname',
        'dni',
        'nationality_id'
    ];
    
    public function user_reservation()
    {
        return $this->belongsTo(UserReservation::class, 'user_reservation_id', 'id');
    }
}
