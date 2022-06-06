<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCharacteristicTranslableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'lenguage_id' => 'exists:lenguages,id',
            'characteristic_id' => 'exists:characteristics,id',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:65535',
        ];
    }
}
