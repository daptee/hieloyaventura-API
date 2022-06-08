<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Characteristic extends Model
{
    use HasFactory;

    protected $with = ['characteristics'];

    protected $fillable = [
        'name',
        'link',
        'characteristic_id',
        'characteristic_type_id',
    ];

    public function characteristics(): HasMany
    {
        return $this->hasMany(self::class, 'characteristic_id', 'id');
    }
    public function characteristic_translables(): HasMany
    {
        return $this->hasMany(CharacteristicTranslable::class, 'characteristic_id', 'id');
    }
}
