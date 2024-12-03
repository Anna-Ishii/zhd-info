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

    public function messages()
    {
        $messages = [
            'title.required' => 'タイトルは必須項目です',
            'title.max' => 'タイトルは20文字までです',
            'file_path.required' => 'ファイルを添付してください',
            'category_id.required' => 'カテゴリを選択してください',
            'start_datetime.date_format' => '日時の形式で入力してください',
            'end_datetime.date_format' => '日時の形式で入力してください',
            'target_roll' => '対象者を選択してください',
            'brand.required' => '対象業態を選択してください',
            'organization_shops.required' => '対象店舗を選択してください',
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
