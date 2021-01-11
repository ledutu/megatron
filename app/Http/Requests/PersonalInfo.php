<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonalInfo extends FormRequest
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
            'passport' => 'required|unique:profile,Profile_Passport_ID',
            'passport_image' => 'required|image|mimes:jpeg,jpg,bmp,png,gif|max:2048',
            'passport_image_selfie' =>'required|mimes:jpeg,jpg,bmp,png,gif|image|max:2048'
        ];
    }
}
