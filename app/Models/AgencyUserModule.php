<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgencyUserModule extends Model
{
    use HasFactory;

    protected $table = 'agency_user_modules';

    protected $fillable = [
        'agency_user_id',
        'agency_module_id',
    ];

    public function agency_module()
    {
        return $this->hasOne(AgencyModule::class, 'id', 'agency_module_id');
    }
}
