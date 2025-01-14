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
use App\Exports\PersonAnalysisExport;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class PersonAnalysisSenderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'person:analysis-sender';


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

        $excelFilePath = null;

        try {
            // ログを作成
            $messageLog = new EmailSendLog();
            $messageLog->email = '';
            $messageLog->subject = '';
            $messageLog->command_name = $this->signature;
            $messageLog->started_at = Carbon::now();
            $messageLog->status = true;
            $messageLog->save();

            // メイン処理を実行
            $this->sendPersonAnalysis();

            // 成功時の処理
            $messageLog->finished_at = Carbon::now();
            $messageLog->save();
        } catch (\Throwable $th) {
            // エラー発生時にログを更新し、エラーメッセージを記録
            $this->finalizeLog($messageLog, false, $th->getMessage());
        } finally {
            // CSVファイルを削除
            if ($excelFilePath && file_exists($excelFilePath)) {
                unlink($excelFilePath);
            }

            // exportフォルダを削除
            $exportDir = storage_path('app/export');
            if (is_dir($exportDir)) {
                $files = array_diff(scandir($exportDir), ['.', '..']);
                foreach ($files as $file) {
                    unlink($exportDir . '/' . $file);
                }
                rmdir($exportDir);
            }

            // 処理後にメモリ制限を元に戻す
            ini_restore('memory_limit');

            $this->info('閲覧状況のサマリをメール送信完了');
        }
    }


    /**
     * ログを完了させるメソッド
     * 処理の終了時に呼び出され、ログの終了時刻とステータスを更新します。
     *
     * @param EmailSendLog|null $messageLog ログオブジェクト
     * @param bool $status 処理が成功したかどうかのステータス
     * @param string|null $errorMessage エラーメッセージ（エラーが発生した場合）
     */
    private function finalizeLog($messageLog, $status, $errorMessage = null)
    {
        if (!$messageLog) {
            // ログオブジェクトが null の場合、新しいログを作成
            $messageLog = new EmailSendLog();
            $messageLog->email = '';
            $messageLog->subject = '';
            $messageLog->command_name = $this->signature;
            $messageLog->started_at = Carbon::now();
        }

        $messageLog->status = $status;
        if (!$status && $errorMessage) {
            $messageLog->error_message = $errorMessage;
        }
        $messageLog->finished_at = Carbon::now();
        $messageLog->save();
    }


    /**
     * メインの閲覧状況メール送信処理
     * データベースからすべてのメッセージを取得し、それぞれについて送信処理を行います。
     * 結果に応じてログを分類し、最終的にログを出力します。
     */
    private function sendPersonAnalysis()
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
        $organization_list = [];
        $viewRates = [];
        $messages = [];
        $message_count = 0;
        $messagesFlg = false;

        // 前週月曜の0:00から前週日曜の23:59に掲載開始した業務連絡を取得
        $startOfLastWeek = now('Asia/Tokyo')->startOfWeek()->subWeek()->format('Y-m-d H:i:s');
        $endOfLastWeek = now('Asia/Tokyo')->endOfWeek()->subWeek()->endOfDay()->format('Y-m-d H:i:s');

        // 業務連絡を取得
        $messages = Message::query()
            ->select('messages.*')
            ->leftJoin('message_user', 'message_user.message_id', '=', 'messages.id')
            ->leftJoin('users', 'message_user.user_id', '=', 'users.id')
            ->leftJoin('shops', 'shops.id', '=', 'message_user.shop_id')
            ->where('start_datetime', '>=', $startOfLastWeek)
            ->where('start_datetime', '<=', $endOfLastWeek)
            ->where('editing_flg', false)
            ->where('messages.organization1_id', '=', $organization1->id)
            ->orderBy('messages.id', 'desc')
            ->groupBy('messages.id')
            ->get();

        if ($messages->isNotEmpty()) {
            // 業務連絡のidを取得
            $_messages = $messages->pluck('id')->toArray();
            $message_count = count($_messages);

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
                // 組織ごと
                if (in_array('BL', $organizations)) {
                    $viewRateOrgSub =
                        DB::table('message_user')
                        ->select([
                            DB::raw('shops.organization5_id as o5_id'),
                            DB::raw('count(crews.id) as count'),
                            DB::raw('count(crew_message_logs.id) as read_count'),
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

                    $viewRate =
                        DB::table('shops')
                        ->select([
                            DB::raw('organization3.name as org3_name'),
                            DB::raw('organization4.name as org4_name'),
                            DB::raw('organization5.id as id'),
                            DB::raw('organization5.order_no as order_no'),
                            DB::raw('organization5.name as org5_name'),
                            DB::raw('sub.count as count'),
                            DB::raw('sub.read_count as read_count'),
                            DB::raw('sub.view_rate as view_rate')
                        ])
                        ->leftJoin('organization3', 'shops.organization3_id', '=', 'organization3.id')
                        ->leftJoin('organization4', 'shops.organization4_id', '=', 'organization4.id')
                        ->leftJoin('organization5', 'shops.organization5_id', '=', 'organization5.id')
                        ->leftJoinSub($viewRateOrgSub, 'sub', function ($join) {
                            $join->on('shops.organization5_id', '=', 'sub.o5_id');
                        })
                        ->where('shops.organization1_id', '=', $organization1->id)
                        ->groupBy('shops.organization3_id', 'shops.organization4_id', 'shops.organization5_id', 'sub.count', 'sub.read_count', 'sub.view_rate')
                        ->orderBy('organization3.order_no')
                        ->orderBy('organization4.order_no')
                        ->orderBy('organization5.order_no')
                        ->get();

                    $viewRates['BL'][] = $viewRate;
                    $viewRatesArray = $viewRate->toArray();
                    foreach ($viewRatesArray as $key => $value) {
                        $viewRates['BL_sum'][$value->id] = ($viewRates['BL_sum'][$value->id] ?? 0) + $value->count;
                        $viewRates['BL_read_sum'][$value->id] = ($viewRates['BL_read_sum'][$value->id] ?? 0) + $value->read_count;
                    }
                }
            }

            // メッセージがある場合はフラグをtrueにする
            $messagesFlg = true;
        }

        // エクセルファイルを生成
        $excelFilePath = null;
        if ($messagesFlg) {
            try {
                $excelFilePath = $this->generateExcelFile($organization1, $startOfLastWeek, $endOfLastWeek);
            } catch (\Exception $e) {
                $this->error("エクセルファイルの生成に失敗しました: " . $e->getMessage());
                return $this->createFailureResponse($organization1);
            }

            // コレクションから最初のメッセージを取得
            $messages = $messages->first();
        }

        // メール送信処理の実行
        return $this->sendPersonAnalysisMail($organization1, $messagesFlg, $messages, $message_count, $viewRates, $startOfLastWeek, $endOfLastWeek, $excelFilePath);

        return $this->createFailureResponse($organization1);
    }


    /**
     * Excelファイルを生成するメソッド
     *
     * @param array $organization1 組織オブジェクト
     * @param string $startOfLastWeek 前週月曜の0:00から前週日曜の23:59に掲載開始した業務連絡の開始日時
     * @param string $endOfLastWeek 前週月曜の0:00から前週日曜の23:59に掲載開始した業務連絡の終了日時
     * @return string 生成されたExcelファイルのパス
     * @throws \Exception Excelファイルの生成に失敗した場合
     */
    private function generateExcelFile($organization1, $startOfLastWeek, $endOfLastWeek)
    {
        $now = new Carbon('now');
        $file_name = '業務連絡閲覧状況_' . $organization1['name'] . $now->format('_Y_m_d') . '.xlsx';

        // exportフォルダが存在しない場合は作成
        $exportDir = storage_path('app/export');
        if (!file_exists($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        // storage/app/exportディレクトリにExcelファイルを生成して保存
        $exportPath = $exportDir . '/' . $file_name;
        $export = new PersonAnalysisExport($organization1, $startOfLastWeek, $endOfLastWeek);
        Excel::store($export, 'export/' . $file_name, 'local');

        // ファイルが正しく保存されたか確認
        if (!Storage::exists('export/' . $file_name)) {
            $this->error("Excelファイルの保存に失敗しました。");
        }

        return $exportPath;
    }


    /**
     * 閲覧状況メールを送信するメソッド
     * 各店舗に対して閲覧状況メールを送信します。
     *
     * @param Organization1 $organization1 組織オブジェクト
     * @param bool $messagesFlg メッセージがあるかどうかのフラグ
     * @param array|null $messages メッセージオブジェクト
     * @param int|null $message_count メッセージの件数
     * @param array|null $viewRates 閲覧状況データ
     * @param string $startOfLastWeek 前週月曜の0:00から前週日曜の23:59に掲載開始した業務連絡の開始日時
     * @param string $endOfLastWeek 前週月曜の0:00から前週日曜の23:59に掲載開始した業務連絡の終了日時
     * @param string|null $excelFilePath Excelファイルのパス
     * @return array メッセージ送信結果のレスポンス（成功、失敗、エラーのいずれか）
     */
    private function sendPersonAnalysisMail($organization1, $messagesFlg, $messages = null, $message_count = null, $viewRates = null, $startOfLastWeek, $endOfLastWeek, $excelFilePath = null)
    {
        // 通知対象の店舗とWowTalk IDを取得
        $messageIds = [];
        if ($messagesFlg) {
            $messageIds = is_array($messages) ? $messages['id'] : [$messages['id']];
        }

        $user_role_data = [];
        $user_role_data = DB::table('users_roles')
            ->when(!empty($messageIds), function ($query) use ($messageIds) {
                $query->join('message_user', function ($join) use ($messageIds) {
                    $join->on('users_roles.user_id', '=', 'message_user.user_id')
                        ->on('users_roles.shop_id', '=', 'message_user.shop_id')
                        ->whereIn('message_user.message_id', $messageIds);
                })
                ->select('message_user.message_id', 'users_roles.DM_email', 'users_roles.BM_email', 'users_roles.AM_email', 'users_roles.DM_view_notification', 'users_roles.BM_view_notification', 'users_roles.AM_view_notification');
            }, function ($query) use ($organization1) {
                $query->join('shops', function ($join) use ($organization1) {
                    $join->on('users_roles.shop_id', '=', 'shops.id')
                        ->where('shops.organization1_id', $organization1->id);
                })
                ->select('users_roles.DM_email', 'users_roles.BM_email', 'users_roles.AM_email', 'users_roles.DM_view_notification', 'users_roles.BM_view_notification', 'users_roles.AM_view_notification');
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
            ->get()
            ->map(function ($result) {
                return [
                    'message_id' => $result->message_id ?? null,
                    'DM_email' => isset($result->DM_view_notification) && $result->DM_view_notification ? $result->DM_email : null,
                    'BM_email' => isset($result->BM_view_notification) && $result->BM_view_notification ? $result->BM_email : null,
                    'AM_email' => isset($result->AM_view_notification) && $result->AM_view_notification ? $result->AM_email : null,
                ];
            })
            ->toArray();

        // 通知対象のユーザーがない場合
        if (empty($user_role_data)) {
            return $this->createFailureResponse($organization1);
        }

        // エラーログのための配列
        $errorLogs = [];

        // メッセージ内容を生成
        $messageContent = null;
        try {
            $messageContent = $this->generateMessageContent($messagesFlg, $message_count, $viewRates);
        } catch (\Exception $e) {
            // メッセージ内容生成に失敗した場合のエラー処理
            $errorLog = $this->createErrorLog(
                $organization1,
                $messages,
                $viewRates,
                'メッセージ生成に失敗しました',
                'message_content_error',
                ['type' => 'message_content_error', 'response_result' => 'メール内容の生成に失敗しました', 'response_status' => $e->getMessage()],
                null,
                $messageContent
            );

            if ($errorLog) {
                $this->notifySystemAdmin(
                    'message_content_error',
                    [
                        'org1_name' => $errorLog['org1_name'] ?? '',
                        'DM_email' => $errorLog['DM_email'] ?? '',
                        'BM_email' => $errorLog['BM_email'] ?? '',
                        'AM_email' => $errorLog['AM_email'] ?? '',
                        'message_id' => $errorLog['message_id'] ?? '',
                        'message_title' => $errorLog['message_title'] ?? '',
                        'response_target' => $errorLog['response_target'] ?? ''
                    ],
                    [
                        'error_message' => $errorLog['error_message'] ?? '',
                        'status' => 'error',
                        'response_target' => $errorLog['response_target'] ?? ''
                    ]
                );
            }

            return $this->createErrorResponse($organization1, $user_role_data, $messages, $e->getMessage());
        }

        // 通知対象のユーザーを50件ずつのバッチに分割してAPIを呼び出す
        $chunkedUserRoleData = array_chunk($user_role_data, 50);
        foreach ($chunkedUserRoleData as $batch) {
            foreach ($batch as $data) {
                try {
                    // メール送信
                    $this->sendMail($data, $organization1, $messageContent, $startOfLastWeek, $endOfLastWeek, $excelFilePath);
                } catch (\Exception $e) {
                    // エラーログを生成
                    $errorLogs[] = $this->createErrorLog(
                        $organization1,
                        $batch,
                        $messages,
                        'メール送信に失敗しました。',
                        'mail_send_error',
                        ['type' => 'mail_send_error', 'response_result' => 'メール送信に失敗しました', 'response_status' => $e->getMessage()],
                        null,
                        $messageContent
                    );
                }
            }
        }

        // すべてのAPI処理が終了した後でエラーログを集約
        if (!empty($errorLogs)) {
            foreach ($errorLogs as $errorLog) {
                $this->notifySystemAdmin(
                    'mail_send_error',
                    [
                        'org1_name' => $errorLog['org1_name'] ?? '',
                        'DM_email' => $errorLog['DM_email'] ?? '',
                        'BM_email' => $errorLog['BM_email'] ?? '',
                        'AM_email' => $errorLog['AM_email'] ?? '',
                        'message_id' => $errorLog['message_id'] ?? '',
                        'message_title' => $errorLog['message_title'] ?? '',
                        'response_target' => $errorLog['response_target'] ?? ''
                    ],
                    [
                        'error_message' => $errorLog['error_message'] ?? '',
                        'status' => 'error',
                        'response_target' => $errorLog['response_target'] ?? ''
                    ]
                );
            }

            // エラー時のレスポンスを返す
            $errorMessageString = implode('; ', array_unique(array_column($errorLogs, 'error_message')));
            return $this->createErrorResponse($organization1, $user_role_data, $messages, $errorMessageString);
        }

        // 成功時のレスポンスを返す
        return $this->createSuccessResponse($organization1);
    }


    /**
     * 各業態の閲覧率などのメッセージ内容を生成するメソッド
     *
     * @param bool $messagesFlg メッセージがあるかどうかのフラグ
     * @param int|null $message_count メッセージの件数
     * @param array|null $viewRates 閲覧状況データ
     * @return string|array 生成されたメッセージ内容またはエラーログ
     * @throws \Exception メール送信に失敗した場合
     */
    private function generateMessageContent($messagesFlg, $message_count = null, $viewRates = null)
    {
        // メッセージ内容を生成
        $messageContent = now('Asia/Tokyo')->format('n/j') . " 4:00時点でのBL単位に集計した個人の閲覧状況を提示させて頂きます。\n";
        if ($messagesFlg) {
            $messageContent .= "当期間に配信された業連は{$message_count}件です。\n\n";
            if (isset($viewRates['BL'][0])) {
                $displayedOrgNames = [];
                foreach ($viewRates['BL'][0] as $v_org_key => $v_o) {
                    if (isset($v_o->org3_name)) {
                        if (!in_array($v_o->org3_name, $displayedOrgNames)) {
                            $messageContent .= "\n{$v_o->org3_name}\n";
                            $displayedOrgNames[] = $v_o->org3_name;
                        }
                    }
                    if (isset($v_o->org4_name)) {
                        if (!in_array($v_o->org4_name, $displayedOrgNames)) {
                            $messageContent .= "\n{$v_o->org4_name}\n";
                            $displayedOrgNames[] = $v_o->org4_name;
                        }
                    }
                    $messageContent .= "・{$v_o->org5_name}：{$viewRates['BL_read_sum'][$v_o->id]} / {$viewRates['BL_sum'][$v_o->id]}";
                    if (isset($viewRates['BL_read_sum'][$v_o->id]) && ($viewRates['BL_sum'][$v_o->id] ?? 0) > 0) {
                        $viewRate = number_format(($viewRates['BL_read_sum'][$v_o->id] / $viewRates['BL_sum'][$v_o->id]) * 100, 1);
                        $messageContent .= " ({$viewRate}%)\n";
                    } else {
                        $messageContent .= " (0.0%)\n";
                    }
                }
            }
        } else {
            $messageContent .= "\n当期間に配信された業連は0件です。\n";
        }

        $messageContent .= "\n以上、よろしくお願いいたします。";

        return $messageContent;
    }


    /**
     * メール送信のメソッド
     *
     * @param array $user_role_data 通知対象のユーザーデータ
     * @param array $organization1 組織オブジェクト
     * @param string $messageContent メッセージ内容
     * @param string $startOfLastWeek 前週月曜の0:00から前週日曜の23:59に掲載開始した業務連絡の開始日時
     * @param string $endOfLastWeek 前週月曜の0:00から前週日曜の23:59に掲載開始した業務連絡の終了日時
     * @param string|null $filePath 生成されたExcelファイルのパス
     * @return string|array 生成されたメッセージ内容またはエラーログ
     * @throws \Exception メール送信に失敗した場合
     */
    private function sendMail($user_role_data, $organization1, $messageContent, $startOfLastWeek, $endOfLastWeek, $filePath = null)
    {
        // 通知対象のメールアドレスを取得
        $to = array_filter([$user_role_data['DM_email'], $user_role_data['BM_email'], $user_role_data['AM_email']]);

        // ここ修正必須！！
        // $to = array_merge($to, WowtalkRecipient::where('target', true)->pluck('email')->toArray());

        $subject =  $organization1['name'] . '_業連閲覧状況(' . date('n/j', strtotime($startOfLastWeek)) . '~' . date('n/j', strtotime($endOfLastWeek)) . ')';

        // メール送信
        $mailer = new SESMailer();
        if ($mailer->sendEmail($to, $subject, $messageContent, $filePath)) {
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
    private function notifySystemAdmin($errorType, $requestData, $responseData)
    {
        // DBから通知対象のメールアドレスを取得
        $to = WowtalkRecipient::where('target', true)->pluck('email')->toArray();
        $subject = '【業連・動画配信システム】閲覧状況メール送信エラー';

        $message = "メール送信でエラーが発生しました。ご確認ください。\n\n";
        $message .= "■エラー内容\n" . ucfirst($errorType) . "が発生しました。\n\n";

        // リクエストデータ
        if (is_array($requestData)) {
            // 基本情報
            $message .= "■基本情報\n";
            $message .= "業態コード : " . ($requestData['org1_name'] ?? '') . "\n";
            $message .= "DM_email : " . ($requestData['DM_email'] ?? '') . "\n";
            $message .= "BM_email : " . ($requestData['BM_email'] ?? '') . "\n";
            $message .= "AM_email : " . ($requestData['AM_email'] ?? '') . "\n";
            $message .= "業連ID : " . ($requestData['message_id'] ?? '') . "\n";
            $message .= "業連名 : " . ($requestData['message_title'] ?? '') . "\n\n";
            $message .= "■リクエスト\n";
            $message .= "target : " . (is_array($requestData['response_target']) ? implode(', ', $requestData['response_target']) : $requestData['response_target']) . "\n\n";
        } else {
            $message .= "■リクエスト : $requestData\n\n";
        }

        // レスポンスデータ
        $message .= "■レスポンス\n";
        if (is_array($responseData)) {
            $message .= "result : " . ($responseData['error_message'] ?? '') . "\n";
            $message .= "status : " . ($responseData['status'] ?? '') . "\n";
            $message .= "target : " . (is_array($responseData['response_target']) ? implode(', ', $responseData['response_target']) : $responseData['response_target']) . "\n";
        } else {
            $message .= "エラーメッセージ : $responseData\n";
        }

        $mailer = new SESMailer();
        if ($mailer->sendEmail($to, $subject, $message)) {
            $this->info("システム管理者にエラーメールを送信しました。");
        } else {
            $this->error("メール送信中にエラーが発生しました。");
            throw new \Exception("システム管理者にエラーメール送信に失敗しました");
        }
    }


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
     * @param array|null $messages メッセージオブジェクト
     * @param string $errorMessage エラーメッセージ
     * @return array エラーレスポンス
     */
    private function createErrorResponse($organization1, $user_role_data, $messages = null, $errorMessage)
    {
        return [
            'status' => 'error',
            'org1_name' => $organization1->name,
            'email' => $user_role_data[0]['DM_email'] . ',' . $user_role_data[0]['BM_email'] . ',' . $user_role_data[0]['AM_email'],
            'subject' => $messages ? $messages['start_datetime'] . ' | ' . $messages['title'] . ' _ ' . now() : null,
            'error_message' => $errorMessage
        ];
    }


    /**
     * エラーログを生成するメソッド
     *
     * @param Organization1 $organization1 組織オブジェクト
     * @param array $data メールデータ
     * @param array|null $messages メッセージオブジェクト
     * @param string|null $messageContent 送信されたメッセージ内容
     * @return array エラーログ
     */
    private function createErrorLog($organization1, $data, $messages = null, $messageContent = null)
    {
        return [
            'type' => 'message_content_error',
            'org1_name' => $organization1->name ?? '',
            'DM_email' => $data['DM_email'] ?? '',
            'BM_email' => $data['BM_email'] ?? '',
            'AM_email' => $data['AM_email'] ?? '',
            'message_id' => $messages ? $messages['id'] : '',
            'message_title' => $messages ? $messages['title'] : '',
            'error_message' => $messageContent ?? '',
            'response_target' => array_filter([$data['DM_email'] ?? '', $data['BM_email'] ?? '', $data['AM_email'] ?? ''])
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
