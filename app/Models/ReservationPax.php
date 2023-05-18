<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ReservationPax extends Model
{
    use HasFactory;
    protected $fillable = [
        'pax_type_id',
        'user_reservation_id',
        'price',
        'quantity',
    ];

    public function pax_type(): HasOne
    {
        return $this->hasOne(PaxType::class, 'id', 'pax_type_id');
    }
}
