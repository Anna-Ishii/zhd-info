<?php

namespace App\Http\Requests\Admin\Manual;

use Illuminate\Foundation\Http\FormRequest;

class PublishUpdateRequest extends FormRequest
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
        $mimeTypesRule = '|mimetypes:' . implode(',', array_values($this->uploadableFileTypes));
        return [
            'title' => 'required',
            'description' => 'nullable',
            'file'  => 'max:150000'.$mimeTypesRule,
            'category_id' => 'required',
            'organization1' => 'nullable',
            'start_datetime' => 'nullable',
            'end_datetime' => 'nullable',
            'manual_flow_title.*' => 'required_with:manual_file',
            'manual_file.*' => 'max:150000'.$mimeTypesRule,
            'manual_flow_detail.*' => 'nullable',
            'content_id.*' => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'タイトルは必須項目です',
            'file.mimetypes' => 'mp4,mov,jpeg,png,jpg形式のファイルを添付してください',
            'file.max' => 'ファイルの容量が大きすぎます。150MB以下にしてください',
            'file' => 'ファイルのアップデートに失敗しました',
            'category_id.required' => 'カテゴリを選択してください',
            'organization1.required' => '対象業態を選択してください',
            'manual_flow_title.*.required_with' => 'タイトルを入力してください',
            'manual_file.*.mimetypes' => 'mp4,mov,jpeg,png,jpg形式のファイルを添付してください',
            'manual_file.*' => '手順ファイルのアップロードに失敗しました'
        ];
    }
}
