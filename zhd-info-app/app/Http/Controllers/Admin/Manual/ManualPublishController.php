<?php

namespace App\Http\Controllers\Admin\Manual;

use App\Enums\PublishStatus;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Repository\AdminRepository;
use App\Http\Requests\Admin\Manual\PublishStoreRequest;
use App\Http\Requests\Admin\Manual\PublishUpdateRequest;
use App\Models\Manual;
use App\Models\ManualCategory;
use App\Models\ManualContent;
use App\Models\Organization1;
use App\Models\Shop;
use App\Models\User;
use App\Utils\ImageConverter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManualPublishController extends Controller
{
    public function index(Request $request)
    {
        $admin = session('admin');
        $category_list = ManualCategory::all();
        $brand_list = $admin->organization1->brand()->orderBy('id', 'asc')->pluck('name')->toArray();
        $category_id = $request->input('category');
        $status = $request->input('status');
        $q = $request->input('q');
        $manual_list =
            Manual::query()
            // 検索機能 キーワード
            ->when(isset($q), function ($query) use ($q) {
                $query->whereLike('title', $q);
            })
            // 検索機能 状態
            ->when(isset($status), function ($query) use ($status) {
                switch ($status) {
                    case PublishStatus::Wait:
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
                    case PublishStatus::Publishing:
                        $query->where('start_datetime', '<=', now('Asia/Tokyo'))
                                ->where(function ($query) {
                                $query->where('end_datetime', '>', now('Asia/Tokyo'))
                                ->orWhereNull('end_datetime');
                        });
                        break;
                    case PublishStatus::Published:
                        $query->where('end_datetime', '<=', now('Asia/Tokyo'));
                        break;
                    case 4: break;
                    default:
                        break;
                }
            })
            // 検索機能 カテゴリ
            ->when(isset($category_id), function ($query) use ($category_id) {
                $query->where('category_id', $category_id);
            })
            ->where('organization1_id', $admin->organization1_id)
            ->orderBy('created_at', 'desc')
            ->paginate(50)
            ->appends(request()->query());

        return view('admin.manual.publish.index', [
            'category_list' => $category_list,
            'manual_list' => $manual_list,
            'brand_list' => $brand_list,
        ]);
    }

    public function new()
    {
        $admin = session("admin");

        $category_list = ManualCategory::all();
        $brand_list = AdminRepository::getBrands($admin);

        return view('admin.manual.publish.new', [
            'category_list' => $category_list,
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
        $manual_params['start_datetime'] = $this->parseDateTime($request->start_datetime);
        $manual_params['end_datetime'] = $this->parseDateTime($request->end_datetime);
        $manual_params = array_merge($manual_params, $this->uploadFile($request->file));
        $manual_params['thumbnails_url'] = ImageConverter::convert2image($manual_params['content_url']);
        $manual_params['create_admin_id'] = $admin->id;
        $manual_params['organization1_id'] = $admin->organization1_id;
        $number = Manual::where('organization1_id', $admin->organization1_id)->max('number');
        $manual_params['number'] = (is_null($number)) ? 1 : $number + 1;
        $manual_params['editing_flg'] = isset($request->save) ? true : false;

        // message_userに該当のユーザーを登録する
        $target_users_data = [];
        // 一時保存の時は、ユーザー登録しない
        if (!isset($request->save)) {
            $shops_id = Shop::select('id')->whereIn('brand_id', $request->brand)->get()->toArray();
            $target_users = User::select('id', 'shop_id')->whereIn('shop_id', $shops_id)->get()->toArray();
            foreach ($target_users as $target_user) {
                $target_users_data[$target_user['id']] = ['shop_id' => $target_user['shop_id']];
            }
        }

        // 手順を登録する
        $content_data = [];
        if(isset($request['manual_flow'])){
            foreach ($request['manual_flow'] as $i => $r) {
                $content_data[$i]['title'] = $r['title'];
                $content_data[$i]['description'] = $r['detail'];
                $content_data[$i]['order_no'] = $i + 1;
                if ($request->hasFile('manual_flow.' . $i . '.file')) {
                    $f = $request->file('manual_flow.' . $i . '.file');
                    $content_data[$i] = array_merge($content_data[$i], $this->uploadFile($f));
                    $content_data[$i]['thumbnails_url'] =
                        ImageConverter::convert2image($content_data[$i]['content_url']);
                }
            }
        }

        try {
            DB::beginTransaction();
            $manual = Manual::create($manual_params);
            $manual->updated_at = null;
            $manual->save();
            $manual->brand()->attach($request->brand);
            $manual->user()->attach($target_users_data);
            $manual->content()->createMany($content_data);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
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

        return view('admin.manual.publish.edit', [
            'manual' => $manual,
            'category_list' => $category_list,
            'brand_list' => $brand_list,
            'target_brand' => $target_brand,
            'contents' => $contents

        ]);
    }

    public function update(PublishUpdateRequest $request, $manual_id)
    {
        $validated = $request->validated();
        $admin = session('admin');

        $manual_params['title'] = $request->title;
        $manual_params['description'] = $request->description;
        $manual_params['category_id'] = $request->category_id;
        $manual_params['start_datetime'] = $this->parseDateTime($request->start_datetime);
        $manual_params['end_datetime'] = $this->parseDateTime($request->end_datetime);
        if(isset($request->file)) {
            $manual_params = array_merge($manual_params, $this->uploadFile($request->file));
            $manual_params['thumbnails_url'] = ImageConverter::convert2image($manual_params['content_url']);
        }
        $manual_params['updated_admin_id'] = $admin->id;

        // 該当のショップID
        $shops_id = Shop::select('id')->whereIn('brand_id', $request->input('brand',[]))->get()->toArray();
        // 該当のユーザー
        $target_users = User::select('id', 'shop_id')->whereIn('shop_id', $shops_id)->get()->toArray();

        // 該当ユーザーを追加する
        $target_user_data = [];
        foreach ($target_users as $target_user) {
            $target_user_data[$target_user['id']] = ['shop_id' => $target_user['shop_id']];
        }

        $content_data = []; // manualcontentに格納するための配列
        $count_order_no = 0;
        // 登録されているコンテンツが削除されていた場合、deleteフラグを立てる
        $contents_id = $request->input('content_id', []); //登録されているコンテンツIDがpostされる
        ManualContent::whereNotIn('id', $contents_id)->delete();


         //手順の数分、繰り返す 
         //タイトルは必須項目なので、タイトルの数はコンテンツの数
        for ($i = 0; $i < count($request['manual_flow_title']); $i++) {

            // 登録されている手順を変更する
            if (isset($request->content_id[$i])) {
                $content = ManualContent::find($request->content_id[$i]);
                $content->title = $request['manual_flow_title'][$i];
                $content->description = $request['manual_flow_detail'][$i];
                $content->order_no = $count_order_no + 1;

                // manual_fileがnullの場合は変更しない
                if (isset($request->file('manual_file')[$i])) {
                    $file = $this->uploadFile($request->file('manual_file')[$i]);
                    $content->content_url = $file['content_url'];
                    $content->content_name = $file['content_name'];
                    $content->thumbnails_url = ImageConverter::convert2image($content->content_url);
                }
                $content->save();
            } else {
                // 手順の新規登録
                if(isset($request['manual_flow_title'][$i]) &&
                    isset($request->file('manual_file')[$i])) {
                    $content_data[$i]['title'] = $request['manual_flow_title'][$i];
                    $content_data[$i]['description'] = $request['manual_flow_detail'][$i];
                    $content_data[$i]['order_no'] = $count_order_no + 1;
                    $f = $request['manual_file'][$i];
                    $content_data[$i] = array_merge($content_data[$i], $this->uploadFile($f));
                    $content_data[$i]['thumbnails_url'] = ImageConverter::convert2image($content_data[$i]['content_url']);
                }
            }
            
        }

        try {
            DB::beginTransaction();
            $manual = Manual::find($manual_id);
            $manual->update($manual_params);
            $manual->brand()->sync($request->brand);
            $manual->user()->sync($target_user_data);
            $manual->content()->createMany($content_data);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
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
        if($status == PublishStatus::Published) return response()->json(['message' => 'すでに掲載終了しています'], status: 500);

        $admin = session('admin');
        $now = Carbon::now();
        Manual::whereIn('id', $manual_id)->update([
            'end_datetime' => $now,
            'updated_admin_id' => $admin->id,
        ]);

        return response()->json(['message' => '停止しました']);
    }

    private function parseDateTime($datetime)
    {
        return (!isset($datetime)) ? null : Carbon::parse($datetime, 'Asia/Tokyo');
    }

    private function uploadFile($file)
    {
        if (!isset($file)) return ['content_name' => null, 'content_url' => null];

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

    private function movie2image($movie_path)
    {
        $shot_sec = 4;
        $dirname = dirname($movie_path);
        $filename = pathinfo($movie_path, PATHINFO_FILENAME);
        $output_path = $dirname . '/' . $filename . '.jpg';
        $cmd = 'python3 /var/www/zhd-info-app/py/m.py "' . $movie_path . '" ' . $shot_sec . ' "' . $output_path . '"';
        exec($cmd, $output);
        return $output_path;
    }
}


