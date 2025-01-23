<?php

namespace App\Http\Controllers\Admin\Message;

use Carbon\Carbon;
use App\Enums\PublishStatus;
use App\Exports\MessageListExport;
use App\Exports\MessageListBBExport;
use App\Exports\MessageStoreCsvExport;
use App\Exports\MessageViewRateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Message\PublishStoreRequest;
use App\Http\Requests\Admin\Message\PublishUpdateRequest;
use App\Models\MessageCategory;
use App\Models\Message;
use App\Models\Roll;
use App\Models\Shop;
use App\Models\User;
use App\Http\Repository\Organization1Repository;
use App\Http\Requests\Admin\Message\FileUpdateApiRequest;
use App\Imports\MessageCsvImport;
use App\Imports\MessageBBCsvImport;
use App\Imports\MessageStoreCsvImport;
use App\Models\Brand;
use App\Models\MessageContent;
use App\Models\MessageOrganization;
use App\Models\MessageTagMaster;
use App\Models\MessageShop;
use App\Models\MessageUser;
use App\Models\Organization1;
use App\Models\Organization3;
use App\Models\Organization4;
use App\Models\Organization5;
use App\Rules\Import\OrganizationRule;
use App\Utils\ImageConverter;
use App\Utils\Util;
use App\Utils\PdfFileJoin;
use App\Jobs\SendWowtalkNotificationJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Exception;
use setasign\Fpdi\TcpdfFpdi;

require_once(resource_path("outputpdf/libs/tcpdf/tcpdf.php"));
require_once(resource_path("outputpdf/libs/fpdi/autoload.php"));

class MessagePublishController extends Controller
{
    public function index(Request $request)
    {
        $admin = session('admin');
        $category_list = MessageCategory::all();
        $organization1_list = $admin->getOrganization1();

        // request
        $category_ids = $request->input('category');
        $statusArray = $request->input('status') ?? [];
        $statuses = array_map(function($status) {
            return PublishStatus::tryFrom((int)$status);
        }, $statusArray);
        $q = $request->input('q');
        $organization1_id = $request->input('brand', $organization1_list[0]->id);
        $label = $request->input('label');
        $publish_date = $request->input('publish-date');

        $organization1 = Organization1::find($organization1_id);

        // セッションにデータを保存
        session()->put('brand_id', $organization1_id);

        $sub = DB::table('messages as m')
            ->select([
                'm.id as m_id',
                DB::raw('
                    case
                        when (count(distinct b.name)) = 0 then ""
                        else group_concat(distinct b.name order by b.name)
                    end as b_name
                ')
            ])
            ->leftjoin('message_brand as m_b', 'm.id', 'm_b.message_id')
            ->leftjoin('brands as b', 'b.id', 'm_b.brand_id')
            ->groupBy('m.id');

        // 閲覧率のデータを集計
        $viewRatesSub = DB::table('message_view_rates')
            ->select([
                'message_id',
                'organization1_id',
                DB::raw('MAX(view_rate) as view_rate'),
                DB::raw('MAX(read_users) as read_users'),
                DB::raw('MAX(total_users) as total_users'),
                DB::raw('MAX(updated_at) as last_updated')
            ])
            ->groupBy('message_id', 'organization1_id');

        $message_list = Message::query()
            ->with('create_user', 'updated_user', 'category', 'brand', 'tag')
            ->leftjoin('message_user', 'messages.id', '=', 'message_id')
            ->leftJoinSub($viewRatesSub, 'view_rates', function ($join) {
                $join->on('messages.id', '=', 'view_rates.message_id');
                $join->on('messages.organization1_id', '=', 'view_rates.organization1_id');
            })
            ->leftJoinSub($sub, 'sub', function ($join) {
                $join->on('sub.m_id', '=', 'messages.id');
            })
            ->select([
                'messages.*',
                'view_rates.view_rate',
                'view_rates.read_users',
                'view_rates.total_users',
                'view_rates.last_updated',
                'sub.b_name as brand_name',
            ])
            ->where('messages.organization1_id', $organization1_id)
            ->groupBy(DB::raw('messages.id'))
            ->when(isset($q), function ($query) use ($q) {
                $query->where(function ($query) use ($q) {
                    $query->whereLike('title', $q)
                        ->orWhereHas('tag', function ($query) use ($q) {
                            $query->where('name', $q);
                        });
                });
            })
            ->when(isset($statuses) && count($statuses) > 0, function ($query) use ($statuses) {
                $query->where(function ($query) use ($statuses) {
                    foreach ($statuses as $status) {
                        switch ($status) {
                            case PublishStatus::Wait:
                                $query->orWhere(function ($q) {
                                    $q->waitMessage();
                                });
                                break;
                            case PublishStatus::Publishing:
                                $query->orWhere(function ($q) {
                                    $q->publishingMessage();
                                });
                                break;
                            case PublishStatus::Published:
                                $query->orWhere(function ($q) {
                                    $q->publishedMessage();
                                });
                                break;
                            case PublishStatus::Editing:
                                $query->orWhere('editing_flg', '=', true);
                                break;
                            default:
                                break;
                        }
                    }
                });
            })
            ->when(isset($category_ids), function ($query) use ($category_ids) {
                $query->whereIn('category_id', $category_ids);
            })
            ->when(isset($label), function ($query) use ($label) {
                $query->where('emergency_flg', true);
            })
            ->when((isset($publish_date[0])), function ($query) use ($publish_date) {
                $query->where('start_datetime', '>=', $publish_date[0]);
            })
            ->when((isset($publish_date[1])), function ($query) use ($publish_date) {
                $query->where(function ($query) use ($publish_date) {
                    $query->where('end_datetime', '<=', $publish_date[1])
                        ->orWhereNull('end_datetime');
                });
            })
            ->join('admin', 'create_admin_id', '=', 'admin.id')
            ->orderBy('messages.id', 'desc')
            ->paginate(50)
            ->appends(request()->query());

        // 添付ファイル
        foreach ($message_list as &$message) {
            $file_list = [];
            $is_first_join = false;

            $all_message_join_file = Message::where('id', $message->id)->get()->toArray();
            $all_message_content_single_files = MessageContent::where('message_id', $message->id)->get()->toArray();

            // 最初の要素をチェックしてフラグを設定
            if (isset($all_message_content_single_files[0]) && $all_message_content_single_files[0]["join_flg"] === "join") {
                $is_first_join = true;
            }

            if ($is_first_join) {
                if ($all_message_join_file) {
                    // PDFファイルのページ数を取得
                    $pdf = new TcpdfFpdi();
                    $file_path = $all_message_join_file[0]["content_url"]; // PDFファイルのパス
                    if (file_exists($file_path)) {
                        $message->main_file = [
                            "file_name" => $all_message_join_file[0]["content_name"],
                            "file_url" => $all_message_join_file[0]["content_url"],
                        ];

                        try {
                            $page_num = $pdf->setSourceFile($file_path);
                            $message->main_file_count = $page_num;
                        } catch (\setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException $e) {
                            // 暗号化されたPDFの処理
                            $message->main_file_count = '暗号化';
                        }
                    }
                }
                foreach ($all_message_content_single_files as $message_content_single_file) {
                    if ($message_content_single_file["join_flg"] === "single") {
                        $file_list[] = [
                            "file_name" => $message_content_single_file["content_name"],
                            "file_url" => $message_content_single_file["content_url"],
                        ];
                    }
                }
            } else {
                if ($all_message_content_single_files) {
                    $message->main_file_count = 1;
                    $message->main_file = [
                        "file_name" => $all_message_content_single_files[0]["content_name"],
                        "file_url" => $all_message_content_single_files[0]["content_url"],
                    ];
                }
                foreach ($all_message_content_single_files as $message_content_single_file) {
                    if ($message_content_single_file["content_name"] === $all_message_join_file[0]["content_name"]) {
                        $file_list[] = [
                            "file_name" => $all_message_join_file[0]["content_name"],
                            "file_url" => $all_message_join_file[0]["content_url"],
                        ];
                        continue;
                    } else if ($message_content_single_file["join_flg"] === "single") {
                        $file_list[] = [
                            "file_name" => $message_content_single_file["content_name"],
                            "file_url" => $message_content_single_file["content_url"],
                        ];
                    }
                }
                // 最初の要素を削除(業態ファイル)
                if (!empty($file_list)) {
                    array_shift($file_list);
                }
            }

            $message->content_files = $file_list;

            // ファイルのカウント
            $message->file_count = count($file_list);
        }

        // BBの場合、SKの場合
        if ($organization1_id == 2 || $organization1_id == 8) {
            // セッションにデータを保存
            session()->put('message_list', $message_list);
        }

        // 店舗数をカウント
        if ($message_list) {
            // すべてのメッセージIDを取得
            $message_ids = $message_list->pluck('id')->toArray();
            // すべての店舗数を取得
            $all_shop_count = Shop::where('organization1_id', $organization1_id)->count();
            // 各メッセージに関連する店舗数を取得
            $message_shop_counts = MessageShop::select('message_id', DB::raw('COUNT(*) as shop_count'))
                ->whereIn('message_id', $message_ids)
                ->groupBy('message_id')
                ->pluck('shop_count', 'message_id');

            // メッセージリストをループして、店舗数を割り当て
            foreach ($message_list as &$message) {
                $shop_count = $message_shop_counts[$message->id] ?? 0;
                // 全店舗数と同じ場合は「全店」と表示
                if ($shop_count == $all_shop_count) {
                    $shop_count = "全店";
                }
                $message->shop_count = $shop_count;
            }
        }

        return view('admin.message.publish.index', [
            'category_list' => $category_list,
            'message_list' => $message_list,
            'organization1' => $organization1,
            'organization1_list' => $organization1_list,
        ]);
    }

    // 閲覧率の更新処理
    public function updateViewRates(Request $request)
    {
        $admin = session('admin');
        $organization1_id = $request->input('brand', $admin->firstOrganization1()->id);
        $rate = $request->input('rate');
        $message_id = $request->input('message_id'); // message_idを取得

        // メッセージの既読・総ユーザー数を一度に集計
        $messageRates = DB::table('message_user')
            ->select([
                'message_user.message_id',
                DB::raw('sum(message_user.read_flg) as read_users'),
                DB::raw('count(distinct message_user.user_id) as total_users'),
                DB::raw('round((sum(message_user.read_flg) / count(distinct message_user.user_id)) * 100, 1) as view_rate')
            ])
            ->join('messages', 'message_user.message_id', '=', 'messages.id')
            ->where('messages.organization1_id', $organization1_id)
            ->when($message_id, function ($query) use ($message_id) {
                $query->where('message_user.message_id', $message_id); // message_idでフィルタリング
            })
            ->groupBy('message_user.message_id')
            ->when((isset($rate[0]) || isset($rate[1])), function ($query) use ($rate) {
                $min = isset($rate[0]) ? $rate[0] : 0;
                $max = isset($rate[1]) ? $rate[1] : 100;
                $query->havingRaw('view_rate between ? and ?', [$min, $max]);
            })
            ->get();

        // バルクアップデート用のデータ準備
        $updateData = [];
        foreach ($messageRates as $message) {
            $updateData[] = [
                'message_id' => $message->message_id,
                'organization1_id' => $organization1_id,
                'view_rate' => $message->view_rate,     // 閲覧率の計算
                'read_users' => $message->read_users,   // 既読ユーザー数
                'total_users' => $message->total_users, // 全体ユーザー数
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // バルクアップデートを実行
        DB::table('message_view_rates')->upsert(
            $updateData,
            ['message_id', 'organization1_id'],
            ['view_rate', 'read_users', 'total_users', 'created_at', 'updated_at']
        );

        // 処理完了後にページをリダイレクトして結果を表示
        return redirect()->back()->with('success', '閲覧率が更新されました。');
    }

    public function show(Request $request, $message_id)
    {
        $message = Message::where('id', $message_id)
            ->withCount(['user as total_users'])
            ->withCount(['readed_user as read_users'])
            ->first();

        $organization1 = $message->organization1;
        $_brand = $organization1->brand()->orderBy('id', 'asc');
        $brands = $_brand->pluck('name')->toArray();
        $brand_list = $_brand->get();
        $org3_list = Organization1Repository::getOrg3($organization1);
        $org4_list = Organization1Repository::getOrg4($organization1);
        $org5_list = Organization1Repository::getOrg5($organization1);

        // request
        $brand_id = $request->input('brand');
        $shop_freeword = $request->input('shop_freeword');
        $org3 = $request->input('org3');
        $org4 = $request->input('org4');
        $org5 = $request->input('org5');
        $read_flg = $request->input('read_flg');
        $readed_date = $request->input('readed_date');

        $shop_list = $message
            ->shop()
            ->when(isset($brand_id), function ($query) use ($brand_id) {
                $query->where('brand_id', $brand_id);
            })
            ->when(isset($shop_freeword), function ($query) use ($shop_freeword) {
                $query->whereLike('name', $shop_freeword)
                    ->orWhere(DB::raw('SUBSTRING(shop_code, -4)'), 'LIKE', '%' . $shop_freeword . '%');
            })
            ->when(isset($org3), function ($query) use ($org3) {
                $query->where('organization3_id', $org3);
            })
            ->when(isset($org4), function ($query) use ($org4) {
                $query->where('organization4_id', $org4);
            })
            ->when(isset($org5), function ($query) use ($org5) {
                $query->where('organization5_id', $org5);
            })
            ->pluck('id')
            ->unique()
            ->toArray();

        $user_list = $message
            ->user()
            ->with(['shop', 'shop.organization3', 'shop.organization4', 'shop.organization5', 'shop.brand'])
            ->when(isset($read_flg), function ($query) use ($read_flg) {
                if ($read_flg == 'true') $query->where('read_flg', true);
                if ($read_flg == 'false') $query->where('read_flg', false);
            })
            ->when((isset($readed_date[0])), function ($query) use ($readed_date) {
                $from = Util::delweek_string($readed_date[0]);
                $query->whereRaw("DATE_FORMAT(readed_datetime, '%Y/%m/%d %H:%i') >= ?", $from);
            })
            ->when((isset($readed_date[1])), function ($query) use ($readed_date) {
                $to = Util::delweek_string($readed_date[1]);
                $query->where(function ($query) use ($to) {
                    $query->whereRaw("DATE_FORMAT(readed_datetime, '%Y/%m/%d %H:%i') <= ?", $to);
                });
            })
            ->wherePivotIn('shop_id', $shop_list)
            ->join('shops', 'users.shop_id', '=', 'shops.id')
            ->leftJoin('organization3', 'shops.organization3_id', '=', 'organization3.id')
            ->leftJoin('organization4', 'shops.organization4_id', '=', 'organization4.id')
            ->leftJoin('organization5', 'shops.organization5_id', '=', 'organization5.id')
            ->orderBy('organization3.order_no')
            ->orderBy('organization4.order_no')
            ->orderBy('organization5.order_no')
            ->orderBy('shops.shop_code')
            ->paginate(50)
            ->appends(request()->query());

        return view('admin.message.publish.show', [
            'message' => $message,
            'user_list' => $user_list,
            'brand_list' => $brand_list,
            'org3_list' => $org3_list,
            'org4_list' => $org4_list,
            'org5_list' => $org5_list,
            'brands' => $brands,
        ]);
    }

    public function new(Organization1 $organization1)
    {
        ini_set('memory_limit', '1024M'); // メモリ制限を一時的に増加

        $category_list = MessageCategory::all();

        $target_roll_list = Roll::get(); //「一般」を使わない場合 Roll::where('id', '!=', '1')->get();
        // ブランド一覧を取得する
        $brand_list = Brand::where('organization1_id', $organization1->id)->get();
        $organization_list = [];
        $organization_list = Shop::query()
            ->leftjoin('organization2', 'organization2_id', '=', 'organization2.id')
            ->leftjoin('organization3', 'organization3_id', '=', 'organization3.id')
            ->leftjoin('organization4', 'organization4_id', '=', 'organization4.id')
            ->leftjoin('organization5', 'organization5_id', '=', 'organization5.id')
            ->distinct('organization4_id')
            ->distinct('organization5_id')
            ->select(
                'organization2_id',
                'organization2.name as organization2_name',
                'organization2.order_no as organization2_order_no',
                'organization3_id',
                'organization3.name as organization3_name',
                'organization3.order_no as organization3_order_no',
                'organization4_id',
                'organization4.name as organization4_name',
                'organization4.order_no as organization4_order_no',
                'organization5_id',
                'organization5.name as organization5_name',
                'organization5.order_no as organization5_order_no',
            )
            ->where('organization1_id', $organization1->id)
            ->orderByRaw('organization2_id is null asc')
            ->orderByRaw('organization3_id is null asc')
            ->orderByRaw('organization4_id is null asc')
            ->orderByRaw('organization5_id is null asc')
            ->orderBy("organization2_order_no", "asc")
            ->orderBy("organization3_order_no", "asc")
            ->orderBy("organization4_order_no", "asc")
            ->orderBy("organization5_order_no", "asc")
            ->get()
            ->toArray();


        // 店舗情報を取得する
        $brand_ids = $brand_list->pluck('id')->toArray();
        $all_shops = Shop::query()
            ->select(
                'shops.id as id',
                'shops.shop_code',
                'shops.display_name',
                'shops.organization5_id',
                'shops.organization4_id',
                'shops.organization3_id',
                'shops.organization2_id'
            )
            ->leftJoin('organization5 as org5', 'shops.organization5_id', '=', 'org5.id')
            ->leftJoin('organization4 as org4', 'shops.organization4_id', '=', 'org4.id')
            ->leftJoin('organization3 as org3', 'shops.organization3_id', '=', 'org3.id')
            ->leftJoin('organization2 as org2', 'shops.organization2_id', '=', 'org2.id')
            ->where('shops.organization1_id', $organization1->id)
            ->whereIn('shops.brand_id', $brand_ids)
            ->orderBy('shops.shop_code', 'asc')
            ->get()
            ->toArray();

        // 組織別にデータを整理する
        $organization_list = array_map(function ($org) use ($all_shops) {
            $org['organization5_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                return $shop['organization5_id'] == $org['organization5_id'];
            });
            $org['organization4_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                return $shop['organization4_id'] == $org['organization4_id'] && is_null($shop['organization5_id']);
            });
            $org['organization3_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                return $shop['organization3_id'] == $org['organization3_id'] && is_null($shop['organization4_id']) && is_null($shop['organization5_id']);
            });
            $org['organization2_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                return $shop['organization2_id'] == $org['organization2_id'] && is_null($shop['organization3_id']) && is_null($shop['organization4_id']) && is_null($shop['organization5_id']);
            });
            return $org;
        }, $organization_list);

