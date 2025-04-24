<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddUpdateProductVariantSetRequest extends FormRequest
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
            'product_id' => 'required',
            'varient_id' => 'required',
            'p_varient_option_id' => 'required',
            'p_varient_id' => 'required',
        ];

    }
     public function messages(){
        return [
            "product_id.required" => __('The product id is required.'),
            "variant_id.required" => __('The variant id is required.'),
            "p_variant_option_id.required" => __('The option id is required.'),
            "p_variant_id.required" => __("The product variant field is required."),
        ];
    }
}
