<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class CharacteristicTranslable extends Model
{
    use HasFactory;

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
        dd(App::getLocale());
        // static::addGlobalScope('request_lenguage', function (Builder $builder) {
        //     $builder->where('lenguage_id', App::getLocale() ?? 1);
        // });
    }
}
