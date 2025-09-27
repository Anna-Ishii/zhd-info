<?php

namespace App\Http\Controllers\Admin\Manual;

use App\Http\Controllers\Controller;
use App\Models\ManualCategoryLevel1;
use App\Models\ManualCategoryLevel2;
use App\Http\Requests\Admin\Manual\CategoryUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManualCategoryController extends Controller
{
    public function __construct() {}

    public function index()
    {
        $admin = session('admin');

        // カテゴリリスト
        // $new_category_list = ManualCategoryLevel2::query()
        // ->select([
        //     'manual_category_level2s.id as id',
        //     DB::raw('concat(manual_category_level1s.name, "|", manual_category_level2s.name) as name')
        // ])
        // ->leftjoin('manual_category_level1s', 'manual_category_level1s.id', '=', 'manual_category_level2s.level1')
        // ->get();

        // 業態
        // $organization1_id = $request->input('brand') ? base64_decode($request->input('brand')) : $organization1_list[0]->id;

        $categories = ManualCategoryLevel1::with('level2s')->get();
        return view('admin.manual.category.index', [
            'categories' => $categories,
        ]);
    }

    public function update(CategoryUpdateRequest $request)
    {
        // フォームリクエストによってバリデーション済みのデータを取得
        $validated = $request->validated();
        dd($validated);

        try {
            // データベーストランザクションを開始
            DB::transaction(function () use ($validated) {

                // --- 1. 既存カテゴリの更新と削除 ---
                if (!empty($validated['categories'])) {
                    foreach ($validated['categories'] as $level1Id => $level1Data) {
                        // 1-1. 親カテゴリの削除 (論理削除か物理削除か未定のためコメントアウト)
                        /*
                        if (isset($level1Data['delete']) && $level1Data['delete'] == '1') {
                            ManualCategoryLevel1::destroy($level1Id);
                            continue; // 削除した場合は、以降の更新処理をスキップ
                        }
                        */

                        // 1-2. 親カテゴリの更新 (変更があった場合のみ)
                        $categoryLevel1 = ManualCategoryLevel1::find($level1Id);
                        if ($categoryLevel1 && $categoryLevel1->name !== $level1Data['name']) {
                            $categoryLevel1->update([
                                'name' => $level1Data['name'],
                            ]);
                        }

                        // 1-3. 子カテゴリの更新と削除
                        if ($categoryLevel1 && !empty($level1Data['subcategories'])) {
                            foreach ($level1Data['subcategories'] as $level2Id => $level2Data) {
                                // 子カテゴリの削除 (コメントアウト)
                                /*
                                if (isset($level2Data['delete']) && $level2Data['delete'] == '1') {
                                    ManualCategoryLevel2::destroy($level2Id);
                                    continue;
                                }
                                */

                                // 子カテゴリの更新 (変更があった場合のみ)
                                $categoryLevel2 = ManualCategoryLevel2::find($level2Id);
                                if ($categoryLevel2 && $categoryLevel2->name !== $level2Data['name']) {
                                    $categoryLevel2->update([
                                        'name' => $level2Data['name'],
                                    ]);
                                }
                            }
                        }
                    }
                }

                // --- 2. 新規カテゴリの登録 ---
                if (!empty($validated['new_categories'])) {
                    foreach ($validated['new_categories'] as $level1Data) {
                        // 親カテゴリ名が空ならスキップ
                        if (empty($level1Data['name'])) {
                            continue;
                        }

                        // 2-1. 新しい親カテゴリを作成
                        $newCategoryLevel1 = ManualCategoryLevel1::create([
                            'name' => $level1Data['name'],
                        ]);

                        // 2-2. 新しい子カテゴリを作成
                        if (!empty($level1Data['subcategories'])) {
                            foreach ($level1Data['subcategories'] as $level2Data) {
                                if (empty($level2Data['name'])) {
                                    continue;
                                }
                                // リレーションを使って子カテゴリを作成
                                $newCategoryLevel1->level2s()->create([
                                    'name' => $level2Data['name'],
                                ]);
                            }
                        }
                    }
                }
            });
        } catch (\Exception $e) {
            // エラーが発生した場合は、エラーメッセージと共にリダイレクト
            return redirect()->back()->with('error', '更新に失敗しました。' . $e->getMessage());
        }

        // 完了したら、成功メッセージと共に元のページにリダイレクト
        return redirect()->back()->with('success', 'カテゴリを更新しました。');
    }
}
