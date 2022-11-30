<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PassengerDisease extends Model
{
    use HasFactory;

    protected $table = "passenger_diseases";
    protected $hidden = ['created_at', 'updated_at'];

    // public function diseases()
    // {
    //     return $this->hasMany(Disease::class, 'disease_id', 'id');
    // }
}
