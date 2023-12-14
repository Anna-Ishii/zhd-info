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
            'title' => 'max:20',
            'description' => 'max:30',
            'manual_flow.*.title' => 'max:20',
            'manual_flow.*.detail' => 'max:30',
        ];

        return [
            'title' => 'required|max:20',
            'tag_name' => ['nullable', new TagRule()],
            'file_path' => 'required',
            'description' => 'nullable|max:30',
            'category_id' => 'required',
            'new_category_id' => 'prohibited_if:new_category_id,null',
            'brand' => 'required',
            'start_datetime' => 'nullable',
            'end_datetime' => 'nullable',
            'manual_flow.*.title' => 'required|max:20',
            'manual_flow.*.detail' => 'nullable|max:30',
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
            'category_id.required' => 'カテゴリを選択してください',
            'new_category_id.prohibited_if' => '新カテゴリを選択してください',
            'brand.required' => '対象業態を選択してください',
            'manual_flow.*.title.required' => '手順名は必須項目です',
            'manual_flow.*.title.max' => '手順名は20文字までです',
            'manual_flow.*.detail.max' => '手順内容は30文字までです',
            'manual_flow.*.file_path' => '手順ファイルを添付してください'
        ];
    }
}
