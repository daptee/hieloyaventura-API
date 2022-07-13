<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Icon extends Model
{
    use HasFactory;

    const SHOW = [];
    const INDEX = [];
    protected $hidden = ['created_at', 'updated_at', 'link'];

    protected $fillable = [
        'link',
        'name',
    ];
}
