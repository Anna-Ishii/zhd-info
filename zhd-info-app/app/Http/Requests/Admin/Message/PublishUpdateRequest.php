<?php

namespace App\Http\Requests\Admin\Message;

use App\Models\Message;
use App\Rules\TagRule;
use Illuminate\Foundation\Http\FormRequest;

class PublishUpdateRequest extends FormRequest
{
    public function rules()
    {
        if ($this->input('save')) return [
        ];

        return [
            'title' => 'required',
            'tag_name' => ['nullable', new TagRule()],
            'file_path' => 'required',
            'category_id' => 'required',
            'emergency_flg' => 'nullable',
            'start_datetime' => 'nullable',
            'end_datetime' => 'nullable',
            'target_roll' => 'required',
            'brand' => 'required',
            'organization_shops' => 'required',
        ];
    }

    public function messages()
    {
        $messages = [
            'title.required' => 'タイトルは必須項目です',
            'title.max' => 'タイトルは20文字までです',
            'file_path.required' => 'ファイルを添付してください',
            'category_id.required' => 'カテゴリを選択してください',
            'target_roll' => '対象者を選択してください',
            'brand.required' => '対象業態を選択してください',
            'organization_shops.required' => '対象店舗を選択してください',
        ];
        return $messages;
    }
}
