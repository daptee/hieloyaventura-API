<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralConfigurations extends Model
{
    use HasFactory;
    
    protected $table = "general_configurations";

    protected $hidden = ['updated_at', 'deleted_at'];

}
