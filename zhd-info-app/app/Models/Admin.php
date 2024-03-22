<?php

namespace App\Models;

use App\Enums\AdminAbility;
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
        'ability'
    ];

    protected $casts = [
        'ability' => AdminAbility::class,
    ];

    public function organization1(): BelongsToMany
    {
        return $this->belongsToMany(Organization1::class, 'admin_organization1', 'admin_id', 'organization1_id');
    }

    public function getBrand()
    {
        $admin_id = $this->attributes['id'];
        return Brand::query()
            ->join('admin_organization1', function ($join) use ($admin_id){
                $join->on('admin_organization1.organization1_id', '=', 'brands.organization1_id');
                $join->where('admin_organization1.admin_id', '=', $admin_id);
            })
            ->orderBy('brands.name', 'asc')
            ->get();
    }

    public function firstBrand()
    {
        $brands = $this->getBrand();
        return $brands[0];
    }

    public function allowpage()
    {
        return $this->belongsToMany(Adminpage::class, 'admin_page', 'admin_id', 'page_id');
    }
}
