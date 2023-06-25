<?php

namespace App\Http\Requests\Admin\Message;

use Illuminate\Foundation\Http\FormRequest;

class PublishStoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            'title' => 'required|string',
            'file'  => 'required|mimes:pdf',
            'category_id' => 'required',
            'emergency_flg' => 'nullable',
            'start_datetime' => 'nullable',
            'end_datetime' => 'nullable',
            'organization4' => 'nullable',
            'target_roll' => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'タイトルは必須項目です',
            'file.required' => 'ファイルを添付してください',
            'file.mimes' => 'PDF形式のファイルを添付してください',
            'category_id.required' => 'カテゴリを選択してください',
        ];
    }
}