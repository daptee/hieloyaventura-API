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
            'user_id' => 'nullable',
            'hotel_id' => 'nullable|required_if:is_transfer,1',
            'excurtion_id' => 'nullable',
            'reservation_status_id' => 'nullable',
            'turn_id' => 'nullable',
            'hotel_name' => 'nullable|required_if:is_transfer,1',
            'price' => 'nullable',
            'children_price' => 'nullable',
            'special_discount' => 'nullable',
            'is_paid' => 'nullable',
            'is_transfer' => 'nullable',
            'reservation' => 'accepted',
            'notifications' => 'accepted',
            'paxs' => 'nullable|array',
        ];
    }
}
