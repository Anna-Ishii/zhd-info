<?php

namespace App\Models;

use App\Models\Traits\WhereLike;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use WhereLike;
    use SoftDeletes;

    protected $fillable =
    [
        'name',
        'belong_label',
        'email',
        'password',
        'employee_code',
        'shop_id',
        'roll_id',
    ];

    public function message(): BelongsToMany
    {
        return $this->belongsToMany(Message::class, 'message_user', 'user_id', 'message_id')
            ->withPivot('read_flg', 'shop_id');
    }

    public function unreadMessages(): BelongsToMany
    {
        return $this->belongsToMany(Message::class, 'message_user', 'user_id', 'message_id')
            ->withPivot('shop_id')
            ->wherePivot('read_flg', false);
    }

    public function manual(): BelongsToMany
    {
        return $this->belongsToMany(Manual::class, 'manual_user', 'user_id', 'manual_id')
            ->withPivot('read_flg', 'shop_id');
    }

    public function unreadManuals(): BelongsToMany
    {
        return $this->belongsToMany(Manual::class, 'manual_user', 'user_id', 'manual_id')
            ->withPivot('shop_id')
            ->wherePivot('read_flg', false);
    }

    public function roll(): BelongsTo
    {
        return $this->belongsTo(Roll::class, 'roll_id', 'id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }

    public function crew(): HasMany
    {
        return $this->hasMany(Crew::class, 'user_id', 'id');
    }

    public function distributeMessages(): Void
    {
        $shop = Shop::find($this->shop_id);
        $messages = [];

        if (isset($shop->brand_id)) {
            $messages = MessageShop::query()
                ->select('message_shop.message_id as id')
                ->where('message_shop.selected_flg', 'all')
                ->where('message_shop.brand_id', $shop->brand_id)
                ->cursor();
        }

        $message_data = [];
        foreach ($messages as $message) {
            $message_data[$message['id']] = ['shop_id' => $shop->id];
        }

        // message_shopテーブルにデータをインサート
        foreach ($message_data as $message_id => $data) {
            MessageShop::insert([
                'message_id' => $message_id,
                'shop_id' => $data['shop_id'],
                'selected_flg' => 'all',
                'created_at' => now(),
                'updated_at' => now(),
                'brand_id' => $shop->brand_id,
            ]);
        }

        $user = User::find($this->id);
        $user->message()->sync($message_data);
    }
}
