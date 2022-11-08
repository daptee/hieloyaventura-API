<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Excurtion extends Model
{
    use HasFactory;
    const INDEX = [
        'characteristics.characteristic_translables',
        'characteristics.characteristic_type',
        'characteristics.icon',
        'pictures',
    ];
    const SHOW = self::INDEX + [];
    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = [
        'link_map',
        'code_excurtion',
        'price',
        'is_transfer',
        'name',
        'external_id',
        'validity_from',
        'validity_to',
        'icon',
    ];

   /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['icon_link'];

    public function characteristics(): HasManyThrough
    {
        return $this->hasManyThrough(Characteristic::class, ExcurtionCharacteristic::class, 'excurtion_id', 'id', 'id', 'characteristic_id');
    }

    public function characteristics2()
    {
        return $this->belongsToMany(Characteristic::class, ExcurtionCharacteristic::class, 'excurtion_id', 'characteristic_id');
    }

    public function pictures(): HasMany
    {
        return $this->hasMany(PictureExcurtion::class, 'excurtion_id', 'id');
    }
    public function icon(): BelongsTo
    {
        return $this->belongsTo(Icon::class, 'icon_id', 'id');
    }
    public function scopeExternal($query, $external_id)
    {
        return $query->where('external_id', $external_id);
    }

    /**
     * Determine if the user is an administrator.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function iconLink(): Attribute
    {
        return new Attribute(
            get: fn () => url($this->icon),
        );
    }
}
