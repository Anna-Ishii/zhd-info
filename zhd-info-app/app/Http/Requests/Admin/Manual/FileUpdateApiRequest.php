<?php

namespace App\Http\Requests\Admin\Manual;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class FileUpdateApiRequest extends ApiRequest
{
    private $uploadableFileTypes = [
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
            'file'  => 'required|max:150000' . $mimeTypesRule,
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'タイトルは必須項目です',
            'file.required' => 'ファイルを添付してください',
            'file.mimetypes' => 'mp4,mov,m4v,jpeg,jpg,png,pdf形式のファイルを添付してください',
            'file' => 'ファイルのアップロードに失敗しました',
            'file.max' => 'ファイルの容量が大きすぎます。150MB以下にしてください',
        ];
    }
}
