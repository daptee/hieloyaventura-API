<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class CharacteristicTranslable extends Model
{
    use HasFactory;
    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable = [
        'lenguage_id',
        'characteristic_id',
        'name',
        'description',
    ];

    protected $casts = [
        'description' => 'array',
    ];

    protected static function booted()
    {
        static::addGlobalScope('request_lenguage', function (Builder $builder) {
            $builder->where('lenguage_id', Session::get('applocale') ?? 1);
        });
    }
}
