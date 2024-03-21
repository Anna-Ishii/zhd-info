<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
    ];

    public function organization1(): BelongsToMany
    {
        return $this->belongsToMany(Organization1::class, 'admin_organization1', 'admin_id', 'organization1_id');
    }
}
