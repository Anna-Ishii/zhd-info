<?php

namespace App\Rules\Import;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class OrganizationRule implements ValidationRule
{

    public function __construct($parameter)
    {
        $this->parameter = $parameter;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!isset($value)) return;
        $noMatches = $this->hasNonMatchingValues($this->strToArray($value), $this->parameter);

        if (!empty($noMatches)) {
            $noMatchesStr = implode(',', $noMatches);
            $fail("選択フォーム以外の値が含まれています({$noMatchesStr})");
        }
    }

    private function hasNonMatchingValues($a, $b)
    {
        // bの中身以外の値がaに含まれているかを確認
        $nonMatchingValues = array_diff($a, $b);

        // 非一致の値が存在すればtrueを返す
        return $nonMatchingValues;
    }

    private  function strToArray(?String $str): array
    {
        if (!isset($str)) return [];

        $array = explode(',', $str);

        $returnArray = [];
        foreach ($array as $key => $value) {
            $returnArray[] = trim($value, "\"");
        }

        return $returnArray;
    }
}
