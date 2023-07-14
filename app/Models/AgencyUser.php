<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgencyUser extends Model
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
        "active"
    ];

    protected $hidden = [
        "password",
        "deleted_at"
    ];

    const SHOW = [
        'user_type'
    ];

    public function user_type()
    {
        return $this->hasOne(AgencyUserType::class, 'id', 'agency_user_type_id');
    }

    public static function getAllDataUser($id)
    {
        return self::with(self::SHOW)->find($id);
    }
}
