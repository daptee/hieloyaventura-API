<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserReservationRequest extends FormRequest
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
            'reservation_number' => 'required',
            'user_id' => 'nullable',
            'hotel_id' => 'nullable|integer|required_if:is_transfer,1',
            'excurtion_id' => 'nullable',
            'reservation_status_id' => 'nullable',
            'date' => 'required|date_format:Y-m-d',
            'turn' => 'required|date_format:H:i',
            'hotel_name' => 'nullable|required_if:is_transfer,1',
            'price' => 'nullable',
            'children_price' => 'nullable',
            'special_discount' => 'nullable',
            'is_paid' => 'nullable',
            'is_transfer' => 'nullable',
            'reservation' => 'accepted',
            'notifications' => 'accepted',
            
            'billing_data' => 'required',
            'billing_data.name' => 'required',

            'contact_data' => 'required',
            'contact_data.name' => 'required',
            'contact_data.email' => 'required',
            'contact_data.lenguage_id' => 'required',

            'create_user' => 'required|boolean'

        ];
    }
}
