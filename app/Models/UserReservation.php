<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserReservation extends Model
{
    use HasFactory;
    protected $casts = [
        'turn' => 'datetime:H:i',
        'date' => 'datetime:Y-m-d'
    ];

    const SHOW = [
        'user',
        'status',
        'excurtion',
        'billing_data',
        'contact_data'
    ];
    const INDEX = [
        'status',
        'excurtion',
        'paxes',
        'reservation_paxes'
    ];

    protected $fillable = [
        'reservation_number',
        'user_id',
        'hotel_id',
        'excurtion_id',
        'reservation_status_id',
        'date',
        'turn',
        'hotel_name',
        'price',
        'children_price',
        'special_discount',
        'is_paid',
        'is_transfer',
        'notifications_accepted',
        'reservation_checked',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function status(): BelongsTo
    {
        return $this->belongsTo(ReservationStatus::class, 'reservation_status_id', 'id');
    }
    
    public function excurtion(): BelongsTo
    {
        return $this->belongsTo(Excurtion::class, 'excurtion_id', 'id');
    }

    public function billing_data(): HasOne
    {
        return $this->hasOne(BillingDataReservation::class, 'user_reservation_id', 'id');
    }

    public function contact_data(): HasOne
    {
        return $this->hasOne(ContactDataReservation::class, 'user_reservation_id', 'id');
    }

    public function paxes()
    {
        return $this->hasMany(Pax::class, 'user_reservation_id', 'id');
    }

    public function reservation_paxes()
    {
        return $this->hasMany(ReservationPax::class, 'user_reservation_id', 'id');
    }
}
