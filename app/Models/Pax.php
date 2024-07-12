<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pax extends Model
{
    protected $fillable = [
        'user_reservation_id',
        'pax_type_id',
        'nationality_id',
        'name',
        'lastname',
        'dni',
        'birthdate',
        'age',
        'blood_type',
        'description'
    ];
    use HasFactory;

    protected $appends = [
        'full_name'
    ];

    public function getFullNameAttribute()
    {
        return $this->name . ' ' . $this->lastname;
    }

    public function files()
    {
        return $this->hasMany(PaxFile::class, 'pax_id', 'id');
    }

    public function diseases()
    {
        return $this->hasMany(PassengerDisease::class, 'passenger_id', 'id');
    }
}
