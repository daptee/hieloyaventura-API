<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "tickets_messages";

    protected $fillable = [
        'ticket_id',
        'message',
        'user_id',
        'file',
    ];

}
