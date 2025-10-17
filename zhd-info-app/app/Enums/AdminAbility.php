<?php

namespace App\Enums;



enum AdminAbility: int
{
    case ReadOnly        = 0;
    case Edit            = 1;

    public function text(): string
    {
        return match ($this) {
            self::ReadOnly       => '閲覧',
            self::Edit           => '編集',
        };
    }
}