        // shop_code でソート済みの $all_shops をそのまま利用
        $all_shop_list = array_map(function ($shop) {
            return [
                'shop_id' => $shop['id'],
                'shop_code' => $shop['shop_code'],
                'display_name' => $shop['display_name'],
            ];
        }, $all_shops);


        // 店舗コードでshopsをソート
        usort($all_shop_list, function ($a, $b) {
            return strcmp($a['shop_code'], $b['shop_code']);
        });

        // デフォルトの設定に戻す
        ini_restore('memory_limit');

        return view('admin.message.publish.new', [
            'organization1' => $organization1,
            'category_list' => $category_list,
            'target_roll_list' => $target_roll_list,
            'brand_list' => $brand_list,
            'organization_list' => $organization_list,
            'all_shop_list' => $all_shop_list,
        ]);
    }

    // 一覧画面追加の組織一覧を取得する
    public function messageNewData($organization1_id = null)
    {
        if ($organization1_id == null) {
            return response()->json(['error' => 'Organization ID is required'], 400);
        }

        $category_list = MessageCategory::all();

        $target_roll_list = Roll::get(); //「一般」を使わない場合 Roll::where('id', '!=', '1')->get();

        // ブランド一覧を取得する
        $brand_list = Brand::where('organization1_id', $organization1_id)->get();

        $organization_list = [];
        $organization_list = Shop::query()
            ->leftjoin('organization2', 'organization2_id', '=', 'organization2.id')
            ->leftjoin('organization3', 'organization3_id', '=', 'organization3.id')
            ->leftjoin('organization4', 'organization4_id', '=', 'organization4.id')
            ->leftjoin('organization5', 'organization5_id', '=', 'organization5.id')
            ->distinct('organization4_id')
            ->distinct('organization5_id')
            ->select(
                'organization2_id',
                'organization2.name as organization2_name',
                'organization2.order_no as organization2_order_no',
                'organization3_id',
                'organization3.name as organization3_name',
                'organization3.order_no as organization3_order_no',
                'organization4_id',
                'organization4.name as organization4_name',
                'organization4.order_no as organization4_order_no',
                'organization5_id',
                'organization5.name as organization5_name',
                'organization5.order_no as organization5_order_no',
            )
            ->where('organization1_id', $organization1_id)
            ->orderByRaw('organization2_id is null asc')
            ->orderByRaw('organization3_id is null asc')
            ->orderByRaw('organization4_id is null asc')
            ->orderByRaw('organization5_id is null asc')
            ->orderBy("organization2_order_no", "asc")
            ->orderBy("organization3_order_no", "asc")
            ->orderBy("organization4_order_no", "asc")
            ->orderBy("organization5_order_no", "asc")
            ->get()
            ->toArray();


        // 店舗情報を取得する
        $brand_ids = $brand_list->pluck('id')->toArray();
        $all_shops = Shop::query()
            ->select(
                'shops.id as id',
                'shops.shop_code',
                'shops.display_name',
                'shops.organization5_id',
                'shops.organization4_id',
                'shops.organization3_id',
                'shops.organization2_id'
            )
            ->leftJoin('organization5 as org5', 'shops.organization5_id', '=', 'org5.id')
            ->leftJoin('organization4 as org4', 'shops.organization4_id', '=', 'org4.id')
            ->leftJoin('organization3 as org3', 'shops.organization3_id', '=', 'org3.id')
            ->leftJoin('organization2 as org2', 'shops.organization2_id', '=', 'org2.id')
            ->where('shops.organization1_id', $organization1_id)
            ->whereIn('shops.brand_id', $brand_ids)
            ->orderBy('shops.shop_code', 'asc')
            ->get()
            ->toArray();

        // 組織別にデータを整理する
        $organization_list = array_map(function ($org) use ($all_shops) {
            $org['organization5_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                return $shop['organization5_id'] == $org['organization5_id'];
            });
            $org['organization4_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                return $shop['organization4_id'] == $org['organization4_id'] && is_null($shop['organization5_id']);
            });
            $org['organization3_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                return $shop['organization3_id'] == $org['organization3_id'] && is_null($shop['organization4_id']) && is_null($shop['organization5_id']);
            });
            $org['organization2_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                return $shop['organization2_id'] == $org['organization2_id'] && is_null($shop['organization3_id']) && is_null($shop['organization4_id']) && is_null($shop['organization5_id']);
            });
            return $org;
        }, $organization_list);

        // shop_code でソート済みの $all_shops をそのまま利用
        $all_shop_list = array_map(function ($shop) {
            return [
                'shop_id' => $shop['id'],
                'shop_code' => $shop['shop_code'],
                'display_name' => $shop['display_name'],
            ];
        }, $all_shops);

        return response()->json([
            'category_list' => $category_list,
            'target_roll_list' => $target_roll_list,
            'brand_list' => $brand_list,
            'organization_list' => $organization_list,
            'all_shop_list' => $all_shop_list,
        ]);
    }

    public function store(PublishStoreRequest $request, Organization1 $organization1)
    {
        ini_set('memory_limit', '1024M'); // メモリ制限を一時的に増加

        $validated = $request->validated();

        // ファイルを移動したかフラグ
        $message_changed_flg = false;

        // メッセージの内容を取得し、手順を登録するために加工する
        $message_contents = $this->messageContentsParam($request);

        // 結合処理
        $join_files = [];
        foreach ($message_contents as $content) {
            if ($content["join_flg"] === "join") {
                $join_files[] = [
                    "content_name" => $content["content_name"],
                    "content_url" => $content["content_url"],
                ];
            }
        }

        $join_file_list = !empty($join_files) ? $this->pdfFileJoin($join_files) : [];

        $admin = session('admin');

        $msg_params['title'] = $request->title;
        $msg_params['category_id'] = $request->category_id;
        $msg_params['emergency_flg'] = ($request->emergency_flg == 'on' ? true : false);
        $msg_params['start_datetime'] = $this->parseDateTime($request->start_datetime);
        $msg_params['end_datetime'] = $this->parseDateTime($request->end_datetime);

        if ($message_contents) {
            // 結合処理したか判定
            if (!empty($join_file_list)) {
                $msg_params['content_name'] = $join_file_list[0]['content_name'];
                $msg_params['content_url'] = $join_file_list[0]['content_url'];
            } else {
                $msg_params['content_name'] = $request->file_name[0] ? $message_contents[0]['content_name'] : null;
                $msg_params['content_url'] = $request->file_path[0] ? $message_contents[0]['content_url'] : null;
            }
        }

        $msg_params['thumbnails_url'] = !empty($msg_params['content_url']) ? ImageConverter::convert2image($msg_params['content_url']) : null;
        $msg_params['create_admin_id'] = $admin->id;
        $msg_params['organization1_id'] = $organization1->id;
        $number = Message::where('organization1_id', $organization1->id)->max('number');
        $msg_params['number'] = is_null($number) ? 1 : $number + 1;
        $msg_params['editing_flg'] = isset($request->save) ? true : false;
        $msg_params['is_broadcast_notification'] = isset($request->wowtalk_notification) && $request->wowtalk_notification == 'on' ? 1 : 0;
        $is_broadcast_notification = $msg_params['is_broadcast_notification'];

        try {
            DB::beginTransaction();
            $message = Message::create($msg_params);
            $message->updated_at = null;
            $message->save();
            $message->roll()->attach($request->target_roll);

            foreach (['org5', 'org4', 'org3', 'org2'] as $level) {
                if (isset($request->organization[$level][0])) {
                    // 事前にIDの配列を取得
                    $ids = explode(',', $request->organization[$level][0]);

                    $bulkData = [];
                    foreach ($ids as $id) {
                        $orgData = [
                            'message_id' => $message->id,
                            'organization1_id' => $organization1->id,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];

                        // レベルごとにフィールドを設定
                        switch ($level) {
                            case 'org5':
                                $orgData['organization5_id'] = $id;
                                break;
                            case 'org4':
                                $orgData['organization4_id'] = $id;
                                break;
                            case 'org3':
                                $orgData['organization3_id'] = $id;
                                break;
                            case 'org2':
                                $orgData['organization2_id'] = $id;
                                break;
                        }

                        // まとめてデータを追加
                        $bulkData[] = $orgData;
                    }

                    // バルクインサート
                    DB::table('message_organization')->insert($bulkData);
                }
            }

            // チャンクサイズを設定
            $chunkSize = 200;

            // message_shopにshop_idとmessage_idをバルクインサート
            if (isset($request->organization_shops)) {
                $organization_shops = explode(',', $request->organization_shops);

                // ショップデータの事前取得とグループ化
                $shopsData = Shop::whereIn('id', $organization_shops)
                    ->whereIn('brand_id', $request->brand)
                    ->get(['id', 'brand_id'])
                    ->groupBy('id');

                $insertData = [];
                // 事前に選択フラグを決定
                $selectedFlg = (isset($request->select_organization['all']) && $request->select_organization['all'] === 'selected') ? 'all' : 'store';

                foreach ($organization_shops as $_shop_id) {
                    if (isset($shopsData[$_shop_id])) {
                        foreach ($shopsData[$_shop_id] as $shop) {
                            $insertData[] = [
                                'message_id' => $message->id,
                                'shop_id' => $shop->id,
                                'brand_id' => $shop->brand_id,
                                'selected_flg' => $selectedFlg,
                                'created_at' => now(),
                                'updated_at' => now()
                            ];

                            // チャンクサイズに達したらバルクインサート
                            if (count($insertData) >= $chunkSize) {
                                MessageShop::insert($insertData);
                                $insertData = [];
                            }
                        }
                    }
                }

                // 最後に残ったデータをインサート
                if (!empty($insertData)) {
                    MessageShop::insert($insertData);
                }
            }

            $message->brand()->attach($request->brand);

            if (!isset($request->save)) {
                $message->user()->attach($this->getTargetUsersByShopId($request));
            }

            $message->content()->createMany($message_contents);

            if (isset($request->tag_name)) {
                $tag_ids = [];
                foreach ($request->tag_name as $tag_name) {
                    $tag = MessageTagMaster::firstOrCreate(['name' => $tag_name]);
                    $tag_ids[] = $tag->id;
                }
                $message->tag()->attach($tag_ids);
            }

            DB::commit();

            // 閲覧率の更新処理
            $this->updateViewRates(new Request(['message_id' => $message->id, 'brand' => $organization1->id]));

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            if ($message_changed_flg) {
                foreach ($request->file_path as $file_path) {
                    $this->rollbackRegisterFile($file_path);
                }
            }
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'データベースエラーです');
        }

        // デフォルトの設定に戻す
        ini_restore('memory_limit');

        // WowTalk通知のジョブをキューに追加
        if ($is_broadcast_notification == 1) {
            SendWowtalkNotificationJob::dispatch($message->id, 'message', 'message_store');
        }

        return redirect()->route('admin.message.publish.index', ['brand' => session('brand_id')]);
    }

    // 一覧画面の登録
    public function messageStoreData(PublishStoreRequest $request)
    {
        $organization1 = Organization1::find($request->input('org1Id'));

        // 各リクエストデータを取得し、'null'文字列をnullに変換
        $title = $request->input('title') === 'null' ? null : $request->input('title');
        $category_id = $request->input('category_id') === 'null' ? null : $request->input('category_id');
        $emergency_flg = $request->input('emergency_flg') === 'null' ? null : $request->input('emergency_flg');
        $wowtalk_notification = $request->input('wowtalk_notification') === 'null' ? null : $request->input('wowtalk_notification');

        $start_datetime_input = $request->input('start_datetime');
        $end_datetime_input = $request->input('end_datetime');
        $start_datetime = $start_datetime_input === null ? null : Carbon::createFromFormat('Y/m/d H:i', $this->cleanDateString($start_datetime_input))->format('Y-m-d H:i:s');
        $end_datetime = $end_datetime_input === null ? null : Carbon::createFromFormat('Y/m/d H:i', $this->cleanDateString($end_datetime_input))->format('Y-m-d H:i:s');

        $tag_name = array_map(function($item) {
            return $item === 'null' ? null : $item;
        }, $request->input('tag_name', []));

        $content_id = array_map(function($item) {
            return $item === 'null' ? null : $item;
        }, $request->input('content_id', []));

        $file_name = array_map(function($item) {
            return $item === 'null' ? null : $item;
        }, $request->input('file_name', []));

        $file_path = array_map(function($item) {
            return $item === 'null' ? null : $item;
        }, $request->input('file_path', []));

        $join_flg = array_map(function($item) {
            return $item === 'null' ? null : $item;
        }, $request->input('join_flg', []));

        $target_roll = array_map(function($item) {
            return $item === 'null' ? null : $item;
        }, $request->input('target_roll', []));

        $brand = array_map(function($item) {
            return $item === 'null' ? null : $item;
        }, $request->input('brand', []));

        $organization = array_map(function($org) {
            return $org === 'null' ? null : $org;
        }, $request->input('organization', []));

        $organization_shops = $request->input('organization_shops') === 'null' ? null : $request->input('organization_shops');

        $select_organization = array_map(function($item) {
            return $item === 'null' ? null : $item;
        }, $request->input('select_organization', []));

        return $this->store($request->merge([
            'title' => $title,
            'category_id' => $category_id,
            'emergency_flg' => $emergency_flg,
            'wowtalk_notification' => $wowtalk_notification,
            'tag_name' => $tag_name,
            'start_datetime' => $start_datetime,
            'end_datetime' => $end_datetime,
            'content_id' => $content_id,
            'file_name' => $file_name,
            'file_path' => $file_path,
            'join_flg' => $join_flg,
            'target_roll' => $target_roll,
            'brand' => $brand,
            'organization' => $organization,
            'organization_shops' => $organization_shops,
            'select_organization' => $select_organization,
        ]), $organization1);
    }

    public function edit($message_id)
    {
        ini_set('memory_limit', '1024M'); // メモリ制限を一時的に増加

        $message = Message::find($message_id);
        if (empty($message)) return redirect()->route('admin.message.publish.index', ['brand' => session('brand_id')]);

        // 複数ファイルの場合の処理
        $message_contents = MessageContent::where('message_id', $message_id)->get();

        $admin = session('admin');

        $category_list = MessageCategory::all();

        $target_roll_list = Roll::get();
        // 業態一覧を取得する
        $brand_list = Brand::where('organization1_id', $message->organization1_id)->get();

        $organization_list = [];
        $organization_list = Shop::query()
            ->leftjoin('organization2', 'organization2_id', '=', 'organization2.id')
            ->leftjoin('organization3', 'organization3_id', '=', 'organization3.id')
            ->leftjoin('organization4', 'organization4_id', '=', 'organization4.id')
            ->leftjoin('organization5', 'organization5_id', '=', 'organization5.id')
            ->distinct('organization4_id')
            ->distinct('organization5_id')
            ->select(
                'organization2_id',
                'organization2.name as organization2_name',
                'organization2.order_no as organization2_order_no',
                'organization3_id',
                'organization3.name as organization3_name',
                'organization3.order_no as organization3_order_no',
                'organization4_id',
                'organization4.name as organization4_name',
                'organization4.order_no as organization4_order_no',
                'organization5_id',
                'organization5.name as organization5_name',
                'organization5.order_no as organization5_order_no',
            )
            ->where('organization1_id', $message->organization1_id)
            ->orderByRaw('organization2_id is null asc')
            ->orderByRaw('organization3_id is null asc')
            ->orderByRaw('organization4_id is null asc')
            ->orderByRaw('organization5_id is null asc')
            ->orderBy("organization2_order_no", "asc")
            ->orderBy("organization3_order_no", "asc")
            ->orderBy("organization4_order_no", "asc")
            ->orderBy("organization5_order_no", "asc")
            ->get()
            ->toArray();


        // 店舗情報を取得する
        $brand_ids = $brand_list->pluck('id')->toArray();
        $all_shops = Shop::query()
            ->select(
                'shops.id as id',
                'shops.shop_code',
                'shops.display_name',
                'shops.organization5_id',
                'shops.organization4_id',
                'shops.organization3_id',
                'shops.organization2_id'
            )
            ->leftJoin('organization5 as org5', 'shops.organization5_id', '=', 'org5.id')
            ->leftJoin('organization4 as org4', 'shops.organization4_id', '=', 'org4.id')
            ->leftJoin('organization3 as org3', 'shops.organization3_id', '=', 'org3.id')
            ->leftJoin('organization2 as org2', 'shops.organization2_id', '=', 'org2.id')
            ->where('shops.organization1_id', $message->organization1_id)
            ->whereIn('shops.brand_id', $brand_ids)
            ->orderBy('shops.shop_code', 'asc')
            ->get()
            ->toArray();

        // 組織別にデータを整理する
        $organization_list = array_map(function ($org) use ($all_shops) {
            $org['organization5_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                return $shop['organization5_id'] == $org['organization5_id'];
            });
            $org['organization4_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                return $shop['organization4_id'] == $org['organization4_id'] && is_null($shop['organization5_id']);
            });
            $org['organization3_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                return $shop['organization3_id'] == $org['organization3_id'] && is_null($shop['organization4_id']) && is_null($shop['organization5_id']);
            });
            $org['organization2_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                return $shop['organization2_id'] == $org['organization2_id'] && is_null($shop['organization3_id']) && is_null($shop['organization4_id']) && is_null($shop['organization5_id']);
            });
            return $org;
        }, $organization_list);

        // shop_code でソート済みの $all_shops をそのまま利用
        $all_shop_list = array_map(function ($shop) {
            return [
                'shop_id' => $shop['id'],
                'shop_code' => $shop['shop_code'],
                'display_name' => $shop['display_name'],
            ];
        }, $all_shops);


        // MessageOrganizationテーブルから各組織IDを取得し、配列に格納
        $target_org = [];
        $target_org['org5'] = MessageOrganization::where('message_id', $message_id)->pluck('organization5_id')->toArray();
        $target_org['org4'] = MessageOrganization::where('message_id', $message_id)->pluck('organization4_id')->toArray();
        $target_org['org3'] = MessageOrganization::where('message_id', $message_id)->pluck('organization3_id')->toArray();
        $target_org['org2'] = MessageOrganization::where('message_id', $message_id)->pluck('organization2_id')->toArray();

        $message_target_roll = $message->roll()->pluck('rolls.id')->toArray();
        $target_brand = $message->brand()->pluck('brands.id')->toArray();

        $target_org['shops'] = [];
        $target_org['select'] = null;

        $selectedFlg = null;
        $chunkSize = 200; // チャンクサイズを設定
        $offset = 0;

        // MessageShopテーブルからメッセージに関連する店舗情報を取得
        while (true) {
            $shops = MessageShop::where('message_id', $message_id)
                ->whereIn('brand_id', $target_brand)
                ->offset($offset)
                ->limit($chunkSize)
                ->get(['shop_id', 'selected_flg']);

            // 取得したデータが空ならループを終了
            if ($shops->isEmpty()) {
                break;
            }

            // 取得した店舗データをtarget_org['shops']配列に格納し、selected_flgを設定
            foreach ($shops as $shop) {
                $target_org['shops'][] = $shop->shop_id;
                if (!$selectedFlg) {
                    $selectedFlg = $shop->selected_flg;
                }
            }
            $offset += $chunkSize;
        }

        // MessageShopテーブルにshop_idが存在しない場合はMessageUserテーブルを確認
        if (empty($target_org['shops'])) {
            MessageUser::where('message_id', $message_id)
                ->orderBy('message_id')
                ->chunk($chunkSize, function ($users) use (&$target_org) {
                    foreach ($users as $user) {
                        $target_org['shops'][] = $user->shop_id;
                    }
                });
            $target_org['select'] = 'oldStore';
        }
        // target_org['shops']の配列内の重複する店舗IDを削除
        $target_org['shops'] = array_unique($target_org['shops']);

        // selectedFlgが設定されている場合はtarget_org['select']にその値を設定
        if ($selectedFlg) {
            $target_org['select'] = $selectedFlg;
        }


        // 店舗コードでshopsをソート
        usort($all_shop_list, function ($a, $b) {
            return strcmp($a['shop_code'], $b['shop_code']);
        });

        // デフォルトの設定に戻す
        ini_restore('memory_limit');

        return view('admin.message.publish.edit', [
            'message' => $message,
            'message_contents' => $message_contents,
            'category_list' => $category_list,
            'target_roll_list' => $target_roll_list,
            'brand_list' => $brand_list,
            'organization_list' => $organization_list,
            'all_shop_list' => $all_shop_list,
            'message_target_roll' => $message_target_roll,
            'target_brand' => $target_brand,
            'target_org' => $target_org,
        ]);
    }

    // 一覧画面編集の業連ファイルと組織一覧を取得する
    public function messageEditData($message_id, $org1_id)
    {
        $message = Message::find($message_id);
        if (empty($message)) return redirect()->route('admin.message.publish.index', ['brand' => session('brand_id')]);

        // 複数ファイルの場合の処理
        $message_contents = MessageContent::where('message_id', $message_id)->get();

        $category_list = MessageCategory::all();

        $message_tag_ids = DB::table('message_tags')->where('message_id', $message_id)->pluck('tag_id')->toArray();
        $target_tag = MessageTagMaster::whereIn('id', $message_tag_ids)->get();

        $target_roll_list = Roll::get();

        // 業態一覧を取得する
        $brand_list = Brand::where('organization1_id', $org1_id)->get();

        $organization_list = [];
        $organization_list = Shop::query()
            ->leftjoin('organization2', 'organization2_id', '=', 'organization2.id')
            ->leftjoin('organization3', 'organization3_id', '=', 'organization3.id')
            ->leftjoin('organization4', 'organization4_id', '=', 'organization4.id')
            ->leftjoin('organization5', 'organization5_id', '=', 'organization5.id')
            ->distinct('organization4_id')
            ->distinct('organization5_id')
            ->select(
                'organization2_id',
                'organization2.name as organization2_name',
                'organization2.order_no as organization2_order_no',
                'organization3_id',
                'organization3.name as organization3_name',
                'organization3.order_no as organization3_order_no',
                'organization4_id',
                'organization4.name as organization4_name',
                'organization4.order_no as organization4_order_no',
                'organization5_id',
                'organization5.name as organization5_name',
                'organization5.order_no as organization5_order_no',
            )
            ->where('organization1_id', $org1_id)
            ->orderByRaw('organization2_id is null asc')
            ->orderByRaw('organization3_id is null asc')
            ->orderByRaw('organization4_id is null asc')
            ->orderByRaw('organization5_id is null asc')
            ->orderBy("organization2_order_no", "asc")
            ->orderBy("organization3_order_no", "asc")
            ->orderBy("organization4_order_no", "asc")
            ->orderBy("organization5_order_no", "asc")
            ->get()
            ->toArray();


        // 店舗情報を取得する
        $brand_ids = $brand_list->pluck('id')->toArray();
        $all_shops = Shop::query()
            ->select(
                'shops.id as id',
                'shops.shop_code',
                'shops.display_name',
                'shops.organization5_id',
                'shops.organization4_id',
                'shops.organization3_id',
                'shops.organization2_id'
            )
            ->leftJoin('organization5 as org5', 'shops.organization5_id', '=', 'org5.id')
            ->leftJoin('organization4 as org4', 'shops.organization4_id', '=', 'org4.id')
            ->leftJoin('organization3 as org3', 'shops.organization3_id', '=', 'org3.id')
            ->leftJoin('organization2 as org2', 'shops.organization2_id', '=', 'org2.id')
            ->where('shops.organization1_id', $org1_id)
            ->whereIn('shops.brand_id', $brand_ids)
            ->orderBy('shops.shop_code', 'asc')
            ->get()
            ->toArray();

        // 組織別にデータを整理する
        $organization_list = array_map(function ($org) use ($all_shops) {
            $org['organization5_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                return $shop['organization5_id'] == $org['organization5_id'];
            });
            $org['organization4_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                return $shop['organization4_id'] == $org['organization4_id'] && is_null($shop['organization5_id']);
            });
            $org['organization3_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                return $shop['organization3_id'] == $org['organization3_id'] && is_null($shop['organization4_id']) && is_null($shop['organization5_id']);
            });
            $org['organization2_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                return $shop['organization2_id'] == $org['organization2_id'] && is_null($shop['organization3_id']) && is_null($shop['organization4_id']) && is_null($shop['organization5_id']);
            });
            return $org;
        }, $organization_list);

        // shop_code でソート済みの $all_shops をそのまま利用
        $all_shop_list = array_map(function ($shop) {
            return [
                'shop_id' => $shop['id'],
                'shop_code' => $shop['shop_code'],
                'display_name' => $shop['display_name'],
            ];
        }, $all_shops);


        // MessageOrganizationテーブルから各組織IDを取得し、配列に格納
        $target_org = [];
        $target_org['org5'] = MessageOrganization::where('message_id', $message_id)->pluck('organization5_id')->toArray();
        $target_org['org4'] = MessageOrganization::where('message_id', $message_id)->pluck('organization4_id')->toArray();
        $target_org['org3'] = MessageOrganization::where('message_id', $message_id)->pluck('organization3_id')->toArray();
        $target_org['org2'] = MessageOrganization::where('message_id', $message_id)->pluck('organization2_id')->toArray();

        $message_target_roll = $message->roll()->pluck('rolls.id')->toArray();
        $target_brand = $message->brand()->pluck('brands.id')->toArray();

        $target_org['shops'] = [];
        $target_org['select'] = null;

        $selectedFlg = null;
        $chunkSize = 200; // チャンクサイズを設定
        $offset = 0;

        // MessageShopテーブルからメッセージに関連する店舗情報を取得
        while (true) {
            $shops = MessageShop::where('message_id', $message_id)
                ->whereIn('brand_id', $target_brand)
                ->offset($offset)
                ->limit($chunkSize)
                ->get(['shop_id', 'selected_flg']);

            // 取得したデータが空ならループを終了
            if ($shops->isEmpty()) {
                break;
            }

            // 取得した店舗データをtarget_org['shops']配列に格納し、selected_flgを設定
            foreach ($shops as $shop) {
                $target_org['shops'][] = $shop->shop_id;
                if (!$selectedFlg) {
                    $selectedFlg = $shop->selected_flg;
                }
            }
            $offset += $chunkSize;
        }

        // MessageShopテーブルにshop_idが存在しない場合はMessageUserテーブルを確認
        if (empty($target_org['shops'])) {
            MessageUser::where('message_id', $message_id)
                ->orderBy('message_id')
                ->chunk($chunkSize, function ($users) use (&$target_org) {
                    foreach ($users as $user) {
                        $target_org['shops'][] = $user->shop_id;
                    }
                });
            $target_org['select'] = 'oldStore';
        }
        // target_org['shops']の配列内の重複する店舗IDを削除
        $target_org['shops'] = array_unique($target_org['shops']);

        // selectedFlgが設定されている場合はtarget_org['select']にその値を設定
        if ($selectedFlg) {
            $target_org['select'] = $selectedFlg;
        }


        // 店舗コードでshopsをソート
        usort($all_shop_list, function ($a, $b) {
            return strcmp($a['shop_code'], $b['shop_code']);
        });

        return response()->json([
            'message' => $message,
            'message_contents' => $message_contents,
            'category_list' => $category_list,
            'target_tag' => $target_tag,
            'target_roll_list' => $target_roll_list,
            'brand_list' => $brand_list,
            'organization_list' => $organization_list,
            'all_shop_list' => $all_shop_list,
            'message_target_roll' => $message_target_roll,
            'target_brand' => $target_brand,
            'target_org' => $target_org,
        ]);
    }

    public function update(PublishUpdateRequest $request, $message_id)
    {
        ini_set('memory_limit', '1024M'); // メモリ制限を一時的に増加

        $validated = $request->validated();

        // ファイルを移動したかフラグ
        $message_changed_flg = false;
        $message_content_changed_flg = false;

        $admin = session('admin');
        $message = Message::find($message_id);
        $message_content = MessageContent::find($message_id);

        $join_path_list = MessageContent::where('message_id', $message->id)->pluck('content_url')->toArray();
        $join_flg_list = MessageContent::where('message_id', $message->id)->pluck('join_flg')->toArray();

        $msg_params['title'] = $request->title;
        $msg_params['category_id'] = $request->category_id;
        $msg_params['emergency_flg'] = ($request->emergency_flg == 'on' ? true : false);
        $msg_params['start_datetime'] = $this->parseDateTime($request->start_datetime);
        $msg_params['end_datetime'] = $this->parseDateTime($request->end_datetime);

        $msg_params['updated_admin_id'] = $admin->id;
        $msg_params['editing_flg'] = isset($request->save) ? true : false;
        $msg_params['is_broadcast_notification'] = isset($request->wowtalk_notification) && $request->wowtalk_notification == 'on' ? 1 : 0;
        $is_broadcast_notification = $msg_params['is_broadcast_notification'];

        // 手順を登録する
        $content_data = [];

        try {
            DB::beginTransaction();
            // 登録されているコンテンツが削除されていた場合、deleteフラグを立てる
            $content = $message->content()->whereNotIn('id', $this->getExistContentIds($request));
            $content->delete();

            //手順を登録する (編集)
            if (!empty($join_path_list)) {
                foreach ($request->file_name as $i => $file_name) {
                    $file_path = $request->file_path[$i] ?? null;
                    $content_id = $request->content_id[$i] ?? null;
                    $join_flg = $request->join_flg[$i] ?? null;

                    // 登録されている手順を変更する
                    if (!empty($file_path)) {
                        if ($content_id) {
                            $id = (int)$content_id;
                            $message_content = MessageContent::find($id);

                            // 変更部分だけ取り込む
                            if ($message_content) {
                                if ($this->isChangedJoinFlg($join_path_list, $request->file_path ?? null) || $this->isChangedJoinFlg($join_flg_list, array_filter($request->join_flg ?? []))) {
                                    $message_content->content_name = $file_name;

                                    // ファイルが存在するか確認
                                    $shouldRegisterFile = file_exists(storage_path('app/' . $file_path));

                                    if ($this->isChangedFile($join_path_list[$i], $file_path) && $shouldRegisterFile) {
                                        $message_content->content_url = $this->registerFile($file_path);
                                    } else {
                                        $message_content->content_url = $file_path;
                                    }

                                    $message_content->thumbnails_url = ImageConverter::convert2image($message_content->content_url);
                                    $message_content->join_flg = $join_flg;
                                    $message_content_changed_flg = true;

                                    $message_content->save();
                                }
                            } else {
                                // 手順の新規登録
                                $content_data[$i]['content_name'] = $file_name;
                                $content_data[$i]['content_url'] = $this->registerFile($file_path);
                                $content_data[$i]['thumbnails_url'] = ImageConverter::convert2image($content_data[$i]['content_url']);
                                $content_data[$i]['join_flg'] = $join_flg;
                            }
                        } else {
                            // 手順の新規登録
                            $content_data[$i]['content_name'] = $file_name;
                            $content_data[$i]['content_url'] = $this->registerFile($file_path);
                            $content_data[$i]['thumbnails_url'] = ImageConverter::convert2image($content_data[$i]['content_url']);
                            $content_data[$i]['join_flg'] = $join_flg;
                        }
                    }
                }
            } else {
                // メッセージに関連する既存のコンテンツパスを取得
                $message_path_list = Message::where('id', $message_id)->pluck('content_url')->toArray();
                foreach ($request->file_name as $i => $file_name) {
                    $file_path = $request->file_path[$i] ?? null;
                    $join_flg = $request->join_flg[$i] ?? null;
                    if ($file_path) {
                        $message_content = new MessageContent();
                        $message_content->message_id = $message_id;
                        $message_content->content_name = $file_name;

                        $existing_file_path = $message_path_list[$i] ?? null;
                        if ($this->isChangedFile($existing_file_path, $file_path)) {
                            $message_content->content_url = $this->registerFile($file_path);
                        } else {
                            $message_content->content_url = $file_path;
                        }

                        $message_content->thumbnails_url = ImageConverter::convert2image($message_content->content_url);
                        $message_content->join_flg = $join_flg;
                        $message_content->save();
                    }
                }
            }

            // メッセージに関連する全てのコンテンツを取得
            $message_contents = MessageContent::where('message_id', $message->id)->get()->toArray();
            if (!empty($message_contents)) {
                $message_contents = array_merge($message_contents, $content_data);

                // 結合フラグが変更されているかチェック
                if ($this->isChangedJoinFlg($join_path_list, $request->file_path ?? null) || $this->isChangedJoinFlg($join_flg_list, array_filter($request->join_flg ?? []))) {
                    // 結合処理
                    $join_files = [];
                    foreach ($message_contents as $content) {
                        if ($content["join_flg"] === "join") {
                            $join_files[] = [
                                "content_name" => $content["content_name"],
                                "content_url" => $content["content_url"],
                            ];
                        }
                    }
                    if (!empty($join_files)) {
                        $join_file_list = $this->pdfFileJoin($join_files);
                    } else {
                        $join_file_list = [];
                    }

                    // 結合処理が行われたかどうかを判定
                    if (!empty($join_file_list)) {
                        $msg_params['content_name'] = $join_file_list[0]['content_name'];
                        $msg_params['content_url'] = $join_file_list[0]['content_url'];
                    } else {
                        $msg_params['content_name'] = $request->file_name[0] ? $message_contents[0]['content_name'] : null;
                        $msg_params['content_url'] = $request->file_path[0] ? $message_contents[0]['content_url'] : null;
                    }
                    $msg_params['thumbnails_url'] = $request->file_path ? ImageConverter::convert2image($msg_params['content_url']) : null;

                    $message_changed_flg = true;
                }
            }

            $message->update($msg_params);
            $message->roll()->sync($request->target_roll);

            // メッセージに関連する組織データを削除
            MessageOrganization::where('message_id', $message_id)->delete();

            foreach (['org5', 'org4', 'org3', 'org2'] as $level) {
                if (isset($request->organization[$level][0])) {
                    // 事前にIDの配列を取得
                    $ids = explode(',', $request->organization[$level][0]);

                    $bulkData = [];
                    foreach ($ids as $id) {
                        $orgData = [
                            'message_id' => $message->id,
                            'organization1_id' => $admin->organization1_id,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];

                        // レベルごとにフィールドを設定
                        switch ($level) {
                            case 'org5':
                                $orgData['organization5_id'] = $id;
                                break;
                            case 'org4':
                                $orgData['organization4_id'] = $id;
                                break;
                            case 'org3':
                                $orgData['organization3_id'] = $id;
                                break;
                            case 'org2':
                                $orgData['organization2_id'] = $id;
                                break;
                        }

                        // まとめてデータを追加
                        $bulkData[] = $orgData;
                    }

                    // バルクインサート
                    DB::table('message_organization')->insert($bulkData);
                }
            }

            // メッセージに関連するショップデータを削除
            MessageShop::where('message_id', $message_id)->delete();

            // チャンクサイズを設定
            $chunkSize = 200;

            // message_shopにshop_idとmessage_idをバルクインサート
            if (isset($request->organization_shops)) {
                $organization_shops = explode(',', $request->organization_shops);

                // ショップデータの事前取得とグループ化
                $shopsData = Shop::whereIn('id', $organization_shops)
                    ->whereIn('brand_id', $request->brand)
                    ->get(['id', 'brand_id'])
                    ->groupBy('id');

                $insertData = [];
                // 事前に選択フラグを決定
                $selectedFlg = (isset($request->select_organization['all']) && $request->select_organization['all'] === 'selected') ? 'all' : 'store';

                foreach ($organization_shops as $_shop_id) {
                    if (isset($shopsData[$_shop_id])) {
                        foreach ($shopsData[$_shop_id] as $shop) {
                            $insertData[] = [
                                'message_id' => $message->id,
                                'shop_id' => $shop->id,
                                'brand_id' => $shop->brand_id,
                                'selected_flg' => $selectedFlg,
                                'created_at' => now(),
                                'updated_at' => now()
                            ];

                            // チャンクサイズに達したらバルクインサート
                            if (count($insertData) >= $chunkSize) {
                                MessageShop::insert($insertData);
                                $insertData = [];
                            }
                        }
                    }
                }

                // 最後に残ったデータをインサート
                if (!empty($insertData)) {
                    MessageShop::insert($insertData);
                }
            }

            $message->brand()->sync($request->brand);

            // 既存ユーザーとターゲットユーザーの比較
            $targetUsers = !isset($request->save) ? $this->getTargetUsersByShopId($request) : [];
            $currentUsers = $message->user()->pluck('user_id')->toArray();

            // チャンクサイズを設定
            $chunkSize = 200;

            // 削除処理
            $usersToDetach = array_diff($currentUsers, array_keys($targetUsers));
            if (!empty($usersToDetach)) {
                foreach (array_chunk($usersToDetach, $chunkSize) as $chunk) {
                    // チャンクごとにユーザーをデタッチ
                    $message->user()->detach($chunk);
                }
            }

            // 追加または更新処理
            $usersToAttach = array_diff_key($targetUsers, array_flip($currentUsers));
            if (!empty($usersToAttach)) {
                // チャンクに分割して処理
                foreach (array_chunk($usersToAttach, $chunkSize, true) as $chunk) {
                    $attachData = [];
                    foreach ($chunk as $userId => $shopData) {
                        $attachData[$userId] = ['shop_id' => $shopData['shop_id']];
                    }
                    // ユーザーを関連付け
                    $message->user()->attach($attachData);
                }
            }

            $message->content()->createMany($content_data);

            $tag_ids = [];
            foreach ($request->input('tag_name', []) as $tag_name) {
                $tag = MessageTagMaster::firstOrCreate(['name' => $tag_name]);
                $tag_ids[] = $tag->id;
            }
            $message->tag()->sync($tag_ids);
            DB::commit();

            // 閲覧率の更新処理
            $this->updateViewRates(new Request(['message_id' => $message->id, 'brand' => $message->organization1_id]));

        } catch (\Throwable $th) {
            DB::rollBack();
            if ($message_changed_flg) {
                foreach ($request->file_path as $file_path) {
                    $this->rollbackRegisterFile($file_path);
                }
            }
            if ($message_content_changed_flg) $this->rollbackMessageContentFile($request);
            Log::error($th->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'データベースエラーです');
        }

        // デフォルトの設定に戻す
        ini_restore('memory_limit');

        // WowTalk通知のジョブをキューに追加
        if ($is_broadcast_notification == 1) {
            SendWowtalkNotificationJob::dispatch($message->id, 'message', 'message_update');
        }

        return redirect()->route('admin.message.publish.index', ['brand' => session('brand_id')]);
    }

    // 一覧画面の編集
    public function messageUpdateData(PublishUpdateRequest $request)
    {
        $message_id = $request->input('message_id');

        // 各リクエストデータを取得し、'null'文字列をnullに変換
        $title = $request->input('title') === 'null' ? null : $request->input('title');
        $category_id = $request->input('category_id') === 'null' ? null : $request->input('category_id');
        $emergency_flg = $request->input('emergency_flg') === 'null' ? null : $request->input('emergency_flg');
        $wowtalk_notification = $request->input('wowtalk_notification') === 'null' ? null : $request->input('wowtalk_notification');

        $start_datetime_input = $request->input('start_datetime');
        $end_datetime_input = $request->input('end_datetime');

        $start_datetime = $start_datetime_input === null ? null : Carbon::createFromFormat('Y/m/d H:i', $this->cleanDateString($start_datetime_input))->format('Y-m-d H:i:s');
        $end_datetime = $end_datetime_input === null ? null : Carbon::createFromFormat('Y/m/d H:i', $this->cleanDateString($end_datetime_input))->format('Y-m-d H:i:s');

        $tag_name = array_map(function($item) {
            return $item === 'null' ? null : $item;
        }, $request->input('tag_name', []));

        $content_id = array_map(function($item) {
            return $item === 'null' ? null : $item;
        }, $request->input('content_id', []));

        $file_name = array_map(function($item) {
            return $item === 'null' ? null : $item;
        }, $request->input('file_name', []));

        $file_path = array_map(function($item) {
            return $item === 'null' ? null : $item;
        }, $request->input('file_path', []));

        $join_flg = array_map(function($item) {
            return $item === 'null' ? null : $item;
        }, $request->input('join_flg', []));

        $target_roll = array_map(function($item) {
            return $item === 'null' ? null : $item;
        }, $request->input('target_roll', []));

        $brand = array_map(function($item) {
            return $item === 'null' ? null : $item;
        }, $request->input('brand', []));

        $organization = array_map(function($org) {
            return $org === 'null' ? null : $org;
        }, $request->input('organization', []));

        $organization_shops = $request->input('organization_shops') === 'null' ? null : $request->input('organization_shops');

        $select_organization = array_map(function($item) {
            return $item === 'null' ? null : $item;
        }, $request->input('select_organization', []));

        return $this->update($request->merge([
            'title' => $title,
            'category_id' => $category_id,
            'emergency_flg' => $emergency_flg,
            'wowtalk_notification' => $wowtalk_notification,
            'tag_name' => $tag_name,
            'start_datetime' => $start_datetime,
            'end_datetime' => $end_datetime,
            'content_id' => $content_id,
            'file_name' => $file_name,
            'file_path' => $file_path,
            'join_flg' => $join_flg,
            'target_roll' => $target_roll,
            'brand' => $brand,
            'organization' => $organization,
            'organization_shops' => $organization_shops,
            'select_organization' => $select_organization,
        ]), $message_id);
    }

    // 一覧画面の一括登録
    public function messageAllSaveData(Request $request)
    {
        $messagesData = $request->input('messagesData', []);
        $errors = [];

        foreach ($messagesData as $index => $messageData) {
            $operation = $messageData['operation'] ?? null;

            try {
                if ($operation == 'new') {
                    $request = Request::create('', 'POST', $messageData);
                    $storeRequest = PublishStoreRequest::createFromBase($request);
                    $storeRequest->setContainer(app());
                    $storeRequest->setRedirector(app('redirect'));
                    $storeRequest->validateResolved();
                    $this->messageStoreData($storeRequest);
                } else {
                    $request = Request::create('', 'POST', $messageData);
                    $updateRequest = PublishUpdateRequest::createFromBase($request);
                    $updateRequest->setContainer(app());
                    $updateRequest->setRedirector(app('redirect'));
                    $updateRequest->validateResolved();
                    $this->messageUpdateData($updateRequest);
                }
            } catch (\Illuminate\Validation\ValidationException $e) {
                $errors[$index] = [
                    'type' => 'validation',
                    'messages' => $e->errors(),
                    'data' => $messageData
                ];
            } catch (\Exception $e) {
                $errors[$index] = [
                    'type' => 'general',
                    'messages' => [$e->getMessage()],
                    'data' => $messageData
                ];
            }
        }

        if (!empty($errors)) {
            return response()->json(['status' => 'error', 'errors' => $errors], 422);
        }

        return response()->json(['status' => 'success']);
    }

    public function stop(Request $request)
    {
        $data = $request->json()->all();
        $message_id = $data['message_id'];
        $message = Message::find($message_id)->first();
        $status = $message->status;
        //掲載終了だと、エラーを返す
        if ($status == PublishStatus::Published) return response()->json(['message' => 'すでに掲載終了しています']);
        $admin = session('admin');
        $now = Carbon::now();
        Message::whereIn('id', $message_id)->update([
            'end_datetime' => $now,
            'updated_admin_id' => $admin->id,
            'editing_flg' => false
        ]);

        return response()->json(['message' => '停止しました']);
    }

    // 詳細画面のエクスポート
    public function export(Request $request, $message_id)
    {
        $now = new Carbon('now');
        $now->format('Y_m_d-H_i_s');
        return Excel::download(
            new MessageViewRateExport($message_id, $request),
            $now->format('Y_m_d-H_i') . '-業務連絡エクスポート.csv'
        );
    }

    // 業務連絡一覧のエクスポート
    public function exportList(Request $request)
    {
        $admin = session('admin');
        $organization1_id = $request->input('brand', $admin->firstOrganization1()->id);
        $organization1 = Organization1::find($organization1_id);

        $file_name = '業務連絡_' . $organization1->name . now()->format('_Y_m_d') . '.csv';

        // BBの場合、SKの場合
        if ($organization1->id == 2 || $organization1->id == 8) {
            return Excel::download(
                new MessageListBBExport($request),
                $file_name
            );
        }

        return Excel::download(
            new MessageListExport($request),
            $file_name
        );
    }

    // 業務連絡店舗CSV エクスポート（新規登録/編集）
    public function csvStoreExport(Request $request)
    {
        ini_set('memory_limit', '1024M'); // メモリ制限を一時的に増加

        // 新規登録か編集かを判定
        $isEdit = $request->has('message_id');
        $organization1_id = null;

        if ($isEdit) {
            // 編集時の処理
            $message_id = (int) $request->input('message_id');
            $message = Message::find($message_id);
            if (!$message) {
                return response()->json(['error' => 'Message not found'], 404);
            }
            $organization1_id = $message->organization1_id;
        } else {
            // 新規登録時の処理
            $organization1_id = (int) $request->input('organization1_id');
        }

        $organization1 = Organization1::find($organization1_id);
        if (!$organization1) {
            return response()->json(['error' => 'Organization not found'], 404);
        }

        $file_name = $organization1->name . now()->format('_Y_m_d') . '.csv';

        // デフォルトの設定に戻す
        ini_restore('memory_limit');

        return Excel::download(
            new MessageStoreCsvExport($organization1_id),
            $file_name
        );
    }

    public function fileUpload(FileUpdateApiRequest $request)
    {
        $validated = $request->validated();

        $fileNames = [];
        $filePaths = [];

        // 送信されたすべてのファイルを処理する
        foreach ($request->file() as $key => $file) {
            // 各ファイルの処理
            $file_path = Storage::putFile('/tmp', $file);
            $file_name = $file->getClientOriginalName();

            // ファイル名とパスを配列に追加
            $fileNames[] = $file_name;
            $filePaths[] = $file_path;
        }

        return response()->json([
            'content_names' => $fileNames,
            'content_urls' => $filePaths
        ]);
    }

    public function csvUpload(Request $request)
    {
        $log_file_name = $request->input('log_file_name');
        $file_path = public_path() . '/log/' . $log_file_name;
        file_put_contents($file_path, "0");

        $admin = session('admin');
        $csv = $request->file;
        $organization1 = (int) $request->input('organization1');

        try {
            $csv_content = file_get_contents($csv);
            $encoding = mb_detect_encoding($csv_content);
            if ($encoding == "UTF-8") {
                $shift_jis_content = mb_convert_encoding($csv_content, 'CP932', 'UTF-8');
                file_put_contents($csv, $shift_jis_content);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'ファイルをアップロードしてください'
            ], 500);
        }

        $organization = $this->getOrganizationForm($organization1);

        // BBの場合、SKの場合
        if ($organization1 == 2 || $organization1 == 8) {
            $shop_list = $this->getShopForm($organization1);
        }

        $csv_path = Storage::putFile('csv', $csv);
        Log::info("業連CSVインポート", [
            'csv_path' => $csv_path,
            'admin' => $admin
        ]);



        try {
            // BBの場合、SKの場合
            if ($organization1 == 2 || $organization1 == 8) {
                $collection = Excel::toCollection(new MessageBBCsvImport($organization1, $organization, $shop_list), $csv, \Maatwebsite\Excel\Excel::CSV);
                $count = $collection[0]->count();
                if ($count >= 100) {
                    File::delete($file_path);
                    return response()->json([
                        'message' => "100行以内にしてください"
                    ], 500);
                }

                Excel::import(new MessageBBCsvImport($organization1, $organization, $shop_list), $csv, \Maatwebsite\Excel\Excel::CSV);

                $last_message = Message::where('organization1_id', $organization1)
                    ->orderBy('number', 'desc')
                    ->value('number');
                $number = $last_message ? $last_message + 1 : 1;

                $array = [];
                foreach (
                    $collection[0] as $key => [
                        $no,
                        $emergency_flg,
                        $category,
                        $title,
                        $check_file,
                        $tag1,
                        $tag2,
                        $tag3,
                        $tag4,
                        $tag5,
                        $start_datetime,
                        $end_datetime,
                        $status,
                        $wowtalk_notification,
                        $brand,
                        $shop
                    ]
                ) {
                    if (is_null($no)) {
                        // noがない場合は新規作成
                        $message = Message::where('organization1_id', $organization1)
                            ->orderBy('number', 'desc')
                            ->first(['id', 'number']);

                        $brand_param = ($brand == "全て") ? array_column($organization, 'brand_id') : Brand::whereIn('name',  $this->strToArray($brand))->pluck('id')->toArray();
                        $shop_param = ($shop == "全店")
                            ? ['all_shops_flag' => true, 'shop_ids' => array_column($shop_list, 'id')]
                            : ['all_shops_flag' => false, 'shop_ids' => Shop::whereIn('display_name', $this->strToArray($shop))->pluck('id')->toArray()];

                        $target_roll = $message->roll()->pluck('id')->toArray();

                        array_push($array, [
                            'number'                    => $number,
                            'emergency_flg'             => isset($emergency_flg),
                            'category'                  => $category ? MessageCategory::where('name', $category)->pluck('id')->first() : NULL,
                            'title'                     => $title,
                            'check_file'                => isset($check_file) && $check_file !== '',
                            'tag'                       => $this->tagImportParam([$tag1, $tag2, $tag3, $tag4, $tag5]),
                            'start_datetime'            => $start_datetime,
                            'end_datetime'              => $end_datetime,
                            'is_broadcast_notification' => isset($wowtalk_notification) && $wowtalk_notification !== '' ? 1 : 0,
                            'brand'                     => $brand_param,
                            'shops'                     => $shop_param,
                            'roll'                      => $target_roll,
                            'is_new'                    => true
                        ]);

                        $number++;

                    } else {
                        // noがある場合は更新
                        $message = Message::where('number', $no)
                            ->where('organization1_id', $organization1)
                            ->firstOrFail();

                        $brand_param = ($brand == "全て") ? array_column($organization, 'brand_id') : Brand::whereIn('name',  $this->strToArray($brand))->pluck('id')->toArray();
                        $shop_param = ($shop == "全店")
                            ? ['all_shops_flag' => true, 'shop_ids' => array_column($shop_list, 'id')]
                            : ['all_shops_flag' => false, 'shop_ids' => Shop::whereIn('display_name', $this->strToArray($shop))->pluck('id')->toArray()];

                        $target_roll = $message->roll()->pluck('id')->toArray();

                        array_push($array, [
                            'id'                        => $message->id,
                            'number'                    => $no,
                            'emergency_flg'             => isset($emergency_flg),
                            'category'                  => $category ? MessageCategory::where('name', $category)->pluck('id')->first() : NULL,
                            'title'                     => $title,
                            'check_file'                => isset($check_file) && $check_file !== '',
                            'tag'                       => $this->tagImportParam([$tag1, $tag2, $tag3, $tag4, $tag5]),
                            'start_datetime'            => $start_datetime,
                            'end_datetime'              => $end_datetime,
                            'is_broadcast_notification' => isset($wowtalk_notification) && $wowtalk_notification !== '' ? 1 : 0,
                            'brand'                     => $brand_param,
                            'shops'                     => $shop_param,
                            'roll'                      => $target_roll,
                            'is_new'                    => false
                        ]);
                    }

                    file_put_contents($file_path, ceil((($key + 1) / $count) * 100));
                }

                return response()->json([
                    'json' => $array
                ], 200);
            } else {
                Excel::import(new MessageCsvImport($organization1, $organization), $csv, \Maatwebsite\Excel\Excel::CSV);

                $collection = Excel::toCollection(new MessageCsvImport($organization1, $organization), $csv, \Maatwebsite\Excel\Excel::CSV);
                $count = $collection[0]->count();
                if ($count >= 100) {
                    File::delete($file_path);
                    return response()->json([
                        'message' => "100行以内にしてください"
                    ], 500);
                }
                $array = [];
                foreach (
                    $collection[0] as $key => [
                        $no,
                        $emergency_flg,
                        $category,
                        $title,
                        $tag1,
                        $tag2,
                        $tag3,
                        $tag4,
                        $tag5,
                        $start_datetime,
                        $end_datetime,
                        $status,
                        $brand
                        // $organization5,
                        // $organization4,
                        // $organization3
                    ]
                ) {
                    $message = Message::firstOrCreate(
                        ['number' => $no, 'organization1_id' => $organization1],
                        [
                            'title'          => $title,
                            'category_id'    => $category ? MessageCategory::where('name', $category)->pluck('id')->first() : null,
                            'emergency_flg'  => isset($emergency_flg),
                            'start_datetime' => $start_datetime,
                            'end_datetime'   => $end_datetime,
                        ]
                    );

                    $brand_param = ($brand == "全て") ? array_column($organization, 'brand_id') : Brand::whereIn('name',  $this->strToArray($brand))->pluck('id')->toArray();
                    // $org3_param = ($organization3 == "全て") ? array_column($organization, 'organization3_id') : Organization3::whereIn('name', $this->strToArray($organization3))->pluck('id')->toArray();
                    // $org4_param = ($organization4 == "全て") ? array_column($organization, 'organization4_id') : Organization4::whereIn('name', $this->strToArray($organization4))->pluck('id')->toArray();
                    // $org5_param = ($organization5 == "全て") ? array_column($organization, 'organization5_id') : Organization5::whereIn('name', $this->strToArray($organization5))->pluck('id')->toArray();

                    $target_roll = $message->roll()->pluck('id')->toArray();

                    array_push($array, [
                        'id'             => $message->id,
                        'number'         => $no,
                        'emergency_flg'  => isset($emergency_flg),
                        'category'       => $category ? MessageCategory::where('name', $category)->pluck('id')->first() : NULL,
                        'title'          => $title,
                        'tag'            => $this->tagImportParam([$tag1, $tag2, $tag3, $tag4, $tag5]),
                        'start_datetime' => $start_datetime,
                        'end_datetime'   => $end_datetime,
                        'brand'          => $brand_param,
                        // 'organization3' => $org3_param,
                        // 'organization4' => $org4_param,
                        // 'organization5' => $org5_param,
                        'roll'           => $target_roll
                    ]);

                    file_put_contents($file_path, ceil((($key + 1) / $count) * 100));
                }

                return response()->json([
                    'json' => $array
                ], 200);
            }
        } catch (ValidationException $e) {
            $failures = $e->failures();

            $errorMessage = [];
            foreach ($failures as $index => $failure) {
                $errorMessage[$index]["row"]       = $failure->row(); // row that went wrong
                $errorMessage[$index]["attribute"] = $failure->attribute(); // either heading key (if using heading row concern) or column index
                $errorMessage[$index]["errors"]    = $failure->errors(); // Actual error messages from Laravel validator
                $errorMessage[$index]["value"]     = $failure->values(); // The values of the row that has failed.
            }

            // 行でソート
            usort($errorMessage, function ($a, $b) {
                return $a['row'] <=> $b['row'];
            });

            File::delete($file_path);
            return response()->json([
                'message' => $errorMessage
            ], 422);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());

            File::delete($file_path);
            return response()->json([
                'message' => "エラーが発生しました"
            ], 500);
        }
    }

    public function progress(Request $request)
    {
        $file_name = $request->file_name;
        $file_path = public_path() . '/log/' . $file_name;
        if (!File::exists($file_path)) {
            return response()->json([
                'message' => "ログファイルが存在しません"
            ], 500);
        }

        $log = File::get($file_path);
        if ($log == 100) {
            File::delete($file_path);
        }
        return $log;
    }

    public function import(Request $request)
    {
        $admin = session('admin');
        $messages = $request->json();

        foreach ($messages as $ms) {
            $org1_id = Brand::where('id', $ms["brand"])->pluck('organization1_id')->first();
            break;
        }

        $log_id = DB::table('message_csv_logs')->insertGetId([
            'imported_datetime' => new Carbon('now'),
            'is_success' => false
        ]);

        try {
            DB::beginTransaction();

            // BBの場合、SKの場合
            if ($org1_id == 2 || $org1_id == 8) {
                foreach ($messages as $key => $ms) {
                    $organization1_id = Brand::where('id', $ms["brand"])->pluck('organization1_id')->first();

                    // ファイルを移動したかフラグ
                    $message_changed_flg = false;

                    // 新規作成
                    if ($ms["is_new"]) {
                        $message                = new Message();
                        $message->number        = $ms["number"];
                        $message->emergency_flg = $ms["emergency_flg"];
                        $message->category_id   = $ms["category"];
                        $message->title         = $ms["title"];

                        // check_fileがtrueの場合
                        if (isset($ms["check_file"]) && $ms["check_file"] === true) {
                            $message->content_name   = !empty($ms["file_name"]) ? $ms["file_name"] : null;
                            $message->content_url    = !empty($ms["file_path"]) ? $this->registerFile($ms["file_path"]) : null;
                            $message->thumbnails_url = !empty($message->content_url) ? ImageConverter::convert2image($message->content_url) : null;
                            $message_changed_flg     = true;
                        }

                        $message->start_datetime            = $ms["start_datetime"];
                        $message->end_datetime              = $ms["end_datetime"];
                        $message->created_at                = now();
                        $message->create_admin_id           = $admin->id;
                        $message->updated_at                = null;
                        $message->updated_admin_id          = null;
                        $message->organization1_id          = $organization1_id;
                        $message->editing_flg               = false;
                        $message->is_broadcast_notification = $ms["is_broadcast_notification"];
                        $message->save();

                        $message->roll()->attach($ms["roll"]);

                        // message_shopにインサート
                        $chunkSize = 200;

                        // message_shopにshop_idとmessage_idをバルクインサート
                        if (isset($ms["shops"])) {

                            // ショップデータの事前取得とグループ化
                            $shopsData = Shop::whereIn('id', $ms["shops"]['shop_ids'])
                                ->whereIn('brand_id', $ms["brand"])
                                ->get(['id', 'brand_id'])
                                ->groupBy('id');

                            $insertData = [];
                            // 事前に選択フラグを決定
                            $selectedFlg = (isset($ms["shops"]['all_shops_flag']) && $ms["shops"]['all_shops_flag'] === true) ? 'all' : 'store';

                            foreach ($ms["shops"]['shop_ids'] as $_shop_id) {
                                if (isset($shopsData[$_shop_id])) {
                                    foreach ($shopsData[$_shop_id] as $shop) {
                                        $insertData[] = [
                                            'message_id'   => $message->id,
                                            'shop_id'      => $shop->id,
                                            'brand_id'     => $shop->brand_id,
                                            'selected_flg' => $selectedFlg,
                                            'created_at'   => now(),
                                            'updated_at'   => now()
                                        ];

                                        // チャンクサイズに達したらバルクインサート
                                        if (count($insertData) >= $chunkSize) {
                                            MessageShop::insert($insertData);
                                            $insertData = [];
                                        }
                                    }
                                }
                            }

                            // 最後に残ったデータをインサート
                            if (!empty($insertData)) {
                                MessageShop::insert($insertData);
                            }
                        }

                        // message_organizationにインサート
                        // Shopモデルを使用して、各組織レベルのIDを取得
                        $shopIds = $ms["shops"]['shop_ids'];
                        $shops = Shop::whereIn('id', $shopIds)->get();

                        $bulkData = [];
                        foreach ($shops as $shop) {
                            $orgData = [
                                'message_id'       => $message->id,
                                'organization1_id' => $message->organization1_id,
                                'created_at'       => now(),
                                'updated_at'       => now(),
                                'organization5_id' => $shop->organization5_id ?? null,
                                'organization4_id' => $shop->organization4_id ?? null,
                                'organization3_id' => $shop->organization3_id ?? null,
                                'organization2_id' => $shop->organization2_id ?? null,
                            ];

                            // 組織IDが設定されている場合のみデータを追加
                            if (!empty(array_intersect_key($orgData, array_flip(['organization5_id', 'organization4_id', 'organization3_id', 'organization2_id'])))) {
                                $bulkData[] = $orgData;
                            }
                        }

                        // バルクインサート
                        if (!empty($bulkData)) {
                            DB::table('message_organization')->insert($bulkData);
                        }

                        // ブランドとタグの同期
                        $message->brand()->sync($ms["brand"]);
                        $message->tag()->sync($ms["tag"]);

                        // message_userの同期
                        if (!$message->editing_flg) {
                            $origin_user = $message->user()->pluck('id')->toArray();
                            $new_target_user = $this->getTargetUsersByShopId((object)[
                                'organization_shops' => implode(',', $ms["shops"]['shop_ids']),
                                'brand' => $ms["brand"],
                                'target_roll' => $ms["roll"]
                            ]);
                            $new_target_user_id = array_keys($new_target_user);
                            $detach_user = array_diff($origin_user, $new_target_user_id);
                            $attach_user = array_diff($new_target_user_id, $origin_user);

                            $message->user()->detach($detach_user);
                            foreach ($attach_user as $key => $user) {
                                $message->user()->attach([$user => $new_target_user[$user]]);
                            }
                        }

                        // check_fileがtrueの場合
                        if (isset($ms["check_file"]) && $ms["check_file"] === true) {
                            // message_contentにインサート
                            DB::table('message_contents')->insert([
                                'message_id'     => $message->id,
                                'content_name'   => $message->content_name,
                                'content_url'    => $message->content_url,
                                'thumbnails_url' => $message->thumbnails_url,
                                'created_at'     => now(),
                                'updated_at'     => now(),
                                'join_flg'       => 'single'
                            ]);
                        }

                    // 更新
                    } else {
                        $message                = Message::find($ms["id"]);
                        $message->number        = $ms["number"];
                        $message->emergency_flg = $ms["emergency_flg"];
                        $message->category_id   = $ms["category"];
                        $message->title         = $ms["title"];

                        // check_fileがtrueの場合
                        if (isset($ms["check_file"]) && $ms["check_file"] === true) {
                            $message->content_name   = !empty($ms["file_name"]) ? $ms["file_name"] : null;
                            $message->content_url    = !empty($ms["file_path"]) ? $this->registerFile($ms["file_path"]) : null;
                            $message->thumbnails_url = !empty($message->content_url) ? ImageConverter::convert2image($message->content_url) : null;
                            $message_changed_flg     = true;
                        }

                        $message->tag()->sync($ms["tag"]);
                        $message->start_datetime            = $ms["start_datetime"];
                        $message->end_datetime              = $ms["end_datetime"];
                        $message->updated_at                = now();
                        if ($message->isDirty()) $message->updated_admin_id = $admin->id;
                        $message->editing_flg               = false;
                        $message->is_broadcast_notification = $ms["is_broadcast_notification"];
                        $message->save();

                        $message->roll()->sync($ms["roll"]);

                        MessageShop::where('message_id', $message->id)->delete();
                        // message_shopにインサート
                        $chunkSize = 200;

                        // message_shopにshop_idとmessage_idをバルクインサート
                        if (isset($ms["shops"])) {

                            // ショップデータの事前取得とグループ化
                            $shopsData = Shop::whereIn('id', $ms["shops"]['shop_ids'])
                                ->whereIn('brand_id', $ms["brand"])
                                ->get(['id', 'brand_id'])
                                ->groupBy('id');

                            $insertData = [];
                            // 事前に選択フラグを決定
                            $selectedFlg = (isset($ms["shops"]['all_shops_flag']) && $ms["shops"]['all_shops_flag'] === true) ? 'all' : 'store';

                            foreach ($ms["shops"]['shop_ids'] as $_shop_id) {
                                if (isset($shopsData[$_shop_id])) {
                                    foreach ($shopsData[$_shop_id] as $shop) {
                                        $insertData[] = [
                                            'message_id'   => $message->id,
                                            'shop_id'      => $shop->id,
                                            'brand_id'     => $shop->brand_id,
                                            'selected_flg' => $selectedFlg,
                                            'created_at'   => now(),
                                            'updated_at'   => now()
                                        ];

                                        // チャンクサイズに達したらバルクインサート
                                        if (count($insertData) >= $chunkSize) {
                                            MessageShop::insert($insertData);
                                            $insertData = [];
                                        }
                                    }
                                }
                            }

                            // 最後に残ったデータをインサート
                            if (!empty($insertData)) {
                                MessageShop::insert($insertData);
                            }
                        }

                        MessageOrganization::where('message_id', $message->id)->delete();
                        // message_organizationにインサート
                        // Shopモデルを使用して、各組織レベルのIDを取得
                        $shopIds = $ms["shops"]['shop_ids'];
                        $shops = Shop::whereIn('id', $shopIds)->get();

                        $bulkData = [];
                        foreach ($shops as $shop) {
                            $orgData = [
                                'message_id'       => $message->id,
                                'organization1_id' => $message->organization1_id,
                                'created_at'       => now(),
                                'updated_at'       => now(),
                                'organization5_id' => $shop->organization5_id ?? null,
                                'organization4_id' => $shop->organization4_id ?? null,
                                'organization3_id' => $shop->organization3_id ?? null,
                                'organization2_id' => $shop->organization2_id ?? null,
                            ];

                            // 組織IDが設定されている場合のみデータを追加
                            if (!empty(array_intersect_key($orgData, array_flip(['organization5_id', 'organization4_id', 'organization3_id', 'organization2_id'])))) {
                                $bulkData[] = $orgData;
                            }
                        }

                        // バルクインサート
                        if (!empty($bulkData)) {
                            DB::table('message_organization')->insert($bulkData);
                        }

                        // ブランドの同期
                        $message->brand()->sync($ms["brand"]);

                        // message_userの同期
                        if (!$message->editing_flg) {
                            $origin_user = $message->user()->pluck('id')->toArray();
                            $new_target_user = $this->getTargetUsersByShopId((object)[
                                'organization_shops' => implode(',', $ms["shops"]['shop_ids']),
                                'brand' => $ms["brand"],
                                'target_roll' => $ms["roll"]
                            ]);
                            $new_target_user_id = array_keys($new_target_user);
                            $detach_user = array_diff($origin_user, $new_target_user_id);
                            $attach_user = array_diff($new_target_user_id, $origin_user);

                            $message->user()->detach($detach_user);
                            foreach ($attach_user as $key => $user) {
                                $message->user()->attach([$user => $new_target_user[$user]]);
                            }
                        }

                        // check_fileがtrueの場合
                        if (isset($ms["check_file"]) && $ms["check_file"] === true) {
                            // message_contentの更新
                            $message->content()->delete();
                            DB::table('message_contents')->insert([
                                'message_id'     => $message->id,
                                'content_name'   => $message->content_name,
                                'content_url'    => $message->content_url,
                                'thumbnails_url' => $message->thumbnails_url,
                                'created_at'     => now(),
                                'updated_at'     => now(),
                                'join_flg'       => 'single'
                            ]);
                        }
                    }

                    // WowTalk通知のジョブをキューに追加
                    if ($message->is_broadcast_notification == 1) {
                        SendWowtalkNotificationJob::dispatch($message->id, 'message', 'message_import');
                    }

                    // 閲覧率の更新処理
                    $rate = $request->input('rate');
                    $message_id = $message->id;

                    // メッセージの既読・総ユーザー数を一度に集計
                    $messageRates = DB::table('message_user')
                        ->select([
                            'message_user.message_id',
                            DB::raw('sum(message_user.read_flg) as read_users'),
                            DB::raw('count(distinct message_user.user_id) as total_users'),
                            DB::raw('round((sum(message_user.read_flg) / count(distinct message_user.user_id)) * 100, 1) as view_rate')
                        ])
                        ->join('messages', 'message_user.message_id', '=', 'messages.id')
                        ->where('messages.organization1_id', $organization1_id)
                        ->when($message_id, function ($query) use ($message_id) {
                            $query->where('message_user.message_id', $message_id);
                        })
                        ->groupBy('message_user.message_id')
                        ->when((isset($rate[0]) || isset($rate[1])), function ($query) use ($rate) {
                            $min = isset($rate[0]) ? $rate[0] : 0;
                            $max = isset($rate[1]) ? $rate[1] : 100;
                            $query->havingRaw('view_rate between ? and ?', [$min, $max]);
                        })
                        ->get();

                    // バルクアップデート用のデータ準備
                    $updateData = [];
                    foreach ($messageRates as $message) {
                        $updateData[] = [
                            'message_id'       => $message->message_id,
                            'organization1_id' => $organization1_id,
                            'view_rate'        => $message->view_rate,     // 閲覧率の計算
                            'read_users'       => $message->read_users,    // 既読ユーザー数
                            'total_users'      => $message->total_users,   // 全体ユーザー数
                            'created_at'       => now(),
                            'updated_at'       => now(),
                        ];
                    }

                    // バルクアップデートを実行
                    DB::table('message_view_rates')->upsert(
                        $updateData,
                        ['message_id', 'organization1_id'],
                        ['view_rate', 'read_users', 'total_users', 'created_at', 'updated_at']
                    );
                }

            } else {
                foreach ($messages as $key => $ms) {
                    $message                 = Message::find($ms["id"]);
                    $message->number         = $ms["number"];
                    $message->emergency_flg  = $ms["emergency_flg"];
                    $message->category_id    = $ms["category"];
                    $message->title          = $ms["title"];
                    $message->tag()->sync($ms["tag"]);
                    $message->start_datetime = $ms["start_datetime"];
                    $message->end_datetime   = $ms["end_datetime"];
                    if ($message->isDirty()) $message->updated_admin_id = $admin->id;
                    $message->save();

                    // MessageOrganization::where('message_id', $message->id)->delete();
                    // foreach ($ms["organization5"] as $org5_id) {
                    //     $message->organization()->create([
                    //         'message_id' => $message->id,
                    //         'organization1_id' => $message->organization1_id,
                    //         'organization5_id' => $org5_id
                    //     ]);
                    // }

                    // foreach ($ms["organization4"] as $org4_id) {
                    //     $message->organization()->create([
                    //         'message_id' => $message->id,
                    //         'organization1_id' => $message->organization1_id,
                    //         'organization4_id' => $org4_id
                    //     ]);
                    // }

                    // foreach ($ms["organization3"] as $org3_id) {
                    //     $message->organization()->create([
                    //         'message_id' => $message->id,
                    //         'organization1_id' => $message->organization1_id,
                    //         'organization3_id' => $org3_id
                    //     ]);
                    // }

                    $message->brand()->sync($ms["brand"]);

                    // if (!$message->editing_flg) {
                    //     $origin_user = $message->user()->pluck('id')->toArray();
                    //     $new_target_user = $this->targetUserParam((object)[
                    //         'organization' => [
                    //             'org5' => $ms["organization5"],
                    //             'org4' => $ms["organization4"],
                    //             'org3' => $ms["organization4"]
                    //         ],
                    //         'brand' => $ms["brand"],
                    //         'target_roll' => $ms["roll"]
                    //     ]);
                    //     $new_target_user_id = array_keys($new_target_user);
                    //     $detach_user = array_diff($origin_user, $new_target_user_id);
                    //     $attach_user = array_diff($new_target_user_id, $origin_user);

                    //     $message->user()->detach($detach_user);
                    //     foreach ($attach_user as $key => $user) {
                    //         $message->user()->attach([$user => $new_target_user[$user]]);
                    //     }
                    // }
                }
            }

            DB::table('message_csv_logs')
            ->where('id', $log_id)
            ->update([
                'imported_datetime' => new Carbon('now'),
                'is_success' => true
            ]);

            DB::commit();

            return response()->json([
                'message' => "インポート完了しました"
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            if ($message_changed_flg) {
                foreach ($messages as $message) {
                    if (isset($message['content_url'])) {
                        $this->rollbackRegisterFile($message['content_url']);
                    }
                }
            }

            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }
    }

    // 業務連絡店舗CSV アップロード（新規登録/編集）
    public function csvStoreUpload(Request $request)
    {
        $log_file_name = $request->input('log_file_name');
        $file_path = public_path() . '/log/' . $log_file_name;
        file_put_contents($file_path, "0");

        $admin = session('admin');
        $csv = $request->file;
        $organization1 = (int) $request->input('organization1');

        $csv_content = file_get_contents($csv);
        $encoding = mb_detect_encoding($csv_content);
        if ($encoding == "UTF-8") {
            $shift_jis_content = mb_convert_encoding($csv_content, 'CP932', 'UTF-8');
            file_put_contents($csv, $shift_jis_content);
        }

        $shop_list = $this->getShopForm($organization1);
        $csv_path = Storage::putFile('csv', $csv);
        Log::info("業連CSVインポート", [
            'csv_path' => $csv_path,
            'admin' => $admin
        ]);
        try {
            Excel::import(new MessageStoreCsvImport($organization1, $shop_list), $csv, \Maatwebsite\Excel\Excel::CSV);
            $collection = Excel::toCollection(new MessageStoreCsvImport($organization1, $shop_list), $csv, \Maatwebsite\Excel\Excel::CSV);

            $array = [];
            foreach (
                $collection[0] as $key => [
                    $store_code,
                    $store_name
                ]
            ) {
                array_push($array, [
                    'store_code' => $store_code,
                    'store_name' => $store_name
                ]);
                file_put_contents($file_path, ceil((($key + 1)) * 100));
            }

            return response()->json([
                'json' => $array
            ], 200);
        } catch (ValidationException $e) {
            $failures = $e->failures();

            $errorMessage = [];
            foreach ($failures as $index => $failure) {
                $errorMessage[$index]["row"] = $failure->row(); // row that went wrong
                $errorMessage[$index]["attribute"] = $failure->attribute(); // either heading key (if using heading row concern) or column index
                $errorMessage[$index]["errors"] = $failure->errors(); // Actual error messages from Laravel validator
                $errorMessage[$index]["value"] = $failure->values(); // The values of the row that has failed.
            }

            File::delete($file_path);
            return response()->json([
                'message' => $errorMessage
            ], 422);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());

            File::delete($file_path);
            return response()->json([
                'message' => "エラーが発生しました"
            ], 500);
        }
    }

    public function storeProgress(Request $request)
    {
        $file_name = $request->file_name;
        $file_path = public_path() . '/log/' . $file_name;
        if (!File::exists($file_path)) {
            return response()->json([
                'message' => "ログファイルが存在しません"
            ], 500);
        }

        $log = File::get($file_path);
        if ($log == 100) {
            File::delete($file_path);
        }
        return $log;
    }

    // 業務連絡店舗CSV インポート（新規登録/編集）
    public function csvStoreImport(Request $request)
    {
        $admin = session('admin');

        // JSONデータの取得
        $storesJson = $request->json('file_json');
        $organization1_id = $request->json('organization1_id');

        $csvStoreIds = [];
        $brand_id = Brand::where('organization1_id', $organization1_id)->pluck('id')->toArray();

        // ショップIDを取得
        $csvStoreIds = DB::table('shops')
            ->join('brands', 'shops.brand_id', '=', 'brands.id')
            ->whereIn('brands.id', $brand_id)
            ->whereIn('shops.shop_code', array_column($storesJson, 'store_code'))
            ->pluck('shops.id')
            ->toArray();

        try {
            // 業態一覧を取得する
            $brand_list = Brand::where('organization1_id', $organization1_id)->get();

            $organization_list = [];
            $organization_list = Shop::query()
                ->leftjoin('organization2', 'organization2_id', '=', 'organization2.id')
                ->leftjoin('organization3', 'organization3_id', '=', 'organization3.id')
                ->leftjoin('organization4', 'organization4_id', '=', 'organization4.id')
                ->leftjoin('organization5', 'organization5_id', '=', 'organization5.id')
                ->distinct('organization4_id')
                ->distinct('organization5_id')
                ->select(
                    'organization2_id',
                    'organization2.name as organization2_name',
                    'organization2.order_no as organization2_order_no',
                    'organization3_id',
                    'organization3.name as organization3_name',
                    'organization3.order_no as organization3_order_no',
                    'organization4_id',
                    'organization4.name as organization4_name',
                    'organization4.order_no as organization4_order_no',
                    'organization5_id',
                    'organization5.name as organization5_name',
                    'organization5.order_no as organization5_order_no',
                )
                ->where('organization1_id', $organization1_id)
                ->orderByRaw('organization2_id is null asc')
                ->orderByRaw('organization3_id is null asc')
                ->orderByRaw('organization4_id is null asc')
                ->orderByRaw('organization5_id is null asc')
                ->orderBy("organization2_order_no", "asc")
                ->orderBy("organization3_order_no", "asc")
                ->orderBy("organization4_order_no", "asc")
                ->orderBy("organization5_order_no", "asc")
                ->get()
                ->toArray();


            // 店舗情報を取得する
            $brand_ids = $brand_list->pluck('id')->toArray();
            $all_shops = Shop::query()
                ->select(
                    'shops.id as id',
                    'shops.shop_code',
                    'shops.display_name',
                    'shops.organization5_id',
                    'shops.organization4_id',
                    'shops.organization3_id',
                    'shops.organization2_id'
                )
                ->leftJoin('organization5 as org5', 'shops.organization5_id', '=', 'org5.id')
                ->leftJoin('organization4 as org4', 'shops.organization4_id', '=', 'org4.id')
                ->leftJoin('organization3 as org3', 'shops.organization3_id', '=', 'org3.id')
                ->leftJoin('organization2 as org2', 'shops.organization2_id', '=', 'org2.id')
                ->where('shops.organization1_id', $organization1_id)
                ->whereIn('shops.brand_id', $brand_ids)
                ->orderBy('shops.shop_code', 'asc')
                ->get()
                ->toArray();


            // 組織別にデータを整理する
            $organization_list = array_map(function ($org) use ($all_shops) {
                $org['organization5_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                    return $shop['organization5_id'] == $org['organization5_id'];
                });
                $org['organization4_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                    return $shop['organization4_id'] == $org['organization4_id'] && is_null($shop['organization5_id']);
                });
                $org['organization3_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                    return $shop['organization3_id'] == $org['organization3_id'] && is_null($shop['organization4_id']) && is_null($shop['organization5_id']);
                });
                $org['organization2_shop_list'] = array_filter($all_shops, function ($shop) use ($org) {
                    return $shop['organization2_id'] == $org['organization2_id'] && is_null($shop['organization3_id']) && is_null($shop['organization4_id']) && is_null($shop['organization5_id']);
                });
                return $org;
            }, $organization_list);

            // shop_code でソート済みの $all_shops をそのまま利用
            $all_shop_list = array_map(function ($shop) {
                return [
                    'shop_id' => $shop['id'],
                    'shop_code' => $shop['shop_code'],
                    'display_name' => $shop['display_name'],
                ];
            }, $all_shops);


            // 店舗コードでshopsをソート
            usort($all_shop_list, function ($a, $b) {
                return strcmp($a['shop_code'], $b['shop_code']);
            });

            // BBの場合、SKの場合
            if ($organization1_id == 2 || $organization1_id == 8) {
                return response()->json([
                    'storesJson' => $storesJson,
                    // 'brand_list' => $brand_list,
                    'organization_list' => $organization_list,
                    'all_shop_list' => $all_shop_list,
                    'csvStoreIds' => $csvStoreIds,
                ], 200);
            }

            return response()
                ->view('common.admin.message-csv-store-modal', [
                    'storesJson' => $storesJson,
                    'brand_list' => $brand_list,
                    'organization_list' => $organization_list,
                    'all_shop_list' => $all_shop_list,
                    'csvStoreIds' => $csvStoreIds,
                ], 200)
                ->header('Content-Type', 'text/plain');
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }
    }

    // PDFの結合処理
    private function pdfFileJoin($join_files): array
    {
        ini_set('memory_limit', '2048M'); // メモリ制限を一時的に増加

        $tempFiles = [];

        // 複数PDFがある場合の表示処理
        if (!empty($join_files)) {
            foreach ($join_files as $join_file) {
                $originalFile = public_path($join_file['content_url']);
                $tempFile = public_path('uploads/temp_' . basename($join_file['content_url']));

                // 元のファイルが存在するか確認
                if (!file_exists($originalFile)) {
                    Log::error("ファイルが存在しません: " . $originalFile);
                    continue;
                }

                PdfFileJoin::recompressPdf($originalFile, $tempFile);
                $tempFiles[] = $tempFile;
            }
        } else {
            // 元のメモリ制限に戻す
            ini_restore('memory_limit');

            return $join_files;
        }

        // ファイルの処理とリネーム
        foreach ($tempFiles as $tempFile) {
            $finalPath = public_path('uploads/' . basename($tempFile));

            // 一時ファイルが存在するか確認
            if (file_exists($tempFile)) {
                if (!rename($tempFile, $finalPath)) {
                    Log::error("ファイルのリネームに失敗しました: " . $tempFile . " から " . $finalPath);
                }
            } else {
                Log::error("一時ファイルが存在しません: " . $tempFile);
            }
        }

        // PDF を生成するための初期化
        $pdf = new TcpdfFpdi();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // 各 PDF を追加
        foreach ($tempFiles as $file) {
            $pageCount = $pdf->setSourceFile($file);
            for ($i = 1; $i <= $pageCount; $i++) {
                $pdf->AddPage();
                $templateId = $pdf->importPage($i);
                $pdf->useTemplate($templateId);
            }
        }

        // 一時ファイルの確認と削除
        foreach ($tempFiles as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }

        $outputFileName = basename($join_files[0]['content_name']);
        $outputFileUrl = 'uploads/join' . basename($join_files[0]['content_url']);
        $outputFilePath = public_path($outputFileUrl);

        // PDFをファイルに保存
        try {
            $pdf->output($outputFilePath, 'F');
        } catch (Exception $e) {
            Log::error("PDFの保存に失敗しました: " . $outputFilePath, ['exception' => $e]);
            return response()->json(['error' => 'PDFの保存に失敗しました'], 500);
        }

        // ファイルが正しく保存されたか確認
        if (!file_exists($outputFilePath)) {
            Log::error("PDFの保存に失敗しました: ファイルが存在しません " . $outputFilePath);
            return response()->json(['error' => 'PDFの保存に失敗しました: ファイルが存在しません'], 500);
        }

        $join_file_list[] = [
            "content_name" => $outputFileName,
            "content_url" => $outputFileUrl,
        ];

        // 元のメモリ制限に戻す
        ini_restore('memory_limit');

        return $join_file_list;
    }

    private function parseDateTime($datetime)
    {
        return (!isset($datetime)) ? null : Carbon::parse($datetime, 'Asia/Tokyo');
    }

    private function registerFile($request_file_path): ?String
    {
        $content_url = 'uploads/' . basename($request_file_path);
        $current_path = storage_path('app/' . $request_file_path);
        $next_path = public_path($content_url);
        rename($current_path, $next_path);
        return $content_url;
    }

    private function rollbackRegisterFile($request_file_path): void
    {
        if (!isset($request_file_path)) return;

        $content_url = 'uploads/' . basename($request_file_path);
        $current_path = storage_path('app/' . $request_file_path);
        $next_path = public_path($content_url);

        // ファイルの存在をチェック
        if (file_exists($next_path)) {
            rename($next_path, $current_path);
        }

        return;
    }

    private function getExistContentIds($request): array
    {
        if (!isset($request)) return [];
        $content_ids = [];
        foreach ($request->content_id as $content_id) {
            if (isset($content_id)) {
                $id = (int)$content_id;
                $content_ids[] = $id;
            }
        }
        return $content_ids;
    }

    private function targetUserParam($organizarions): array
    {
        $shops_id = [];
        $target_user_data = [];

        // organizationごとにshopを取得する
        if (isset($organizarions->organization['org5'])) {
            $_shops_id = Shop::select('id')
                ->whereIn('organization5_id', $organizarions->organization['org5'])
                ->whereIn('brand_id', $organizarions->brand)
                ->get()
                ->toArray();
            $shops_id = array_merge($shops_id, $_shops_id);
        }
        if (isset($organizarions->organization['org4'])) {
            $_shops_id = Shop::select('id')
                ->whereIn('organization4_id', $organizarions->organization['org4'])
                ->whereIn('brand_id', $organizarions->brand)
                ->get()
                ->toArray();
            $shops_id = array_merge($shops_id, $_shops_id);
        }
        if (isset($organizarions->organization['org3'])) {
            $_shops_id = Shop::select('id')
                ->whereIn('organization3_id', $organizarions->organization['org3'])
                ->whereIn('brand_id', $organizarions->brand)
                ->whereNull('organization4_id')
                ->whereNull('organization5_id')
                ->get()
                ->toArray();
            $shops_id = array_merge($shops_id, $_shops_id);
        }
        if (isset($organizarions->organization['org2'])) {
            $_shops_id = Shop::select('id')
                ->whereIn('organization2_id', $organizarions->organization['org2'])
                ->whereIn('brand_id', $organizarions->brand)
                ->whereNull('organization4_id')
                ->whereNull('organization5_id')
                ->get()
                ->toArray();
            $shops_id = array_merge($shops_id, $_shops_id);
        }

        // 取得したshopのリストからユーザーを取得する
        $target_users = User::select('id', 'shop_id')->whereIn('shop_id', $shops_id)->whereIn('roll_id', $organizarions->target_roll)->get()->toArray();
        // ユーザーに業務連絡の閲覧権限を与える
        foreach ($target_users as $target_user) {
            $target_user_data[$target_user['id']] = ['shop_id' => $target_user['shop_id']];
        }

        return $target_user_data;
    }

    private function getTargetUsersByShopId($organizations): array
    {
        $target_user_data = [];
        $chunkSize = 200; // チャンクサイズを設定

        if (isset($organizations->organization_shops)) {
            $organization_shops = explode(',', $organizations->organization_shops);

            // ユーザーとショップのデータを一度に取得
            $all_users = User::query()
                ->select(
                    'users.id as user_id',
                    'users.shop_id',
                    'shops.brand_id',
                    'shops.organization5_id',
                    'shops.organization4_id',
                    'shops.organization3_id',
                    'shops.organization2_id'
                )
                ->leftJoin('shops', 'users.shop_id', '=', 'shops.id')
                ->whereIn('shops.id', $organization_shops)
                ->whereIn('shops.brand_id', $organizations->brand)
                ->whereIn('users.roll_id', $organizations->target_roll)
                ->orderBy('users.shop_id', 'asc')
                ->get()
                ->toArray();

            // データをチャンクで処理
            $user_chunks = array_chunk($all_users, $chunkSize);

            foreach ($user_chunks as $chunk) {
                foreach ($chunk as $user) {
                    $target_user_data[$user['user_id']] = ['shop_id' => $user['shop_id']];
                }
            }
        }

        return $target_user_data;
    }

    // 「手順」を登録するために加工する
    private function messageContentsParam($request): array
    {
        // ファイル名がセットされていない場合、空の配列を返す
        if (!isset($request->file_name)) return [];

        $content_data = [];

        // 各ファイル名に対して処理を行う
        foreach ($request->file_name as $i => $file_name) {
            if (!empty($file_name)) {
                $file_path = $request->file_path[$i] ?? null;
                $join_flg = $request->join_flg[$i] ?? null;

                // ファイルパスが存在する場合のみ処理を行う
                if ($file_path) {
                    $content_data[$i]['content_name'] = $file_name;
                    $content_data[$i]['content_url'] = $this->registerFile($file_path);
                    $content_data[$i]['thumbnails_url'] = ImageConverter::convert2image($content_data[$i]['content_url']);
                    $content_data[$i]['join_flg'] = $join_flg;
                }
            }
        }

        return $content_data;
    }

    private function rollbackMessageContentFile($request): Void
    {
        if (!(isset($request->file_path))) return;
        foreach ($request->file_path as $file_path) {
            if (isset($file_path)) {
                $current_path = storage_path('app/' . $file_path);
                $next_path = public_path('uploads/' . basename($file_path));
                try {
                    rename($next_path, $current_path);
                } catch (\Throwable $th) {
                    Log::error($th->getMessage());
                }
            }
        }
    }

    private function hasRequestFile($request)
    {
        if (!isset($request->file_name) || !isset($request->file_path)) return false;
        return true;
    }

    private function isChangedFile($current_file_path, $next_file_path): Bool
    {
        $currnt_path = $current_file_path ? basename($current_file_path) : null;
        $next_path = $next_file_path ? basename($next_file_path) : null;

        return !($currnt_path == $next_path);
    }

    private function isChangedJoinFlg(array $current_join_flg, array $next_join_flg): Bool
    {
        // join_flgリストのサイズが異なる場合は変更されたとみなす
        if (count($current_join_flg) !== count($next_join_flg)) {
            return true;
        }

        // 各要素を比較
        foreach ($current_join_flg as $index => $join_flg) {
            if ($join_flg != $next_join_flg[$index]) {
                return true;
            }
        }

        // すべての要素が同じ場合は変更されていない
        return false;
    }

    private function tagImportParam(?array $tags): array
    {
        if (!isset($tags)) return [];

        $tags_pram = [];
        foreach ($tags as $key => $tag_name) {
            if (!isset($tag_name)) continue;
            $tag = MessageTagMaster::firstOrCreate(['name' => trim($tag_name, "\"")]);
            $tags_pram[] = $tag->id;
        }
        return $tags_pram;
    }

    private  function strToArray(?String $str): array
    {
        if (!isset($str)) return [];

        $array = explode(',', $str);

        $returnArray = [];
        foreach ($array as $key => $value) {
            $returnArray[] = trim($value, "\"");
        }

        return $returnArray;
    }

    private function getBrandAll(Int $org1_id): array
    {
        return Brand::query()
            ->where('organization1_id', '=', $org1_id)
            ->pluck('id')
            ->toArray();
    }

    private function getOrg3All(Int $org1_id): array
    {
        return Shop::query()
            ->distinct('organization3.id')
            ->where('organization1_id', '=', $org1_id)
            ->leftjoin('organization3', 'organization3_id', '=', 'organization3.id')
            ->pluck('organization3.id')
            ->toArray();
    }

    private function getOrg4All(Int $org1_id): array
    {
        return Shop::query()
            ->distinct('organization4.id')
            ->where('organization1_id', '=', $org1_id)
            ->leftjoin('organization4', 'organization4_id', '=', 'organization4.id')
            ->pluck('organization4.id')
            ->toArray();
    }

    private function getOrg5All(Int $org1_id): array
    {
        return Shop::query()
            ->distinct('organization5.id')
            ->where('organization1_id', '=', $org1_id)
            ->leftjoin('organization5', 'organization5_id', '=', 'organization5.id')
            ->pluck('organization5.id')
            ->toArray();
    }

    private function getOrganizationForm($organization1_id)
    {
        return Shop::query()
            ->leftjoin('brands', 'brand_id', '=', 'brands.id')
            ->leftjoin('organization2', 'organization2_id', '=', 'organization2.id')
            ->leftjoin('organization3', 'organization3_id', '=', 'organization3.id')
            ->leftjoin('organization4', 'organization4_id', '=', 'organization4.id')
            ->leftjoin('organization5', 'organization5_id', '=', 'organization5.id')
            ->distinct('brand_id')
            ->distinct('organization2_id')
            ->distinct('organization3_id')
            ->distinct('organization4_id')
            ->distinct('organization5_id')
            ->select(
                'brand_id',
                'brands.name as brand_name',
                'organization2_id',
                'organization2.name as organization2_name',
                'organization3_id',
                'organization3.name as organization3_name',
                'organization4_id',
                'organization4.name as organization4_name',
                'organization5_id',
                'organization5.name as organization5_name'
            )
            ->where('shops.organization1_id', $organization1_id)
            ->get()
            ->toArray();
    }

    private function getShopForm($organization1_id)
    {
        return Shop::query()
            ->select([
                'shops.*',
                DB::raw("GROUP_CONCAT(brands.name SEPARATOR ',') as brand_names")
            ])
            ->join('brands', function ($join) {
                $join->on('shops.organization1_id', '=', 'brands.organization1_id')
                    ->on('shops.brand_id', '=', 'brands.id');
            })
            ->where('shops.organization1_id', $organization1_id)
            ->groupBy('shops.id')
            ->get()
            ->toArray();
    }

    private function cleanDateString($dateString)
    {
        // 正規表現で日付文字列から曜日を削除
        return preg_replace('/\(.+\)/', '', $dateString);
    }
}
