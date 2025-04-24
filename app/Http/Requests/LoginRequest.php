<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class LoginRequest extends FormRequest
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

        if(is_numeric(\Request::get('email') ) )
        {
            return [
                'email' => 'required',
                'dialCode'  => 'required',
                'countryData'  => 'required|string',
                'device_type' => 'required|string',
                'device_token' => 'required|string',
            ];
        }else{
            return [
                'email' => 'required|email',
                'device_type' => 'required|string',
                'device_token' => 'required|string',
                'password' => 'required|string|min:6|max:50',
            ];
        }
        

    }
     public function messages(){
        if(is_numeric(\Request::get('email') ) ){
            return [
                "email.required" => __('The phone number field is required.'),
                "dialCode.required" => __('The dial code field is required.'),
                "countryData.required" => __('The country code field is required.'),
            ];
        }else{
            return [
                "email.required" => __('The email field is required.'),
                "password.required" => __("The password field is required."),
            ];
        }
        
    }

   
}
