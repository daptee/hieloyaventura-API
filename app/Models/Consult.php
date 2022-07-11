<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consult extends Model
{
    use HasFactory;

    const SHOW = [];
    const INDEX = [];
    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = [
        'email',
        'name',
        'message',
    ];
}
