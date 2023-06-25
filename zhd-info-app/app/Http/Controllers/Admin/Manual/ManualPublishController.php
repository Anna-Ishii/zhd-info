<?php

namespace App\Http\Controllers\Admin\Manual;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Manual\PublishStoreRequest;
use App\Http\Requests\Admin\Manual\PublishUpdateRequest;
use App\Models\Manual;
use App\Models\ManualCategory;
use App\Models\Manualcontent;
use App\Models\Organization1;
use App\Models\Roll;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ManualPublishController extends Controller
{
    public function index(Request $request)
    {
        $category_id = $request->input('category');

        $manual_list = Manual::query()
            ->when(isset($category_id), function ($query) use ($category_id) {
                $query->where('category_id', $category_id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(5)
            ->appends(request()->query());

        $category_list = ManualCategory::all();

        return view('admin.manual.publish.index', [
            'category_list' => $category_list,
            'manual_list' => $manual_list,
        ]);
    }

    public function new()
    {
        $category_list = ManualCategory::all();
        $organization1_list = Organization1::all();

        return view('admin.manual.publish.new', [
            'category_list' => $category_list,
            'organization1_list' => $organization1_list,
        ]);
    }

    public function store(PublishStoreRequest $request)
    {
        $validated = $request->validated();

        $manual_params['title'] = $request->title;
        $manual_params['description'] = $request->description;
        $manual_params['category_id'] = $request->category_id;
        $manual_params['start_datetime'] = $this->parseDateTime($request->start_datetime);
        $manual_params['end_datetime'] = $this->parseDateTime($request->end_datetime);
        $manual_params = array_merge($manual_params, $this->uploadFile($request->file));
        $manual_params['create_user_id'] = session('user')->id;

        $data = [];
        if (isset($request->organization1)) {
            $shops_id = Shop::select('id')->whereIn('organization1_id', $request->organization1)->get()->toArray();
            $target_users = User::select('id', 'shop_id')->whereIn('shop_id', $shops_id)->get()->toArray();

            foreach ($target_users as $target_user) {
                $data[$target_user['id']] = ['shop_id' => $target_user['shop_id']];
            }
        }

        $content_data = [];
        if (
            isset($request['manual_flow_title']) &&
            isset($request['manual_file']) &&
            isset($request['manual_flow_detail'])
        ) {
            for ($i = 0; $i < count($validated['manual_flow_title']); $i++) {
                $content_data[$i]['title'] = $request['manual_flow_title'][$i];
                $content_data[$i]['description'] = $request['manual_flow_detail'][$i];
                $content_data[$i]['order_no'] = $i + 1;
                $f = $request['manual_file'][$i];
                $content_data[$i] = array_merge($content_data[$i], $this->uploadFile($f));
            }
        }

        try {
            $manual = Manual::create($manual_params);
            $manual->organization1()->attach($request->organization1);
            $manual->user()->attach($data);
            $manual->content()->createMany($content_data);
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', '登録できませんでした。');
        }

        return redirect()->route('admin.manual.publish.index');
    }

    public function edit($manual_id)
    {
        $manual = Manual::find($manual_id);
        if (empty($manual)) return redirect()->route('admin.manual.publish.index');

        $category_list = ManualCategory::all();
        // 業態一覧を取得する
        $organization1_list = Organization1::all();
        $target_organization1 = $manual->organization1()->pluck('organization1.id')->toArray();
        $contents = $manual->content()
            ->orderBy("order_no")
            ->where('is_deleted','=','false')
            ->get();

        return view('admin.manual.publish.edit', [
            'manual' => $manual,
            'category_list' => $category_list,
            'organization1_list' => $organization1_list,
            'target_organization1' => $target_organization1,
            'contents' => $contents

        ]);
    }

    public function update(PublishUpdateRequest $request, $manual_id)
    {
        $validated = $request->validated();

        $manual_params['title'] = $request->title;
        $manual_params['description'] = $request->description;
        $manual_params['category_id'] = $request->category_id;
        $manual_params['start_datetime'] = $this->parseDateTime($request->start_datetime);
        $manual_params['end_datetime'] = $this->parseDateTime($request->end_datetime);
        if(isset($request->file)) $manual_params = array_merge($manual_params, $this->uploadFile($request->file));
        $manual_params['create_user_id'] = session('user')->id;

        // 該当のショップID
        $shops_id = Shop::select('id')->whereIn('organization1_id', $request->input('organization1',[]))->get()->toArray();
        // 該当のユーザー
        $target_users = User::select('id', 'shop_id')->whereIn('shop_id', $shops_id)->get()->toArray();

        $data = [];
        foreach ($target_users as $target_user) {
            $data[$target_user['id']] = ['shop_id' => $target_user['shop_id']];
        }

        $content_data = []; // manualcontentに格納するための配列
        $count_order_no = 0;
        // 登録されているコンテンツが削除されていた場合、deleteフラグを立てる
        $contents_id = $request->input('content_id', []); //登録されているコンテンツIDがpostされる
        Manualcontent::whereNotIn('id', $contents_id)->update(['is_deleted' => true]);

        if (
            isset($request['manual_flow_title']) &&
            isset($request['manual_file']) &&
            isset($request['manual_flow_detail'])
        ) {
            //手順の数分、繰り返す 
            for ($i = 0; $i < count($request['manual_flow_title']); $i++) {

                // 登録されているコンテンツを変更する
                if (isset($request->content_id[$i])) {
                    $content = Manualcontent::find($request->content_id[$i]);
                    $content->title = $request['manual_flow_title'][$i];
                    $content->description = $request['manual_flow_detail'][$i];
                    $content->order_no = $count_order_no + 1;

                    // manual_fileがnullの場合は変更しない
                    if (isset($request->file('manual_file')[$i])) {
                        $content = array_merge($content, $this->uploadFile($request->file('manual_file')[$i]));
                    }
                    $content->save();
                } else {
                    $content_data[$i]['title'] = $request['manual_flow_title'][$i];
                    $content_data[$i]['description'] = $request['manual_flow_detail'][$i];
                    $content_data[$i]['order_no'] = $count_order_no + 1;
                    $f = $request['manual_file'][$i];
                    $content_data[$i] = array_merge($content_data[$i], $this->uploadFile($f));
                }
            }
        }

        try {
            $manual = Manual::find($manual_id);
            $manual->update($manual_params);
            $manual->organization1()->sync($request->organization1);
            $manual->user()->sync($data);
            $manual->content()->createMany($content_data);
        } catch (\Throwable $th) {
            return redirect()
                ->route('admin.manual.publish.new')
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
        $now = Carbon::now();
        Manual::whereIn('id', $manual_id)->update(['end_datetime' => $now]);

        return response()->json(['message' => '停止しました']);
    }

    private function parseDateTime($datetime)
    {
        return ($datetime === 'on') ? null : Carbon::parse($datetime, 'Asia/Tokyo');
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


