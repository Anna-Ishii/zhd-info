<?php

namespace App\Http\Requests\Admin\Manual;

use Illuminate\Foundation\Http\FormRequest;

class PublishStoreRequest extends FormRequest
{
    private $uploadableFileTypes = [
        'avi' => 'video/x-msvideo',
        'mov' => 'video/quicktime',
        'mp4' => 'video/mp4',
        'm4v' => 'video/x-m4v',
        'webm' => 'video/webm',
        'wmv' => 'video/x-ms-wmv',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
    ];

    public function rules()
    {
        $mimeTypesRule = '|mimetypes:'. implode(',', array_values($this->uploadableFileTypes));
        return [
            'title' => 'required',
            'description' => 'nullable',
            'file'  => 'required|max:150000'.$mimeTypesRule,
            'category_id' => 'required',
            'organization1' => 'required',
            'start_datetime' => 'nullable',
            'end_datetime' => 'nullable',
            'manual_flow_title.*' => 'required',
            'manual_file.*' => $mimeTypesRule,
            'manual_flow_detail.*' => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'タイトルは必須項目です',
            'file.required' => 'ファイルを添付してください',
            'file.mimetypes' => 'mp4,mov,jpeg,png,jpg形式のファイルを添付してください',
            'file' => 'ファイルのアップロードに失敗しました',
            'file.max' => 'ファイルの容量が大きすぎます。150MB以下にしてください',
            'category_id.required' => 'カテゴリを選択してください',
            'organization1.required' => '対象業態を選択してください',
            'manual_flow_title.*.required' => '手順のタイトルを入力してください',
            'manual_file.*.mimetypes' => 'mp4・mov・jpeg・png・jpg形式のファイルを添付してください',
            'manual_file.*' => '手順ファイルのアップロードに失敗しました',
        ];
    }
}
