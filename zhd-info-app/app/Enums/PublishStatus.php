<?php

namespace App\Enums;



enum PublishStatus: int
{
    case Wait        = 1;
    case Publishing  = 2;
    case Published   = 3;
    case Editing     = 4;

    public function text(): string
    {
        return match ($this) {
            self::Wait       => '待機',
            self::Publishing => '掲載中',
            self::Published  => '掲載終了',
            self::Editing    => '編集中',
        };
    }

}