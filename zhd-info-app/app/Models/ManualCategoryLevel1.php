<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class ManualCategoryLevel1 extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable =
    [
        'name',
        'sort_order',
        'organization1_id',
    ];

    public function level2s(): HasMany
    {
        return $this->hasMany(ManualCategoryLevel2::class, 'level1', 'id')
            ->orderBy('sort_order', 'asc');
    }

    public function organization1(): BelongsTo
    {
        return $this->belongsTo(Organization1::class, 'organization1_id');
    }

    /**
     * 指定された業態に紐づく、ソート済みのカテゴリとサブカテゴリを取得します。
     *
     * @param int|null $organization_id
     * @return \Illuminate\Database\Eloquent\Collection<ManualCategoryLevel1>
     */
    public static function getSortedByOrganization(?int $organization_id): Collection
    {
        return self::with(['level2s' => function ($query) {
            $query->orderBy('sort_order', 'asc');
        }])
            ->where('organization1_id', $organization_id)
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    /**
     * 送信されたデータに基づいて、カテゴリを一括で同期（更新・登録・削除）します。
     *
     * @param array $validated_data バリデーション済みのリクエストデータ
     * @return void
     * @throws \Throwable
     */
    public static function synchronizeCategories(array $validated_data): void
    {
        $organization_id = $validated_data['organization1_id'];

        DB::transaction(function () use ($validated_data, $organization_id) {

            // --- 既存カテゴリの更新と削除 ---
            if (!empty($validated_data['categories'])) {
                foreach ($validated_data['categories'] as $level1Id => $level1Data) {
                    $categoryLevel1 = self::find($level1Id);
                    if (!$categoryLevel1) continue;

                    if (isset($level1Data['delete']) && $level1Data['delete']) {
                        $categoryLevel1->delete();
                        continue;
                    }

                    $categoryLevel1->fill(['name' => $level1Data['name'], 'sort_order' => $level1Data['sort_order']]);
                    if ($categoryLevel1->isDirty()) $categoryLevel1->save();

                    // 小カテゴリ
                    ManualCategoryLevel2::synchronize($categoryLevel1, $level1Data['sub_categories'] ?? null);
                }
            }

            // --- 新規カテゴリの登録 (復元ロジックを含む) ---
            if (!empty($validated_data['new_categories'])) {
                $last_sort_order = self::where('organization1_id', $organization_id)->max('sort_order') ?? 0;

                foreach ($validated_data['new_categories'] as $level1Data) {
                    if (empty($level1Data['name'])) continue;

                    $last_sort_order++;

                    $newCategoryLevel1 = self::findAndRestoreOrCreate(
                        $level1Data['name'],
                        $organization_id,
                        $last_sort_order
                    );

                    // 小カテゴリ
                    ManualCategoryLevel2::synchronize($newCategoryLevel1, $level1Data['sub_categories'] ?? null);
                }
            }
        });
    }

    /**
     * 削除済みの同名カテゴリがあれば復元し、なければ新規作成
     *
     * @param string $name 名称
     * @param int $organization_id 業態ID
     * @param int $sort_order ソート順
     * @return self
     */
    protected static function findAndRestoreOrCreate(string $name, int $organization_id, int $sort_order = 1): self
    {
        $category = self::withTrashed()
            ->where('organization1_id', $organization_id)
            ->where('name', $name)
            ->first();

        if ($category) {
            if ($category->trashed()) $category->restore();
            $category->sort_order = $sort_order;
            $category->save();
            return $category;
        }

        return self::create([
            'organization1_id' => $organization_id,
            'name' => $name,
            'sort_order' => $sort_order,
        ]);
    }
}
