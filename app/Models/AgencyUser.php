<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;

class AgencyUser extends Authenticatable implements JWTSubject
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "agency_user_type_id",
        "user",
        "password",
        "name",
        "last_name",
        "email",
        "agency_code",
        "active",
        "can_view_all_sales",
        "terms_and_conditions"
    ];

    protected $hidden = [
        "password",
        "deleted_at"
    ];

    const SHOW = [
        'user_type'
    ];
    
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function user_type()
    {
        return $this->hasOne(AgencyUserType::class, 'id', 'agency_user_type_id');
    }

    public static function getAllDataUser($id)
    {
        return self::with(self::SHOW)->find($id);
    }
}
