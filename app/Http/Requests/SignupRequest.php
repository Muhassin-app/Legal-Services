<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\UserRegistrationDocuments;
use App\Models\ClientPreference;

class SignupRequest extends FormRequest{
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

        $preferences = ClientPreference::first();
        $user_registration_documents = UserRegistrationDocuments::with('primary')->get();
        $rules = [
            'name' => 'required|min:3|max:50',
            'password' => 'required|string|min:6|max:50',
            'device_type' => 'required|string',
            'device_token' => 'required|string',
            'term_and_condition' => 'accepted',
            'refferal_code' => 'nullable|exists:user_refferals,refferal_code',
        ];

        $preferences = ClientPreference::first();


        if($preferences->verify_email == 1){
            $rules['email'] = 'required|email|unique:users';
        }
        
        if($preferences->verify_phone == 1){
            $rules['phone_number'] = 'required|string|min:7|max:15|unique:users';
        }
        foreach ($user_registration_documents as $user_registration_document) {
            if($user_registration_document->is_required == 1){
                $rules[$user_registration_document->primary->slug] = 'required';
            }
        }
        return $rules;
    }
    public function messages(){
        return [
            "name.required" => __('The name field is required.'),
            "email.required" => __('The email field is required.'),
            "email.unique" => __('The email has already been taken.'),
            "name.min" => __('The name must be at least 3 characters.'),
            "password.required" => __("The password field is required."),
            "name.max" => __('The name may not be greater than 50 characters.'),
            "phone_number.required" => __('The phone number field is required.'),
            "phone_number.unique" => __('The phone number has already been taken.'),
            "term_and_condition.required" => __('The term and condition must be accepted.'),
        ];
    }
}
