<?php

namespace App\Http\Controllers\Admin\Manual;

use App\Http\Controllers\Controller;
use App\Models\Manual;
use App\Models\ManualCategory;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;

class ManualManageController extends Controller
{
    public function index()
    {
        $category_list = ManualCategory::all();

        // $message_list = $user->message;
        $manual_list = Manual::orderBy('created_at', 'desc');
        return view('admin.manual.manage.index', [
            'category_list' => $category_list,
            'manual_list' => $manual_list->paginate(5)->appends(request()->query())
        ]);
    }

    public function detail($manual_id)
    {
        $manual = Manual::find($manual_id);
        // $target_user = $manual->user;
        // $target_org1 = $manual->organization1()->pluck('organization1.id')->toArray();
        $target_shops = self::get_target_users($manual);
        $category_list = ManualCategory::all();

        return view('admin.manual.manage.detail', [
            'manual' => $manual,
            'content' => $manual->content,
            'target_shops' => $target_shops->paginate(10)
                ->appends(request()->query()),
            'category_list' => $category_list
        ]);
    }

    /**
     * 動画マニュアルの該当shopと、該当userの閲覧数と在籍者数を取得する
     *  
     * @param Manual $manual マニュアルオブジェクト
     * @return Shop 
     */
    private function get_target_users(Manual $manual)
    {
        $target_user_isread = DB::table('manual_user')
        ->select('shop_id')
        ->selectRaw('COUNT(*) as total')
        ->where('manual_id', $manual->id)
        ->where('read_flg', 1)
        ->groupBy('shop_id');

        $target_user = DB::table('manual_user')
            ->select('shop_id')
            ->selectRaw('COUNT(*) as total')
            ->where('manual_id', $manual->id)
            ->groupBy('shop_id');

        $result = Shop::leftJoinSub($target_user_isread, 'target_user_isread', function ($join) {
                $join->on('shops.id', '=', 'target_user_isread.shop_id');
            })
            ->leftJoinSub($target_user, 'target_user', function ($join) {
                $join->on('shops.id', '=', 'target_user.shop_id');
            })
            ->select('shops.*', 'target_user_isread.total as target_user_isread_total', 'target_user.total as target_user_total');
        return $result;
    }
}
