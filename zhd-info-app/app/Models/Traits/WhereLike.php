<?php
// 参考
// https://qiita.com/take_3/items/1154ecbd8033a9a3beaf

namespace App\Models\Traits;

trait WhereLike
{
// 部分一致検索
public function scopeWhereLike($query, string $column, string $keyword)
{
return $query->where($column, 'like', '%' . addcslashes($keyword, '%_\\') . '%');
}

// 前方一致検索
public function scopeWhereLikeForward($query, string $column, string $keyword)
{
return $query->where($column, 'like', addcslashes($keyword, '%_\\') . '%');
}
}