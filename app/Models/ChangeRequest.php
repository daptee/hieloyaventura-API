<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChangeRequest extends Model
{
    use HasFactory;

    protected $table = 'change_requests';

    protected $fillable = [
        'user_id',
        'user_reservation_id',
        'text',
    ];

    // Relaciones (opcional, si tienes los modelos)
    public function user()
    {
        return $this->belongsTo(AgencyUser::class, 'user_id');
    }

    public function reservation()
    {
        return $this->belongsTo(UserReservation::class, 'user_reservation_id');
    }

    public function files()
    {
        return $this->hasMany(ChangeRequestFile::class);
    }

}