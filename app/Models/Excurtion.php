<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Excurtion extends Model
{
    use HasFactory;
    const INDEX = [];
    const SHOW = ['characteristics'];

    protected $fillable = [
        'link_map',
        'code_excurtion',
        'price',
        'price_children',
        'price_special',
        'is_transfer',
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
}
