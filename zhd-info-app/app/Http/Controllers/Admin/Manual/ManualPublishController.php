<?php

namespace App\Http\Controllers\Admin\Manual;

use App\Enums\PublishStatus;
use App\Exports\ManualListExport;
use App\Exports\ManualViewRateExport;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Repository\Organization1Repository;
use App\Http\Requests\Admin\Manual\FileUpdateApiRequest;
use App\Http\Requests\Admin\Manual\PublishStoreRequest;
use App\Http\Requests\Admin\Manual\PublishUpdateRequest;
use App\Imports\ManualCsvImport;
use App\Models\Brand;
use App\Models\Manual;
use App\Models\ManualCategoryLevel1;
use App\Models\ManualCategoryLevel2;
use App\Models\ManualContent;
use App\Models\ManualTagMaster;
use App\Models\Organization1;
use App\Models\Shop;
use App\Models\User;
use App\Utils\ImageConverter;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class ManualPublishController extends Controller
{
    public function index(Request $request)
    {
        $admin = session('admin');
        $new_category_list = ManualCategoryLevel2::query()
            ->select([
                'manual_category_level2s.id as id',
                DB::raw('concat(manual_category_level1s.name, "|", manual_category_level2s.name) as name')
            ])
            ->leftjoin('manual_category_level1s', 'manual_category_level1s.id', '=', 'manual_category_level2s.level1')
            ->get();

        $brand_list = $admin->getBrand();
        // request
        $new_category_id = $request->input('new_category');
        $status = PublishStatus::tryFrom($request->input('status'));
        $q = $request->input('q');
        $rate = $request->input('rate');
        $brand_id = $request->input('brand', $brand_list[0]->id);
        $publish_date = $request->input('publish-date');

        $organization1 = Brand::find($brand_id)->organization1;

        // セッションにデータを保存
        session()->put('brand_id', $brand_id);

        $sub = DB::table('manuals as m')
                    ->select([
                        'm.id as m_id',
                        DB::raw('
                            case
                                when (count(distinct b.name)) = 0 then ""
                                else group_concat(distinct b.name order by b.name)
                            end as b_name
                        ')
                    ])
                    ->leftjoin('manual_brand as m_b', 'm.id', 'm_b.manual_id')
                    ->leftjoin('brands as b', 'b.id', 'm_b.brand_id')
                    ->groupBy('m.id');
        $manual_list =
            Manual::query()
            ->with('create_user', 'updated_user', 'brand', 'tag', 'category_level1', 'category_level2')
            ->leftjoin('manual_user', 'manuals.id', '=', 'manual_id')
            ->leftjoin('manual_brand', 'manuals.id', '=', 'manual_brand.manual_id')
            ->leftjoin('brands', 'brands.id', '=', 'manual_brand.brand_id')
            ->leftJoinSub($sub, 'sub', function ($join) {
                $join->on('sub.m_id', '=', 'manuals.id');
            })
            ->select([
                'manuals.*',
                DB::raw('ifnull(sum(manual_user.read_flg),0) as read_users'),
                DB::raw('count(manual_user.user_id) as total_users'),
                DB::raw('round((sum(manual_user.read_flg) / count(manual_user.user_id)) * 100, 1) as view_rate'),
                DB::raw('sub.b_name as brand_name'),
            ])
            ->where('manuals.organization1_id', $organization1->id)
            ->whereNull('manual_brand.brand_id')
            ->orWhere('manual_brand.brand_id', '=', $brand_id)
            ->groupBy(DB::raw('manuals.id'))
            // 検索機能 キーワード
            ->when(isset($q), function ($query) use ($q) {
                $query->where(function ($query) use ($q) {
                    $query->whereLike('title', $q)
                        ->orWhereHas('tag', function ($query) use ($q) {
                            $query->where('name', $q);
                        });
                });
            })
            // 検索機能 状態
            ->when(isset($status), function ($query) use ($status) {
                switch ($status) {
                    case PublishStatus::Wait:
                        $query->waitManual();
                        break;
                    case PublishStatus::Publishing:
                        $query->publishingManual();
                        break;
                    case PublishStatus::Published:
                        $query->publishedManual();
                        break;
                    case PublishStatus::Editing:
                        $query->where('editing_flg', '=', true);
                        break;
                    default:
                        break;
                }
            })
            // 検索機能 カテゴリ
            ->when(isset($new_category_id), function ($query) use ($new_category_id) {
                $query->where('category_level2_id', $new_category_id);
            })
            ->when((isset($rate[0])|| isset($rate[1])), function ($query) use ($rate) {
                $min = isset($rate[0]) ? $rate[0] : 0;
                $max = isset($rate[1]) ? $rate[1] : 100;
                $query->havingRaw('view_rate between ? and ?', [$min, $max]);
            })
            ->when((isset($publish_date[0])), function ($query) use ($publish_date) {
                $query
                    ->where('start_datetime', '>=', $publish_date[0]);
            })
            ->when((isset($publish_date[1])), function ($query) use ($publish_date) {
                $query
                    ->where(function ($query) use ($publish_date) {
                        $query->where('end_datetime', '<=', $publish_date[1])
                            ->orWhereNull('end_datetime');
                    });
            })
            ->join('admin', 'create_admin_id', '=', 'admin.id')
            ->orderBy('manuals.number', 'desc')
            ->paginate(50)
            ->appends(request()->query());

        return view('admin.manual.publish.index', [
            'new_category_list' => $new_category_list,
            'manual_list' => $manual_list,
            'brand_list' => $brand_list,
            'organization1' => $organization1,
        ]);
    }

    public function show(Request $request, $manual_id)
    {
        $manual = Manual::where('id', $manual_id)
            ->withCount(['user as total_users'])
            ->withCount(['readed_user as read_users'])
            ->first();

        $organization1 = $manual->organization1;
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

        $shop_list = $manual
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

        $user_list = $manual
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

        return view('admin.manual.publish.show', [
            'manual' => $manual,
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
        $new_category_list = ManualCategoryLevel2::query()
            ->select([
                'manual_category_level2s.id as id',
                DB::raw('concat(manual_category_level1s.name, "|", manual_category_level2s.name) as name')
            ])
            ->leftjoin('manual_category_level1s', 'manual_category_level1s.id', '=', 'manual_category_level2s.level1')
            ->get();

        // ブランド一覧を取得する
        $brand_list = Brand::where('organization1_id', $organization1->id)->get();

        return view('admin.manual.publish.new', [
            'new_category_list' => $new_category_list,
            'brand_list' => $brand_list,
        ]);
    }

    public function store(PublishStoreRequest $request, Organization1 $organization1)
    {
        $validated = $request->validated();

        $admin = session('admin');
        $manual_params['title'] = $request->title;
        $manual_params['description'] = $request->description;
        $manual_params['category_level1_id'] = $this->level1CategoryParam($request->new_category_id);
        $manual_params['category_level2_id'] = $this->level2CategoryParam($request->new_category_id);
        $manual_params['start_datetime'] = $this->parseDateTime($request->start_datetime);
        $manual_params['end_datetime'] = $this->parseDateTime($request->end_datetime);
        $manual_params['content_name'] = $request->file_name;
        $manual_params['content_url'] = $request->file_path ? $this->registerFile($request->file_path) : null;
        $manual_params['thumbnails_url'] = $request->file_path ? ImageConverter::convert2image($manual_params['content_url']) : null;
        $manual_params['create_admin_id'] = $admin->id;
        $manual_params['organization1_id'] = $organization1->id;
        $manual_params['number'] = Manual::getCurrentNumber($organization1->id) + 1;
        $manual_params['editing_flg'] = isset($request->save);

        try {
            DB::beginTransaction();
            $manual = Manual::create($manual_params);
            $manual->updated_at = null;
            $manual->save();
            $manual->brand()->attach($request->brand);
            $manual->user()->attach(
                !isset($request->save) ? $this->targetUserParam($request->brand) : []
            );
            $manual->content()->createMany($this->manualContentsParam($request));

            if (isset($request->tag_name)) {
                $tag_ids = [];
                foreach ($request->tag_name as $tag_name) {
                    $tag = ManualTagMaster::firstOrCreate(['name' => $tag_name]);
                    $tag_ids[] = $tag->id;
                }
                $manual->tag()->attach($tag_ids);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->rollbackRegisterFile($request->file_path);
            $this->rollbackManualContentFile($request);
            Log::error($th->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'データベースエラーです');
        }

        return redirect()->route('admin.manual.publish.index', ['brand' => session('brand_id')] );
    }

    public function edit($manual_id)
    {
        $manual = Manual::find($manual_id);
        if (empty($manual)) return redirect()->route('admin.manual.publish.index', ['brand' => session('brand_id')] );

        $admin = session('admin');

        // 業態一覧を取得する
        $brand_list = Brand::where('organization1_id', $manual->organization1_id)->get();
        $target_brand = $manual->brand()->pluck('brands.id')->toArray();
        $contents = $manual->content()
            ->orderBy("order_no")
            ->get();

        $new_category_list = ManualCategoryLevel2::query()
                                ->select([
                                    'manual_category_level2s.id as id',
                                    DB::raw('concat(manual_category_level1s.name, "|", manual_category_level2s.name) as name')
                                ])
                                ->leftjoin('manual_category_level1s', 'manual_category_level1s.id', '=', 'manual_category_level2s.level1')
                                ->get();


        return view('admin.manual.publish.edit', [
            'manual' => $manual,
            'brand_list' => $brand_list,
            'target_brand' => $target_brand,
            'contents' => $contents,
            'new_category_list' => $new_category_list,

        ]);
    }

    public function update(PublishUpdateRequest $request, $manual_id)
    {
        $validated = $request->validated();

        // ファイルを移動したかフラグ
        $manual_changed_flg = false;
        $manualcontent_changed_flg = false;

        $admin = session('admin');
        $manual = Manual::find($manual_id);
        $manual_params['title'] = $request->title;
        $manual_params['description'] = $request->description;
        $manual_params['category_level1_id'] = $this->level1CategoryParam($request->new_category_id);
        $manual_params['category_level2_id'] = $this->level2CategoryParam($request->new_category_id);
        $manual_params['start_datetime'] = $this->parseDateTime($request->start_datetime);
        $manual_params['end_datetime'] = $this->parseDateTime($request->end_datetime);
        if($this->isChangedFile($manual->content_url, $request->file_path)) {
            $manual_params['content_name'] = $request->file_name;
            $manual_params['content_url'] = $request->file_path ? $this->registerFile($request->file_path) : null;
            $manual_params['thumbnails_url'] = $request->file_path ? ImageConverter::convert2image($manual_params['content_url']) : null;
            $manual_changed_flg = true;
        } else {
            $manual_params['content_name'] = $manual->content_name;
            $manual_params['content_url'] = $manual->content_url;
            $manual_params['thumbnails_url'] = $manual->thumbnails_url;
        }
        $manual_params['updated_admin_id'] = $admin->id;
        $manual_params['editing_flg'] = isset($request->save) ? true : false;


        // 手順を登録する
        $content_data = [];
        $count_order_no = 0;

        try {
            DB::beginTransaction();
        // 登録されているコンテンツが削除されていた場合、deleteフラグを立てる
        $manual = Manual::find($manual_id);
        $content = $manual->content()->whereNotIn('id', $this->getExistContentIds($request['manual_flow']));
        $content->delete();

        //手順を登録する
        if (isset($request['manual_flow'])) {
            foreach ($request['manual_flow'] as $i => $r) {
                // 登録されている手順を変更する
                if (isset( $r['content_id'])) {
                    $id = (int)$r['content_id'];
                    $manual_content = ManualContent::find($id);
                    $manual_content->title = $r['title'];
                    $manual_content->description = $r['detail'];
                    $manual_content->order_no = $i + 1;

                    // 変更部分だけ取り込む
                    if ($this->isChangedFile($manual_content->content_url, $r['file_path'])) {
                        $manual_content->content_name = $r['file_name'];
                        $manual_content->content_url = $this->registerFile($r['file_path']);
                        $manual_content->thumbnails_url = ImageConverter::convert2image($manual_content->content_url);
                        $manualcontent_changed_flg = true;
                    }

                    $manual_content->save();
                } else {
                    // 手順の新規登録
                    $content_data[$i]['title'] = $r['title'];
                    $content_data[$i]['description'] = $r['detail'];
                    $content_data[$i]['order_no'] = $i + 1;
                    if (isset($r['file_name']) && isset($r['file_path'])) {
                        $content_data[$i]['content_name'] = $r['file_name'];
                        $content_data[$i]['content_url'] = $this->registerFile($r['file_path']);
                        $content_data[$i]['thumbnails_url'] =
                            ImageConverter::convert2image($content_data[$i]['content_url']);
                    }
                }

            }
        }


            $manual->update($manual_params);
            $manual->brand()->sync($request->brand);
            $manual->user()->sync(
                !isset($request->save) ? $this->targetUserParam($request->brand) : []
            );
            $manual->content()->createMany($content_data);


            $tag_ids = [];
            foreach ($request->input('tag_name', []) as $tag_name) {
                $tag = ManualTagMaster::firstOrCreate(['name' => $tag_name]);
                $tag_ids[] = $tag->id;
            }
            $manual->tag()->sync($tag_ids);


            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            if ($manual_changed_flg) $this->rollbackRegisterFile($request->file_path);
            if ($manualcontent_changed_flg) $this->rollbackManualContentFile($request);
            Log::error($th->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', '入力エラーがあります');
        }

        return redirect()->route('admin.manual.publish.index', ['brand' => session('brand_id')] );
    }

    public function detail($manual_id)
    {
        $manual = Manual::find($manual_id);
        $contents = $manual->content()
                            ->orderBy("order_no", "desc")
                            ->get();
        $target_org1 = $manual->organization1()->pluck('organization1.id')->toArray();
        $target_shop = Shop::whereIn("organization4_id", $target_org1)->get();

        return view('admin.manual.publish.edit',[
            "manual" => $manual,
            "contents" => $contents,
            "target_shop" => $target_shop
        ]);
    }

    public function stop(Request $request)
    {
        $data = $request->json()->all();
        $manual_id = $data['manual_id'];
        $manual = Manual::find($manual_id)->first();
        $status = $manual->status;
        //掲載終了だと、エラーを返す
        if($status == PublishStatus::Published) return response()->json(['message' => 'すでに掲載終了しています']);

        $admin = session('admin');
        $now = Carbon::now();
        Manual::whereIn('id', $manual_id)->update([
            'end_datetime' => $now,
            'updated_admin_id' => $admin->id,
            'editing_flg' => false
        ]);

        return response()->json(['message' => '停止しました']);
    }

    // 詳細画面のエクスポート
    public function export(Request $request, $manual_id)
    {
        $now = new Carbon('now');
        $now->format('Y_m_d-H_i_s');
        return Excel::download(
            new ManualViewRateExport($manual_id, $request),
            $now->format('Y_m_d-H_i') . '-動画マニュアルエクスポート.csv'
        );
    }

    // マニュアル一覧のエクスポート
    public function exportList(Request $request)
    {
        $admin = session('admin');
        $brand_id = $request->input('brand', $admin->firstBrand()->id);
        $organization1 = Brand::find($brand_id)->organization1;

        $file_name = '動画マニュアル_' . $organization1->name . now()->format('_Y_m_d') . '.csv';
        return Excel::download(
            new ManualListExport($request),
            $file_name
        );
    }

    public function fileUpload(FileUpdateApiRequest $request)
    {
        $validated = $request->validated();

        $file = $request->file;
        // $file->move(sys_get_temp_dir(),uniqid() . '.' . $file->getClientOriginalExtension());
        $file_path = Storage::putFile('/tmp', $file);
        $file_name = $file->getClientOriginalName();

        return  response()->json([
                    'content_name' => $file_name,
                    'content_url' => $file_path
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

        $brands = $this->getBrandNameArray($organization1);

        $csv_path = Storage::putFile('csv', $csv);
        Log::info("マニュアルCSVインポー", [
            'csv_path' => $csv_path,
            'admin' => $admin
        ]);

        try {
            Excel::import(new ManualCsvImport($organization1, $brands), $csv, \Maatwebsite\Excel\Excel::CSV);
            $collection = Excel::toCollection(new ManualCsvImport($organization1, $brands), $csv, \Maatwebsite\Excel\Excel::CSV);

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
                $cateory,
                $title,
                $tag1,
                $tag2,
                $tag3,
                $tag4,
                $tag5,
                $start_datetime,
                $end_datetime,
                $status,
                $brand,
                $description
            ]) {
                $manual = Manual::where('number', $no)
                    ->where('organization1_id', $organization1)
                    ->firstOrFail();

                $CONTENTS_RAW_NUMBER = 13;
                $row_contents = $collection[0][$key]->slice($CONTENTS_RAW_NUMBER);

                $contents = [];
                if (isset($manual->content)) {
                    foreach ($manual->content as $key => $content) {
                        $content = [];
                        $content["title"] = $row_contents[($key * 2) + 13] ?? '';
                        $content["description"] = $row_contents[($key * 2) + 14] ?? '';
                        array_push($contents, $content);
                    }
                }
                $brand_param = ($brand == "全て") ? $brands : Brand::whereIn('name',  $this->strToArray($brand))->pluck('id')->toArray();
                $category_array = isset($cateory) ? explode('|', $cateory) : null;
                $category_level1_name = isset($category_array[0]) ? str_replace(' ', '', trim($category_array[0], "\"")) : NULL;
                $category_level2_name = isset($category_array[1]) ? str_replace(' ', '', trim($category_array[1], "\"")) : NULL;

                array_push($array, [
                    'id' => $manual->id,
                    'number' => $no,
                    'category_level1_id' =>  isset($category_array[0]) ? ManualCategoryLevel1::where('name', $category_level1_name)->pluck('id')->first() : NULL,
                    'category_level2_id' =>  isset($category_array[1]) ? ManualCategoryLevel2::where('name', $category_level2_name)->pluck('id')->first() : NULL,
                    'title' => $title,
                    'tag' => $this->tagImportParam([$tag1, $tag2, $tag3, $tag4, $tag5]),
                    'start_datetime' => $start_datetime,
                    'end_datetime' => $end_datetime,
                    'brand' => $brand_param,
                    'description' => $description,
                    'contents' => $contents
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
                'message' => $th->getMessage()
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
        $manuals = $request->json();

        $admin = session('admin');

        $log_id = DB::table('manual_csv_logs')->insertGetId([
            'imported_datetime' => new Carbon('now'),
            'is_success' => false
        ]);

        try {
            DB::beginTransaction();
            foreach ($manuals as $key => $ml) {
                $manual = Manual::find($ml["id"]);
                $manual->number = $ml["number"];
                $manual->description = $ml["description"];
                $manual->category_level1_id = $ml["category_level1_id"];
                $manual->category_level2_id = $ml["category_level2_id"];
                $manual->title = $ml["title"];
                $manual->tag()->sync($ml["tag"]);
                $manual->start_datetime = $ml["start_datetime"];
                $manual->end_datetime = $ml["end_datetime"];
                if ($manual->isDirty()) $manual->updated_admin_id = $admin->id;
                $manual->save();

                $manual->brand()->sync($ml["brand"]);

                if (isset($manual->content)) {
                    foreach ($manual->content as $key => $content) {
                        $content_title = $ml["contents"][$key]["title"] ?? '';
                        $content_description = $ml["contents"][$key]["description"]  ?? '';
                        $content->title = $content_title;
                        $content->description = $content_description;
                        $content->save();
                    }
                }

                // ユーザー配信
                if(!$manual->editing_flg) {
                    $origin_user = $manual->user()->pluck('id')->toArray();
                    $new_target_user = $this->targetUserParam($ml["brand"]);
                    $new_target_user_id = array_keys($new_target_user);
                    $detach_user = array_diff($origin_user, $new_target_user_id);
                    $attach_user = array_diff($new_target_user_id, $origin_user);

                    $manual->user()->detach($detach_user);
                    foreach ($attach_user as $key => $user) {
                        $manual->user()->attach([$user => $new_target_user[$user]]);
                    }
                }
            }

            DB::table('manual_csv_logs')
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

    private function parseDateTime($datetime)
    {
        return (!isset($datetime)) ? null : Carbon::parse($datetime, 'Asia/Tokyo');
    }

    private function isChangedFile($current_file_path, $next_file_path): Bool {
        $currnt_path = $current_file_path? basename($current_file_path) : null;
        $next_path = $next_file_path? basename($next_file_path) : null;

        return !($currnt_path == $next_path);
    }

    private function targetUserParam($brand): Array {
        // manual_userに該当のユーザーを登録する
        $target_users_data = [];
        // 該当のショップID
        $shops_id = Shop::select('id')->whereIn('brand_id', $brand)->get()->toArray();
        // 該当のユーザー
        $target_users = User::select('id', 'shop_id')->whereIn('shop_id', $shops_id)->get()->toArray();
        foreach ($target_users as $target_user) {
            $target_users_data[$target_user['id']] = ['shop_id' => $target_user['shop_id']];
        }
        return $target_users_data;
    }

    // 「手順」を登録するために加工する
    private function manualContentsParam($request): Array {
        if(!(isset($request['manual_flow']))) return [];

        $content_data = [];
        foreach ($request['manual_flow'] as $i => $r) {
            $content_data[$i]['title'] = $r['title'];
            $content_data[$i]['description'] = $r['detail'];
            $content_data[$i]['order_no'] = $i + 1;

            if (isset($r['file_name']) && isset($r['file_path'])) {
                $content_data[$i]['content_name'] = $r['file_name'];
                $content_data[$i]['content_url'] = $this->registerFile($r['file_path']);
                $content_data[$i]['thumbnails_url'] =
                ImageConverter::convert2image($content_data[$i]['content_url']);
            }
        }
        return $content_data;
    }

    private function rollbackManualContentFile($request): Void {
        if(!(isset($request['manual_flow']))) return;
        foreach ($request['manual_flow'] as $i => $r) {
            if (isset($r['file_name']) && isset($r['file_path'])) {
                $current_path = storage_path('app/' . $r['file_path']);
                $next_path = public_path('uploads/' . basename($r['file_path']));
                try{
                    rename($next_path, $current_path);
                }catch(\Throwable $th){
                    Log::error($th->getMessage());
                }
            }
        }
    }

    private function registerFile($request_file_path): ?String {
        $content_url = 'uploads/' . basename($request_file_path);
        $current_path = storage_path('app/' . $request_file_path);
        $next_path = public_path($content_url);
        rename($current_path, $next_path);
        return $content_url;

    }
    private function rollbackRegisterFile($request_file_path): Void
    {
        if(!(isset($request_file_path))) return;
        $content_url = 'uploads/' . basename($request_file_path);
        $current_path = storage_path('app/' . $request_file_path);
        $next_path = public_path($content_url);
        try {
            rename($next_path, $current_path);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
        return;
    }

    private function getExistContentIds($request_manual_flow) : Array {
        if(!isset($request_manual_flow)) return [];

        $content_ids = [];
        foreach ($request_manual_flow as $i => $r) {
            if (isset($r['content_id'])) {
                $id = (int)$r['content_id'];
                $content_ids[] = $id;
            }
        }
        return $content_ids;

    }

    private function level1CategoryParam($level2_category_id): ?Int {
        if(!isset($level2_category_id) || $level2_category_id == "null") return null;
        return ManualCategoryLevel2::find($level2_category_id)->level1;
    }

    private function level2CategoryParam($level2_category_id): ?Int {
        if($level2_category_id == "null") return null;
        return $level2_category_id;
    }

    private function tagImportParam(?array $tags): array
    {
        if (!isset($tags)) return [];

        $tags_pram = [];
        foreach ($tags as $key => $tag_name) {
            if (!isset($tag_name)) continue;
            $tag = ManualTagMaster::firstOrCreate(['name' => trim($tag_name, "\"")]);
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
    private function getBrandNameArray(Int $org1_id): array
    {
        return Brand::query()
            ->where('organization1_id', '=', $org1_id)
            ->pluck('name')
            ->toArray();
    }
}


