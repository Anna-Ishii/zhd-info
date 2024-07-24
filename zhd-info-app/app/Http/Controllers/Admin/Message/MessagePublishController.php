<?php

namespace App\Http\Controllers\Admin\Message;

use Carbon\Carbon;
use App\Enums\PublishStatus;
use App\Exports\MessageListExport;
use App\Exports\MessageNewStoreListExport;
use App\Exports\MessageEditStoreListExport;
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
        $category_id = $request->input('category');
        $status = PublishStatus::tryFrom($request->input('status'));
        $q = $request->input('q');
        $rate = $request->input('rate');
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

        $readUsersSub = DB::table('message_user')
            ->select([
                'message_user.message_id',
                DB::raw('sum(message_user.read_flg) as read_users')
            ])
            ->groupBy('message_user.message_id');

        $message_list = Message::query()
            ->with('create_user', 'updated_user', 'category', 'create_user', 'updated_user', 'brand', 'tag')
            ->leftjoin('message_user', 'messages.id', '=', 'message_id')
            ->leftjoin('message_brand', 'messages.id', '=', 'message_brand.message_id')
            ->leftjoin('brands', 'brands.id', '=', 'message_brand.brand_id')
            ->leftJoinSub($sub, 'sub', function ($join) {
                $join->on('sub.m_id', '=', 'messages.id');
            })
            ->leftJoinSub($readUsersSub, 'read_users_sub', function ($join) {
                $join->on('read_users_sub.message_id', '=', 'messages.id');
            })
            ->select([
                'messages.*',
                DB::raw('ifnull(read_users_sub.read_users, 0) as read_users'),
                DB::raw('count(distinct message_user.user_id) as total_users'),
                DB::raw('round((ifnull(read_users_sub.read_users, 0) / count(distinct message_user.user_id)) * 100, 1) as view_rate'),
                DB::raw('sub.b_name as brand_name'),
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
            ->when(isset($status), function ($query) use ($status) {
                switch ($status) {
                    case PublishStatus::Wait:
                        $query->waitMessage();
                        break;
                    case PublishStatus::Publishing:
                        $query->publishingMessage();
                        break;
                    case PublishStatus::Published:
                        $query->publishedMessage();
                        break;
                    case PublishStatus::Editing:
                        $query->where('editing_flg', '=', true);
                        break;
                    default:
                        break;
                }
            })
            ->when(isset($category_id), function ($query) use ($category_id) {
                $query->where('category_id', $category_id);
            })
            ->when(isset($label), function ($query) use ($label) {
                $query->where('emergency_flg', true);
            })
            ->when((isset($rate[0]) || isset($rate[1])), function ($query) use ($rate) {
                $min = isset($rate[0]) ? $rate[0] : 0;
                $max = isset($rate[1]) ? $rate[1] : 100;
                $query->havingRaw('view_rate between ? and ?', [$min, $max]);
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
            ->orderBy('messages.number', 'desc')
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

        // 店舗数をカウント
        foreach ($message_list as &$message) {
            $shop_count = 0;

            // すべての店舗数
            $all_shop_count = Shop::where('organization1_id', $organization1_id)->count();
            // チェックされている店舗数
            $shop_count = MessageShop::where('message_id', $message->id)->count();
            if ($shop_count == 0) {
                $shop_count = MessageUser::where('message_id', $message->id)->count();
            }
            if ($all_shop_count ==  $shop_count) {
                $shop_count = "全店";
            }

            $message->shop_count = $shop_count;
        }

        return view('admin.message.publish.index', [
            'category_list' => $category_list,
            'message_list' => $message_list,
            'organization1' => $organization1,
            'organization1_list' => $organization1_list,
        ]);
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
                    ->orwhere(DB::raw('SUBSTRING(shop_code, -4)'), 'LIKE', '%' . $shop_freeword . '%');
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
        // メモリ制限を一時的に増加
        ini_set('memory_limit', '512M');
        // 300秒 (5分) に設定
        set_time_limit(300);

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


        // shopを取得する
        $all_shop_list = [];

        $chunkSize = 100; // チャンクサイズを適切に設定

        foreach ($organization_list as $index => $organization) {
            $organization_list[$index]['organization5_shop_list'] = [];
            $organization_list[$index]['organization4_shop_list'] = [];
            $organization_list[$index]['organization3_shop_list'] = [];
            $organization_list[$index]['organization2_shop_list'] = [];

            // ブランドリストを配列に変換
            $brand_array = $brand_list->toArray();
            $brand_chunks = array_chunk($brand_array, $chunkSize);

            if (isset($organization['organization5_id'])) {
                foreach ($brand_chunks as $brand_chunk) {
                    $shops = Shop::where('organization5_id', $organization['organization5_id'])
                        ->whereIn('brand_id', array_column($brand_chunk, 'id'))
                        ->get()
                        ->toArray();

                    foreach ($shops as $shop) {
                        $all_shop_list[] = [
                            'shop_id' => $shop['id'],
                            'shop_code' => $shop['shop_code'],
                            'display_name' => $shop['display_name'],
                        ];
                    }

                    $organization_list[$index]['organization5_shop_list'] = array_merge($organization_list[$index]['organization5_shop_list'], $shops);
                }
            }

            if (isset($organization['organization4_id'])) {
                foreach ($brand_chunks as $brand_chunk) {
                    $shops = Shop::where('organization4_id', $organization['organization4_id'])
                        ->whereIn('brand_id', array_column($brand_chunk, 'id'))
                        ->get()
                        ->toArray();

                    foreach ($shops as $shop) {
                        $all_shop_list[] = [
                            'shop_id' => $shop['id'],
                            'shop_code' => $shop['shop_code'],
                            'display_name' => $shop['display_name'],
                        ];
                    }

                    $organization_list[$index]['organization4_shop_list'] = array_merge($organization_list[$index]['organization4_shop_list'], $shops);
                }
            }

            if (isset($organization['organization3_id'])) {
                foreach ($brand_chunks as $brand_chunk) {
                    $shops = Shop::where('organization3_id', $organization['organization3_id'])
                        ->whereIn('brand_id', array_column($brand_chunk, 'id'))
                        ->whereNull('organization4_id')
                        ->whereNull('organization5_id')
                        ->get()
                        ->toArray();

                    foreach ($shops as $shop) {
                        $all_shop_list[] = [
                            'shop_id' => $shop['id'],
                            'shop_code' => $shop['shop_code'],
                            'display_name' => $shop['display_name'],
                        ];
                    }

                    $organization_list[$index]['organization3_shop_list'] = array_merge($organization_list[$index]['organization3_shop_list'], $shops);
                }
            }

            if (isset($organization['organization2_id'])) {
                foreach ($brand_chunks as $brand_chunk) {
                    $shops = Shop::where('organization2_id', $organization['organization2_id'])
                        ->whereIn('brand_id', array_column($brand_chunk, 'id'))
                        ->whereNull('organization4_id')
                        ->whereNull('organization5_id')
                        ->get()
                        ->toArray();

                    foreach ($shops as $shop) {
                        $all_shop_list[] = [
                            'shop_id' => $shop['id'],
                            'shop_code' => $shop['shop_code'],
                            'display_name' => $shop['display_name'],
                        ];
                    }

                    $organization_list[$index]['organization2_shop_list'] = array_merge($organization_list[$index]['organization2_shop_list'], $shops);
                }
            }
        }

        // shop_codeを基準にソートするためのカスタム比較関数を定義
        usort($all_shop_list, function ($a, $b) {
            return strcmp($a['shop_code'], $b['shop_code']);
        });

        return view('admin.message.publish.new', [
            'organization1' => $organization1,
            'category_list' => $category_list,
            'target_roll_list' => $target_roll_list,
            'brand_list' => $brand_list,
            'organization_list' => $organization_list,
            'all_shop_list' => $all_shop_list,
        ]);
    }

    public function store(PublishStoreRequest $request, Organization1 $organization1)
    {
        // メモリ制限を一時的に増加
        ini_set('memory_limit', '512M');
        // 300秒 (5分) に設定
        set_time_limit(300);

        $validated = $request->validated();

        // ファイルを移動したかフラグ
        $message_changed_flg = false;

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
        if (!empty($join_files)) {
            $join_file_list = $this->pdfFileJoin($join_files);
        } else {
            $join_file_list = [];
        }

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

        $msg_params['thumbnails_url'] = $request->file_path[0] ? ImageConverter::convert2image($msg_params['content_url']) : null;
        $msg_params['create_admin_id'] = $admin->id;
        $msg_params['organization1_id'] = $organization1->id;
        $number = Message::where('organization1_id', $organization1->id)->max('number');
        $msg_params['number'] = (is_null($number)) ? 1 : $number + 1;
        $msg_params['editing_flg'] = isset($request->save) ? true : false;

        try {
            DB::beginTransaction();
            $message = Message::create($msg_params);
            $message->updated_at = null;
            $message->save();
            $message->roll()->attach($request->target_roll);

            if (isset($request->organization['org5'][0])) {
                // カンマ区切りの文字列を配列に変換
                $org5_ids = explode(',', $request->organization['org5'][0]);
                foreach ($org5_ids as $org5_id) {
                    $message->organization()->create([
                        'message_id' => $message->id,
                        'organization1_id' => $organization1->id,
                        'organization5_id' => $org5_id
                    ]);
                }
            }
            if (isset($request->organization['org4'][0])) {
                // カンマ区切りの文字列を配列に変換
                $org4_ids = explode(',', $request->organization['org4'][0]);
                foreach ($org4_ids as $org4_id) {
                    $message->organization()->create([
                        'message_id' => $message->id,
                        'organization1_id' => $organization1->id,
                        'organization4_id' => $org4_id
                    ]);
                }
            }
            if (isset($request->organization['org3'][0])) {
                // カンマ区切りの文字列を配列に変換
                $org3_ids = explode(',', $request->organization['org3'][0]);
                foreach ($org3_ids as $org3_id) {
                    $message->organization()->create([
                        'message_id' => $message->id,
                        'organization1_id' => $organization1->id,
                        'organization3_id' => $org3_id
                    ]);
                }
            }
            if (isset($request->organization['org2'][0])) {
                // カンマ区切りの文字列を配列に変換
                $org2_ids = explode(',', $request->organization['org2'][0]);
                foreach ($org2_ids as $org2_id) {
                    $message->organization()->create([
                        'message_id' => $message->id,
                        'organization1_id' => $organization1->id,
                        'organization2_id' => $org2_id
                    ]);
                }
            }

            // チャンクサイズを設定
            $chunkSize = 100;

            // message_shopにshop_idとmessage_idを格納
            if (isset($request->organization_shops)) {
                // カンマ区切りの文字列を配列に変換
                $organization_shops = explode(',', $request->organization_shops);

                // チャンクごとに処理
                $organization_shops_chunks = array_chunk($organization_shops, $chunkSize);

                foreach ($organization_shops_chunks as $organization_shops_chunk) {
                    foreach ($organization_shops_chunk as $_shop_id) {
                        $selectedFlg = null;
                        if (isset($request->select_organization['all']) && $request->select_organization['all'] === 'selected') {
                            $selectedFlg = 'all';
                        } elseif (isset($request->select_organization['store']) && $request->select_organization['store'] === 'selected') {
                            $selectedFlg = 'store';
                        } else {
                            $selectedFlg = 'store';
                        }
                        if ($selectedFlg) {
                            foreach ($request->brand as $brand) {
                                // 業態で絞込
                                $shops = Shop::where('id', $_shop_id)
                                    ->where('brand_id', $brand)
                                    ->get(['id', 'brand_id']);
                                foreach ($shops as $shop) {
                                    MessageShop::create([
                                        'message_id' => $message->id,
                                        'shop_id' => $shop->id,
                                        'brand_id' => $shop->brand_id,
                                        'selected_flg' => $selectedFlg
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            $message->brand()->attach($request->brand);
            $message->user()->attach(!isset($request->save) ? $this->getTargetUsersByShopId($request) : []);

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

        return redirect()->route('admin.message.publish.index', ['brand' => session('brand_id')]);
    }

    public function edit($message_id)
    {
        // メモリ制限を一時的に増加
        ini_set('memory_limit', '512M');
        // 300秒 (5分) に設定
        set_time_limit(300);

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


        // shopを取得する
        $all_shop_list = [];
        foreach ($organization_list as $index => $organization) {

            $organization_list[$index]['organization5_shop_list'] = [];
            $organization_list[$index]['organization4_shop_list'] = [];
            $organization_list[$index]['organization3_shop_list'] = [];
            $organization_list[$index]['organization2_shop_list'] = [];

            if (isset($organization['organization5_id'])) {
                foreach ($brand_list as $brand) {
                    $shops = Shop::where('organization5_id', $organization['organization5_id'])
                        ->where('brand_id', $brand->id)
                        ->get()
                        ->toArray();

                    // shop_codeとdisplay_nameを合体
                    foreach ($shops as $shop) {

                        // すべてのshopリスト
                        $all_shop_list[] = [
                            'shop_id' => $shop['id'],
                            'shop_code' => $shop['shop_code'],
                            'display_name' => $shop['display_name'],
                        ];
                    }

                    $organization_list[$index]['organization5_shop_list'] = array_merge($organization_list[$index]['organization5_shop_list'], $shops);
                }
            }
            if (isset($organization['organization4_id'])) {
                foreach ($brand_list as $brand) {
                    $shops = Shop::where('organization4_id', $organization['organization4_id'])
                        ->where('brand_id', $brand->id)
                        ->get()
                        ->toArray();

                    // shop_codeとdisplay_nameを合体
                    foreach ($shops as $shop) {

                        // すべてのshopリスト
                        $all_shop_list[] = [
                            'shop_id' => $shop['id'],
                            'shop_code' => $shop['shop_code'],
                            'display_name' => $shop['display_name'],
                        ];
                    }

                    $organization_list[$index]['organization4_shop_list'] = array_merge($organization_list[$index]['organization4_shop_list'], $shops);
                }
            }
            if (isset($organization['organization3_id'])) {
                foreach ($brand_list as $brand) {
                    $shops = Shop::where('organization3_id', $organization['organization3_id'])
                        ->where('brand_id', $brand->id)
                        ->whereNull('organization4_id')
                        ->whereNull('organization5_id')
                        ->get()
                        ->toArray();

                    // shop_codeとdisplay_nameを合体
                    foreach ($shops as $shop) {

                        // すべてのshopリスト
                        $all_shop_list[] = [
                            'shop_id' => $shop['id'],
                            'shop_code' => $shop['shop_code'],
                            'display_name' => $shop['display_name'],
                        ];
                    }

                    $organization_list[$index]['organization3_shop_list'] = array_merge($organization_list[$index]['organization3_shop_list'], $shops);
                }
            }
            if (isset($organization['organization2_id'])) {
                foreach ($brand_list as $brand) {
                    $shops = Shop::where('organization2_id', $organization['organization2_id'])
                        ->where('brand_id', $brand->id)
                        ->whereNull('organization4_id')
                        ->whereNull('organization5_id')
                        ->get()
                        ->toArray();

                    // shop_codeとdisplay_nameを合体
                    foreach ($shops as $shop) {

                        // すべてのshopリスト
                        $all_shop_list[] = [
                            'shop_id' => $shop['id'],
                            'shop_code' => $shop['shop_code'],
                            'display_name' => $shop['display_name'],
                        ];
                    }

                    $organization_list[$index]['organization2_shop_list'] = array_merge($organization_list[$index]['organization2_shop_list'], $shops);
                }
            }
        }

        $target_org = [];
        $target_org['org5'] = MessageOrganization::where('message_id', $message_id)->pluck('organization5_id')->toArray();
        $target_org['org4'] = MessageOrganization::where('message_id', $message_id)->pluck('organization4_id')->toArray();
        $target_org['org3'] = MessageOrganization::where('message_id', $message_id)->pluck('organization3_id')->toArray();
        $target_org['org2'] = MessageOrganization::where('message_id', $message_id)->pluck('organization2_id')->toArray();

        $message_target_roll = $message->roll()->pluck('rolls.id')->toArray();
        $target_brand = $message->brand()->pluck('brands.id')->toArray();

        // shopが閲覧可能か確認
        $target_org['shops'] = [];
        $target_org['select'] = null;

        $selectedFlg = null;
        $chunkSize = 100; // チャンクサイズを設定
        $offset = 0;

        while (true) {
            // ブランドごとに処理を分けることで効率的なデータ取得を行います
            $shops = MessageShop::where('message_id', $message_id)
                ->whereIn('brand_id', $target_brand)
                ->offset($offset)
                ->limit($chunkSize)
                ->get(['shop_id', 'selected_flg']);

            if ($shops->isEmpty()) {
                break; // チャンク内にデータがない場合、ループを抜ける
            }

            foreach ($shops as $shop) {
                $target_org['shops'][] = $shop->shop_id;
                if (!$selectedFlg) {
                    $selectedFlg = $shop->selected_flg;
                }
            }

            $offset += $chunkSize; // 次のチャンクに進む
        }

        // MessageShopにshop_idが見つからない場合はMessageUserを確認
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

        $target_org['shops'] = array_unique($target_org['shops']); // 重複を削除

        // selectedFlgが存在する場合は設定
        if ($selectedFlg) {
            $target_org['select'] = $selectedFlg;
        }

        // shop_codeを基準にソートするためのカスタム比較関数を定義
        usort($all_shop_list, function ($a, $b) {
            return strcmp($a['shop_code'], $b['shop_code']);
        });

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


    public function update(PublishUpdateRequest $request, $message_id)
    {
        // メモリ制限を一時的に増加
        ini_set('memory_limit', '512M');
        // 300秒 (5分) に設定
        set_time_limit(300);

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

        // 手順を登録する
        $content_data = [];
        try {
            DB::beginTransaction();

            // 登録されているコンテンツが削除されていた場合、deleteフラグを立てる
            $content = $message->content()->whereNotIn('id', $this->getExistContentIds($request));
            $content->delete();

            //手順を登録する (編集)
            if (!empty($join_path_list)) {
                if (isset($request->file_name)) {
                    foreach ($request->file_name as $i => $file_name) {
                        if (!empty($request->file_path[$i])) {
                            // 登録されている手順を変更する
                            if (isset($request->content_id[$i])) {
                                $id = (int)$request->content_id[$i];
                                $message_content = MessageContent::find($id);

                                // 変更部分だけ取り込む
                                if (isset($message_content->content_url)) {
                                    if ($this->isChangedJoinFlg($join_path_list, $request->file_path ?? null) || $this->isChangedJoinFlg($join_flg_list, array_filter($request->join_flg ?? []))) {
                                        $message_content->content_name = $file_name;

                                        if ($this->isChangedFile($join_path_list[$i], isset($request->file_path[$i]) ? $request->file_path[$i] : null)) {
                                            $message_content->content_url = $request->file_path[$i] ? $this->registerFile($request->file_path[$i]) : null;
                                        } else {
                                            $message_content->content_url = $request->file_path[$i];
                                        }

                                        $message_content->thumbnails_url = ImageConverter::convert2image($message_content->content_url);
                                        $message_content->join_flg = $request->join_flg[$i];
                                        $message_content_changed_flg = true;

                                        $message_content->save();
                                    }
                                    // 手順の新規登録
                                } else {
                                    if (isset($file_name)) {
                                        $content_data[$i]['content_name'] = $file_name;
                                        $content_data[$i]['content_url'] = $this->registerFile($request->file_path[$i]);
                                        $content_data[$i]['thumbnails_url'] = ImageConverter::convert2image($content_data[$i]['content_url']);
                                        $content_data[$i]['join_flg'] = $request->join_flg[$i];
                                    }
                                }

                                // 手順の新規登録
                            } else {
                                if (isset($file_name)) {
                                    $content_data[$i]['content_name'] = $file_name;
                                    $content_data[$i]['content_url'] = $this->registerFile($request->file_path[$i]);
                                    $content_data[$i]['thumbnails_url'] = ImageConverter::convert2image($content_data[$i]['content_url']);
                                    $content_data[$i]['join_flg'] = $request->join_flg[$i];
                                }
                            }
                        }
                    }
                }
            } else {
                $message_path_list = Message::where('id', $message_id)->pluck('content_url')->toArray();
                foreach ($request->file_name as $i => $file_name) {
                    if (isset($request->file_path[$i])) {
                        $message_content = new MessageContent();
                        $message_content->message_id = $message_id;
                        $message_content->content_name = $file_name;

                        $existing_file_path = isset($message_path_list[$i]) ? $message_path_list[$i] : null;
                        if ($this->isChangedFile($existing_file_path, $request->file_path[$i] ?? null)) {
                            $message_content->content_url = $request->file_path[$i] ? $this->registerFile($request->file_path[$i]) : null;
                        } else {
                            $message_content->content_url = $request->file_path[$i];
                        }

                        $message_content->thumbnails_url = ImageConverter::convert2image($request->file_path[$i]);
                        $message_content->join_flg = $request->join_flg[$i];
                        $message_content->save();
                    }
                }
            }

            $message_contents = MessageContent::where('message_id', $message->id)->get()->toArray();
            if (!empty($message_contents)) {
                $message_contents = array_merge($message_contents, $content_data);

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

                    // 結合処理したか判定
                    if (!empty($join_file_list)) {
                        $msg_params['content_name'] = $join_file_list[0]['content_name'];
                        $msg_params['content_url'] = $join_file_list[0]['content_url'];
                    } else {
                        $msg_params['content_name'] = $request->file_name[0] ? $message_contents[0]['content_name'] : null;
                        $msg_params['content_url'] = $request->file_path[0] ? $message_contents[0]['content_url'] : null;
                    }
                    $msg_params['thumbnails_url'] = $request->file_path ? ImageConverter::convert2image($msg_params['content_url']) : null;

                    $message_changed_flg = true;
                } else {
                    $message_params['content_name'] = $message->content_name;
                    $message_params['content_url'] = $message->content_url;
                    $message_params['thumbnails_url'] = $message->thumbnails_url;
                }
            } else {

                $message_params['content_name'] = $message->content_name;
                $message_params['content_url'] = $message->content_url;
                $message_params['thumbnails_url'] = $message->thumbnails_url;
            }

            $message->update($msg_params);
            $message->roll()->sync($request->target_roll);

            MessageOrganization::where('message_id', $message_id)->delete();

            if (isset($request->organization['org5'][0])) {
                // カンマ区切りの文字列を配列に変換
                $org5_ids = explode(',', $request->organization['org5'][0]);
                foreach ($org5_ids as $org5_id) {
                    $message->organization()->create([
                        'message_id' => $message->id,
                        'organization1_id' => $admin->organization1_id,
                        'organization5_id' => $org5_id
                    ]);
                }
            }
            if (isset($request->organization['org4'][0])) {
                // カンマ区切りの文字列を配列に変換
                $org4_ids = explode(',', $request->organization['org4'][0]);
                foreach ($org4_ids as $org4_id) {
                    $message->organization()->create([
                        'message_id' => $message->id,
                        'organization1_id' => $admin->organization1_id,
                        'organization4_id' => $org4_id
                    ]);
                }
            }
            if (isset($request->organization['org3'][0])) {
                // カンマ区切りの文字列を配列に変換
                $org3_ids = explode(',', $request->organization['org3'][0]);
                foreach ($org3_ids as $org3_id) {
                    $message->organization()->create([
                        'message_id' => $message->id,
                        'organization1_id' => $admin->organization1_id,
                        'organization3_id' => $org3_id
                    ]);
                }
            }
            if (isset($request->organization['org2'][0])) {
                // カンマ区切りの文字列を配列に変換
                $org2_ids = explode(',', $request->organization['org2'][0]);
                foreach ($org2_ids as $org2_id) {
                    $message->organization()->create([
                        'message_id' => $message->id,
                        'organization1_id' => $admin->organization1_id,
                        'organization2_id' => $org2_id
                    ]);
                }
            }

            MessageShop::where('message_id', $message_id)->delete();

            // チャンクサイズを設定
            $chunkSize = 100;

            // message_shopにshop_idとmessage_idを格納
            if (isset($request->organization_shops)) {
                // カンマ区切りの文字列を配列に変換
                $organization_shops = explode(',', $request->organization_shops);

                // チャンクごとに処理
                $organization_shops_chunks = array_chunk($organization_shops, $chunkSize);

                foreach ($organization_shops_chunks as $organization_shops_chunk) {
                    $insertData = []; // バルクインサート用のデータ配列

                    foreach ($organization_shops_chunk as $_shop_id) {
                        $selectedFlg = null;
                        if (isset($request->select_organization['all']) && $request->select_organization['all'] === 'selected') {
                            $selectedFlg = 'all';
                        } elseif (isset($request->select_organization['store']) && $request->select_organization['store'] === 'selected') {
                            $selectedFlg = 'store';
                        } else {
                            $selectedFlg = 'store';
                        }

                        if ($selectedFlg) {
                            // ブランドごとにデータを取得
                            $brands = $request->brand;
                            $shops = Shop::where('id', $_shop_id)
                                ->whereIn('brand_id', $brands)
                                ->get(['id', 'brand_id']);

                            foreach ($shops as $shop) {
                                $insertData[] = [
                                    'message_id' => $message->id,
                                    'shop_id' => $shop->id,
                                    'brand_id' => $shop->brand_id,
                                    'selected_flg' => $selectedFlg,
                                    'created_at' => now(), // 追加
                                    'updated_at' => now()  // 追加
                                ];
                            }
                        }
                    }

                    if (!empty($insertData)) {
                        // バルクインサートを実行
                        MessageShop::insert($insertData);
                    }
                }
            }

            $message->brand()->sync($request->brand);
            $message->user()->sync(!isset($request->save) ? $this->getTargetUsersByShopId($request) : []);

            $message->content()->createMany($content_data);

            $tag_ids = [];
            foreach ($request->input('tag_name', []) as $tag_name) {
                $tag = MessageTagMaster::firstOrCreate(['name' => $tag_name]);
                $tag_ids[] = $tag->id;
            }
            $message->tag()->sync($tag_ids);
            DB::commit();
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

        return redirect()->route('admin.message.publish.index', ['brand' => session('brand_id')]);
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
        return Excel::download(
            new MessageListExport($request),
            $file_name
        );
    }

    // 業務連絡店舗のエクスポート（新規登録）
    public function newCsvStoreExport(Request $request)
    {
        // メモリ制限を一時的に増加
        ini_set('memory_limit', '512M');
        // 300秒 (5分) に設定
        set_time_limit(300);

        $organization1_id = (int) $request->input('organization1_id');
        $organization1 = Organization1::find($organization1_id);

        if (!$organization1) {
            return response()->json(['error' => 'Organization not found'], 404);
        }

        $file_name = $organization1->name . now()->format('_Y_m_d') . '.csv';

        return Excel::download(
            new MessageNewStoreListExport($organization1_id),
            $file_name
        );
    }

    // 業務連絡店舗のエクスポート（編集）
    public function editCsvStoreExport(Request $request)
    {
        // メモリ制限を一時的に増加
        ini_set('memory_limit', '512M');
        // 300秒 (5分) に設定
        set_time_limit(300);

        $message_id = (int) $request->input('message_id');
        $message = Message::find($message_id);
        $organization1 = Organization1::find($message->organization1_id);

        if (!$organization1) {
            return response()->json(['error' => 'Organization not found'], 404);
        }

        $file_name = $organization1->name . now()->format('_Y_m_d') . '.csv';

        return Excel::download(
            new MessageEditStoreListExport($message_id),
            $file_name
        );
    }

    // API
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

        $csv_content = file_get_contents($csv);
        $encoding = mb_detect_encoding($csv_content);
        if ($encoding == "UTF-8") {
            $shift_jis_content = mb_convert_encoding($csv_content, 'CP932', 'UTF-8');
            file_put_contents($csv, $shift_jis_content);
        }

        $organization = $this->getOrganizationForm($organization1);

        $csv_path = Storage::putFile('csv', $csv);
        Log::info("業連CSVインポート", [
            'csv_path' => $csv_path,
            'admin' => $admin
        ]);
        try {
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
            foreach ($collection[0] as $key => [
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
            ]) {
                $message = Message::where('number', $no)
                    ->where('organization1_id', $organization1)
                    ->firstOrFail();

                $brand_param = ($brand == "全て") ? array_column($organization, 'brand_id') : Brand::whereIn('name',  $this->strToArray($brand))->pluck('id')->toArray();
                // $org3_param = ($organization3 == "全て") ? array_column($organization, 'organization3_id') : Organization3::whereIn('name', $this->strToArray($organization3))->pluck('id')->toArray();
                // $org4_param = ($organization4 == "全て") ? array_column($organization, 'organization4_id') : Organization4::whereIn('name', $this->strToArray($organization4))->pluck('id')->toArray();
                // $org5_param = ($organization5 == "全て") ? array_column($organization, 'organization5_id') : Organization5::whereIn('name', $this->strToArray($organization5))->pluck('id')->toArray();

                $target_roll = $message->roll()->pluck('id')->toArray();

                array_push($array, [
                    'id' => $message->id,
                    'number' => $no,
                    'emergency_flg' => isset($emergency_flg),
                    'category' =>  $category ? MessageCategory::where('name', $category)->pluck('id')->first() : NULL,
                    'title' => $title,
                    'tag' => $this->tagImportParam([$tag1, $tag2, $tag3, $tag4, $tag5]),
                    'start_datetime' => $start_datetime,
                    'end_datetime' => $end_datetime,
                    'brand' => $brand_param,
                    // 'organization3' => $org3_param,
                    // 'organization4' => $org4_param,
                    // 'organization5' => $org5_param,
                    'roll' => $target_roll
                ]);

                file_put_contents($file_path, ceil((($key + 1) / $count) * 100));
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

        $log_id = DB::table('message_csv_logs')->insertGetId([
            'imported_datetime' => new Carbon('now'),
            'is_success' => false
        ]);

        try {
            DB::beginTransaction();
            foreach ($messages as $key => $ms) {
                $message = Message::find($ms["id"]);
                $message->number = $ms["number"];
                $message->emergency_flg = $ms["emergency_flg"];
                $message->category_id = $ms["category"];
                $message->title = $ms["title"];
                $message->tag()->sync($ms["tag"]);
                $message->start_datetime = $ms["start_datetime"];
                $message->end_datetime = $ms["end_datetime"];
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
            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }
    }

    // 店舗CSV インポート
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
            foreach ($collection[0] as $key => [
                $brand,
                $store_code,
                $store_name,
                $checked_store
            ]) {
                array_push($array, [
                    'brand' => $brand,
                    'store_code' => $store_code,
                    'store_name' => $store_name,
                    'checked_store' => $checked_store
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

    public function storeImport(Request $request)
    {
        $admin = session('admin');

        // インポートされたCSVの値
        $storesJson = $request->json();
        $csvStoreIds = [];

        foreach ($storesJson->all() as $store) {
            // checked_storeが"先行"であるかどうかを確認
            if (isset($store['checked_store']) && $store['checked_store'] === '先行') {
                $brand_id = Brand::where('name', $store['brand'])->pluck('id');
                $shopId = Shop::where('shop_code', $store['store_code'])->where('display_name', $store['store_name'])->where('brand_id', $brand_id)->pluck('id')->toArray();

                // 取得したidを$shopIds配列にマージ
                $csvStoreIds = array_merge($csvStoreIds, $shopId);
            }
        }

        try {
            // 業態一覧を取得する
            $brand_list = Brand::where('organization1_id', session('brand_id'))->get();

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
                ->where('organization1_id', session('brand_id'))
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

            // shopを取得する
            $all_shop_list = [];
            foreach ($organization_list as $index => $organization) {

                $organization_list[$index]['organization5_shop_list'] = [];
                $organization_list[$index]['organization4_shop_list'] = [];
                $organization_list[$index]['organization3_shop_list'] = [];
                $organization_list[$index]['organization2_shop_list'] = [];

                if (isset($organization['organization5_id'])) {
                    foreach ($brand_list as $brand) {
                        $shops = Shop::where('organization5_id', $organization['organization5_id'])
                            ->where('brand_id', $brand->id)
                            ->get()
                            ->toArray();

                        // shop_codeとdisplay_nameを合体
                        foreach ($shops as $shop) {

                            // すべてのshopリスト
                            $all_shop_list[] = [
                                'shop_id' => $shop['id'],
                                'shop_code' => $shop['shop_code'],
                                'display_name' => $shop['display_name'],
                            ];
                        }

                        $organization_list[$index]['organization5_shop_list'] = array_merge($organization_list[$index]['organization5_shop_list'], $shops);
                    }
                }
                if (isset($organization['organization4_id'])) {
                    foreach ($brand_list as $brand) {
                        $shops = Shop::where('organization4_id', $organization['organization4_id'])
                            ->where('brand_id', $brand->id)
                            ->get()
                            ->toArray();

                        // shop_codeとdisplay_nameを合体
                        foreach ($shops as $shop) {

                            // すべてのshopリスト
                            $all_shop_list[] = [
                                'shop_id' => $shop['id'],
                                'shop_code' => $shop['shop_code'],
                                'display_name' => $shop['display_name'],
                            ];
                        }

                        $organization_list[$index]['organization4_shop_list'] = array_merge($organization_list[$index]['organization4_shop_list'], $shops);
                    }
                }
                if (isset($organization['organization3_id'])) {
                    foreach ($brand_list as $brand) {
                        $shops = Shop::where('organization3_id', $organization['organization3_id'])
                            ->where('brand_id', $brand->id)
                            ->whereNull('organization4_id')
                            ->whereNull('organization5_id')
                            ->get()
                            ->toArray();

                        // shop_codeとdisplay_nameを合体
                        foreach ($shops as $shop) {

                            // すべてのshopリスト
                            $all_shop_list[] = [
                                'shop_id' => $shop['id'],
                                'shop_code' => $shop['shop_code'],
                                'display_name' => $shop['display_name'],
                            ];
                        }

                        $organization_list[$index]['organization3_shop_list'] = array_merge($organization_list[$index]['organization3_shop_list'], $shops);
                    }
                }
                if (isset($organization['organization2_id'])) {
                    foreach ($brand_list as $brand) {
                        $shops = Shop::where('organization2_id', $organization['organization2_id'])
                            ->where('brand_id', $brand->id)
                            ->whereNull('organization4_id')
                            ->whereNull('organization5_id')
                            ->get()
                            ->toArray();

                        // shop_codeとdisplay_nameを合体
                        foreach ($shops as $shop) {

                            // すべてのshopリスト
                            $all_shop_list[] = [
                                'shop_id' => $shop['id'],
                                'shop_code' => $shop['shop_code'],
                                'display_name' => $shop['display_name'],
                            ];
                        }

                        $organization_list[$index]['organization2_shop_list'] = array_merge($organization_list[$index]['organization2_shop_list'], $shops);
                    }
                }
            }

            // shop_codeを基準にソートするためのカスタム比較関数を定義
            usort($all_shop_list, function ($a, $b) {
                return strcmp($a['shop_code'], $b['shop_code']);
            });

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
        // メモリ制限を一時的に増加
        ini_set('memory_limit', '2048M');

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
        $shops_id = [];
        $target_user_data = [];

        // shopを取得する
        if (isset($organizations->organization_shops)) {
            $organization_shops = explode(',', $organizations->organization_shops);
            foreach ($organization_shops as $_shop_id) {
                foreach ($organizations->brand as $brand) {
                    $_shops_id = Shop::select('id')
                        ->where('id', $_shop_id)
                        ->where('brand_id', $brand)
                        ->get()
                        ->toArray();
                    $shops_id = array_merge($shops_id, $_shops_id);
                }
            }
        }

        // 取得したshopのリストからユーザーを取得する
        $target_users = User::select('id', 'shop_id')->whereIn('shop_id', $shops_id)->whereIn('roll_id', $organizations->target_roll)->get()->toArray();
        // ユーザーに業務連絡の閲覧権限を与える
        foreach ($target_users as $target_user) {
            $target_user_data[$target_user['id']] = ['shop_id' => $target_user['shop_id']];
        }

        return $target_user_data;
    }

    // 「手順」を登録するために加工する
    private function messageContentsParam($request): array
    {
        if (!(isset($request->file_name))) return [];
        $content_data = [];
        foreach ($request->file_name as $i => $file_name) {
            if (isset($file_name)) {
                $content_data[$i]['content_name'] = $file_name;
                $content_data[$i]['content_url'] = $this->registerFile($request->file_path[$i]);
                $content_data[$i]['thumbnails_url'] = ImageConverter::convert2image($content_data[$i]['content_url']);
                $content_data[$i]['join_flg'] = $request->join_flg[$i];
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
}
