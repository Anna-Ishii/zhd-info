<?php

namespace App\Http\Controllers\Admin\Message;

use Carbon\Carbon;
use App\Enums\PublishStatus;
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
use App\Utils\ImageConverter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

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
                ->with('user', 'category', 'create_user', 'updated_user', 'brand')
                ->withCount(['user as total_users'])
                ->withCount(['readed_user as read_users'])
                ->when(isset($q), function ($query) use ($q) {
                    $query->whereLike('title', $q);
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
                        $query->whereHas('brand', function($q) use($brand_id)  {
                        $q->where('brand_id', $brand_id);
                    });
                })
                ->when(isset($label), function ($query) use ($label) {
                    $query->where('emergency_flg', true);
                })
                ->when((isset($rate[0])|| isset($rate[1])), function ($query) use ($rate) {
                    $query->viewRateBetween($rate[0], $rate[1]);
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
                ->where('organization1_id', $admin->organization1_id)
                ->orderBy('created_at', 'desc')                
                ->paginate(50)
                ->appends(request()->query());

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
        
        $this->organization_list = [];
        $this->organization_list = Organization1Repository::getOrg5($admin->organization1_id);
        $this->organization_type = 5;  // ブロックを表示する
        if($this->organization_list->isEmpty()) {
            $this->organization_list = Organization1Repository::getOrg4($admin->organization1_id);  
            $this->organization_type = 4; // エリアを表示する
        }

        return view('admin.message.publish.new', [
            'category_list' => $category_list,
            'target_roll_list' => $target_roll_list,
            'brand_list' => $brand_list,
            'organization_type' => $this->organization_type,
            'organization_list' => $this->organization_list
        ]);
    }

    public function store(PublishStoreRequest $request)
    {
        $validated = $request->validated();
        $admin = session('admin');

        $msg_params['title'] = $request->title;
        $msg_params['category_id'] = $request->category_id;
        $msg_params['emergency_flg'] = ($request->emergency_flg == 'on' ? true : false);
        $msg_params['start_datetime'] = $this->parseDateTime($request->start_datetime);
        $msg_params['end_datetime'] = $this->parseDateTime($request->end_datetime);
        $msg_params = array_merge($msg_params, $this->uploadFile($request->file));
        $msg_params['create_admin_id'] = $admin->id;
        $msg_params['organization1_id'] = $admin->organization1_id;
        $number = Message::where('organization1_id', $admin->organization1_id)->max('number');
        $msg_params['number'] = (is_null($number)) ? 1 : $number + 1;
        $msg_params['thumbnails_url'] = ImageConverter::pdf2image($msg_params['content_url']);
        $msg_params['editing_flg'] = isset($request->save) ? true : false;

        // ブロックかエリアかを判断するタイプ
        $organization_type = $request->organization_type;

        $shops_id = [];
        $target_user_data = [];

        // 一時保存の時は、ユーザー登録しない
        if (!isset($request->save)) {
            if ($organization_type == 4) {
                $shops_id = Shop::select('id')->whereIn('organization4_id', $request->organization)->whereIn('brand_id', $request->brand)->get()->toArray();
            } elseif ($organization_type == 5) {
                $shops_id = Shop::select('id')->whereIn('organization5_id', $request->organization)->whereIn('brand_id', $request->brand)->get()->toArray();
            }
            $target_users = User::select('id', 'shop_id')->whereIn('shop_id', $shops_id)->whereIn('roll_id', $request->target_roll)->get()->toArray();
            foreach ($target_users as $target_user) {
                $target_user_data[$target_user['id']] = ['shop_id' => $target_user['shop_id']];
            }
        }

        try {
            DB::beginTransaction();
            $message = Message::create($msg_params);
            $message->updated_at = null;
            $message->save();
            $message->roll()->attach($request->target_roll);

            if ($organization_type == 4) {
                $message->organization4()->attach($request->organization);
            } elseif ($organization_type == 5) {
                $message->organization5()->attach($request->organization);
            }

            $message->brand()->attach($request->brand);
            $message->user()->attach($target_user_data);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
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

        $this->organization_list = [];
        $this->organization_list = Organization1Repository::getOrg5($admin->organization1_id);
        $this->organization_type = 5;
        $this->target_org = [];
        $this->target_org = $message->organization5()->pluck('organization5.id')->toArray();
        if ($this->organization_list->isEmpty()) {
            $this->organization_list = Organization1Repository::getOrg4($admin->organization1_id);
            $this->organization_type = 4;
            $this->target_org = $message->organization4()->pluck('organization4.id')->toArray();
        }

        $message_target_roll = $message->roll()->pluck('rolls.id')->toArray();
        
        $target_brand = $message->brand()->pluck('brands.id')->toArray();

        return view('admin.message.publish.edit', [
            'message' => $message,
            'category_list' => $category_list,
            'target_roll_list' => $target_roll_list,
            'brand_list' => $brand_list,
            'organization_list' => $this->organization_list,
            'message_target_roll' => $message_target_roll,
            'target_brand' => $target_brand,
            'target_org' => $this->target_org,
            'organization_type' => $this->organization_type
        ]);
    }

    public function update(PublishUpdateRequest $request, $message_id)
    {
        $validated = $request->validated();
        $admin = session('admin');
        $msg_params['title'] = $request->title;
        $msg_params['category_id'] = $request->category_id;
        $msg_params['emergency_flg'] =
            ($request->emergency_flg == 'on' ? true : false);
        $msg_params['start_datetime'] = $this->parseDateTime($request->start_datetime);
        $msg_params['end_datetime'] = $this->parseDateTime($request->end_datetime);
        if (isset($request->file)) {
            $msg_params = array_merge($msg_params, $this->uploadFile($request->file));
            $msg_params['thumbnails_url'] = ImageConverter::pdf2image($msg_params['content_url']);
        }
        $msg_params['updated_admin_id'] = $admin->id;
        $msg_params['editing_flg'] = isset($request->save) ? true : false;

        // ブロックかエリアかを判断するタイプ
        $organization_type = $request->organization_type;

        $shops_id = [];
        $target_user_data = [];

        if(!isset($request->save)) {
            if ($organization_type == 4) {
                $shops_id = Shop::select('id')->whereIn('organization4_id', $request->organization)->whereIn('brand_id', $request->brand)->get()->toArray();
            } elseif ($organization_type == 5) {
                $shops_id = Shop::select('id')->whereIn('organization5_id', $request->organization)->whereIn('brand_id', $request->brand)->get()->toArray();
            }

            $target_users = User::select('id', 'shop_id')->whereIn('shop_id', $shops_id)->whereIn('roll_id', $request->target_roll)->get()->toArray();
                
            foreach ($target_users as $target_user) {
                $target_user_data[$target_user['id']] = ['shop_id' => $target_user['shop_id']];
            }
        }
        
        try {
            DB::beginTransaction();
            $message = Message::find($message_id);
            $message->update($msg_params);
            $message->roll()->sync($request->target_roll);

            if ($organization_type == 4) {
                $message->organization4()->sync($request->organization);
            } elseif ($organization_type == 5) {
                $message->organization5()->sync($request->organization);
            }

            $message->brand()->sync($request->brand);

            if (!isset($request->save)) {
                $message->user()->sync($target_user_data);
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
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

    private function parseDateTime($datetime)
    {
        return (!isset($datetime)) ? null : Carbon::parse($datetime, 'Asia/Tokyo');
    }

    private function uploadFile($file)
    {
        if(!isset($file)) return ['content_name' => null, 'content_url' => null];

        $filename_upload = uniqid() . '.' . $file->getClientOriginalExtension();
        $filename_input = $file->getClientOriginalName();
        $path = public_path('uploads');
        $file->move($path, $filename_upload);
        $content_url = 'uploads/' . $filename_upload;
        return [
            'content_name' => $filename_input,
            'content_url' => $content_url,
        ];
    }
}
