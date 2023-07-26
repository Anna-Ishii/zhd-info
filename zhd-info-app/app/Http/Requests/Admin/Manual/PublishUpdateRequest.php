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
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'pdf_file' => 'application/pdf',
    ];

    public function rules()
    {
        $mimeTypesRule = '|mimetypes:' . implode(',', array_values($this->uploadableFileTypes));
        return [
            'title' => 'required',
            'description' => 'nullable',
            'file'  => 'max:150000'.$mimeTypesRule,
            'category_id' => 'required',
            'brand' => 'nullable',
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
            'file.mimetypes' => 'mp4,mov,jpeg,png,jpg,pdf形式のファイルを添付してください',
            'file' => 'ファイルのアップデートに失敗しました',
            'file.max' => 'ファイルの容量が大きすぎます。150MB以下にしてください',
            'category_id.required' => 'カテゴリを選択してください',
            'brand.required' => '対象業態を選択してください',
            'manual_flow_title.*.required_with' => '手順名は必須項目です',
            'manual_file.*.mimetypes' => '手順ファイルはmp4,mov,png,jpeg,jpg,pdf形式のファイルを添付してください',
            'manual_file.*' => '手順ファイルのアップロードに失敗しました'
        ];
    }
}
