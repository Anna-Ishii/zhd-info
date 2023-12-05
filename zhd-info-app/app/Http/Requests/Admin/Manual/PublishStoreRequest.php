<?php

namespace App\Http\Requests\Admin\Manual;

use App\Rules\TagRule;
use Illuminate\Foundation\Http\FormRequest;

class PublishStoreRequest extends FormRequest
{

    public function rules()
    {
        // 一時保存
        if ($this->input('save')) return [
            'title' => 'max:20',
            'manual_flow.*.title' => 'max:20',
        ];

        return [
            'title' => 'required|max:20',
            'tag_id' => ['nullable', new TagRule()],
            'file_path' => 'required',
            'description' => 'nullable',
            'category_id' => 'required',
            'brand' => 'required',
            'start_datetime' => 'nullable',
            'end_datetime' => 'nullable',
            'manual_flow.*.title' => 'required|max:20',
            'manual_flow.*.detail' => 'nullable',
            'manual_flow.*.file_path' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'タイトルは必須項目です',
            'title.max' => 'タイトルは20文字までです',
            'file_path.required' => 'ファイルを添付してください',
            'category_id.required' => 'カテゴリを選択してください',
            'brand.required' => '対象業態を選択してください',
            'manual_flow.*.title.required' => '手順名は必須項目です',
            'manual_flow.*.title.max' => '手順名は20文字までです',
            'manual_flow.*.file_path.required' => '手順ファイルを添付してください'
        ];
    }
}
