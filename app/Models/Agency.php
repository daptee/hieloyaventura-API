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

    protected $casts = [
        'configurations' => 'array'
    ];
}
