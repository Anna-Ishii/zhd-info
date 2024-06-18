<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ManualUser extends Pivot
{
    protected $table = 'manual_user';

    protected $fillable = [
        'user_id',
        'manual_id',
        'read_flg',
        'shop_id',
        'created_at',
        'updated_at',
        'readed_datetime',
    ];
    protected $dates = ['readed_datetime'];

    public function getFormattedReadedDatetimeAttribute()
    {
        $before_datetime = $this->attributes['readed_datetime'];
        Carbon::setLocale('ja');
        return $before_datetime ? Carbon::parse($before_datetime)->isoFormat('YYYY/MM/DD(ddd) HH:mm') : null;
    }
}
