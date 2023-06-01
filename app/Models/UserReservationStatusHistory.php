<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserReservationStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'user_reservations_status_history';

    public function status(): HasOne
    {
        return $this->hasOne(ReservationStatus::class, 'id', 'status_id');
    }

    public function user_reservation(): HasOne
    {
        return $this->hasOne(UserReservation::class, 'user_reservation_id', 'id');
    }
}
