<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Agency extends Model
{
    use HasFactory;

    protected $fillable = [
        "agency_code",
        "api_key",
        "configurations",
        "email_integration_notification"
    ];

    protected $hidden = ['api_key'];

    protected $appends = ['has_apikey'];

    protected $casts = [
        'configurations' => 'array'
    ];

    /**
     * Atributo accesible que indica si la agencia tiene una API key configurada
     */
    public function getHasApikeyAttribute()
    {
        return !is_null($this->api_key);
    }
}
