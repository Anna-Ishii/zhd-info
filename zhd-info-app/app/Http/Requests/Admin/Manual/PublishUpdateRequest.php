<?php

namespace App\Http\Requests\Admin\Manual;

use Illuminate\Foundation\Http\FormRequest;

class PublishUpdateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'start_datetime' => 'nullable|date',
            'end_datetime' => 'nullable|date|after_or_equal:start_datetime',
            'start_pending' => 'nullable|boolean',
            'end_pending' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'end_datetime.after_or_equal' => '終了日時は開始日時以降を指定してください',
        ];
    }
}
