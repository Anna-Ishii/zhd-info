<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admin extends Model
{
    use SoftDeletes;

    protected $table = 'admin';
    protected $fillable =
    [
        'name',
        'email',
        'password',
        'employee_code',
        'organization1_id',
    ];

    public function organization1(): BelongsTo
    {
        return $this->belongsTo(Organization1::class, 'organization1_id', 'id');
    }
    // public function getOrganization1()
    // {
    //     $org1 = DB::
    // }
}
