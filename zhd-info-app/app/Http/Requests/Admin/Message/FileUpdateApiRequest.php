<?php

namespace App\Http\Requests\Admin\Message;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class FileUpdateApiRequest extends ApiRequest
{
    public function rules()
    {
        $maxFiles = request()->input('max_files', 10); // パラメータで上限数を取得、デフォルトは10
        $fileCount = count(request()->file());
        $rules = [];

        // ファイル数が上限を超えているかチェック
        if ($fileCount > $maxFiles) {
            return [
                'file_limit' => 'max:0' // ダミーのルールを設定してエラーを発生させる
            ];
        }

        // ファイル数だけルールを生成
        for ($i = 0; $i < $fileCount; $i++) {
            $rules['file' . $i] = 'required|mimes:pdf|max:150000';
        }

        return $rules;
    }

    public function messages()
    {
        $maxFiles = request()->input('max_files', 10); // パラメータで上限数を取得、デフォルトは10
        $messages = [];
        $files = request()->file();

        // 上限を超えた場合のメッセージを追加
        if (count($files) > $maxFiles) {
            $messages['file_limit.max'] = "登録可能なファイルの上限は${maxFiles}件です";
        }

        // ファイルごとにメッセージを生成
        foreach ($files as $key => $file) {
            $messages[$key . '.required'] = 'ファイルを添付してください';
            $messages[$key . '.mimes'] = 'pdf形式のファイルを添付してください';
            $messages[$key . '.max'] = 'ファイルの容量が大きすぎます。150MB以下にしてください';
        }

        return $messages;
    }

}
