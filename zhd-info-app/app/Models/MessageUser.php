<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MessageUser extends Pivot
{
    protected $dates = ['readed_datetime'];

    public function getFormattedReadedDatetimeAttribute()
    {
        $before_datetime = $this->attributes['readed_datetime'];
        Carbon::setLocale('ja');
        return $before_datetime ? Carbon::parse($before_datetime)->isoFormat('YYYY/MM/DD(ddd) HH:mm') : null;
    }
}