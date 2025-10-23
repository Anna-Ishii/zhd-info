<?php

namespace App\Http\Requests\Admin\Manual;

use App\Models\Manual;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class UpdateManualCategoryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'organization1_id' => ['required', 'integer', 'exists:organization1,id'],

            // --- 既存カテゴリの配列 ---
            'categories' => ['nullable', 'array'],
            'categories.*.sort_order' => ['required', 'integer', 'min:1'],
            'categories.*.delete' => ['nullable', 'boolean'],
            'categories.*.sub_categories' => ['nullable', 'array'],
            'categories.*.sub_categories.*.name' => ['required', 'string', 'max:255'],
            'categories.*.sub_categories.*.sort_order' => ['required', 'integer', 'min:1'],
            'categories.*.sub_categories.*.delete' => ['nullable', 'boolean'],

            // --- 新規カテゴリの配列 ---
            'new_categories' => ['nullable', 'array'],
            'new_categories.*.name' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('manual_category_level1s', 'name')
                    ->where('organization1_id', $this->input('organization1_id'))
            ],
            'new_categories.*.sub_categories' => ['nullable', 'array'],
            'new_categories.*.sub_categories.*.name' => ['nullable', 'string', 'max:255'],
            'new_categories.*.sub_categories.*.sort_order' => ['nullable', 'integer', 'min:1'],
        ];

        // 既存カテゴリ名のユニークチェック（自分自身を除く）
        if ($this->input('categories')) {
            foreach (array_keys($this->input('categories')) as $categoryId) {
                if (is_numeric($categoryId)) {
                    $rules['categories.' . $categoryId . '.name'] = [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('manual_category_level1s', 'name')
                            ->where('organization1_id', $this->input('organization1_id'))
                            ->ignore($categoryId),
                    ];
                }
            }
        }

        return $rules;
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            if (!$this->input('categories')) {
                return;
            }
            foreach ($this->input('categories') as $categoryId => $categoryData) {
                if (is_numeric($categoryId) && isset($categoryData['delete']) && $categoryData['delete']) {
                    if (Manual::where('category_level1_id', $categoryId)->exists()) {
                        $validator->errors()->add("categories.{$categoryId}.name", 'このカテゴリはマニュアルに紐付いているため削除できません。');
                    }
                }

                if (!empty($categoryData['sub_categories'])) {
                    foreach ($categoryData['sub_categories'] as $subcategoryId => $subcategoryData) {
                        if (is_numeric($subcategoryId) && isset($subcategoryData['delete']) && $subcategoryData['delete']) {
                            if (Manual::where('category_level2_id', $subcategoryId)->exists()) {
                                $validator->errors()->add("categories.{$categoryId}.sub_categories.{$subcategoryId}.name", 'この小カテゴリはマニュアルに紐付いているため削除できません。');
                            }
                        }
                    }
                }
            }

            if ($this->input('new_categories')) {
                foreach ($this->input('new_categories') as $index => $level1Data) {
                    // 親カテゴリ名が空かどうかをチェック
                    $parentNameIsEmpty = empty($level1Data['name']);

                    // 子カテゴリが1つでも入力されているかどうかをチェック
                    $hasSubcategories = false;
                    if (!empty($level1Data['sub_categories'])) {
                        foreach ($level1Data['sub_categories'] as $subCategoryData) {
                            if (!empty($subCategoryData['name'])) {
                                $hasSubcategories = true;
                                break;
                            }
                        }
                    }

                    // 親が空で、かつ子が存在する場合
                    if ($parentNameIsEmpty && $hasSubcategories) {
                        $validator->errors()->add(
                            'new_categories.' . $index . '.name',
                            'カテゴリ名は必須です。'
                        );
                    }
                }
            }
        });
    }
}
