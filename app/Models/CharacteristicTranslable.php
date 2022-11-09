<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Casts\Attribute;

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

    protected static function booted()
    {
        if (Session::has('language-id')) {
            static::addGlobalScope('request_lenguage', function (Builder $builder) {
                $builder->where('lenguage_id', Session::get('language-id') ?? 1);
            });
        }
    }

    protected $appends = ['language_code'];


    /**
     * Determine if the user is an administrator.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function languageCode(): Attribute
    {
        return new Attribute(
            get: fn () => Lenguage::find($this->lenguage_id)->abreviation,
        );
    }
}
