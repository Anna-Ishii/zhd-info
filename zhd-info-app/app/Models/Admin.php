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

    public function getBrand()
    {
        $admin_id = $this->attributes['id'];
        return Brand::query()
            ->join('admin_organization1', function ($join) use ($admin_id){
                $join->on('admin_organization1.organization1_id', '=', 'brands.organization1_id');
                $join->where('admin_organization1.admin_id', '=', $admin_id);
            })
            ->orderBy('brands.id', 'asc')
            ->get();
    }

    public function firstBrand()
    {
        $brands = $this->getBrand();
        return $brands[0];
    }
}
