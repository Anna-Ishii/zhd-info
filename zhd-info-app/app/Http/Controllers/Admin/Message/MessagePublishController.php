<?php

namespace App\Http\Controllers\Admin\Message;

use Carbon\Carbon;
use App\Enums\PublishStatus;
use App\Exports\MessageListExport;
use App\Exports\MessageViewRateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Message\PublishStoreRequest;
use App\Http\Requests\Admin\Message\PublishUpdateRequest;
use App\Models\MessageCategory;
use App\Models\Message;
use App\Models\Roll;
use App\Models\Shop;
use App\Models\User;
use App\Http\Repository\AdminRepository;
use App\Http\Repository\Organization1Repository;
use App\Http\Requests\Admin\Message\FileUpdateApiRequest;
use App\Imports\MessageCsvImport;
use App\Models\Brand;
use App\Models\ManualCategory;
use App\Models\ManualTagMaster;
use App\Models\MessageOrganization;
use App\Models\MessageTagMaster;
use App\Models\Organization1;
use App\Models\Organization3;
use App\Models\Organization4;
use App\Models\Organization5;
use App\Utils\ImageConverter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class MessagePublishController extends Controller
{
    public function index(Request $request)
    {
        $admin = session('admin');
        $category_list = MessageCategory::all();
        $_brand = $admin->organization1->brand()->orderBy('id', 'asc');
        $brands = $_brand->pluck('name')->toArray();
        $brand_list = $_brand->get();

        // request
        $category_id = $request->input('category');
        $status = PublishStatus::tryFrom($request->input('status'));
        $q = $request->input('q');
        $rate = $request->input('rate');
        $brand_id = $request->input('brand');
        $label = $request->input('label');
        $publish_date = $request->input('publish-date');
        $message_list =
            Message::query()
                ->with('category', 'create_user', 'updated_user', 'brand', 'tag')
                ->leftjoin('message_user','messages.id', '=', 'message_id')
                ->selectRaw('
                            messages.*,
                            ifnull(sum(message_user.read_flg),0) as read_users, 
                            count(message_user.user_id) as total_users,
                            round((sum(message_user.read_flg) / count(message_user.user_id)) * 100, 1) as view_rate
                        ')
                ->where('messages.organization1_id', $admin->organization1_id)
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
                ->when(isset($brand_id), function ($query) use ($brand_id) {
                    $query->leftjoin('message_brand', 'messages.id', '=', 'message_brand.message_id')
                    ->where('message_brand.brand_id', '=', $brand_id);
                })
                ->when(isset($label), function ($query) use ($label) {
                    $query->where('emergency_flg', true);
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
                            $query->where('end_datetime', '<=',$publish_date[1])
                                ->orWhereNull('end_datetime');
                        });
                })
                ->join('admin', 'create_admin_id', '=', 'admin.id')
                ->orderBy('messages.number', 'desc')               
                ->paginate(50)
                ->appends(request()->query());

        $csv_log = DB::table('message_csv_logs')
                                ->select('imported_datetime')
                                ->orderBy('id', 'desc')
                                 ->limit(1)
                                 ->pluck('imported_datetime');
        view()->share('message_csv_log', isset($csv_log[0]) ? Carbon::parse($csv_log[0])->isoFormat('YYYY/MM/DD(ddd) HH:mm') :NULL);

        return view('admin.message.publish.index', [
            'category_list' => $category_list,
            'message_list' => $message_list,
            'brand_list' => $brand_list,
            'brands' => $brands,
        ]);
    }

    public function show(Request $request, $message_id)
    {
        $admin = session('admin');
        $message = Message::where('id', $message_id)
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
        $shop_code = $request->input('shop_code');
        $shop_name = $request->input('shop_name');
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
                        ->when(isset($shop_code), function ($query) use ($shop_code) {
                            $query->where('shop_code', $shop_code);
                        })
                        ->when(isset($shop_name), function ($query) use ($shop_name) {
                            $query->whereLike('name', $shop_name);
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
                        ->with(['shop', 'shop.organization3','shop.organization4', 'shop.organization5'])
                        ->when(isset($read_flg), function ($query) use ($read_flg) {
                            if($read_flg == 'true') $query->where('read_flg', true);
                            if($read_flg == 'false') $query->where('read_flg', false);
                        })
                        ->when((isset($readed_date[0])), function ($query) use ($readed_date) {
                            $query
                                ->where('readed_datetime', '>=', $readed_date[0]);
                        })
                        ->when((isset($readed_date[1])), function ($query) use ($readed_date) {
                            $query
                                ->where(function ($query) use ($readed_date) {
                                    $query->where('readed_datetime', '<=', $readed_date[1]);
                                });
                        })
                        ->wherePivotIn('shop_id', $shop_list)
                        ->paginate(50);

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

    public function new()
    {
        $admin = session("admin");
        $category_list = MessageCategory::all();

        $target_roll_list = Roll::get(); //「一般」を使わない場合 Roll::where('id', '!=', '1')->get();
        // ブランド一覧を取得する
        $brand_list = AdminRepository::getBrands($admin);
        
        $organization_list = [];
        $organization_list = Shop::query()
                                ->leftjoin('organization2', 'organization2_id', '=', 'organization2.id')
                                ->leftjoin('organization3', 'organization3_id', '=', 'organization3.id')
                                ->leftjoin('organization4', 'organization4_id', '=', 'organization4.id')
                                ->leftjoin('organization5', 'organization5_id', '=', 'organization5.id')
                                ->distinct('organization4_id')
                                ->distinct('organization5_id')
                                ->select('organization2_id', 'organization2.name as organization2_name', 
                                         'organization3_id', 'organization3.name as organization3_name',
                                         'organization4_id', 'organization4.name as organization4_name',
                                         'organization5_id', 'organization5.name as organization5_name')
                                ->where('organization1_id', $admin->organization1_id)
                                ->orderByRaw('organization2_id is null asc')
                                ->orderByRaw('organization3_id is null asc')
                                ->orderByRaw('organization4_id is null asc')
                                ->orderByRaw('organization5_id is null asc')
                                ->orderBy("organization2_id", "asc")
                                ->orderBy("organization3_id", "asc")
                                ->orderBy("organization4_id", "asc")
                                ->orderBy("organization5_id", "asc")
                                ->get()
                                ->toArray();

        $organization_type = 5;  // ブロックを表示する
        if (!Organization1Repository::isExistOrg5($admin->organization1_id)) {
            $organization_type = 4; // エリアを表示する
        }

        return view('admin.message.publish.new', [
            'category_list' => $category_list,
            'target_roll_list' => $target_roll_list,
            'brand_list' => $brand_list,
            'organization_type' => $organization_type,
            'organization_list' => $organization_list,
        ]);
    }

    public function store(PublishStoreRequest $request)
    {
        $validated = $request->validated();

        // ファイルを移動したかフラグ
        $message_changed_flg = false;

        $admin = session('admin');
        $msg_params['title'] = $request->title;
        $msg_params['category_id'] = $request->category_id;
        $msg_params['emergency_flg'] = ($request->emergency_flg == 'on' ? true : false);
        $msg_params['start_datetime'] = $this->parseDateTime($request->start_datetime);
        $msg_params['end_datetime'] = $this->parseDateTime($request->end_datetime);
        $msg_params['content_name'] = $request->file_name;
        $msg_params['content_url'] = $request->file_path ? $this->registerFile($request->file_path) : null;
        $msg_params['thumbnails_url'] = $request->file_path ? ImageConverter::convert2image($msg_params['content_url']) : null;
        $msg_params['create_admin_id'] = $admin->id;
        $msg_params['organization1_id'] = $admin->organization1_id;
        $number = Message::where('organization1_id', $admin->organization1_id)->max('number');
        $msg_params['number'] = (is_null($number)) ? 1 : $number + 1;
        $msg_params['thumbnails_url'] = ImageConverter::pdf2image($msg_params['content_url']);
        $msg_params['editing_flg'] = isset($request->save) ? true : false;

        // ブロックかエリアかを判断するタイプ
        $organization_type = $request->organization_type;

        try {
            DB::beginTransaction();
            $message = Message::create($msg_params);
            $message->updated_at = null;
            $message->save();
            $message->roll()->attach($request->target_roll);

            if (isset($request->organization['org5'])) {
                foreach ($request->organization['org5'] as $org5_id) {
                    $message->organization()->create([
                        'message_id' => $message->id,
                        'organization1_id' => $admin->organization1_id,
                        'organization5_id' => $org5_id
                    ]);
                }
            }
            if (isset($request->organization['org4'])) {
                foreach ($request->organization['org4'] as $org4_id) {
                    $message->organization()->create([
                        'message_id' => $message->id,
                        'organization1_id' => $admin->organization1_id,
                        'organization4_id' => $org4_id
                    ]);
                }
            }
            if (isset($request->organization['org3'])) {
                foreach ($request->organization['org3'] as $org3_id) {
                    $message->organization()->create([
                        'message_id' => $message->id,
                        'organization1_id' => $admin->organization1_id,
                        'organization3_id' => $org3_id
                    ]);
                }
            }
            if (isset($request->organization['org2'])) {
                foreach ($request->organization['org2'] as $org2_id) {
                    $message->organization()->create([
                        'message_id' => $message->id,
                        'organization1_id' => $admin->organization1_id,
                        'organization2_id' => $org2_id
                    ]);
                }
            }

            $message->brand()->attach($request->brand);
            $message->user()->attach(
                !isset($request->save) ? $this->targetUserParam($request) : []
            );

            if(isset($request->tag_name)) {
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
            if ($message_changed_flg) $this->rollbackRegisterFile($request->file_path);
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'データベースエラーです');
        }

        return redirect()->route('admin.message.publish.index');
    }

    public function edit($message_id)
    {
        $message = Message::find($message_id);
        if(empty($message)) return redirect()->route('admin.message.publish.index');

        $admin = session('admin');
        // ログインユーザーとは違う業態のものは編集画面を出さない
        if ($message->organization1_id != $admin->organization1_id) return redirect()->route('admin.message.publish.index');

        $category_list = MessageCategory::all();

        $target_roll_list = Roll::get();
        // 業態一覧を取得する
        $brand_list = AdminRepository::getBrands($admin);

        $organization_list = [];
        $organization_list = Shop::query()
                                ->leftjoin('organization2', 'organization2_id', '=', 'organization2.id')
                                ->leftjoin('organization3', 'organization3_id', '=', 'organization3.id')
                                ->leftjoin('organization4', 'organization4_id', '=', 'organization4.id')
                                ->leftjoin('organization5', 'organization5_id', '=', 'organization5.id')
                                ->distinct('organization4_id')
                                ->distinct('organization5_id')
                                ->select('organization2_id', 'organization2.name as organization2_name',
                                         'organization3_id', 'organization3.name as organization3_name',
                                         'organization4_id', 'organization4.name as organization4_name',
                                         'organization5_id', 'organization5.name as organization5_name')
                                ->where('organization1_id', $admin->organization1_id)
                                ->orderByRaw('organization2_id is null asc')
                                ->orderByRaw('organization3_id is null asc')
                                ->orderByRaw('organization4_id is null asc')
                                ->orderByRaw('organization5_id is null asc')
                                ->orderBy("organization2_id", "asc")
                                ->orderBy("organization3_id", "asc")
                                ->orderBy("organization4_id", "asc")
                                ->orderBy("organization5_id", "asc")
                                ->get()
                                ->toArray();

                    
        $organization_type = 5;  // ブロックを表示する
        if (!Organization1Repository::isExistOrg5($admin->organization1_id)) {
            $organization_type = 4; // エリアを表示する
        }

        $target_org = [];
        $target_org['org5'] = MessageOrganization::where('message_id', $message_id)->pluck('organization5_id')->toArray();
        $target_org['org4'] = MessageOrganization::where('message_id', $message_id)->pluck('organization4_id')->toArray();
        $target_org['org3'] = MessageOrganization::where('message_id', $message_id)->pluck('organization3_id')->toArray();
        $target_org['org2'] = MessageOrganization::where('message_id', $message_id)->pluck('organization2_id')->toArray();


        $message_target_roll = $message->roll()->pluck('rolls.id')->toArray();
        
        $target_brand = $message->brand()->pluck('brands.id')->toArray();

        return view('admin.message.publish.edit', [
            'message' => $message,
            'category_list' => $category_list,
            'target_roll_list' => $target_roll_list,
            'brand_list' => $brand_list,
            'organization_list' => $organization_list,
            'message_target_roll' => $message_target_roll,
            'target_brand' => $target_brand,
            'target_org' => $target_org,
            'organization_type' => $organization_type,
        ]);
    }

    public function update(PublishUpdateRequest $request, $message_id)
    {
        $validated = $request->validated();

        // ファイルを移動したかフラグ
        $message_changed_flg = false;

        $admin = session('admin');
        $message = Message::find($message_id);
        $msg_params['title'] = $request->title;
        $msg_params['category_id'] = $request->category_id;
        $msg_params['emergency_flg'] = ($request->emergency_flg == 'on' ? true : false);
        $msg_params['start_datetime'] = $this->parseDateTime($request->start_datetime);
        $msg_params['end_datetime'] = $this->parseDateTime($request->end_datetime);
        if ($this->isChangedFile($message->content_url, $request->file_path)) {
            $msg_params['content_name'] = $request->file_name;
            $msg_params['content_url'] = $request->file_path ? $this->registerFile($request->file_path) : null;
            $msg_params['thumbnails_url'] = $request->file_path ? ImageConverter::convert2image($msg_params['content_url']) : null;
            $message_changed_flg = true;
        } else {
            $message_params['content_name'] = $message->content_name;
            $message_params['content_url'] = $message->content_url;
            $message_params['thumbnails_url'] = $message->thumbnails_url;
        }
        $msg_params['updated_admin_id'] = $admin->id;
        $msg_params['editing_flg'] = isset($request->save) ? true : false;

        // ブロックかエリアかを判断するタイプ
        $organization_type = $request->organization_type;
        
        try {
            DB::beginTransaction();
            $message = Message::find($message_id);
            $message->update($msg_params);
            $message->roll()->sync($request->target_roll);

            MessageOrganization::where('message_id', $message_id)->delete();
            if (isset($request->organization['org5'])) {
                foreach ($request->organization['org5'] as $org5_id) {
                    $message->organization()->create([
                        'message_id' => $message->id,
                        'organization1_id' => $admin->organization1_id,
                        'organization5_id' => $org5_id
                    ]);
                }
            }
            if (isset($request->organization['org4'])) {
                foreach ($request->organization['org4'] as $org4_id) {
                    $message->organization()->create([
                        'message_id' => $message->id,
                        'organization1_id' => $admin->organization1_id,
                        'organization4_id' => $org4_id
                    ]);
                }
            }
            if (isset($request->organization['org3'])) {
                foreach ($request->organization['org3'] as $org3_id) {
                    $message->organization()->create([
                        'message_id' => $message->id,
                        'organization1_id' => $admin->organization1_id,
                        'organization3_id' => $org3_id
                    ]);
                }
            }
            if (isset($request->organization['org2'])) {
                foreach ($request->organization['org2'] as $org2_id) {
                    $message->organization()->create([
                        'message_id' => $message->id,
                        'organization1_id' => $admin->organization1_id,
                        'organization2_id' => $org2_id
                    ]);
                }
            }

            $message->brand()->sync($request->brand);
            $message->user()->sync(
                !isset($request->save) ? $this->targetUserParam($request) : []
            );

            if (isset($request->tag_name)) {
                $tag_ids = [];
                foreach ($request->tag_name as $tag_name) {
                    $tag = MessageTagMaster::firstOrCreate(['name' => $tag_name]);
                    $tag_ids[] = $tag->id;
                }
                $message->tag()->sync($tag_ids);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            if ($message_changed_flg) $this->rollbackRegisterFile($request->file_path);
            Log::error($th->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'データベースエラーです');
        }

        return redirect()->route('admin.message.publish.index');
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

    public function export(Request $request, $message_id)
    {
        $now = new Carbon('now');
        $now->format('Y_m_d-H_i_s');
        return Excel::download(
            new MessageViewRateExport($message_id, $request),
            $now->format('Y_m_d-H_i').'-業務連絡エクスポート.csv'
        );
    }

    public function exportList(Request $request)
    {
        $admin = session('admin');
        $organization1 = $admin->organization1->name;
        $now = new Carbon('now');
        $file_name = '業務連絡_' . $organization1 . $now->format('_Y_m_d') . '.csv';
        return Excel::download(
            new MessageListExport($request),
            $file_name
        );
    }

    // API
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

    public function csvUpload($request)
    {
        $validated = $request->validated();
        $file = $request->file;

        $messages = (new MessageCsvImport)
            ->toCollection($csv, \Maatwebsite\Excel\Excel::CSV);

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
                'organization3_id',
                'organization3.name as organization3_name',
                'organization4_id',
                'organization4.name as organization4_name',
                'organization5_id',
                'organization5.name as organization5_name'
            )
            ->where('organization1_id', $admin->organization1_id)
            ->orderByRaw('organization2_id is null asc')
            ->orderByRaw('organization3_id is null asc')
            ->orderByRaw('organization4_id is null asc')
            ->orderByRaw('organization5_id is null asc')
            ->orderBy("organization2_id", "asc")
            ->orderBy("organization3_id", "asc")
            ->orderBy("organization4_id", "asc")
            ->orderBy("organization5_id", "asc")
            ->get()
            ->toArray();
        
    }

    public function import(Request $request)
    {
        $csv = $request->file;

        $admin = session('admin');

        DB::beginTransaction();
        try {
            $messages = Excel::import(new MessageCsvImport, $csv, \Maatwebsite\Excel\Excel::CSV);
            // $this->importMessage($messages[0], $admin->organization1);
            DB::table('message_csv_logs')->insert([
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

    private function registerFile($request_file_path): ?String
    {
        $content_url = 'uploads/' . basename($request_file_path);
        $current_path = storage_path('app/' . $request_file_path);
        $next_path = public_path($content_url);
        rename($current_path, $next_path);
        return $content_url;
    }

    private function rollbackRegisterFile($request_file_path): Void
    {
        if (!(isset($request_file_path))) return;
        $content_url = 'uploads/' . basename($request_file_path);
        $current_path = storage_path('app/' . $request_file_path);
        $next_path = public_path($content_url);
        rename($next_path, $current_path);
        return;
    }

    private function targetUserParam($organizarions): Array {
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

    private function hasRequestFile($request) {
        if(!isset($request->file_name) || !isset($request->file_path)) return false;
        return true;
    }

    private function isChangedFile($current_file_path, $next_file_path): Bool
    {
        $currnt_path = $current_file_path ? basename($current_file_path) : null;
        $next_path = $next_file_path ? basename($next_file_path) : null;

        return !($currnt_path == $next_path);
    }

    

    private function tagImportParam(?Array $tags): Array
    {
        if(!isset($tags)) return [];

        $tags_pram = [];
        foreach ($tags as $key => $tag_name) {
            if(!isset($tag_name)) continue;
            $tag = MessageTagMaster::firstOrCreate(['name' => trim($tag_name, "\"")]);
            $tags_pram[] = $tag->id;
        }
        return $tags_pram;
    }

    private  function strToArray(?String $str): Array
    {
        if(!isset($str)) return [];
        
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
    
    private function getOrg3All(Int $org1_id): Array
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
    
}
