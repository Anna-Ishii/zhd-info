<?php

namespace App\Http\Requests\Admin\Message;

use Illuminate\Foundation\Http\FormRequest;

class PublishStoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            'title' => 'required',
            'file'  => 'required|mimes:pdf|max:150000',
            'category_id' => 'required',
            'emergency_flg' => 'nullable',
            'start_datetime' => 'nullable|date_format:Y/m/d H:i',
            'end_datetime' => 'nullable|date_format:Y/m/d H:i',
            'target_roll' => 'required',
            'organization1' => 'required',
            'organization4' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'タイトルは必須項目です',
            'file.required' => 'ファイルを添付してください',
            'file.mimes' => 'PDF形式のファイルを添付してください',
            'file.max' => 'ファイルの容量が大きすぎます。150MB以下にしてください',
            'file' => 'ファイルのアップロードに失敗しました',
            'category_id.required' => 'カテゴリを選択してください',
            'start_datetime.date_format' => '日時の形式で入力してください',
            'end_datetime.date_format' => '日時の形式で入力してください',
            'target_roll' => '対象者を選択してください',
            'organization1.required' => '対象業態を選択してください',
            'organization4.required' => '対象フロックを選択してください',
        ];
    }
}