<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TagRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        if (!$this->isArrayUnique($value)) 
            $fail('タグが重複しています');
        if (count($value) > 5) 
            $fail('タグは最大5つまでです');
        
    }

    // 配列の中身がユニークであればtrueを返す
    private function isArrayUnique($array): Bool
    {
        $uniqueArray = array_unique($array);
        if (count($array) == count($uniqueArray)) {
            return true;
        } else {
            return false;
        }

    }
}
