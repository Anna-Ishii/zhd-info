<?php

namespace App\Http\Requests\Admin\Message;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class FileUpdateApiRequest extends ApiRequest
{

    public function rules()
    {       
        return [
            'file'  => 'required|mimes:pdf|max:150000',
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'ファイルを添付してください',
            'file.mimetypes' => 'mp4,mov,m4v,jpeg,jpg,png,pdf形式のファイルを添付してください',
            'file' => 'ファイルのアップロードに失敗しました',
            'file.max' => 'ファイルの容量が大きすぎます。150MB以下にしてください',
        ];
    }
}
