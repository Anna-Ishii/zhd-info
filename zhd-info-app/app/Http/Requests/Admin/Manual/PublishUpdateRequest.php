<?php

namespace App\Http\Requests\Admin\Manual;

use Illuminate\Foundation\Http\FormRequest;

class PublishUpdateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'title' => 'required',
            'description' => 'nullable',
            'file'  => 'mimes:mp4,mov,jpeg,png,jpg,wmv|max:150000',
            'category_id' => 'required',
            'organization1' => 'nullable',
            'start_datetime' => 'nullable',
            'end_datetime' => 'nullable',
            'manual_flow_title.*' => 'required_with:manual_file',
            'manual_file.*' => 'mimes:mp4,mov,jpeg,png,jpg,wmv|max:150000',
            'manual_flow_detail.*' => 'nullable',
            'content_id.*' => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'タイトルは必須項目です',
            'file.mimes' => 'mp4・mov・jpeg・png・jpg・wmv形式のファイルを添付してください',
            'file.max' => 'ファイルの容量が大きすぎます。150MB以下にしてください',
            'file' => 'ファイルのアップデートに失敗しました',
            'category_id.required' => 'カテゴリを選択してください',
            'organization1.required' => '対象業態を選択してください',
            'manual_flow_title.*.required_with' => 'タイトルを入力してください',
            'manual_file.*.mimes' => 'mp4・mov・jpeg・png・jpg・wmv形式のファイルを添付してください',
            'manual_file' => 'ファイルのアップデートに失敗しました'
        ];
    }
}
