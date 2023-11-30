<?php

namespace App\Http\Requests\Admin\Manual;

use Illuminate\Foundation\Http\FormRequest;

class PublishStoreRequest extends FormRequest
{

    public function rules()
    {
        // 一時保存
        if ($this->input('save')) return [];

        return [
            'title' => 'required',
            'description' => 'nullable',
            'category_id' => 'required',
            'brand' => 'required',
            'start_datetime' => 'nullable',
            'end_datetime' => 'nullable',
            'manual_flow.*.title' => 'required',
            'manual_flow.*.detail' => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'タイトルは必須項目です',
            'category_id.required' => 'カテゴリを選択してください',
            'brand.required' => '対象ブランドを選択してください',
            'manual_flow.*.title.required' => '手順名は必須項目です',
        ];
    }
}
