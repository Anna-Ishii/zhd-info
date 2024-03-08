<?php

namespace App\Http\Controllers\Admin\Manual;

use App\Enums\PublishStatus;
use App\Exports\ManualListExport;
use App\Exports\ManualViewRateExport;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Repository\AdminRepository;
use App\Http\Repository\Organization1Repository;
use App\Http\Requests\Admin\Manual\FileUpdateApiRequest;
use App\Http\Requests\Admin\Manual\PublishStoreRequest;
use App\Http\Requests\Admin\Manual\PublishUpdateRequest;
use App\Imports\ManualCsvImport;
use App\Models\Manual;
use App\Models\ManualCategoryLevel1;
use App\Models\ManualCategoryLevel2;
use App\Models\ManualContent;
use App\Models\ManualTagMaster;
use App\Models\Shop;
use App\Models\User;
use App\Utils\ImageConverter;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class ManualPublishController extends Controller
{
    public function index(Request $request)
    {
        $admin = session('admin');
        $_brand = $admin->organization1->brand()->orderBy('id', 'asc');
        $brands = $_brand->pluck('name')->toArray();
        $brand_list = $_brand->get();
        $new_category_list = ManualCategoryLevel2::query()
            ->select([
                'manual_category_level2s.id as id',
                DB::raw('concat(manual_category_level1s.name, "|", manual_category_level2s.name) as name')
            ])
            ->leftjoin('manual_category_level1s', 'manual_category_level1s.id', '=', 'manual_category_level2s.level1')
            ->get();

        // request
        $new_category_id = $request->input('new_category');
        $status = PublishStatus::tryFrom($request->input('status'));
        $q = $request->input('q');
        $rate = $request->input('rate');
        $brand_id = $request->input('brand');
        $publish_date = $request->input('publish-date');

        $manual_list =
            Manual::query()
            ->with('create_user', 'updated_user', 'brand', 'tag')
            ->leftjoin('manual_user', 'manuals.id', '=', 'manual_id')
            ->selectRaw('
                        manuals.*,
                        ifnull(sum(manual_user.read_flg),0) as read_users, 
                        count(manual_user.user_id) as total_users,
                        round((sum(manual_user.read_flg) / count(manual_user.user_id)) * 100, 1) as view_rate
                        ')
            ->where('manuals.organization1_id', $admin->organization1_id)
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
            ->when(isset($brand_id), function ($query) use ($brand_id) {
                $query->leftjoin('manual_brand', 'manuals.id', '=', 'manual_brand.manual_id')
                    ->where('manual_brand.brand_id', '=', $brand_id);
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
            'brands' => $brands,
        ]);
    }

    public function show(Request $request, $manual_id)
    {
        $admin = session('admin');
        $manual = Manual::where('id', $manual_id)
            ->withCount(['user as total_users'])
            ->withCount(['readed_user as read_users'])
            ->first();

        $_brand = $admin->organization1->brand()->orderBy('id', 'asc');
        $brands = $_brand->pluck('name')->toArray();
        $brand_list = $_brand->get();
        $org3_list = Organization1Repository::getOrg3($admin->organization1_id);
        $org4_list = Organization1Repository::getOrg4($admin->organization1_id);
        $org5_list = Organization1Repository::getOrg5($admin->organization1_id);

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
            ->with(['shop', 'shop.organization3', 'shop.organization4', 'shop.organization5'])
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

    public function new()
    {
        $admin = session("admin");

        $category_list = ManualCategory::all();
        $new_category_list = ManualCategoryLevel2::query()
            ->select([
                'manual_category_level2s.id as id',
                DB::raw('concat(manual_category_level1s.name, "|", manual_category_level2s.name) as name')
            ])
            ->leftjoin('manual_category_level1s', 'manual_category_level1s.id', '=', 'manual_category_level2s.level1')
            ->get();
        $brand_list = AdminRepository::getBrands($admin);

        return view('admin.manual.publish.new', [
            'category_list' => $category_list,
            'new_category_list' => $new_category_list,
            'brand_list' => $brand_list,
        ]);
    }

    public function store(PublishStoreRequest $request)
    {
        $validated = $request->validated();

        $admin = session('admin');
        $manual_params['title'] = $request->title;
        $manual_params['description'] = $request->description;
        $manual_params['category_id'] = $request->category_id;
        $manual_params['category_level1_id'] = $this->level1CategoryParam($request->new_category_id);
        $manual_params['category_level2_id'] = $this->level2CategoryParam($request->new_category_id);
        $manual_params['start_datetime'] = $this->parseDateTime($request->start_datetime);
        $manual_params['end_datetime'] = $this->parseDateTime($request->end_datetime);
        $manual_params['content_name'] = $request->file_name;
        $manual_params['content_url'] = $request->file_path ? $this->registerFile($request->file_path) : null;
        $manual_params['thumbnails_url'] = $request->file_path ? ImageConverter::convert2image($manual_params['content_url']) : null;
        $manual_params['create_admin_id'] = $admin->id;
        $manual_params['organization1_id'] = $admin->organization1_id;
        $manual_params['number'] = Manual::getCurrentNumber($admin->organization1_id) + 1;
        $manual_params['editing_flg'] = isset($request->save);

        try {
            DB::beginTransaction();
            $manual = Manual::create($manual_params);
            $manual->updated_at = null;
            $manual->save();
            $manual->brand()->attach($request->brand);
            $manual->user()->attach(
                !isset($request->save) ? $this->targetUserParam($request) : [] 
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
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'データベースエラーです');
        }

        return redirect()->route('admin.manual.publish.index');
    }

    public function edit($manual_id)
    {
        $manual = Manual::find($manual_id);
        if (empty($manual)) return redirect()->route('admin.manual.publish.index');

        $admin = session('admin');
        // ログインユーザーとは違う業態のものは編集画面を出さない
        if($manual->organization1_id != $admin->organization1_id) return redirect()->route('admin.manual.publish.index');

        $category_list = ManualCategory::all();
        // 業態一覧を取得する
        $brand_list = AdminRepository::getBrands($admin);
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
            'category_list' => $category_list,
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
        $manual_params['category_id'] = $request->category_id;
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
                !isset($request->save) ? $this->targetUserParam($request) : [] 
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
            return redirect()
                ->back()
                ->withInput()
                ->with('error', '入力エラーがあります');
        }

        return redirect()->route('admin.manual.publish.index');
    }

    public function detail($manual_id)
    {
        $manual = Manual::find($manual_id);
        $contents = $manual->content()
                            ->orderBy("order_no", "desc")
                            ->get();
        $target_user = $manual->user;
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

    public function export(Request $request, $manual_id)
    {
        $now = new Carbon('now');
        $now->format('Y_m_d-H_i_s');
        return Excel::download(
            new ManualViewRateExport($manual_id, $request),
            $now->format('Y_m_d-H_i') . '-動画マニュアルエクスポート.csv'
        );
    }

    public function exportList(Request $request)
    {
        $admin = session('admin');
        $organization1 = $admin->organization1->name;
        $now = new Carbon('now');
        $file_name = '動画マニュアル_' . $organization1 . $now->format('_Y_m_d') . '.csv';
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

    public function import(Request $request)
    {
        $csv = $request->file;
        $csv_path = Storage::putFile('csv', $csv);

        $csv_content = file_get_contents($csv);
        $encoding = mb_detect_encoding($csv_content);
        if ($encoding == "UTF-8") {
            $shift_jis_content = mb_convert_encoding($csv_content, 'CP932', 'UTF-8');
            file_put_contents($csv, $shift_jis_content);
        }

        $admin = session('admin');
        Log::info("マニュアルCSVインポート",[
            'csv_path' => $csv_path,
            'admin' => $admin
        ]);
        $log_id = DB::table('manual_csv_logs')->insertGetId([
            'imported_datetime' => new Carbon('now'),
            'is_success' => false
        ]);
        

        DB::beginTransaction();
        try {
            Excel::import(new ManualCsvImport, $csv, \Maatwebsite\Excel\Excel::CSV);
            // $this->importMessage($messages[0], $admin->organization1);
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
        } catch (ValidationException $e) {
            $failures = $e->failures();

            $errorMessage = [];
            foreach ($failures as $index => $failure) {
                $errorMessage[$index]["row"] = $failure->row(); // row that went wrong
                $errorMessage[$index]["attribute"] = $failure->attribute(); // either heading key (if using heading row concern) or column index
                $errorMessage[$index]["errors"] = $failure->errors(); // Actual error messages from Laravel validator
                $errorMessage[$index]["value"] = $failure->values(); // The values of the row that has failed.
            }

            return response()->json([
                'error' => 'Validation failed',
                'error_message' => $errorMessage
            ], 422);
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

    private function hasManualFile($request): Bool {
        if(!isset($request->file_name) || !isset($request->file_path)) return false;
        return true;
    }

    private function hasManualContentFile($request): Bool {
        if(isset($request['manual_flow'])){
            foreach ($request['manual_flow'] as $i => $r) {
                if(!isset($r['file_name']) || !isset($r['file_path'])){
                    return false;
                }
            }
        }
        return true;
    }

    private function isChangedFile($current_file_path, $next_file_path): Bool {
        $currnt_path = $current_file_path? basename($current_file_path) : null;
        $next_path = $next_file_path? basename($next_file_path) : null;

        return !($currnt_path == $next_path);
    }

    private function targetUserParam($request): Array {
        // manual_userに該当のユーザーを登録する
        $target_users_data = [];
        // 該当のショップID
        $shops_id = Shop::select('id')->whereIn('brand_id', $request->brand)->get()->toArray();
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
                rename($next_path, $current_path);
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
        rename($next_path, $current_path);
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
}


