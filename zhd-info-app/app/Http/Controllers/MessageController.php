<?php

namespace App\Http\Controllers;

use App\Enums\SearchPeriod;
use App\Http\Requests\MessagesSearchRequest;
use App\Models\Crew;
use App\Models\MessageCategory;
use App\Models\MessageContent;
use App\Models\Message;
use Carbon\Carbon;
use Carbon\CarbonInterface as CI;
use App\Utils\OutputContentPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use setasign\Fpdi\TcpdfFpdi;

require_once(resource_path("outputpdf/libs/tcpdf/tcpdf.php"));
require_once(resource_path("outputpdf/libs/fpdi/autoload.php"));

class MessageController extends Controller
{
    public function index(MessagesSearchRequest $request)
    {
        session()->put('current_url', $request->fullUrl());
        $keyword = $request->input('keyword');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        $user = session("member");

        $today = Carbon::now();
        $temp = $today->copy()->startOfWeek()->subWeeks(4);
        $query_date = $this->daysBetween($temp, $today);

        $messages = DB::table('messages')
            ->leftjoin('message_user as m_u', 'messages.id', '=', 'm_u.message_id')
            ->where('m_u.user_id', $user->id)
            ->where('start_datetime', '<=', $today)
            ->where('end_datetime', '>=', $query_date)
            ->orderBy('id', 'desc')
            ->select(['messages.id', 'messages.title', 'messages.content_url', 'messages.start_datetime', 'messages.end_datetime'])
            ->get()
            ->map(function ($m) {
                $m->start = Carbon::parse($m->start_datetime);
                $m->end   = Carbon::parse($m->end_datetime);
                return $m;
            });

        $messages_by_day = [];
        $messages_by_week_partial = [];
        $messages_by_week_full = [];

        $dayStart = $today->copy()->subDays(6);
        for ($d = $today->copy(); $d->gte($dayStart); $d->subDay()) {
            $label = $this->formatDateWithWeekdayJp($d->format('Y-m-d'));
            foreach ($messages as $message) {
                if ($d->between($message->start, $message->end)) {
                    $messages_by_day[$label][] = [
                        'title' => $message->title,
                        'url'   => route('message.detail', ['message_id' => $message->id, 'message_content_url' => $message->content_url]),
                    ];
                }
            }
        }

        $partialStart = $dayStart->copy()->startOfWeek(CI::MONDAY);
        $partialEnd   = $dayStart->copy()->subDay();
        $hasPartial = $partialStart->lte($partialEnd);
        if ($hasPartial) {
            $label = $this->formatRangeJp($partialStart, $partialEnd);
            foreach ($messages as $message) {
                if ($message->start->lte($partialEnd) && $message->end->gte($partialStart)) {
                    $messages_by_week_partial[$label][] = [
                        'title' => $message->title,
                        'url'   => route('message.detail', ['message_id' => $message->id, 'message_content_url' => $message->content_url]),
                    ];
                }
            }
        }

        $weeks = 3;
        $firstFullWeekStart = $hasPartial
            ? $partialStart->copy()->subWeek()
            : $today->copy()->startOfWeek(CI::MONDAY)->subWeek();

        for ($w = 0; $w < $weeks; $w++) {
            $ws = $firstFullWeekStart->copy()->subWeeks($w);
            $we = $ws->copy()->endOfWeek(CI::SUNDAY);
            $label = $this->formatRangeJp($ws, $we);
            foreach ($messages as $message) {
                if ($message->start->lte($we) && $message->end->gte($ws)) {
                    $messages_by_week_full[$label][] = [
                        'title' => $message->title,
                        'url'   => route('message.detail', ['message_id' => $message->id, 'message_content_url' => $message->content_url]),
                    ];
                }
            }
        }

        $search_query = DB::table('messages')
            ->leftJoin('message_user as m_u', 'messages.id', '=', 'm_u.message_id')
            ->where('m_u.user_id', $user->id);

        if ($keyword) {
            $search_query->where('messages.title', 'like', "%{$keyword}%");
        }


        if ($start_date && $end_date) {
            $start_carbon = Carbon::createFromFormat('Y-m-d', $start_date)->startOfDay();
            $end_carbon = Carbon::createFromFormat('Y-m-d', $end_date)->endOfDay();
            $search_query->where('messages.end_datetime', '>=', $start_carbon)
                ->where('messages.start_datetime', '<=', $end_carbon);
        } elseif ($start_date) {
            $start_carbon = Carbon::createFromFormat('Y-m-d', $start_date)->startOfDay();
            $search_query->where('end_datetime', '>=', $start_carbon);
        } elseif ($end_date) {
            $end_carbon = Carbon::createFromFormat('Y-m-d', $end_date)->endOfDay();
            $search_query->where('start_datetime', '<=', $end_carbon);
        }

        $search_msgs = $search_query
            ->orderBy('id', 'desc')
            ->select([
                'messages.id',
                'messages.title',
                'messages.content_url',
                'messages.start_datetime',
                'messages.end_datetime'
            ])
            ->get();

        $search_messages = [];
        foreach ($search_msgs as $m) {
            $search_messages[][] = [
                'title' => $m->title,
                'url'   => route('message.detail', ['message_id' => $m->id, 'message_content_url' => $m->content_url]),
            ];
        }

        return view(
            'message.index',
            compact(
                'messages_by_day',
                'messages_by_week_partial',
                'messages_by_week_full',
                'start_date',
                'end_date',
                'keyword',
                'search_messages'
            )
        );
    }

    private function daysBetween(Carbon $start, Carbon $end): array
    {
        $days = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $days[] = $d->format('Y-m-d');
        }
        return array_reverse($days);
    }

    private function formatDateWithWeekdayJp(string $ymd): string
    {
        $dow = ['日', '月', '火', '水', '木', '金', '土'];
        $c = Carbon::createFromFormat('Y-m-d', $ymd);
        return $c->format("Y/m/d") . '(' . $dow[$c->dayOfWeek] . ')';
    }

    private function formatRangeJp($start, $end): string
    {
        $s = $start instanceof Carbon ? $start : Carbon::parse($start, 'Asia/Tokyo');
        $e = $end   instanceof Carbon ? $end   : Carbon::parse($end,   'Asia/Tokyo');
        return $s->isoFormat('YYYY/M/D(ddd)') . ' - ' . $e->isoFormat('YYYY/M/D(ddd)');
    }

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

        return view('message.detail', [
            'message' => $message,
        ]);
    }

    public function search(Request $request)
    {
        $user = session('member');
        $param = [
            'keyword' => $request['keyword'],
            'start_date' => $request['start_date'],
            'end_date' => $request['end_date'],
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

            // SKの場合、PDFを別ページで表示
            if ($message->organization1_id === 8) {
                // detailメソッドにリダイレクト
                $url = action([MessageController::class, 'detail'], ['message_id' => $message_id]);
                return redirect()->to($url)->withInput();
            } else {
                if (!empty($message_content)) {
                    if (count($message_content) > 1) {
                        $first_content = $message_content[0];
                        if ($message->content_name !== $first_content['content_name']) {
                            $message->content_url = $first_content['content_url'];
                        }
                    } else {
                        $single_content = $message_content[0];
                        $message->content_url = $single_content['content_url'];
                    }
                }
                // 既読が無事できたらpdfへ
                return redirect()->to($message->content_url)->withInput();
            }
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
