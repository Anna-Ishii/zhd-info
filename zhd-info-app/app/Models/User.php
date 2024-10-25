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

    // public function distributeMessages(): Void
    // {
    //     $shop = Shop::find($this->shop_id);
    //     $messages = [];
    //     if (isset($shop->organization5_id)) {
    //         $messages = MessageOrganization::query()
    //             ->join('message_brand', 'message_organization.message_id', '=', 'message_brand.message_id')
    //             ->select('message_organization.message_id as id')
    //             ->where('message_organization.organization5_id', $shop->organization5_id)
    //             ->where('message_brand.brand_id', $shop->brand_id)
    //             ->get()
    //             ->toArray();
    //     } elseif (isset($shop->organization4_id)) {
    //         $messages = MessageOrganization::query()
    //             ->join('message_brand', 'message_organization.message_id', '=', 'message_brand.message_id')
    //             ->select('message_organization.message_id as id')
    //             ->where('message_organization.organization4_id', $shop->organization4_id)
    //             ->where('message_brand.brand_id', $shop->brand_id)
    //             ->get()
    //             ->toArray();
    //     } elseif (isset($shop->organization3_id)) {
    //         $messages = MessageOrganization::query()
    //             ->join('message_brand', 'message_organization.message_id', '=', 'message_brand.message_id')
    //             ->select('message_organization.message_id as id')
    //             ->where('message_organization.organization3_id', $shop->organization3_id)
    //             ->where('message_brand.brand_id', $shop->brand_id)
    //             ->get()
    //             ->toArray();
    //     } elseif (isset($shop->organization2_id)) {
    //         $messages = MessageOrganization::query()
    //             ->join('message_brand', 'message_organization.message_id', '=', 'message_brand.message_id')
    //             ->select('message_organization.message_id as id')
    //             ->where('message_organization.organization2_id', $shop->organization2_id)
    //             ->where('message_brand.brand_id', $shop->brand_id)
    //             ->get()
    //             ->toArray();
    //     }

    //     $message_data = [];
    //     foreach ($messages as $message) {
    //         $message_data[$message['id']] = ['shop_id' => $shop->id];
    //     }

    //     $user = User::find($this->id);
    //     $user->message()->sync($message_data);
    // }

    public function distributeMessages(): Void
    {
        $shop = Shop::find($this->shop_id);
        $messages = [];

        if (isset($shop->brand_id)) {
            $messages = MessageShop::query()
                ->select('message_shop.message_id as id', 'message_shop.selected_flg as selected_flg')
                ->where('message_shop.brand_id', $shop->brand_id)
                ->get()
                ->toArray();
        }

        // if (isset($shop->organization5_id)) {
        //     $messages = MessageOrganization::query()
        //         ->join('message_brand', 'message_organization.message_id', '=', 'message_brand.message_id')
        //         ->select('message_organization.message_id as id')
        //         ->where('message_organization.organization5_id', $shop->organization5_id)
        //         ->where('message_brand.brand_id', $shop->brand_id)
        //         ->get()
        //         ->toArray();
        // } elseif (isset($shop->organization4_id)) {
        //     $messages = MessageOrganization::query()
        //         ->join('message_brand', 'message_organization.message_id', '=', 'message_brand.message_id')
        //         ->select('message_organization.message_id as id')
        //         ->where('message_organization.organization4_id', $shop->organization4_id)
        //         ->where('message_brand.brand_id', $shop->brand_id)
        //         ->get()
        //         ->toArray();
        // } elseif (isset($shop->organization3_id)) {
        //     $messages = MessageOrganization::query()
        //         ->join('message_brand', 'message_organization.message_id', '=', 'message_brand.message_id')
        //         ->select('message_organization.message_id as id')
        //         ->where('message_organization.organization3_id', $shop->organization3_id)
        //         ->where('message_brand.brand_id', $shop->brand_id)
        //         ->get()
        //         ->toArray();
        // } elseif (isset($shop->organization2_id)) {
        //     $messages = MessageOrganization::query()
        //         ->join('message_brand', 'message_organization.message_id', '=', 'message_brand.message_id')
        //         ->select('message_organization.message_id as id')
        //         ->where('message_organization.organization2_id', $shop->organization2_id)
        //         ->where('message_brand.brand_id', $shop->brand_id)
        //         ->get()
        //         ->toArray();
        // }

        $message_data = [];
        foreach ($messages as $message) {
            $selected_flg = MessageShop::where('message_id', $message['id'])
                ->value('selected_flg');

            // selected_flgが'all'の場合のみ追加
            if ($selected_flg === 'all') {
                $message_data[$message['id']] = ['shop_id' => $shop->id];

                // message_shopテーブルにデータをインサート ここが問題　めっちゃとうろくされてまう
                MessageShop::insert(['message_id' => $message['id'], 'shop_id' => $shop->id, 'brand_id' => $shop->brand_id, 'selected_flg' => 'all']);
            }
        }

        $user = User::find($this->id);
        $user->message()->sync($message_data);
    }
}
