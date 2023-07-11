<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthLoginRequest extends FormRequest
{
    public function rules()
    {
        return [
            'employee_code' => 'required',
            'password'  => 'required',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => '社員IDを入力してください',
            'password' => 'パスワードを入力してください',
        ];
    }
}
