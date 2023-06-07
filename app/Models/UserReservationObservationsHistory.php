<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserReservationObservationsHistory extends Model
{
    use HasFactory;

    protected $table = 'user_reservations_observations_history';

    protected $fillable = [
        'user_reservation_id',
        'user_id',
        'observation'
    ];

    protected $hidden = [
        'user_reservation_id',
        'user_id',
        'deleted_at'
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function user_reservation(): HasOne
    {
        return $this->hasOne(UserReservation::class, 'id', 'user_reservation_id');
    }

    // public function user(): HasOne
    // {
    //     return $this->hasOne(User::class, 'id', 'user_id')->selectRaw('name');
    // }

    const RELATIONS = [
        'user',
        'user_reservation',
    ];
}
