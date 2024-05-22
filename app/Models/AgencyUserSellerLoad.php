<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgencyUserSellerLoad extends Model
{
    use HasFactory;

    protected $table = 'agency_users_seller_load';

    protected $hidden = ['id_user', 'created_at', 'updated_at'];

    protected $fillable = [
        'id_user',
        'agency_code',
        'seller_load',
    ];

    public function user()
    {
        return $this->hasOne(AgencyUser::class, 'id', 'id_user');
    }
}
