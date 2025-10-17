<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SearchCondition extends Model
{
    use SoftDeletes;

    protected $table = 'search_conditions';
    protected $fillable =
    [
        'id',
        'admin_id',
        'page_name',
        'url',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
