<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class PassengerDisease extends Model
{
    use HasFactory;

    protected $table = "passenger_diseases";
    protected $hidden = ['created_at', 'updated_at'];

    public function disease(): HasOne
    {
        return $this->hasOne(Disease::class, 'id', 'disease_id');
    }
}
