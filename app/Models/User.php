<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, Notifiable, HasFactory;

    const SHOW = [
        'user_type',
        'language',
        'modules.module',
    ];
    const INDEX = [
        'user_type',
        'language',
        'modules.module',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'lenguage_id',
        "nationality_id",
        "birth_date",
        "dni",
        "phone"
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

    public static function createUser($data)
    {
        return User::create($data);
    }

    public function reservations()
    {
        return $this->hasMany(UserReservation::class, 'user_id', 'id');
    }

    public function user_type()
    {
        return $this->hasOne(UserType::class, 'id', 'user_type_id');
    }

    public function language()
    {
        return $this->hasOne(Lenguage::class, 'id', 'lenguage_id');
    }

    public function nationality()
    {
        return $this->hasOne(Nationality::class, 'id', 'nationality_id');
    }

    public function modules()
    {
        return $this->hasMany(UserModule::class, 'user_id', 'id');
    }

    public static function getAllDataUser($user_type_id, $id)
    {
        $array_data = ['user_type', 'language', 'nationality', 'modules.module'];

        // if($user_type_id == UserType::ADMIN)
        //     array_push($array_data, 'modules.module');

        return User::with($array_data)->find($id);
    }
}
