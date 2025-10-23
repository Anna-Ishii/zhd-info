<?php

namespace App\Http\Requests\Admin\Manual;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class PublishStoreRequest extends FormRequest
{

    public function rules()
    {
        return [
            'manual_type' => ['required'],
            'new_category_id' => ['required'],
            'title' => ['required'],
            'file' => ['required', 'file', 'mimes:mp4,mov,m4v,jpeg,jpg,png,pdf', 'max:102400'],
            'file_name' => ['nullable'],
            'file_path' => ['required'],
            'step.*.title' => ['nullable'],
            'step.*.detail' => ['nullable'],
            'step.*.file' => ['nullable', 'file', 'mimes:mp4,mov,m4v,jpeg,jpg,png,pdf', 'max:102400'],
            'step.*.file_name' => ['nullable'],
            'step.*.file_path' => ['nullable'],
            'start_datetime' => ['nullable'],
            'end_datetime' => ['nullable'],
            'brand' => ['required'],
            'selected_shops' => ['required'],
            'wowtalk_notification' => ['nullable', 'boolean'],
            'description' => ['nullable'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $steps = $this->input('step', []);
            foreach ($steps as $i => $step) {
                $hasTitle    = !empty($step['title']);
                $hasFilePath = !empty($step['file_path']);


                if (($hasTitle || $hasFilePath) && !($hasTitle && $hasFilePath)) {
                    if (!$hasTitle) {
                        $validator->errors()->add("step.$i.title", "タイトルとファイルパスは両方入力してください。");
                    }
                    if (!$hasFilePath) {
                        $validator->errors()->add("step.$i.file_path", "タイトルとファイルパスは両方入力してください。");
                    }
                }
            }
        });
    }

    public function messages()
    {
        return [
            'manual_type.required' => '形式を選択してください',
            'new_category_id.required' => 'カテゴリを選択してください',
            'title.required' => 'タイトルは必須項目です',
            'file.required' => 'メインファイルは必須項目です',
            'file.file' => 'メインファイルの形式が正しくありません',
            'file.mimes' => 'メインファイルの形式が正しくありません',
            'file.max' => 'メインファイルのサイズは100MB以内にしてください',
            'file_path.required' => 'ファイルが正しくアップロードされていません。',
            'brand.required' => '対象業態を選択してください',
            'selected_shops.required' => '対象店舗を選択してください',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        Log::error('バリデーションエラー:', [
            'errors' => $validator->errors()->all(),
            'input' => $this->all()
        ]);

        throw new HttpResponseException(
            response()->json(['errors' => $validator->errors()], 422)
        );
    }
}
