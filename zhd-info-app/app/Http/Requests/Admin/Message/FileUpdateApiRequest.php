<?php

namespace App\Http\Requests\Admin\Message;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class FileUpdateApiRequest extends ApiRequest
{

    public function rules()
    {
        $rules = [];

        // ファイル数だけルールを生成
        for ($i = 0; $i < count(request()->file()); $i++) {
            $rules['file' . $i] = 'required|mimes:pdf|max:150000';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'file.required' => 'ファイルを添付してください',
            'file.mimes' => 'pdf形式のファイルを添付してください',
            'file.max' => 'ファイルの容量が大きすぎます。150MB以下にしてください',
        ];
    }
}
