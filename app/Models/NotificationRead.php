<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationRead extends Model
{
    use HasFactory;

    // La tabla solo maneja read_at, sin created_at/updated_at de Laravel
    public $timestamps = false;

    protected $fillable = [
        'notification_id',
        'agency_user_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }

    public function agencyUser()
    {
        return $this->belongsTo(AgencyUser::class);
    }
}
