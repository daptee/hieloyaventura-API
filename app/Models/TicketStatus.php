<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketStatus extends Model
{
    use HasFactory;

    const OPEN = 1;
    const IN_REVIEW = 2;
    const CLOSED = 3;

    protected $table = "tickets_status";

    protected $fillable = [
        'name',
    ];

}
