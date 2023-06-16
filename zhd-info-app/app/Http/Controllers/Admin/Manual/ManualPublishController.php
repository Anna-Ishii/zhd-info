<?php

namespace App\Http\Controllers\Admin\Manual;

use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Manual;
use App\Models\Manualcategory;
use App\Models\Message;
use App\Models\Organization1;
use App\Models\Organization4;
use App\Models\Roll;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Queue\NullQueue;
use Illuminate\Support\Facades\DB;

class ManualPublishController extends Controller
{
    public function index(Request $request)
    {

        $category_list = Manualcategory::all();

        // $message_list = $user->message;
        $manual_list = Manual::all();
        return view('admin.manual.publish.index',[
            'category_list' => $category_list,
            'manual_list' => $manual_list
        ]);
    }

    public function new(Request $request)
    {
        if ($request->isMethod('post')) {
            $manual_params = $request
                ->only([
                    'title',
                    'description',
                    'file',
                    'category_id',
                    'start_datetime',
                    'end_datetime',
                    'organization4'
                ]);
            $contents_params = $request
                ->only([
                    'contents_title',
                    'contents_file',
                    'contents_description'
                ]);

            if ($request->start_datetime == 'on') $request->start_datetime = null;
            $manual_params['start_datetime'] =
                !empty($request->start_datetime) ? Carbon::parse($request->start_datetime) : null;

            if ($request->end_datetime == 'on') $request->end_datetime = null;
            $manual_params['end_datetime'] =
                !empty($request->end_datetime) ? Carbon::parse($request->end_datetime) : null;


            $file = $request->file('file');
            $directory = 'uploads';
            // ファイル名を生成します（一意の名前を使用する場合は、例えばユーザーIDやタイムスタンプを組み合わせることもできます）
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = public_path('uploads');
            $file->move($path, $filename);
            $content_url = 'uploads/' . $filename;

            // ファイルを指定したディレクトリに保存します
            // $path = $file->storeAs($directory, $filename, 'public');
            $manual_params['content_url'] = $content_url;
            $manual_params['create_user_id'] = session('user')->id;


            $shops_id = Shop::select('id')->whereIn('organization1_id', $request->organization1)->get()->toArray();
            $target_users = User::select('id', 'shop_id')->whereIn('shop_id', $shops_id)->whereIn('roll_id', $request->target_roll)->get()->toArray();

            $data = [];
            foreach ($target_users as $target_user) {
                $data[$target_user['id']] = ['shop_id' => $target_user['shop_id']];
            }

            try {
                $manual = Manual::create($manual_params);
                $manual->organization1()->attach($request->organization1);
                $manual->user()->attach($data);
            } catch (\Throwable $th) {
                return redirect()
                    ->route('admin.message.publish.new')
                    ->withInput()
                    ->with('error', '入力エラーがあります');
            }

            return redirect()->route('admin.manual.publish.index');
        }

        $category_list = Manualcategory::all();

        // 業態一覧を取得する // 今回は、検証画面なので、使わない // 業態が増えたら使う
        // $organization1_list = Organization1::all();

        return view('admin.manual.publish.new', [
            'category_list' => $category_list,
        ]);
    }

    public function edit(Request $request, $message_id)
    {
        if ($request->isMethod('post')) {
            $msg_params = $request
                ->only([
                    'title',
                    'category_id',
                ]);
            $msg_params['emergency_flg'] =
                ($request->emergency_flg == 'on' ? true : false);

            if ($request->start_datetime == 'on') $request->start_datetime = null;
            $msg_params['start_datetime'] =
                !empty($request->start_datetime) ? Carbon::parse($request->start_datetime) : null;

            if ($request->end_datetime == 'on') $request->end_datetime = null;
            $msg_params['end_datetime'] =
                !empty($request->end_datetime) ? Carbon::parse($request->end_datetime) : null;


            if ($request->file('file')) {
                $file = $request->file('file');
                $directory = 'uploads';
                // ファイル名を生成します（一意の名前を使用する場合は、例えばユーザーIDやタイムスタンプを組み合わせることもできます）
                $filename = uniqid() . '.' . $file->getClientOriginalExtension();

                $path = public_path('uploads');
                $file->move($path, $filename);
                $content_url = 'uploads/' . $filename;
                // ファイルを指定したディレクトリに保存します
                // $path = $file->storeAs($directory, $filename, 'public');
                $msg_params['content_url'] = $content_url;
            }
            $msg_params['create_user_id'] = session('user')->id;

            $shops_id = Shop::select('id')->whereIn('organization4_id', $request->organization4)->get()->toArray();
            $target_users = User::select('id', 'shop_id')->whereIn('shop_id', $shops_id)->whereIn('roll_id', $request->target_roll)->get()->toArray();
            $data = [];
            foreach ($target_users as $target_user) {
                $data[$target_user['id']] = ['shop_id' => $target_user['shop_id']];
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
                    ->route('admin.message.publish.edit', ['message_id' => $message_id])
                    ->withInput()
                    ->with('error', '入力エラーがあります');
            }
            // $target_roll = $request->target_roll;
            // $target_organization1 = $request->target_organization1;
            // $target_block = $request->target_block;

            //TODO
            // target_roll
            // target_organizationがが含まれているかチェック
            // ロールと対象ブロックは後で。
            return redirect()->route('admin.message.publish.index');
        }

        $message = Message::find($message_id);
        if (empty($message)) return redirect()->route('admin.message.publish.index');

        $category_list = Category::all();
        // 「一般」は使わない
        $target_roll_list = Roll::where('id', '!=', '1')->get();
        // 業態一覧を取得する
        $organization4_list = Organization4::all();

        $message_target_roll = $message->roll()->pluck('rolls.id')->toArray();
        $target_orgs4 = $message->organization4()->pluck('organization4.id')->toArray();
        // $target_orgs1 = Shop::select('organization1_id')->whereIn('organization4_id', $target_orgs4);

        return view('admin.message.publish.edit', [
            'message' => $message,
            'category_list' => $category_list,
            'target_roll_list' => $target_roll_list,
            'organization4_list' => $organization4_list,
            'message_target_roll' => $message_target_roll,
            'message_target_org4' => $target_orgs4

        ]);
    }
}
