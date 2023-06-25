<?php

namespace App\Http\Requests\Admin\Account;

use Illuminate\Foundation\Http\FormRequest;

class AccountStoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string',
            'belong_label'  => 'required',
            'shop_id' => 'required',
            'employee_code' => 'required|unique:users',
            'password' => 'required|confirmed|min:6',
            'email' => 'required',
            'roll_id' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '名前を入力してください',
            'belong_label.required' => '所属を入力してください',
            'shop_id.required' => '店舗を選択してください',
            'employee_code.required' => '社員コードを入力してください',
            'employee_code.unique' => '社員コードが重複しています',
            'password.required' => 'パスワードを入力してください',
            'password.confirmed' => 'パスワードが一致しません',
            'password.min' => 'パスワードを6文字以上です',
            'email.required' => 'メールアドレスは必須です',
            'roll_id.required' => '権限を設定してください',
        ];
    }
}
