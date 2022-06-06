<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExcurtionRequest extends FormRequest
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
            // 'link_map' => 'nullable|string|max:255',
            // 'code_excurtion' => 'nullable|string|max:255',
            // 'price' => 'nullable|numeric|between:0,99999999.99',
            // 'price_children' => 'nullable|numeric|between:0,99999999.99',
            // 'price_special' => 'nullable|numeric|between:0,99999999.99',
            // 'is_transfer' => 'nullable|boolean',
        ];
    }
}
