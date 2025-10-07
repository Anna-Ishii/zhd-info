<?php

namespace App\Http\Controllers\Admin\Manual;

use App\Http\Controllers\Controller;
use App\Models\ManualCategoryLevel1;
use App\Models\Organization1;
use App\Http\Requests\Admin\Manual\UpdateManualCategoryRequest;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ManualCategoryController extends Controller
{
    /**
     * 管理画面03_マニュアル管理_業態ごと設定
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(Request $request): Factory|View
    {
        $all_organizations = Organization1::getAllOrderedById();

        // クエリパラメータから現在の業態IDを取得。なければ最初の業態をデフォルトにする
        $current_organization_id = $request->input('organization1');
        if (is_null($current_organization_id) && $all_organizations->isNotEmpty()) {
            $current_organization_id = $all_organizations->first()->id;
        }

        $categories = ManualCategoryLevel1::getSortedByOrganization($current_organization_id);

        return view('admin.manual.category.index', [
            'all_organizations' => $all_organizations,
            'current_organization_id' => $current_organization_id,
            'categories' => $categories,
        ]);
    }

    /**
     * 管理画面03_マニュアル管理_業態ごと設定_更新処理
     *
     * @param \App\Http\Requests\Admin\Manual\UpdateManualCategoryRequest $request
     * @return RedirectResponse
     */
    public function update(UpdateManualCategoryRequest $request): RedirectResponse
    {
         $validated_data = $request->validated();
        try {
            ManualCategoryLevel1::synchronizeCategories($request->validated());
        } catch (\Throwable $th) {
            Log::error('マニュアル管理_業態ごと設定_更新処理失敗：' . $th->getMessage());
            return redirect()->route('admin.manual.category.index', ['organization1' => $validated_data['organization1_id']])->with('error', '更新中にエラーが発生しました。');
        }
        return redirect()->route('admin.manual.category.index', ['organization1' => $validated_data['organization1_id']])
            ->with('success', 'マニュアルカテゴリを更新しました。');
    }
}
