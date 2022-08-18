<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'lenguage_id' => 'integer|exists:lenguages,id',
            'role_id' => 'string|max:65535',
            'answer' => 'string|max:65535',
            'password' => 'confirmed',
        ];
    }
}
