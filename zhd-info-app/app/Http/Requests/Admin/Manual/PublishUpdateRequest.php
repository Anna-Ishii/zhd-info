<?php

namespace App\Http\Requests\Admin\Manual;

use App\Rules\TagRule;
use Illuminate\Foundation\Http\FormRequest;

class PublishUpdateRequest extends FormRequest
{
    public function rules()
    {
        // 一時保存
        if ($this->input('save')) return [

        ];

        return [
            'title' => 'required',
            'tag_name' => ['nullable', new TagRule()],
            'file_path' => 'required',
            'description' => 'nullable',
            'new_category_id' => 'prohibited_if:new_category_id,null',
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
            'title.max' => 'タイトルは20文字までです',
            'tag_id.*.distinct' => 'タグが重複しています',
            'file_path.required' => 'ファイルを添付してください',
            'description.max' => '説明文は30文字までです',
            'new_category_id.prohibited_if' => 'カテゴリを選択してください',
            'brand.required' => '対象業態を選択してください',
            'manual_flow.*.title.required' => '手順名は必須項目です',
            'manual_flow.*.title.max' => '手順名は20文字までです',
            'manual_flow.*.detail.max' => '手順内容は30文字までです',
            'manual_flow.*.file_path' => '手順ファイルを添付してください'
        ];
    }
}
