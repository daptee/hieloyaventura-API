<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'recipients_type',
        'send_to_all_agencies',
        'user_id',
    ];

    protected $casts = [
        'send_to_all_agencies' => 'boolean',
    ];

    /**
     * Usuario que creó la notificación.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Agencias destinatarias específicas (solo cuando send_to_all_agencies = false).
     */
    public function agencies()
    {
        return $this->hasMany(NotificationAgency::class);
    }

    /**
     * Registros de lectura de esta notificación.
     */
    public function reads()
    {
        return $this->hasMany(NotificationRead::class);
    }
}
