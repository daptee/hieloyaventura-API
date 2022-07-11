<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;
    protected $hidden = ['created_at', 'updated_at'];

    const SHOW = [];
    const INDEX = [];

    protected $fillable = [
        'lenguage_id',
        'question',
        'answer',
    ];
}
