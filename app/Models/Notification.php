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
    ];

    protected $casts = [
        'send_to_all_agencies' => 'boolean',
    ];

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
