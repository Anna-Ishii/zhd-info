<?php

namespace App\Utils;

class Util {

    public static function delweek_string($text)
    {
        $result = preg_replace('/\s*\([^)]*\)\s*/', ' ', $text);
        return $result;
    }
}