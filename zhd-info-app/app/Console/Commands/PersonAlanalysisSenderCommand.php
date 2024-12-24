<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Message;
use App\Models\Organization1;
use App\Models\WowtalkRecipient;
use App\Utils\SESMailer;
use App\Models\EmailSendLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PersonAlanalysisSenderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'person:alanalysis-sender';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '閲覧状況のサマリをメール送信するコマンドです。';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        // メモリ制限を無効にする
        ini_set('memory_limit', '-1');

        $this->info('閲覧状況のサマリをメール送信開始');
        $messageLog = new EmailSendLog();
        try {
            // 閲覧状況のサマリメール送信のログを作成
            $messageLog->email = '';
            $messageLog->subject = '';
            $messageLog->command_name = $this->signature;
            $messageLog->started_at = Carbon::now();
            $messageLog->status = true;
            $messageLog->save();

            // 閲覧状況メッセージ送信のメイン処理を実行
            $this->sendPersonAlanalysis();

            // 成功時の処理
            $messageLog->finished_at = Carbon::now();
            $messageLog->save();

            $this->info('閲覧状況のサマリをメール送信完了');
        } catch (\Throwable $th) {
            // エラー発生時にログを更新し、エラーメッセージを記録
            $this->finalizeLog($messageLog, false, $th->getMessage());
        } finally {
            // 処理後にメモリ制限を元に戻す
            ini_restore('memory_limit');
        }
    }


    /**
     * ログを完了させるメソッド
     * 処理の終了時に呼び出され、ログの終了時刻とステータスを更新します。
     *
     * @param WowTalkNotificationLog|null $log ログオブジェクト
     * @param bool $status 処理が成功したかどうかのステータス
     * @param string|null $errorMessage エラーメッセージ（エラーが発生した場合）
     */
    private function finalizeLog($log, $status, $errorMessage = null)
    {
        if (!$log) {
            // ログオブジェクトが null の場合、新しいログを作成
            $log = new EmailSendLog();
            $log->email = '';
            $log->subject = '';
            $log->command_name = $this->signature;
            $log->started_at = Carbon::now();
        }

        $log->status = $status;
        if (!$status && $errorMessage) {
            $log->error_message = $errorMessage;
        }
        $log->finished_at = Carbon::now();
        $log->save();
    }


    /**
     * メインの閲覧状況メール送信処理
     * データベースからすべてのメッセージを取得し、それぞれについて送信処理を行います。
     * 結果に応じてログを分類し、最終的にログを出力します。
     */
    private function sendPersonAlanalysis()
    {
        // 全業態を取得
        $organization1_list = Organization1::get();

        // 各種ログ用の配列を初期化
        $successLogs = [];
        $failureLogs = [];
        $errorLogs = [];

        foreach ($organization1_list as $organization1) {
            $sendResult = $this->processItem($organization1);

            // 処理結果に応じてログを分類
            switch ($sendResult['status']) {
                case 'success':
                    $successLogs[] = [
                        'org1_name' => $organization1->name,
                    ];
                    break;
                case 'failure':
                    $failureLogs[] = [
                        'org1_name' => $organization1->name,
                        'email' => $sendResult['email'] ?? '',
                        'subject' => $sendResult['subject'] ?? '',
                        'error_message' => $sendResult['error_message'] ?? '不明なエラー'
                    ];
                    break;
                case 'error':
                    $errorLogs[] = [
                        'org1_name' => $organization1->name,
                        'email' => $sendResult['email'] ?? '',
                        'subject' => $sendResult['subject'] ?? '',
                        'error_message' => $sendResult['error_message'] ?? '不明なエラー'
                    ];
                    break;
            }
        }

        // ログの出力
        $this->logResults($successLogs, $failureLogs, $errorLogs);
    }


    /**
     * 各業態の閲覧状況データ取得の処理
     * 各組織が送信対象かを確認し、対象であれば送信処理を実行します。
     *
     * @param Organization1 $org1 組織オブジェクト
     * @return array 処理結果のレスポンス（成功、失敗、エラーのいずれか）
     */
    private function processItem($organization1)
    {
        $organizations = [];
        $viewrates = [];
        $messages = [];

        // 業務連絡を9件取得
        $messages = Message::query()
            ->select('messages.*')
            ->leftJoin('message_user', 'message_user.message_id', '=', 'messages.id')
            ->leftJoin('users', 'message_user.user_id', '=', 'users.id')
            ->leftJoin('shops', 'shops.id', '=', 'message_user.shop_id')
            ->where('start_datetime', '<=', now('Asia/Tokyo'))
            ->where('editing_flg', false)
            ->where('messages.organization1_id', '=', $organization1->id)
            ->orderBy('messages.id', 'desc')
            ->groupBy('messages.id')
            ->limit(1)
            ->get();
        // 業務連絡の10件のidを取得
        $_messages = $messages->pluck('id')->toArray();

        // DS, AR, BLがあるかで処理を分ける
        if ($organization1->isExistOrg3()) {
            $organizations[] = "DS";
            $organization_list["DS"] = $organization1->getOrganization3();
        }
        if ($organization1->isExistOrg4()) {
            $organizations[] = "AR";
            $organization_list["AR"] = $organization1->getOrganization4();
        }
        if ($organization1->isExistOrg5()) {
            $organizations[] = "BL";
            $organization_list["BL"] = $organization1->getOrganization5();
        }

        foreach ($_messages as $key => $ms) {
            // 業業 (計)
            $viewrate_org1 = DB::table('message_user')
                ->select([
                    DB::raw('count(crews.id) as count'),
                    DB::raw('count(crew_message_logs.crew_id) as readed_count'),
                    DB::raw('round((count(crew_message_logs.crew_id) / count(crews.id)) * 100, 1)  as view_rate')
                ])
                ->leftJoin('messages', 'message_user.message_id', '=', 'messages.id')
                ->leftJoin('shops', 'message_user.shop_id', '=', 'shops.id')
                ->leftJoin('organization1', 'shops.organization1_id', '=', 'organization1.id')
                ->leftJoin('users', 'message_user.user_id', '=', 'users.id')
                ->leftJoin('crews', 'crews.user_id', '=', 'users.id')
                ->leftJoin('crew_message_logs', function ($join) use ($ms) {
                    $join->on('crew_message_logs.crew_id', '=', 'crews.id')
                        ->where('crew_message_logs.message_id', '=', $ms);
                })
                ->where('messages.id', '=', $ms)
                ->groupBy('shops.organization1_id')
                ->get();

            $viewrates['org1'][] = $viewrate_org1;
            $viewrates['org1_sum'] = ($viewrates['org1_sum'] ?? 0) + ($viewrate_org1[0]->count ?? 0);
            $viewrates['org1_readed_sum'] = ($viewrates['org1_readed_sum'] ?? 0) +  ($viewrate_org1[0]->readed_count ?? 0);

            // 組織ごと
            if (in_array('DS', $organizations)) {
                $viewrates_org_sub =
                    DB::table('message_user')
                    ->select([
                        DB::raw('shops.organization3_id as o3_id'),
                        DB::raw('count(crews.id) as count'),
                        DB::raw('count(crew_message_logs.id) as readed_count'),
                        DB::raw('round((count(crew_message_logs.id) / count(crews.id)) * 100, 1) as view_rate')
                    ])
                    ->leftJoin('users', 'users.id', '=', 'message_user.user_id')
                    ->leftJoin('crews', 'crews.user_id', '=', 'users.id')
                    ->leftJoin('crew_message_logs', function ($join) use ($ms) {
                        $join->on('crew_message_logs.crew_id', '=', 'crews.id')
                            ->where('crew_message_logs.message_id', '=', $ms);
                    })
                    ->leftJoin('shops', 'message_user.shop_id', '=', 'shops.id')
                    ->where('message_user.message_id', '=', $ms)
                    ->groupBy('shops.organization3_id');

                $viewrate =
                    DB::table('shops')
                    ->select([
                        DB::raw('organization3.id as id'),
                        DB::raw('organization3.order_no as order_no'),
                        DB::raw('organization3.name as name'),
                        DB::raw('sub.count as count'),
                        DB::raw('sub.readed_count as readed_count'),
                        DB::raw('sub.view_rate as view_rate')
                    ])
                    ->leftJoin('organization3', 'shops.organization3_id', '=', 'organization3.id')
                    ->leftJoinSub($viewrates_org_sub, 'sub', function ($join) {
                        $join->on('shops.organization3_id', '=', 'sub.o3_id');
                    })
                    ->where('shops.organization1_id', '=', $organization1->id)
                    ->groupBy('shops.organization3_id', 'sub.count', 'sub.readed_count', 'sub.view_rate')
                    ->orderBy('organization3.order_no')
                    ->get();

                $viewrates['DS'][] = $viewrate;
                $viewrates_array = $viewrate->toArray();
                foreach ($viewrates_array as $key => $value) {
                    $viewrates['DS_sum'][$value->id] = ($viewrates['DS_sum'][$value->id] ?? 0) + $value->count;
                    $viewrates['DS_readed_sum'][$value->id] = ($viewrates['DS_readed_sum'][$value->id] ?? 0) + $value->readed_count;
                }
            }
            if (in_array('AR', $organizations)) {
                $viewrates_org_sub =
                    DB::table('message_user')
                    ->select([
                        DB::raw('shops.organization4_id as o4_id'),
                        DB::raw('count(crews.id) as count'),
                        DB::raw('count(crew_message_logs.id) as readed_count'),
                        DB::raw('round((count(crew_message_logs.id) / count(crews.id)) * 100, 1) as view_rate')
                    ])
                    ->leftJoin('users', 'users.id', '=', 'message_user.user_id')
                    ->leftJoin('crews', 'crews.user_id', '=', 'users.id')
                    ->leftJoin('crew_message_logs', function ($join) use ($ms) {
                        $join->on('crew_message_logs.crew_id', '=', 'crews.id')
                            ->where('crew_message_logs.message_id', '=', $ms);
                    })
                    ->leftJoin('shops', 'message_user.shop_id', '=', 'shops.id')
                    ->where('message_user.message_id', '=', $ms)
                    ->groupBy('shops.organization4_id');

                $viewrate =
                    DB::table('shops')
                    ->select([
                        DB::raw('organization4.id as id'),
                        DB::raw('organization4.order_no as order_no'),
                        DB::raw('organization4.name as name'),
                        DB::raw('sub.count as count'),
                        DB::raw('sub.readed_count as readed_count'),
                        DB::raw('sub.view_rate as view_rate')
                    ])
                    ->leftJoin('organization4', 'shops.organization4_id', '=', 'organization4.id')
                    ->leftJoinSub($viewrates_org_sub, 'sub', function ($join) {
                        $join->on('shops.organization4_id', '=', 'sub.o4_id');
                    })
                    ->where('shops.organization1_id', '=', $organization1->id)
                    ->groupBy('shops.organization4_id', 'sub.count', 'sub.readed_count', 'sub.view_rate')
                    ->orderBy('organization4.order_no')
                    ->get();

                $viewrates['AR'][] = $viewrate;
                $viewrates_array = $viewrate->toArray();
                foreach ($viewrates_array as $key => $value) {
                    $viewrates['AR_sum'][$value->id] = ($viewrates['AR_sum'][$value->id] ?? 0) + $value->count;
                    $viewrates['AR_readed_sum'][$value->id] = ($viewrates['AR_readed_sum'][$value->id] ?? 0) + $value->readed_count;
                }
            }
            if (in_array('BL', $organizations)) {
                $viewrates_org_sub =
                    DB::table('message_user')
                    ->select([
                        DB::raw('shops.organization5_id as o5_id'),
                        DB::raw('count(crews.id) as count'),
                        DB::raw('count(crew_message_logs.id) as readed_count'),
                        DB::raw('round((count(crew_message_logs.id) / count(crews.id)) * 100, 1) as view_rate')
                    ])
                    ->leftJoin('users', 'users.id', '=', 'message_user.user_id')
                    ->leftJoin('crews', 'crews.user_id', '=', 'users.id')
                    ->leftJoin('crew_message_logs', function ($join) use ($ms) {
                        $join->on('crew_message_logs.crew_id', '=', 'crews.id')
                            ->where('crew_message_logs.message_id', '=', $ms);
                    })
                    ->leftJoin('shops', 'message_user.shop_id', '=', 'shops.id')
                    ->where('message_user.message_id', '=', $ms)
                    ->groupBy('shops.organization5_id');

                $viewrate =
                    DB::table('shops')
                    ->select([
                        DB::raw('organization5.id as id'),
                        DB::raw('organization5.order_no as order_no'),
                        DB::raw('organization5.name as name'),
                        DB::raw('sub.count as count'),
                        DB::raw('sub.readed_count as readed_count'),
                        DB::raw('sub.view_rate as view_rate')
                    ])
                    ->leftJoin('organization5', 'shops.organization5_id', '=', 'organization5.id')
                    ->leftJoinSub($viewrates_org_sub, 'sub', function ($join) {
                        $join->on('shops.organization5_id', '=', 'sub.o5_id');
                    })
                    ->where('shops.organization1_id', '=', $organization1->id)
                    ->groupBy('shops.organization5_id', 'sub.count', 'sub.readed_count', 'sub.view_rate')
                    ->orderBy('organization5.order_no')
                    ->get();

                $viewrates['BL'][] = $viewrate;
                $viewrates_array = $viewrate->toArray();
                foreach ($viewrates_array as $key => $value) {
                    $viewrates['BL_sum'][$value->id] = ($viewrates['BL_sum'][$value->id] ?? 0) + $value->count;
                    $viewrates['BL_readed_sum'][$value->id] = ($viewrates['BL_readed_sum'][$value->id] ?? 0) + $value->readed_count;
                }
            }

            // 店舗ごと
            $viewrate_sub = DB::table('message_user')
                ->select([
                    DB::raw('message_user.shop_id as _shop_id'),
                    DB::raw('count(crews.id) as count'),
                    DB::raw('count(crew_message_logs.id) as readed_count'),
                    DB::raw('round((count(crew_message_logs.id) / count(crews.id)) * 100, 1) as view_rate')
                ])
                ->leftJoin('users', 'users.id', '=', 'message_user.user_id')
                ->leftJoin('crews', 'crews.user_id', 'users.id')
                ->leftJoin('crew_message_logs', function ($join) use ($ms) {
                    $join->on('crew_message_logs.crew_id', '=', 'crews.id')
                        ->where('crew_message_logs.message_id', '=', $ms);
                })
                ->where('message_user.message_id', '=', $ms)
                ->groupBy('message_user.shop_id');

            $viewrate = DB::table('shops')
                ->select([
                    DB::raw('organization5.name as o5_name'),
                    DB::raw('organization4.name as o4_name'),
                    DB::raw('organization3.name as o3_name'),
                    DB::raw('shops.name as shop_name'),
                    DB::raw('shops.shop_code as shop_code'),
                    DB::raw('view_rate.*')
                ])
                ->leftJoin('organization5', 'shops.organization5_id', '=', 'organization5.id')
                ->leftJoin('organization4', 'shops.organization4_id', '=', 'organization4.id')
                ->leftJoin('organization3', 'shops.organization3_id', '=', 'organization3.id')
                ->leftJoinSub($viewrate_sub, 'view_rate', function ($join) {
                    $join->on('shops.id', '=', 'view_rate._shop_id');
                })
                ->where('shops.organization1_id', '=', $organization1->id)
                ->orderBy('organization3.order_no')
                ->orderBy('organization4.order_no')
                ->orderBy('organization5.order_no')
                ->orderBy('shops.shop_code')
                ->groupBy(
                    'shops.id',
                    'shops.shop_code',
                    'shops.name',
                    'organization3.name',
                    'organization3.order_no',
                    'organization4.name',
                    'organization4.order_no',
                    'organization5.name',
                    'organization4.order_no',
                )
                ->get();

            $viewrates['shop'][] = $viewrate;
            $viewrates_array = $viewrate->toArray();
            foreach ($viewrates_array as $key => $value) {
                $viewrates['shop_sum'][$value->shop_code] = ($viewrates['shop_sum'][$value->shop_code] ?? 0) + $value->count;
                $viewrates['shop_readed_sum'][$value->shop_code] = ($viewrates['shop_readed_sum'][$value->shop_code] ?? 0) + $value->readed_count;
            }
        }

        // エクセルファイルを生成
        $excelFilePath = null;
        try {
            $excelFilePath = $this->generateExcelFile($organization_list, $organization1, $messages, $organizations, $viewrates);
        } catch (\Exception $e) {
            $this->error("エクセルファイルの生成に失敗しました: " . $e->getMessage());
            return $this->createFailureResponse($organization1);
        }

        // コレクションから最初のメッセージを取得
        $messages = $messages->first();

        if ($messages && $viewrates && $organizations && $excelFilePath) {
            // メール送信処理の実行
            return $this->sendPersonAlanalysisMail($organization_list, $organization1, $messages, $organizations, $viewrates, $excelFilePath);
        }

        return $this->createFailureResponse($organization1);
    }


    /**
     * 閲覧状況メールを送信するメソッド
     * 各店舗に対して閲覧状況メールを送信します。
     *
     * @param Organization1 $organization1 組織オブジェクト
     * @param array $messages メッセージオブジェクト
     * @param array $viewrates 閲覧状況データ
     * @param array $organizations 組織オブジェクト
     * @param string $excelFilePath Excelファイルのパス
     * @return array メッセージ送信結果のレスポンス（成功、失敗、エラーのいずれか）
     */
    private function sendPersonAlanalysisMail($organization_list, $organization1, $messages, $organizations, $viewrates, $excelFilePath)
    {
        // 通知対象の店舗とWowTalk IDを取得
        $messageIds = is_array($messages) ? $messages['id'] : [$messages['id']];

        $user_role_data = [];
        $user_role_data = DB::table('users_roles')
            ->join('message_user', function ($join) use ($messageIds) {
                $join->on('users_roles.user_id', '=', 'message_user.user_id')
                        ->on('users_roles.shop_id', '=', 'message_user.shop_id')
                        ->whereIn('message_user.message_id', $messageIds);
            })
            ->where(function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->whereNotNull('users_roles.DM_email')
                            ->where('users_roles.DM_view_notification', true);
                })
                ->orWhere(function ($subQuery) {
                    $subQuery->whereNotNull('users_roles.BM_email')
                            ->where('users_roles.BM_view_notification', true);
                })
                ->orWhere(function ($subQuery) {
                    $subQuery->whereNotNull('users_roles.AM_email')
                            ->where('users_roles.AM_view_notification', true);
                });
            })
            ->select('message_user.message_id', 'users_roles.DM_email', 'users_roles.BM_email', 'users_roles.AM_email')
            ->get()
            ->map(function ($result) {
                return [
                    'message_id' => $result->message_id,
                    'DM_email' => $result->DM_email,
                    'BM_email' => $result->BM_email,
                    'AM_email' => $result->AM_email,
                ];
            })
            ->toArray();

        // 通知対象のユーザーがない場合
        if (empty($user_role_data)) {
            return $this->createFailureResponse($organization1);
        }

        // エラーログのための配列
        $errorLogs = [];
        try {
            foreach ($user_role_data as $data) {
                try {
                    // メール送信
                    $this->calculateAndGenerateMessageContent($data, $messages, $organizations, $viewrates, $excelFilePath);
                } catch (\Exception $e) {
                    // エラーログを生成
                    $errorLogs[] = $this->createErrorLog($organization1, $data, $messages, 'メール送信に失敗しました。');
                }
            }
        } finally {
            // CSVファイルを削除
            if ($excelFilePath && file_exists($excelFilePath)) {
                unlink($excelFilePath);
            }
        }

        // すべてのAPI処理が終了した後でエラーログを集約
        if (!empty($errorLogs)) {
            // foreach ($errorLogs as $errorLog) {
            //     $this->notifySystemAdmin(
            //         'message_content_error',
            //         [
            //             'org1_name' => $errorLog['org1_name'],
            //             'DM_email' => $errorLog['DM_email'],
            //             'BM_email' => $errorLog['BM_email'],
            //             'AM_email' => $errorLog['AM_email'],
            //             'message_id' => $errorLog['message_id'],
            //             'message_title' => $errorLog['message_title'],
            //             'response_target' => $errorLog['response_target']
            //         ],
            //         [
            //             'error_message' => $errorLog['error_message'],
            //             'status' => 'error',
            //             'response_target' => $errorLog['response_target']
            //         ]
            //     );
            // }

            // エラー時のレスポンスを返す
            $errorMessageString = implode('; ', array_unique(array_column($errorLogs, 'error_message')));
            return $this->createErrorResponse($organization1, $user_role_data, $messages, $errorMessageString);
        }

        // 成功時のレスポンスを返す
        return $this->createSuccessResponse($organization1);
    }


    /**
     * Excelファイルを生成するメソッド
     *
     * @param array $organization_list 組織オブジェクト
     * @param array $organization1 組織オブジェクト
     * @param array $messages メッセージオブジェクト
     * @param array $organizations 組織オブジェクト
     * @param array $viewrates 閲覧状況データ
     * @return string 生成されたExcelファイルのパス
     * @throws \Exception Excelファイルの生成に失敗した場合
     */
    private function generateExcelFile($organization_list, $organization1, $messages, $organizations, $viewrates)
    {
        // スプレッドシートを作成
        $spreadsheet = new Spreadsheet();

        // 1つ目のデータセットをBladeテンプレートで生成
        $organization1Name = $organization1['name'];
        $now = Carbon::now();
        $fileName = '閲覧状況_' . $organization1Name . $now->format('_Y_m_d') . '.xlsx';
        $filePath = Storage::path($fileName);

        $header1 = ['DS', 'BL', 'AL', '店舗コード', '店舗名', '閲覧数 / 合計', '閲覧率', $messages['title'] ?? ''];
        $csvData1[] = $header1;

        if (isset($viewrates['shop'][0])) {
            $viewrateShop = $viewrates['shop'][0];
            foreach ($viewrateShop as $m_c) {
                $total = $viewrates['shop_sum'][$m_c->shop_code] ?? 0;
                $readed = $viewrates['shop_readed_sum'][$m_c->shop_code] ?? 0;

                // ゼロチェックを追加
                $viewRate = $total > 0 ? number_format(($readed / $total) * 100, 1) . '%' : '0%';

                $row = [
                    $m_c->o3_name ?? '',
                    $m_c->o5_name ?? '',
                    $m_c->o4_name ?? '',
                    $m_c->shop_code,
                    $m_c->shop_name,
                    "{$readed} / {$total}",
                    $viewRate
                ];

                $csvData1[] = $row;
            }
        }

        // 1つ目のシートにデータを書き込み
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('店舗ごとの閲覧状況');
        $sheet1->fromArray($csvData1, null, 'A1');

        // 2つ目のデータセットを直接生成
        $csvData2 = [];
        $header2 = ['DS', 'BL', 'AL', '店舗コード', '店舗名', '閲覧数 / 合計', '閲覧率', $messages['title'] ?? ''];
        $csvData2[] = $header2;

        if (isset($viewrates['shop'][0])) {
            $viewrateShop = $viewrates['shop'][0];
            foreach ($viewrateShop as $m_c) {
                $total = $viewrates['shop_sum'][$m_c->shop_code] ?? 0;
                $readed = $viewrates['shop_readed_sum'][$m_c->shop_code] ?? 0;

                // ゼロチェックを追加
                $viewRate = $total > 0 ? number_format(($readed / $total) * 100, 1) . '%' : '0%';

                $row = [
                    $m_c->o3_name ?? '',
                    $m_c->o5_name ?? '',
                    $m_c->o4_name ?? '',
                    $m_c->shop_code,
                    $m_c->shop_name,
                    "{$readed} / {$total}",
                    $viewRate
                ];

                $csvData2[] = $row;
            }
        }

        // 新しいシートを作成してデータを書き込み
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('ユーザー単位の閲覧状況');
        $sheet2->fromArray($csvData2, null, 'A1');

        // Excelファイルに書き込み
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        // ファイルが正しく保存されたか確認
        if (!Storage::exists($fileName)) {
            throw new \Exception("Excelファイルの保存に失敗しました。");
        }

        return $filePath;
    }


    /**
     * 各業態の閲覧率などのメッセージ内容を生成するメソッド
     *
     * @param Message $message メッセージオブジェクト
     * @param int $shopId 店舗ID
     * @return string|array 生成されたメッセージ内容またはエラーログ
     * @throws \Exception メール送信に失敗した場合
     */
    private function calculateAndGenerateMessageContent($user_role_data, $messages, $organizations, $viewrates, $filePath)
    {
        // 通知対象のメールアドレスを取得
        $to = array_filter([$user_role_data['DM_email'], $user_role_data['BM_email'], $user_role_data['AM_email']]);
        $subject =  $messages['start_datetime'] . ' | ' . $messages['title'] . ' _ ' . now();

        // メッセージ内容を生成
        $message = "組織ごとの閲覧率レポートをご確認ください。\n\n";
        $message .= "組織名 | 閲覧数 / 合計 | " . collect([$messages])->map(fn($ms) => $ms['title'])->implode(' | ') . "\n";
        $message .= str_repeat('-', 50) . "\n";

        foreach ($organizations as $organization) {
            if (isset($viewrates[$organization][0])) {
                foreach ($viewrates[$organization][0] as $v_org_key => $v_o) {
                    $message .= "{$v_o->name} | ";
                    foreach ($messages as $key => $ms) {
                        if (isset($viewrates[$organization][$key][$v_org_key]->count)) {
                            $message .= "{$viewrates[$organization][$key][$v_org_key]->readed_count} / {$viewrates[$organization][$key][$v_org_key]->count} ({$viewrates[$organization][$key][$v_org_key]->view_rate}%) | ";
                        } else {
                            $message .= "0 / 0 (0.0%) | ";
                        }
                    }
                    $message .= "\n";
                }
            }
        }

        // メール送信
        $mailer = new SESMailer();
        if ($mailer->sendEmail($to, $subject, $message, $filePath)) {
            $this->info("閲覧率のメールを送信しました。");
        } else {
            $this->error("メール送信中にエラーが発生しました。");
        }
    }


    /**
     * システム管理者にエラーを通知する関数
     *
     * @param string $errorType エラータイプ ('api_error' または 'message_content_error')
     * @param array $requestData リクエストに関するデータ
     * @param array|string $responseData レスポンスに関するデータまたはエラーメッセージ
     */
    // private function notifySystemAdmin($errorType, $requestData, $responseData)
    // {
    //     // DBから通知対象のメールアドレスを取得
    //     $to = WowtalkRecipient::where('target', true)->pluck('email')->toArray();
    //     $subject = '【業連・動画配信システム】閲覧状況メール送信エラー';

    //     $message = "メール送信でエラーが発生しました。ご確認ください。\n\n";
    //     $message .= "■エラー内容\n" . ucfirst($errorType) . "が発生しました。\n\n";

    //     // リクエストデータ
    //     if (is_array($requestData)) {
    //         // 基本情報
    //         $message .= "■基本情報\n";
    //         $message .= "業態コード : " . ($requestData['org1_name'] ?? '') . "\n";
    //         $message .= "DM_email : " . ($requestData['DM_email'] ?? '') . "\n";
    //         $message .= "BM_email : " . ($requestData['BM_email'] ?? '') . "\n";
    //         $message .= "AM_email : " . ($requestData['AM_email'] ?? '') . "\n";
    //         $message .= "業連ID : " . ($requestData['message_id'] ?? '') . "\n";
    //         $message .= "業連名 : " . ($requestData['message_title'] ?? '') . "\n\n";
    //         $message .= "■リクエスト\n";
    //         $message .= "target : " . (is_array($requestData['response_target']) ? implode(', ', $requestData['response_target']) : $requestData['response_target']) . "\n\n";
    //     } else {
    //         $message .= "■リクエスト : $requestData\n\n";
    //     }

    //     // レスポンスデータ
    //     $message .= "■レスポンス\n";
    //     if (is_array($responseData)) {
    //         $message .= "result : " . ($responseData['error_message'] ?? '') . "\n";
    //         $message .= "status : " . ($responseData['status'] ?? '') . "\n";
    //         $message .= "target : " . (is_array($responseData['response_target']) ? implode(', ', $responseData['response_target']) : $responseData['response_target']) . "\n";
    //     } else {
    //         $message .= "エラーメッセージ : $responseData\n";
    //     }

    //     $mailer = new SESMailer();
    //     if ($mailer->sendEmail($to, $subject, $message)) {
    //         $this->info("システム管理者にエラーメールを送信しました。");
    //     } else {
    //         $this->error("メール送信中にエラーが発生しました。");
    //         throw new \Exception("システム管理者にエラーメール送信に失敗しました");
    //     }
    // }


    /**
     * 成功時のレスポンスを生成するメソッド
     *
     * @param Organization1 $organization1 組織オブジェクト
     * @return array 成功レスポンス
     */
    private function createSuccessResponse($organization1)
    {
        return [
            'status' => 'success',
            'org1_name' => $organization1->name
        ];
    }


    /**
     * 失敗時のレスポンスを生成するメソッド
     *
     * @param Organization1 $organization1 組織オブジェクト
     * @return array 失敗レスポンス
     */
    private function createFailureResponse($organization1)
    {
        return [
            'status' => 'failure',
            'org1_name' => $organization1->name
        ];
    }


    /**
     * エラーレスポンスを生成するメソッド
     *
     * @param Organization1 $organization1 組織オブジェクト
     * @param array $user_role_data 通知対象のユーザーデータ
     * @param array $messages メッセージオブジェクト
     * @param string $errorMessage エラーメッセージ
     * @return array エラーレスポンス
     */
    private function createErrorResponse($organization1, $user_role_data, $messages, $errorMessage)
    {
        return [
            'status' => 'error',
            'org1_name' => $organization1->name,
            'email' => $user_role_data[0]['DM_email'] . ',' . $user_role_data[0]['BM_email'] . ',' . $user_role_data[0]['AM_email'],
            'subject' => $messages['start_datetime'] . ' | ' . $messages['title'] . ' _ ' . now(),
            'error_message' => $errorMessage
        ];
    }



    /**
     * エラーログを生成するメソッド
     *
     * @param Organization1 $organization1 組織オブジェクト
     * @param array $data メールデータ
     * @param array $messages メッセージオブジェクト
     * @param string $messageContent 送信されたメッセージ内容
     * @return array エラーログ
     */
    private function createErrorLog($organization1, $data, $messages, $messageContent = null)
    {
        return [
            'type' => 'message_content_error',
            'org1_name' => $organization1->name ?? '',
            'DM_email' => $data['DM_email'] ?? '',
            'BM_email' => $data['BM_email'] ?? '',
            'AM_email' => $data['AM_email'] ?? '',
            'message_id' => $messages['id'],
            'message_title' => $messages['title'],
            'error_message' => $messageContent ?? '',
            'response_target' => array_filter([$data['DM_email'], $data['BM_email'], $data['AM_email']])
        ];
    }


    /**
     * 成功、失敗、エラーログを出力する関数
     */
    private function logResults($successLogs, $failureLogs, $errorLogs)
    {
        $this->info("---送信する---");
        foreach ($successLogs as $log) {
            $label = "業態：";
            $this->info($label . $log['org1_name']);
        }

        $this->info("---送信しない---");
        foreach ($failureLogs as $log) {
            $label = "業態：";
            $this->warn($label . $log['org1_name']);
        }

        $this->info("---送信エラー---");
        foreach ($errorLogs as $log) {
            $label = "業態：";
            $this->error($label . $log['org1_name']);
            $this->error("エラー内容：" . $log['error_message']);

            // エラーログをデータベースに保存
            $this->sendEmailErrorLogsInDatabase($log);
        }
    }


    /**
     * エラーログをデータベースに格納する関数
     */
    private function sendEmailErrorLogsInDatabase($log)
    {
        try {
            $errorLog = new EmailSendLog();
            $errorLog->email = $log['email'];
            $errorLog->subject = $log['subject'];
            $errorLog->command_name = $this->signature;
            $errorLog->started_at = Carbon::now();
            $errorLog->status = false;

            // エラーメッセージを文にして保存
            $errorLog->error_message = $log['org1_name'] . "：" . $log['error_message'];

            $errorLog->finished_at = Carbon::now();
            $errorLog->save();
        } catch (\Exception $e) {
            $this->error("エラーログのデータベース保存に失敗しました: " . $e->getMessage());
        }
    }
}
