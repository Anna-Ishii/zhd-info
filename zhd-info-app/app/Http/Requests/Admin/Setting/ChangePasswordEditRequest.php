<?php

namespace App\Http\Requests\Admin\Setting;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordEditRequest extends FormRequest
{
    public function rules()
    {
        return [
            'oldpasswd' => 'required',
            'newpasswd' => 'required|min:8',
        ];
    }

    public function messages()
    {
        return [
            'oldpasswd.required' => 'メールアドレスを入力してください',
            'newpasswd.email' => 'メールアドレス形式で入力してください',
            'newpasswd.min' => 'パスワードを8文字以上です',
        ];
    }
}
