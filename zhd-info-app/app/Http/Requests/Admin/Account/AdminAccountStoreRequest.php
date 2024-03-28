<?php

namespace App\Http\Requests\Admin\Account;

use Illuminate\Foundation\Http\FormRequest;

class AdminAccountStoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            'employee_code'  => 'required|unique:admin',
            'name' => 'required',
            'ability' => 'required',
            'organization1' => 'nullable',
            'page' => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'employee_code.required' => '社員コードを入力してください',
            'name.required' => '名前を入力してください',
            'employee_code.unique' => '社員コードが重複しています',
            'ability.required' => '権限を選択してください',

        ];
    }
}
