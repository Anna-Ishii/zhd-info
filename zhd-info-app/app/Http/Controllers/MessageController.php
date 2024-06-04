<?php

namespace App\Http\Controllers;

use App\Enums\SearchPeriod;
use App\Models\Crew;
use App\Models\MessageCategory;
use App\Models\MessageContent;
use App\Models\Message;
use Carbon\Carbon;
use App\Utils\OutputContentPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use setasign\Fpdi\TcpdfFpdi;

require_once(resource_path("outputpdf/libs/tcpdf/tcpdf.php"));
require_once(resource_path("outputpdf/libs/fpdi/autoload.php"));

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');
        $not_read_check = $request->input('not_read_check');
        $check_crew = session("check_crew", null);
        $search_period = SearchPeriod::tryFrom($request->input('search_period', SearchPeriod::All->value));

        $user = session("member");

        $sub = DB::table('messages')
            ->select([
                DB::raw('messages.id as message_id'),
                DB::raw('count(c.id) as crew_count'),
                DB::raw('count(c_m_l.crew_id) as readed_crew_count'),
                DB::raw('round((count(c_m_l.crew_id) / count(c.id)) * 100, 0) as view_rate')
            ])
            ->leftjoin('message_user as m_u', 'messages.id', '=', 'm_u.message_id')
            ->leftjoin('crews as c', 'm_u.user_id', '=', 'c.user_id')
            ->leftjoin('crew_message_logs as c_m_l', function ($join) {
                $join->on('c_m_l.crew_id', '=', 'c.id')
                    ->where('c_m_l.message_id', '=', DB::raw('messages.id'));
            })
            ->where('m_u.user_id', '=', $user->id)
            ->when(isset($check_crew[0]), function ($query) use ($check_crew) {
                $query->where('c.id', $check_crew[0]->id);
            })
            ->groupBy('messages.id');

        // 掲示中のデータをとってくる
        $messages = $user->message()
            ->with('category', 'tag')
            ->select([
                'sub.crew_count as crew_count',
                'sub.readed_crew_count as readed_crew_count',
                'sub.view_rate as view_rate'
            ])
            ->publishingMessage()
            ->LeftJoinSub($sub, 'sub', 'messages.id', 'sub.message_id')
            ->when(isset($keyword), function ($query) use ($keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->whereLike('title', $keyword)
                        ->orWhereHas('tag', function ($query) use ($keyword) {
                            $query->where('name', $keyword);
                        });
                });
            })
            ->when(isset($search_period), function ($query) use ($search_period) {
                switch ($search_period) {
                    case SearchPeriod::All:
                        break;
                    case SearchPeriod::Past_week:
                        $query->where('start_datetime', '>=', now('Asia/Tokyo')->subWeek()->isoFormat('YYYY/MM/DD'));
                        break;
                    case SearchPeriod::Past_month:
                        $query->where('start_datetime', '>=', now('Asia/Tokyo')->subMonth()->isoFormat('YYYY/MM/DD'));
                        break;
                    default:
                        break;
                }
            })
            ->when(!empty($crews), function ($query) {
                $query->orderByRaw('
                    case
                        when readed_crew_count = 0 or readed_crew_count is null then 0
                        else 1
                    end, readed_crew_count asc
                ');
            })
            ->when(isset($not_read_check) && isset($check_crew[0]), function ($query) {
                $query->where('sub.readed_crew_count', '<', 1);
            })
            ->orderBy('created_at', 'desc')

            ->paginate(20)
            ->appends(request()->query());

        $categories = MessageCategory::get();
        $organization1_id =  $user->shop->organization1->id;
        $keywords = DB::table("message_search_logs as m_s_l")
            ->select([
                'keyword',
                DB::raw('COUNT(*) as count'),
            ])
            ->leftJoin('shops as s', 's.id', 'm_s_l.shop_id')
            ->Join('organization1 as o1', function ($join) use ($organization1_id) {
                $join->on('o1.id', '=', 's.organization1_id')
                    ->where('o1.id', '=', $organization1_id);
            })
            ->groupBy('keyword', 'o1.id')
            ->orderBy('count', 'desc')
            ->limit(3)
            ->get();

        return view('message.index', [
            'messages' => $messages,
            'categories' => $categories,
            'keywords' => $keywords,
            'user' => $user
        ]);
    }

    public function detail($message_id)
    {
        $user = session('member');
        $crews = session('crews');
        $message = Message::findOrFail($message_id);

        $user->message()->wherePivot('read_flg', false)->updateExistingPivot($message->id, [
            'read_flg' => true,
            'readed_datetime' => Carbon::now(),
        ]);

        $message->putCrewRead($crews);
        return redirect()->to($message->content_url)->withInput();
    }

    public function search(Request $request)
    {
        $user = session('member');
        $param = [
            'keyword' => $request['keyword'],
            'search_period' => $request['search_period'],
            'not_read_check' => $request['not_read_check']
        ];

        if ($request->filled('keyword')) {
            DB::table('message_search_logs')->insert([
                'keyword' => $request['keyword'],
                'shop_id' => $user->shop_id,
                'searched_datetime' => new Carbon('now')
            ]);
        }

        return redirect()->route('message.index', $param);
    }

    public function putCrews(Request $request)
    {
        $previousUrl = app('url')->previous();
        $check_crew = $request->input('read_edit_radio');
        $crew = Crew::findOrFail($check_crew);

        $separator = parse_url($previousUrl, PHP_URL_QUERY) ? '&' : '?';

        $request->session()->put('check_crew', $crew);
        return redirect()->to($previousUrl . $separator . http_build_query(['not_read_check' => 1]))->withInput();
    }

    public function putReading(Request $request)
    {
        $user = session('member');
        $reading_crews = $request->input('read_edit_radio', []);
        $message_id = $request->input('message');

        // セッションの登録
        $request->session()->put('reading_crews', $reading_crews);

        // 既読機能
        try {
            DB::beginTransaction();
            $message = Message::findOrFail($message_id);
            $message->putCrewRead($reading_crews);
            $user->message()->wherePivot('read_flg', false)->updateExistingPivot($message_id, [
                'read_flg' => true,
                'readed_datetime' => Carbon::now(),
            ]);
            DB::commit();

            // 既読が無事できたらpdfへ
            return $this->outputContentsPdf($message_id);
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->withInput();
        }
    }

    public function crewsLogout(Request $request)
    {
        $request->session()->forget('check_crew');
        $request->session()->forget('reading_crews');

        return back()->withInput();
    }

    //
    // API
    //
    public function getCrews(Request $request)
    {
        $user = session('member');
        $crews = $user->crew()
            ->select([
                DB::raw(" * "),
                DB::raw("
                            case
                                when name_kana regexp '^[ｱ-ｵ]' then 1
                                when name_kana regexp '^[ｶ-ｺ]' then 2
                                when name_kana regexp '^[ｻ-ｿ]' then 3
                                when name_kana regexp '^[ﾀ-ﾄ]' then 4
                                when name_kana regexp '^[ﾅ-ﾉ]' then 5
                                when name_kana regexp '^[ﾊ-ﾎ]' then 6
                                when name_kana regexp '^[ﾏ-ﾓ]' then 7
                                when name_kana regexp '^[ﾔ-ﾖ]' then 8
                                when name_kana regexp '^[ﾗ-ﾛ]' then 9
                                when name_kana regexp '^[ﾜ-ﾝ]' then 10
                                else 0
                            end as name_sort
                        "),
            ])
            ->orderBy("name_kana", 'asc')
            ->get();

        return response()->json([
            'crews' => $crews,
        ], 200);
    }

    public function getCrewsMessage(Request $request)
    {
        $message = $request->input('message');
        $text = $request->input('text');
        $user = session('member');

        $crews = DB::table('messages as m')
            ->select([
                DB::raw('
                            c.part_code as part_code,
                            c.name as name,
                            c.name_kana as name_kana,
                            c.id as c_id
                        '),
                DB::raw('m.start_datetime'),
                DB::raw('DATE_FORMAT(c_m_l.readed_at, "%m/%d %H:%i") as readed_at'),
                DB::raw('
                            case
                                when c.register_date > m.start_datetime then true else false
                            end as new_face
                        '),
                DB::raw('
                            case
                                when c_m_l.id is null then false else true
                            end as readed
                        '),
                DB::raw("
                            case
                                when c.name_kana regexp '^[ｱ-ｵ]' then 1
                                when c.name_kana regexp '^[ｶ-ｺ]' then 2
                                when c.name_kana regexp '^[ｻ-ｿ]' then 3
                                when c.name_kana regexp '^[ﾀ-ﾄ]' then 4
                                when c.name_kana regexp '^[ﾅ-ﾉ]' then 5
                                when c.name_kana regexp '^[ﾊ-ﾎ]' then 6
                                when c.name_kana regexp '^[ﾏ-ﾓ]' then 7
                                when c.name_kana regexp '^[ﾔ-ﾖ]' then 8
                                when c.name_kana regexp '^[ﾗ-ﾛ]' then 9
                                when c.name_kana regexp '^[ﾜ-ﾝ]' then 10
                                else 0
                            end as name_sort
                        "),
            ])
            ->leftJoin('message_user as m_u', 'm.id', 'm_u.message_id')
            ->leftJoin('users as u', 'm_u.user_id', 'u.id')
            ->leftJoin('crews as c', 'u.id', 'c.user_id')
            ->leftJoin('crew_message_logs as c_m_l', function ($join) use ($message) {
                $join->on('c_m_l.crew_id', '=', 'c.id')
                    ->where('c_m_l.message_id', '=', $message);
            })
            ->where('m.id', '=', $message)
            ->where('u.id', '=', $user->id)
            ->when(isset($text), function ($query) use ($text) {
                $query->where('c.name', 'like', '%' . addcslashes($text, '%_\\') . '%')
                    ->orWhere('c.part_code', 'like', '%' . addcslashes($text, '%_\\') . '%')
                    ->orWhere('c.name_kana', 'like', '%' . addcslashes($text, '%_\\') . '%');
            })
            ->orderBy('c.name_kana', 'asc')
            ->get();

        return response()->json([
            'crews' => $crews,
        ], 200);
    }

    // PDFの表示処理
    private function outputContentsPdf($message_id)
    {
        // メモリ制限を一時的に増加
        ini_set('memory_limit', '256M');

        $message_contents = MessageContent::where('message_id', $message_id)->pluck('content_url')->toArray();

        $tempFiles = [];

        // 複数PDFがある場合の表示処理
        if (!empty($message_contents)) {
            foreach ($message_contents as $content_path) {
                $originalFile = public_path('uploads/' . basename($content_path));
                $tempFile = public_path('uploads/temp_' . basename($content_path));
                OutputContentPdf::recompressPdf($originalFile, $tempFile);
                $tempFiles[] = $tempFile;
            }

        // 単一PDFがある場合の表示処理
        } else {
            $message_content = Message::where('id', $message_id)->pluck('content_url')->first();

            return redirect()->to(asset($message_content));
        }

        // PDF を生成するための初期化
        $pdf = new TcpdfFpdi();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // 各 PDF を追加
        foreach ($tempFiles as $file) {
            $count = $pdf->setSourceFile($file);
            for ($i = 1; $i <= $count; $i++) {
                $pdf->addPage();
                $pdf->useTemplate($pdf->importPage($i));
            }
        }

        // 一時ファイルを削除
        foreach ($tempFiles as $file) {
            @unlink($file);
        }

        // PDFを出力して返す
        $outputFileName = 'output_contents.pdf';
        $response = response()->stream(function() use ($pdf, $outputFileName) {
            $pdf->output($outputFileName, 'I');
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$outputFileName.'"'
        ]);

        // 元のメモリ制限に戻す
        ini_restore('memory_limit');

        return $response;
    }
}
