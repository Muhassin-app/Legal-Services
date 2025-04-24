<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddManualTimeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(){
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(){
        return [
            'variant_id' => 'required',
            'memo' => 'required|unique:chat_sockets',
            'product_id' => 'required',
            'booking_start_end'=>'required',
        ];

    }
     public function messages(){
        return [
            "variant_id" => __('The title field is required.'),
            "memo" => __("The memo is required."),
            "product_id" =>__("The product id is required."),
            "booking_start_end" => __("The start end time field is required.")
        ];
    }
}
