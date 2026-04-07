<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationAgency extends Model
{
    use HasFactory;

    // Solo tiene created_at, no updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'notification_id',
        'agency_code',
    ];

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }
}
