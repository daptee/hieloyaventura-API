<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    const INDEX = [
        'reservation',
        'user',
        'status'
    ];

    protected $fillable = [
        'reservation_id',
        'user_id',
        'status_id',
    ];
    
    protected $hidden = [
        'reservation_id',
        'user_id',
        'status_id',
        'deleted_at',
    ];

    public function reservation()
    {
        return $this->hasOne(UserReservation::class, 'id', 'reservation_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function status()
    {
        return $this->hasOne(TicketStatus::class, 'id', 'status_id');
    }

    public static function getAllDataTicket($ticket_id)
    {
        return Ticket::with(['reservation', 'user', 'status'])->find($ticket_id);
    }
}
