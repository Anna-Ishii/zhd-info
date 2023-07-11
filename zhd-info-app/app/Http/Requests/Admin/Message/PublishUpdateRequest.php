<?php

namespace App\Http\Requests\Admin\Message;

use Illuminate\Foundation\Http\FormRequest;

class PublishUpdateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'title' => 'required',
            'file'  => 'mimes:pdf|max:150000',
            'category_id' => 'required',
            'emergency_flg' => 'nullable',
            'start_datetime' => 'nullable',
            'end_datetime' => 'nullable',
            'target_roll' => 'required',
            'brand' => 'required',
            'organization_type' => 'required',
            'organization' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'タイトルは必須項目です',
            'file.mimes' => 'PDF形式のファイルを添付してください',
            'file.max' => 'ファイルの容量が大きすぎます。150MB以下にしてください',
            'file' => 'ファイルのアップロードに失敗しました',
            'category_id.required' => 'カテゴリを選択してください',
            'target_roll' => '対象者を選択してください',
            'brand.required' => '対象ブランドを選択してください',
        ];

        if ($this->organization_type == '5') {
            $messages = array_merge($messages, [
                'organization.required' => '対象ブロックを選択してください',
            ]);
        }
        if ($this->organization_type == '4') {
            $messages = array_merge($messages, [
                'organization.required' => '対象エリアを選択してください',
            ]);
        }
    }
}
