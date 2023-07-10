<?php

namespace App\Http\Repository;

use App\Models\Admin;
use App\Models\Brand;

class AdminRepository
{
    static function getBrands(Admin $admin)
    {
        return Brand::where('organization1_id', $admin->organization1_id)->get();
    }
}
