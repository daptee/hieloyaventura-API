<?php

namespace App\Models;

use App\Helpers\UploadFileHelper;
use App\Http\Requests\StoreCharacteristicRequest;
use App\Http\Requests\StoreCharacteristicTranslableRequest;
use App\Http\Requests\StoreCharacteristicTypeRequest;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Characteristic extends Model
{
    use HasFactory;
    const INDEX = [];
    const SHOW = ['characteristics', 'characteristic_translables', 'characteristic_type'];

    protected $with = ['characteristics', 'characteristic_translables'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = [
        'link',
        'characteristic_id',
        'characteristic_type_id',
        'icon_id',
        'icon',
    ];

    public function characteristics(): HasMany
    {
        return $this->hasMany(self::class, 'characteristic_id', 'id');
    }
    public function characteristic_translables(): HasMany
    {
        return $this->hasMany(CharacteristicTranslable::class, 'characteristic_id', 'id');
    }
    public function characteristic_type(): BelongsTo
    {
        return $this->belongsTo(CharacteristicType::class, 'characteristic_type_id', 'id');
    }
    public function icon(): BelongsTo
    {
        return $this->belongsTo(Icon::class, 'icon_id', 'id');
    }

    public static function updateCharacteristic2(array $characteristic, $new_excurtion_id = null, $characteristic_id = null)
    {
        try {
            if (isset($data['borrar'])) {
                Characteristic::withoutGlobalScopes()->destroy($characteristic['id']);
                return;
            }
            $data = Characteristic::findOrFail($characteristic['id']);
            $data->fill($characteristic);
            $data->save();

            if (isset($characteristic['characteristics'])) {
                self::addCharacteristic($characteristic['characteristics'], null, $data->id);
            }
            if (isset($characteristic['translables'])) {
                foreach ($characteristic['translables'] as $translable) {
                    if (isset($translable['borrar'])) {
                        CharacteristicTranslable::withoutGlobalScopes()->destroy($translable['id']);
                        continue;
                    }
                    if (isset($translable['id'])) {
                        CharacteristicTranslable::whereId($translable['id'])->update($translable);
                        continue;
                    }
                    CharacteristicTranslable::updateOrCreate(['characteristic_id' => $data->id, 'lenguage_id' => $translable['lenguage_id']], $translable);
                }
            }
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }
    public static function updateCharacteristic(array $characteristic, $new_excurtion_id = null, $characteristic_id = null)
    {
        if (isset($data['borrar'])) {
            Characteristic::withoutGlobalScopes()->destroy($characteristic['id']);
            return;
        }
        $update_characteristic = Characteristic::findOrFail($characteristic['id']);
        $update_characteristic->fill($characteristic);
        if (isset($characteristic['icon'])) {
            if (isset($characteristic['icon']['id'])) {
                Icon::destroy($characteristic['icon']['id']);
            } else {
                if ($characteristic['icon']['file'] != null) {
                    $link = UploadFileHelper::createFiles($characteristic['icon']['file'], 'iconsCharacteristics', $characteristic['icon']['name'], '');
                    $icon = Icon::create(['name' => $characteristic['icon']['name']]);
                    $update_characteristic->fill(['icon_id' => $icon->id]);
                }
            }
        }

        if (isset($characteristic['translables'])) {
            foreach ($characteristic['translables'] as $translable) {
                if (isset($translable['id'])) {
                    if (isset($translable['borrar'])) {
                        Icon::destroy($translable['id']);
                        continue;
                    }
                    $characteristictranslable = CharacteristicTranslable::findOrFail($translable['id']);
                    $characteristictranslable->fill($translable);
                    $characteristictranslable->save();
                    continue;
                }

                new StoreCharacteristicTranslableRequest($translable);
                CharacteristicTranslable::create($translable + ['characteristic_id' => $update_characteristic->id]);

                if (isset($translable['description'])) {
                    $description = json_decode($translable['description'], true);
                    if (!is_array($description)) {
                        continue;
                    }
                    foreach (json_decode($translable['description'], true) as $item) {
                        if (isset($item['icon'])) {
                            if ($characteristic['icon']['file'] != null) {
                                $link = UploadFileHelper::createFiles($item['icon']['file'], 'iconsCharacteristics', $item['icon']['name'], '');
                                Icon::create(['name' => $item['icon']['name']]);
                            }
                        }
                    }
                }
            }
        }

        $update_characteristic->save();
    }
    public static function addCharacteristic(array $characteristic, $new_excurtion_id = null, $characteristic_id = null)
    {
        try {
            new StoreCharacteristicRequest($characteristic);
            if (isset($characteristic['characteristic_type'])) {
                new StoreCharacteristicTypeRequest(['name' => $characteristic['characteristic_type']]);
                $characteristic['characteristic_type_id'] = CharacteristicType::firstOrCreate(['name' => $characteristic['characteristic_type']])->id;
            }

            if (isset($characteristic['icon'])) {
                if (isset($characteristic['icon']['file']) and $characteristic['icon']['file'] != null) {
                    $link = UploadFileHelper::createFiles($characteristic['icon']['file'], 'iconsCharacteristics', $characteristic['icon']['name'], '');
                    Icon::create(['name' => $characteristic['icon']['name']]);
                }
            }

            $new_characteristic = Characteristic::create($characteristic + ['characteristic_id' => $characteristic_id]);

            if (isset($characteristic['translables'])) {
                foreach ($characteristic['translables'] as $translable) {
                    new StoreCharacteristicTranslableRequest($translable);
                    CharacteristicTranslable::create($translable + ['characteristic_id' => $new_characteristic->id]);

                    if (isset($translable['description'])) {
                        $description = json_decode($translable['description'], true);
                        if (!is_array($description)) {
                            continue;
                        }
                        foreach (json_decode($translable['description'], true) as $item) {
                            if (isset($item['icon'])) {
                                if ($characteristic['icon']['file'] != null) {
                                    $link = UploadFileHelper::createFiles($item['icon']['file'], 'iconsCharacteristics', $item['icon']['name'], '');
                                    Icon::create(['name' => $item['icon']['name']]);
                                }
                            }
                        }
                    }
                }
            }

            if(!is_null($new_excurtion_id)){
                ExcurtionCharacteristic::create(['characteristic_id' => $new_characteristic->id, 'excurtion_id' => $new_excurtion_id]);
            }
            if (isset($characteristic['characteristics'])) {
                foreach ($characteristic['characteristics'] as $characteristic_new) {
                    self::addCharacteristic($characteristic_new, null, $new_characteristic->id);
                }
            }
            return Characteristic::with(Characteristic::SHOW)->findOrFail($new_characteristic->id);
        } catch (Exception $th) {
            throw new Exception($th);
        }
    }
}
