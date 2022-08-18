<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserReservation extends Model
{
    use HasFactory;
    const SHOW = [
        'user',
        'hotel',
        'status',
        'turn',
        'excurtion',
    ];
    const INDEX = [];

    protected $fillable = [
        'user_id',
        'hotel_id',
        'excurtion_id',
        'reservation_status_id',
        'turn_id',
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
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class, 'hotel_id', 'id');
    }
    public function status(): BelongsTo
    {
        return $this->belongsTo(ReservationStatus::class, 'reservation_status_id', 'id');
    }
    public function turn(): BelongsTo
    {
        return $this->belongsTo(Turn::class, 'turn_id', 'id');
    }
    public function excurtion(): BelongsTo
    {
        return $this->belongsTo(Excurtion::class, 'excurtion_id', 'id');
    }
}
