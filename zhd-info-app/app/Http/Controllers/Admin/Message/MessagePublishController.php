<?php

namespace App\Http\Controllers\Admin\Message;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Message\PublishStoreRequest;
use App\Http\Requests\Admin\Message\PublishUpdateRequest;
use App\Models\MessageCategory;
use App\Models\Message;
use App\Models\Organization1;
use App\Models\Organization4;
use App\Models\Roll;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessagePublishController extends Controller
{
    public function index(Request $request)
    {
        $category_list = MessageCategory::all();
        $category_id = $request->input('category');
        $status = $request->input('status');
        $q = $request->input('q');
        $message_list =
            Message::query()
                ->when(isset($q), function ($query) use ($q) {
                    $query->whereLike('title', $q);
                })
                ->when(isset($status), function ($query) use ($status) {
                    switch ($status) {
                        case 1:
                            $query->where('end_datetime', '>', now('Asia/Tokyo'))
                            ->where(function ($query) {
                                $query->where('start_datetime', '>', now('Asia/Tokyo'))
                                ->orWhereNull('start_datetime');
                            })
                                ->orWhereNull('end_datetime')
                                ->where(function ($query) {
                                    $query->where('start_datetime', '>', now('Asia/Tokyo'))
                                    ->orWhereNull('start_datetime');
                                });
                            break;
                        case 2:
                            $query->where('start_datetime', '<=', now('Asia/Tokyo'))
                            ->where(function ($query) {
                                $query->where('end_datetime', '>', now('Asia/Tokyo'))
                                ->orWhereNull('end_datetime');
                            });
                            break;
                        case 3:
                            $query->where('end_datetime', '<=', now('Asia/Tokyo'));
                            break;
                        default:
                            break;
                    }
                })
                ->when(isset($category_id), function ($query) use ($category_id) {
                    $query->where('category_id', $category_id);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(5)
                ->appends(request()->query());

        return view('admin.message.publish.index', [
            'category_list' => $category_list,
            'message_list' => $message_list

        ]);
    }

    public function new()
    {
        $category_list = MessageCategory::all();

        $target_roll_list = Roll::get(); //「一般」を使わない場合 Roll::where('id', '!=', '1')->get();
        // 業態一覧を取得する
        $organization1_list = Organization1::all();
        
        $organization4_list = Organization4::all();
        
        return view('admin.message.publish.new', [
            'category_list' => $category_list,
            'target_roll_list' => $target_roll_list,
            'organization1_list' => $organization1_list,
            'organization4_list' => $organization4_list
        ]);
    }

    public function store(PublishStoreRequest $request)
    {
        $validated = $request->validated();

        $msg_params['title'] = $request->title;
        $msg_params['category_id'] = $request->category_id;
        $msg_params['emergency_flg'] = 
        ($request->emergency_flg == 'on' ? true : false);
        $msg_params['start_datetime'] = $this->parseDateTime($request->start_datetime);
        $msg_params['end_datetime'] = $this->parseDateTime($request->end_datetime);
        $msg_params = array_merge($msg_params, $this->uploadFile($request->file));
        $msg_params['create_admin_id'] = session('admin')->id;

        $data = [];
        if (isset($request->organization4)) {
            $shops_id = Shop::select('id')->whereIn('organization4_id', $request->organization4)->get()->toArray();
            $target_users = User::select('id', 'shop_id')->whereIn('shop_id', $shops_id)->whereIn('roll_id', $request->target_roll)->get()->toArray();
        
            foreach ($target_users as $target_user) {
                $data[$target_user['id']] = ['shop_id' => $target_user['shop_id']];
            }
        }

        try {
            DB::beginTransaction();
            $message = Message::create($msg_params);
            $message->roll()->attach($request->target_roll);
            $message->organization4()->attach($request->organization4);
            $message->user()->attach($data);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
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

        $category_list = MessageCategory::all();

        $target_roll_list = Roll::get(); //「一般」を使わない場合 Roll::where('id', '!=', '1')->get();
        // 業態一覧を取得する
        $organization1_list = Organization1::all();

        $organization4_list = Organization4::all();

        $message_target_roll = $message->roll()->pluck('rolls.id')->toArray();

        $target_orgs4 = $message->organization4()->pluck('organization4.id')->toArray();
        $target_orgs1 = Shop::whereIn('organization4_id', $target_orgs4)
                                ->pluck('organization1_id')
                                ->toArray();

        return view('admin.message.publish.edit', [
            'message' => $message,
            'category_list' => $category_list,
            'target_roll_list' => $target_roll_list,
            'organization1_list' => $organization1_list,
            'organization4_list' => $organization4_list,
            'message_target_roll' => $message_target_roll,
            'message_target_org1' => $target_orgs1,
            'message_target_org4' => $target_orgs4
        ]);
    }

    public function update(PublishUpdateRequest $request, $message_id)
    {
        $validated = $request->validated();

        $msg_params['title'] = $request->title;
        $msg_params['category_id'] = $request->category_id;
        $msg_params['emergency_flg'] =
            ($request->emergency_flg == 'on' ? true : false);
        $msg_params['start_datetime'] = $this->parseDateTime($request->start_datetime);
        $msg_params['end_datetime'] = $this->parseDateTime($request->end_datetime);
        if (isset($request->file)) $msg_params = array_merge($msg_params, $this->uploadFile($request->file));
        $msg_params['create_admin_id'] = session('admin')->id;

        $data = [];
        if(isset($request->organization4)) {
            $shops_id = Shop::select('id')->whereIn('organization4_id', $request->organization4)->get()->toArray();
            $target_users = User::select('id', 'shop_id')->whereIn('shop_id', $shops_id)->whereIn('roll_id', $request->target_roll)->get()->toArray();
            
            foreach ($target_users as $target_user) {
                $data[$target_user['id']] = ['shop_id' => $target_user['shop_id']];
            }
        }

        try {
            DB::beginTransaction();
            $message = Message::find($message_id);
            $message->update($msg_params);
            $message->roll()->sync($request->target_roll);
            $message->organization4()->sync($request->organization4);
            $message->user()->sync($data);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
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
        
        $now = Carbon::now();
        Message::whereIn('id', $message_id)->update(['end_datetime' => $now]);
        
        return response()->json(['message' => '停止しました']);
    }

    private function parseDateTime($datetime)
    {
        return (!isset($datetime)) ? null : Carbon::parse($datetime, 'Asia/Tokyo');
    }

    private function uploadFile($file)
    {
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
