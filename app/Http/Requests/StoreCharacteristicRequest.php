<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCharacteristicRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'icon_id' => 'nullable|exists:icons,id',
            'characteristic_type_id' => 'nullable|exists:characteristics_types,id',
            'order' => 'nullable|integer',
        ];
    }
}
