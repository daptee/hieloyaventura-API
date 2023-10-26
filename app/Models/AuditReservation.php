<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditReservation extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = "audit_reservations";

    protected $casts = [
        "detail" => 'json'
    ];

    public static function store_audit_reservation($user_reservation_id, $detail)
    {
        $user_reservation_status = new AuditReservation();
        $user_reservation_status->user_reservation_id = $user_reservation_id;
        $user_reservation_status->detail = $detail;
        $user_reservation_status->save();
    }

}
