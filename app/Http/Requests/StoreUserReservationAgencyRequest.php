<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserReservationAgencyRequest extends FormRequest
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
            'agency_code' => 'required',
            'reservation_number' => 'required',
            'date' => 'required|date_format:Y-m-d',
            'turn' => 'required|date_format:H:i',
            'is_transfer' => 'nullable',
        ];
    }
}
