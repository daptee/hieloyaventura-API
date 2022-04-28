<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailAdressConsults extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'email_adress_consult';
    protected $fillable = [
        'email',
    ];
}
