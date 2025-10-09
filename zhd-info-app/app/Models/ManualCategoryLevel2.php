<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManualCategoryLevel2 extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable =
    [
        'name',
        'level1',
        'sort_order',
    ];

    public function level1(): BelongsTo
    {
        return $this->belongsTo(ManualCategoryLevel1::class, 'level1', 'id');
    }

    public function manuals(): HasMany
    {
        return $this->hasMany(Manual::class, 'category_level2_id', 'id')
            ->RecentPublishing();
    }

    /**
     * 送信されたデータに基づいて、特定の親カテゴリに紐づく小カテゴリを一括で同期
     *
     * @param ManualCategoryLevel1 $manual_category_level_1
     * @param array $sub_categories
     * @return void
     */
    public static function synchronize(ManualCategoryLevel1 $manual_category_level_1, ?array $sub_categories): void
    {
        if (is_null($sub_categories)) {
            return;
        }

        foreach ($sub_categories as $id => $data) {
            // キーが数値の場合は既存のレコード、文字列('new_...')の場合は新規レコードとして処理
            if (is_numeric($id)) {
                // --- 既存の小カテゴリの処理 ---
                $subcategory = self::find($id);
                if (!$subcategory) continue;

                if (isset($data['delete']) && $data['delete']) {
                    $subcategory->delete();
                    continue;
                }

                $subcategory->fill(['name' => $data['name'], 'sort_order' => $data['sort_order']]);
                if ($subcategory->isDirty()) {
                    $subcategory->save();
                }
            } else {
                // --- 新規の小カテゴリの処理 ---
                if (empty($data['name'])) continue;

                self::findAndRestoreOrCreate(
                    $data['name'],
                    $manual_category_level_1,
                    $data['sort_order'] ?? 1
                );
            }
        }
    }

    /**
     * 親カテゴリに紐づく削除済みの同名小カテゴリがあれば復元し、なければ新規作成します。
     *
     * @param string $name
     * @param ManualCategoryLevel1 $manual_category_level_1
     * @param int $sort_order
     * @return self
     */
    protected static function findAndRestoreOrCreate(string $name, ManualCategoryLevel1 $manual_category_level_1, int $sort_order = 1): self
    {
        $subcategory = $manual_category_level_1->level2s()->withTrashed()
            ->where('name', $name)
            ->first();

        if ($subcategory) {
            if ($subcategory->trashed()) {
                $subcategory->restore();
            }
            $subcategory->sort_order = $sort_order;
            $subcategory->save();
            return $subcategory;
        }

        return $manual_category_level_1->level2s()->create([
            'name' => $name,
            'sort_order' => $sort_order,
        ]);
    }
}
