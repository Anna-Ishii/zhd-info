<?php

namespace App\Http\Requests\Admin\Manual;

use App\Models\Manual;
use Illuminate\Foundation\Http\FormRequest;

class PublishUpdateRequest extends FormRequest
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
        // 一時保存
        if ($this->input('save'))
        return [
            'file'  => 'max:150000' . $mimeTypesRule,
            'manual_flow.*.file' => 'max:150000' . $mimeTypesRule,
        ];

        $manual_id = $this->route('manual_id');
        $manual = Manual::findOrFail($manual_id);
        return [
            'title' => 'required',
            'description' => 'nullable',
            'file'  => 'max:150000'.$mimeTypesRule.(isset($manual->content_url) ? '' : '|required'),
            'category_id' => 'required',
            'brand' => 'required',
            'start_datetime' => 'nullable',
            'end_datetime' => 'nullable',
            'manual_flow.*.title' => 'required',
            'manual_flow.*.file' => 'max:150000'.$mimeTypesRule,
            'manual_flow.*.detail' => 'nullable',
            'content_id.*' => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'タイトルは必須項目です',
            'file.mimetypes' => 'mp4,mov,m4v,jpeg,jpg,png,pdf形式のファイルを添付してください',
            'file' => 'ファイルのアップデートに失敗しました',
            'file.max' => 'ファイルの容量が大きすぎます。150MB以下にしてください',
            'category_id.required' => 'カテゴリを選択してください',
            'brand.required' => '対象業態を選択してください',
            'manual_flow.*.title.required' => '手順名は必須項目です',
            'manual_flow.*.file.required' => '手順ファイルは必須項目です',
            'manual_flow.*.file.mimetypes' => '手順ファイルはmp4,mov,m4v,jpeg,jpg,png,pdf形式のファイルを添付してください',
            'manual_flow.*.file' => '手順ファイルのアップロードに失敗しました',
        ];
    }
}
