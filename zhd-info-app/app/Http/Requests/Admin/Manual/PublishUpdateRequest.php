<?php

namespace App\Http\Requests\Admin\Manual;

use Illuminate\Foundation\Http\FormRequest;

class PublishUpdateRequest extends FormRequest
{
    public function rules()
    {
        // 一時保存
        if ($this->input('save')) return [];

        return [
            'title' => 'required',
            'file_path' => 'required',
            'description' => 'nullable',
            'category_id' => 'required',
            'brand' => 'required',
            'start_datetime' => 'nullable',
            'end_datetime' => 'nullable',
            'manual_flow.*.title' => 'required',
            'manual_flow.*.detail' => 'nullable',
            'manual_flow.*.file_path' => 'required',
            'content_id.*' => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'タイトルは必須項目です',
            'file_path.required' => 'ファイルを添付してください',
            'category_id.required' => 'カテゴリを選択してください',
            'brand.required' => '対象業態を選択してください',
            'manual_flow.*.title.required' => '手順名は必須項目です',
            'manual_flow.*.file_path' => '手順ファイルを添付してください'
        ];
    }
}
