<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Register extends FormRequest
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
            'email' => 'required|unique:users,User_Email|max:255',
            'password' => 'required|min:6|max:255',
            'password_comfirm' => 'required|same:password',
            'sponser' => 'required|exists:users,User_ID'
        ];
    }
}
