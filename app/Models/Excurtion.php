<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Excurtion extends Model
{
    use HasFactory;
    const INDEX = [];
    const SHOW = ['characteristics.characteristic_translables', 'characteristics.characteristic_type'];
    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = [
        'link_map',
        'code_excurtion',
        'price',
        'price_children',
        'price_special',
        'is_transfer',
        'name',
        'external_id',
        'validity_from',
        'validity_to',
    ];
    /**
     * Get all of the characteristics for the Excurtion
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function characteristics(): HasManyThrough
    {
        return $this->hasManyThrough(Characteristic::class, ExcurtionCharacteristic::class, 'excurtion_id', 'id', 'id', 'characteristic_id');
    }
    /**
     * Get all of the pictures for the Excurtion
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pictures(): HasMany
    {
        return $this->hasMany(PictureExcurtion::class, 'excurtion_id', 'id');
    }

    public function scopeExternal($query, $external_id)
    {
        return $query->where('external_id', $external_id);
    }
}
