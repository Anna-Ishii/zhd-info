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
        session()->put('current_url', $request->fullUrl());
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

        // 添付ファイル
        foreach ($messages as $message) {
            $this->attachFilesToMessage($message);
        }

        return view('message.index', [
            'messages' => $messages,
            'categories' => $categories,
            'keywords' => $keywords,
            'user' => $user,
            'organization1_id' => $organization1_id
        ]);
    }

    // public function detail($message_id)
    // {
    //     $user = session('member');
    //     $crews = session('crews');
    //     $message = Message::findOrFail($message_id);

    //     $user->message()->wherePivot('read_flg', false)->updateExistingPivot($message->id, [
    //         'read_flg' => true,
    //         'readed_datetime' => Carbon::now(),
    //     ]);

    //     $message->putCrewRead($crews);
    //     return redirect()->to($message->content_url)->withInput();
    // }

    public function detail($message_id)
    {
        $message = Message::findOrFail($message_id);

        // URLから message_content_url を取得
        $message_content_url = request()->query('message_content_url');

        // メッセージに添付ファイルを追加
        $this->attachFilesToMessage($message);

        // URLの message_content_url と一致するファイルがある場合、そのファイルをメインファイルとして設定
        if ($message_content_url && isset($message->content_files)) {
            foreach ($message->content_files as $file) {
                if ($file['file_url'] === $message_content_url) {
                    $message->main_file = $file;
                    break;
                }
            }
        }

        // 新着業務連絡を取得する
        $user = session('member');

        $start_date_time = Carbon::now()->subDays(7)->startOfDay();

        $latest_messages = $user->message()
            ->whereBetween('start_datetime', [$start_date_time, now('Asia/Tokyo')])
            ->where(fn($q) => $q->where('end_datetime', '>', now('Asia/Tokyo'))->orWhereNull('end_datetime'))
            ->where('editing_flg', false)
            ->latest('start_datetime')
            ->get();

        return view('message.detail', [
            'message' => $message,
            'latest_messages' => $latest_messages,
        ]);
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

            $message_content = MessageContent::where('message_id', $message->id)->get()->toArray();

            // 既読をつける
            $user->message()->updateExistingPivot($message_id, [
                'read_flg' => true,
                'readed_datetime' => Carbon::now(),
            ]);
            DB::commit();

            // detailメソッドにリダイレクト
            $url = action([MessageController::class, 'detail'], ['message_id' => $message_id]);
            return redirect()->to($url)->withInput();

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

        $query = $user->crew()
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
            ->orderBy("name_kana", 'asc');

        $crews = [];
        $query->chunk(100, function ($chunk) use (&$crews) {
            foreach ($chunk as $crew) {
                $crews[] = $crew;
            }
        });

        return response()->json([
            'crews' => $crews,
        ], 200);
    }

    public function getCrewsMessage(Request $request)
    {
        $message = $request->input('message');
        $text = $request->input('text');
        $user = session('member');

        $query = DB::table('messages as m')
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
            ->orderBy('c.name_kana', 'asc');

        $crews = [];
        $query->chunk(100, function ($chunk) use (&$crews) {
            foreach ($chunk as $crew) {
                $crews[] = $crew;
            }
        });

        return response()->json([
            'crews' => $crews,
        ], 200);
    }

    private function attachFilesToMessage(&$message)
    {
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
}
