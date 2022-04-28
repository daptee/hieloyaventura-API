<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFaqRequest extends FormRequest
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
            'lenguage_id' => 'nullable|integer|exists:lenguages,id',
            'question' => 'nullable|string|max:65535',
            'answer' => 'nullable|string|max:65535',
        ];
    }
}
