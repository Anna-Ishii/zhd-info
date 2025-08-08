<?php

namespace App\Http\Requests\Admin\Message;

use App\Rules\TagRule;
use Illuminate\Foundation\Http\FormRequest;

class PublishStoreRequest extends FormRequest
{
    public function rules()
    {
        // 一時保存の時は、バリデーションしない
        if ($this->input('save')) return [
        ];
        $this->all();

        return [
            /*
             * TODO:メッセージに20文字以内の指定があるがタイトルにmaxのバリデーションがありません
             * 文字列型の場合カラム(または仕様)に応じて文字長をバリデーションする必要があります
             * また文字ではなく配列を送信される可能性もあるのでstringであるかのバリデーションも必要です
             * 例：
             * 'title' = [
             *     'required',
             *     'string',
             *     'max:20',
             * ]
            */
            'title' => 'required',
            'tag_name' => ['nullable', new TagRule()],
            'file_path' => ['required', 'array', function($attribute, $value, $fail) {
                if (empty(array_filter($value))) {
                    $fail('ファイルを添付してください');
                }
            }],
            'category_id' => 'required',
            'emergency_flg' => 'nullable',
            'start_datetime' => 'nullable|date_format:Y/m/d H:i',
            'end_datetime' => 'nullable|date_format:Y/m/d H:i',
            'target_roll' => 'required',
            'brand' => 'required',
            'organization_shops' => 'required',
        ];
    }

    // resource/lang/ja/validation.phpを使用する場合に各フォーム項目の項目名の定義が必要
    public function attributes()
    {
        return [
            'title'              => 'タイトル',
            'tag_name'           => '検索タグ',
            'file_path'          => 'ファイル',
            'category_id'        => 'カテゴリ',
            'emergency_flg'      => 'ラベル',
            'start_datetime'     => '掲載開始日時',
            'end_datetime'       => '掲載終了日時',
            'target_roll'        => '対象業態',
            'brand'              => '対象業態',
            'organization_shops' => '対象店舗',
        ];
    }
    public function messages()
    {
        $messages = [
            /* resource/lang/ja/validation.phpにバリデーション用の言語ファイルを追加したので特殊なメッセージ以外は
             * ここに登録しなくても大丈夫です。
            'title.required' => 'タイトルは必須項目です',
            'title.max' => 'タイトルは20文字までです',
            'file_path.required' => 'ファイルを添付してください',
            'category_id.required' => 'カテゴリを選択してください',
            'start_datetime.date_format' => '日時の形式で入力してください',
            'end_datetime.date_format' => '日時の形式で入力してください',
            'target_roll' => '対象者を選択してください',
            'brand.required' => '対象業態を選択してください',
            'organization_shops.required' => '対象店舗を選択してください',
            */
        ];

        return $messages;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'file_path' => array_filter($this->input('file_path', [])),
            'start_datetime' => $this->input('start_datetime') === 'null' ? null : $this->input('start_datetime'),
            'end_datetime' => $this->input('end_datetime') === 'null' ? null : $this->input('end_datetime'),
        ]);
    }
}
