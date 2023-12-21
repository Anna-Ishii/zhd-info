<?php

namespace App\Enums;



enum SearchPeriod:string
{
    case All = 'all';
    case Past_week = 'past_week';
    case Past_month = 'past_month';

    public function text(): string
    {
        return match ($this) {
            self::All       => '全て',
            self::Past_week => '過去1週間',
            self::Past_month  => '過去1ヶ月',
        };
    }

    public function query()
    {
        return match ($this) {
            self::All       => '全て',
            self::Past_week => '過去一週間',
            self::Past_month  => '過去一ヶ月',
        };
    }
}
