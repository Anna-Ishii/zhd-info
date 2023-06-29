<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthLoginRequest extends FormRequest
{
    public function rules()
    {
        return [
            'email' => 'required|email:filter',
            'password'  => 'required',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => 'メールアドレス形式で入力してください',
            'password' => 'パスワードを入力してください',
        ];
    }
}
