<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Message;
use App\Models\Organization1;
use App\Models\AdminRecipient;
use App\Models\IncidentNotificationsRecipient;
use App\Utils\SESMailer;
use App\Models\EmailSendLog;
use App\Exports\PersonAnalysisExport;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
// use setasign\Fpdi\TcpdfFpdi;

// require_once(resource_path("outputpdf/libs/tcpdf/tcpdf.php"));
// require_once(resource_path("outputpdf/libs/fpdi/autoload.php"));

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
        // 業態を取得
        $organization1_list = [];
        $organization1_list = DB::table('batch_process_dates')
            ->join('organization1', 'batch_process_dates.organization1_id', '=', 'organization1.id')
            ->where('batch_process_dates.process_name', 'person_analysis')
            ->select('organization1.id', 'organization1.name', 'batch_process_dates.execution_date')
            ->get()
            ->map(function ($result) {
                return [
                    'id' => $result->id,
                    'name' => $result->name,
                    'execution_date' => $result->execution_date,
                ];
            })
            ->toArray();

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
                        'org1_name' => $organization1['name'],
                    ];
                    break;
                case 'failure':
                    $failureLogs[] = [
                        'org1_name' => $organization1['name'],
                        'email' => $sendResult['email'] ?? '',
                        'subject' => $sendResult['subject'] ?? '',
                        'error_message' => $sendResult['error_message'] ?? '不明なエラー'
                    ];
                    break;
                case 'error':
                    $errorLogs[] = [
                        'org1_name' => $organization1['name'],
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
        $viewRates = [];
        $messages = [];
        $message_count = 0;
        $messagesFlg = false;

        // 現在の日付から7日前の0:00時から現在のexecution_dateまでの業務連絡を取得
        $startOfLastWeek = now('Asia/Tokyo')->subWeek()->startOfDay()->format('Y-m-d H:i:s');
        $endOfLastWeek = now('Asia/Tokyo')->format('Y-m-d') . ' ' . Carbon::parse($organization1['execution_date'])->format('H:i:s');

        // 業務連絡を取得
        $messages = Message::query()
            ->select('messages.*')
            ->leftJoin('message_user', 'message_user.message_id', '=', 'messages.id')
            ->leftJoin('users', 'message_user.user_id', '=', 'users.id')
            ->leftJoin('shops', 'shops.id', '=', 'message_user.shop_id')
            ->where('start_datetime', '>=', $startOfLastWeek)
            ->where('start_datetime', '<=', $endOfLastWeek)
            ->where('editing_flg', false)
            ->where('messages.organization1_id', '=', $organization1['id'])
            ->orderBy('messages.id', 'desc')
            ->groupBy('messages.id')
            ->get();

        if ($messages->isNotEmpty()) {
            // 業務連絡のidを取得
            $_messages = $messages->pluck('id')->toArray();
            $message_count = count($_messages);

            // DS, AR, BLがあるかで処理を分ける
            if (isset($organization1['id'])) {
                $org1Model = Organization1::find($organization1['id']);
                if ($org1Model && $org1Model->isExistOrg3()) {
                    $organizations[] = "DS";
                    $organization_list["DS"] = $org1Model->getOrganization3();
                }
                if ($org1Model && $org1Model->isExistOrg4()) {
                    $organizations[] = "AR";
                    $organization_list["AR"] = $org1Model->getOrganization4();
                }
                if ($org1Model && $org1Model->isExistOrg5()) {
                    $organizations[] = "BL";
                    $organization_list["BL"] = $org1Model->getOrganization5();
                }
            }

            foreach ($_messages as $key => $ms) {
                // 組織ごと
                if (in_array('DS', $organizations)) {
                    $viewRates_org_sub =
                        DB::table('message_user')
                        ->select([
                            DB::raw('shops.organization3_id as o3_id'),
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
                        ->groupBy('shops.organization3_id');

                    $viewRate =
                        DB::table('shops')
                        ->select([
                            DB::raw('organization3.id as id'),
                            DB::raw('organization3.order_no as order_no'),
                            DB::raw('organization3.name as org3_name'),
                            DB::raw('sub.count as count'),
                            DB::raw('sub.read_count as read_count'),
                            DB::raw('sub.view_rate as view_rate')
                        ])
                        ->leftJoin('organization3', 'shops.organization3_id', '=', 'organization3.id')
                        ->leftJoinSub($viewRates_org_sub, 'sub', function ($join) {
                            $join->on('shops.organization3_id', '=', 'sub.o3_id');
                        })
                        ->where('shops.organization1_id', '=', $organization1['id'])
                        ->groupBy('shops.organization3_id', 'sub.count', 'sub.read_count', 'sub.view_rate')
                        ->orderBy('organization3.order_no')
                        ->get();

                    $viewRates['DS'][] = $viewRate;
                    $viewRatesArray = $viewRate->toArray();
                    foreach ($viewRatesArray as $key => $value) {
                        $viewRates['DS_sum'][$value->id] = ($viewRates['DS_sum'][$value->id] ?? 0) + $value->count;
                        $viewRates['DS_read_sum'][$value->id] = ($viewRates['DS_read_sum'][$value->id] ?? 0) + $value->read_count;
                    }
                }

                if (in_array('AR', $organizations)) {
                    $viewRates_org_sub =
                        DB::table('message_user')
                        ->select([
                            DB::raw('shops.organization4_id as o4_id'),
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
                        ->groupBy('shops.organization4_id');

                    $viewRate =
                        DB::table('shops')
                        ->select([
                            DB::raw('organization3.name as org3_name'),
                            DB::raw('organization5.name as org5_name'),
                            DB::raw('organization4.id as id'),
                            DB::raw('organization4.order_no as order_no'),
                            DB::raw('organization4.name as org4_name'),
                            DB::raw('sub.count as count'),
                            DB::raw('sub.read_count as read_count'),
                            DB::raw('sub.view_rate as view_rate')
                        ])
                        ->leftJoin('organization3', 'shops.organization3_id', '=', 'organization3.id')
                        ->leftJoin('organization4', 'shops.organization4_id', '=', 'organization4.id')
                        ->leftJoin('organization5', 'shops.organization5_id', '=', 'organization5.id')
                        ->leftJoinSub($viewRates_org_sub, 'sub', function ($join) {
                            $join->on('shops.organization4_id', '=', 'sub.o4_id');
                        })
                        ->where('shops.organization1_id', '=', $organization1['id'])
                        ->groupBy('shops.organization3_id', 'shops.organization4_id', 'shops.organization5_id', 'sub.count', 'sub.read_count', 'sub.view_rate')
                        ->orderBy('organization3.order_no')
                        ->orderBy('organization4.order_no')
                        ->orderBy('organization5.order_no')
                        ->get();

                    $viewRates['AR'][] = $viewRate;
                    $viewRatesArray = $viewRate->toArray();
                    foreach ($viewRatesArray as $key => $value) {
                        $viewRates['AR_sum'][$value->id] = ($viewRates['AR_sum'][$value->id] ?? 0) + $value->count;
                        $viewRates['AR_read_sum'][$value->id] = ($viewRates['AR_read_sum'][$value->id] ?? 0) + $value->read_count;
                    }
                }

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
                        ->where('shops.organization1_id', '=', $organization1['id'])
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
        }

        // メール送信処理の実行
        return $this->sendPersonAnalysisMail($organization1, $messagesFlg, $messages, $message_count, $viewRates, $startOfLastWeek, $endOfLastWeek, $excelFilePath);
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
        $org1 = Organization1::find($organization1['id']);
        $export = new PersonAnalysisExport($org1, $startOfLastWeek, $endOfLastWeek);
        Excel::store($export, 'export/' . $file_name, 'local');

        // ファイルが正しく保存されたか確認
        if (!Storage::exists('export/' . $file_name)) {
            $this->error("Excelファイルの保存に失敗しました。");
        }

        return $exportPath;
    }


    // /**
    //  * Excelファイルを生成し、PDFに変換するメソッド
    //  *
    //  * @param array $organization1 組織オブジェクト
    //  * @param string $startOfLastWeek 前週月曜の0:00から前週日曜の23:59に掲載開始した業務連絡の開始日時
    //  * @param string $endOfLastWeek 前週月曜の0:00から前週日曜の23:59に掲載開始した業務連絡の終了日時
    //  * @return string 生成されたPDFファイルのパス
    //  * @throws \Exception Excelファイルの生成に失敗した場合
    //  */
    // private function generateExcelFile($organization1, $startOfLastWeek, $endOfLastWeek)
    // {
    //     $now = new Carbon('now');
    //     $file_name = '業務連絡閲覧状況_' . $organization1['name'] . $now->format('_Y_m_d') . '.xlsx';

    //     // exportフォルダが存在しない場合は作成
    //     $exportDir = storage_path('app/export');
    //     if (!file_exists($exportDir)) {
    //         mkdir($exportDir, 0755, true);
    //     }

    //     // storage/app/exportディレクトリにExcelファイルを生成して保存
    //     $exportPath = $exportDir . '/' . $file_name;
    //     $org1 = Organization1::find($organization1['id']);
    //     $export = new PersonAnalysisExport($org1, $startOfLastWeek, $endOfLastWeek);
    //     Excel::store($export, 'export/' . $file_name, 'local');

    //     // ファイルが正しく保存されたか確認
    //     if (!Storage::exists('export/' . $file_name)) {
    //         $this->error("Excelファイルの保存に失敗しました。");
    //     }

    //     // ExcelファイルをPDFに変換
    //     $pdfFileName = pathinfo($file_name, PATHINFO_FILENAME) . '.pdf';
    //     $pdfFilePath = $exportDir . '/' . $pdfFileName;

    //     // PDF生成処理
    //     $this->convertExcelToPdf($exportPath, $pdfFilePath);

    //     return $pdfFilePath;
    // }

    // /**
    //  * ExcelファイルをPDFに変換するメソッド
    //  *
    //  * @param string $excelFilePath Excelファイルのパス
    //  * @param string $pdfFilePath PDFファイルの保存先パス
    //  */
    // private function convertExcelToPdf($excelFilePath, $pdfFilePath)
    // {
    //     // Excelファイルを読み込む
    //     $data = Excel::toArray([], $excelFilePath);

    //     // PDFを生成する
    //     $pdf = new TcpdfFpdi();
    //     $pdf->AddPage();

    //     // ExcelデータをPDFに書き込む
    //     foreach ($data as $sheet) {
    //         foreach ($sheet as $row) {
    //             $pdf->Cell(0, 10, implode(' ', $row), 0, 1);
    //         }
    //     }

    //     // PDFを保存する
    //     $pdf->Output($pdfFilePath, 'F');
    // }


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
        $user_role_data = [];
            $user_role_data = DB::table('users_roles')
            ->join('shops', function ($join) use ($organization1) {
                $join->on('users_roles.shop_id', '=', 'shops.id')
                    ->where('shops.organization1_id', $organization1['id']);
            })
            ->select('users_roles.DM_email', 'users_roles.BM_email', 'users_roles.AM_email', 'users_roles.DM_view_notification', 'users_roles.BM_view_notification', 'users_roles.AM_view_notification')
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
                    'DM_email' => $result->DM_view_notification ? $result->DM_email : null,
                    'BM_email' => $result->BM_view_notification ? $result->BM_email : null,
                    'AM_email' => $result->AM_view_notification ? $result->AM_email : null,
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
            $messageContent = $this->generateMessageContent($organization1, $messagesFlg, $startOfLastWeek, $endOfLastWeek, $messages, $message_count, $viewRates);
        } catch (\Exception $e) {
            // メッセージ内容生成に失敗した場合のエラー処理
            $errorLog = $this->createErrorLog(
                $organization1,
                $user_role_data,
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
                        'response_target' => $errorLog['response_target'] ?? ''
                    ],
                    [
                        'error_message' => $errorLog['error_message'] ?? '',
                        'status' => 'error',
                        'response_target' => $errorLog['response_target'] ?? ''
                    ]
                );
            }

            return $this->createErrorResponse($organization1, $user_role_data, $e->getMessage());
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
            return $this->createErrorResponse($organization1, $user_role_data, $errorMessageString);
        }

        // 成功時のレスポンスを返す
        return $this->createSuccessResponse($organization1);
    }


    /**
     * 曜日の漢字を取得するメソッド
     *
     * @param array $organization1 組織オブジェクト
     * @return string 曜日の漢字
     */
    private function getWeekdayKanji($organization1)
    {
        $dayOfWeek = Carbon::parse($organization1['execution_date'])->format('w');
        $kanjiDays = ['日', '月', '火', '水', '木', '金', '土'];
        $weekdayKanji = $kanjiDays[$dayOfWeek];
        return $weekdayKanji;
    }


    /**
     * 各業態の閲覧率などのメッセージ内容を生成するメソッド
     *
     * @param array $organization1 組織オブジェクト
     * @param bool $messagesFlg メッセージがあるかどうかのフラグ
     * @param string $startOfLastWeek 前週月曜の0:00から前週日曜の23:59に掲載開始した業務連絡の開始日時
     * @param string $endOfLastWeek 前週月曜の0:00から前週日曜の23:59に掲載開始した業務連絡の終了日時
     * @param array|null $messages メッセージデータ
     * @param int|null $message_count メッセージの件数
     * @param array|null $viewRates 閲覧状況データ
     * @return string|array 生成されたメッセージ内容またはエラーログ
     * @throws \Exception メール送信に失敗した場合
     */
    private function generateMessageContent($organization1, $messagesFlg, $startOfLastWeek, $endOfLastWeek, $messages = null, $message_count = null, $viewRates = null)
    {
        // メッセージ内容を生成
        $messageContent = "・集約期間：" . date('n/j', strtotime($startOfLastWeek)) . '~' . date('n/j', strtotime($endOfLastWeek . ' -1 day')) . "\n";
        if ($messagesFlg) {
            $messageContent .= "・配信業連：" . mb_convert_kana((string)$message_count, 'n') . "件\n\n";
            $messageContent .= "※期間中に発行された業務連絡が集計対象。\n";
            $messageContent .= "※期間中に対象の業務連絡を閲覧したクルー数を集計。\n";
            $messageContent .= "※集約日時の時点で、本部システム上在籍している全クルーが対象。\n";
            $messageContent .= "（雇用手続きの関係上、実際の稼働人数と異なる場合があります。）\n\n";
            $messageContent .= now('Asia/Tokyo')->format('n/j') . "(" . $this->getWeekdayKanji($organization1) . ")" . Carbon::parse($organization1['execution_date'])->format('H:i') . "時点\n";

            // 業務連絡のidを取得
            foreach ($messages as $key => $ms) {
                $messageContent .= "\n業連" . ($key + 1) . "：{$ms->title}\n";

                // DSの情報を表示
                if (isset($viewRates['DS'][$key])) {
                    foreach ($viewRates['DS'][$key] as $v_org3_key => $v_o3) {
                        if (isset($viewRates['DS'][$key][$v_org3_key]->count)) {
                            $messageContent .= "\n■{$viewRates['DS'][$key][$v_org3_key]->org3_name}：{$viewRates['DS'][$key][$v_org3_key]->read_count} / {$viewRates['DS'][$key][$v_org3_key]->count} ({$viewRates['DS'][$key][$v_org3_key]->view_rate}%)\n\n";
                        } else {
                            $messageContent .= "\n■{$viewRates['DS'][$key][$v_org3_key]->org3_name}：0 / 0 (0.0%)\n\n";
                        }

                        // BLの情報を表示
                        if (isset($viewRates['BL'][$key])) {
                            foreach ($viewRates['BL'][$key] as $v_org5_key => $v_o5) {
                                if (isset($viewRates['BL'][$key][$v_org5_key]->count)) {
                                    if ($viewRates['DS'][$key][$v_org3_key]->org3_name == $viewRates['BL'][$key][$v_org5_key]->org3_name) {
                                        $messageContent .= "・ {$viewRates['BL'][$key][$v_org5_key]->org5_name}：{$viewRates['BL'][$key][$v_org5_key]->read_count} / {$viewRates['BL'][$key][$v_org5_key]->count} ({$viewRates['BL'][$key][$v_org5_key]->view_rate}%)\n";
                                    }
                                } else {
                                    $messageContent .= "・{$viewRates['BL'][$key][$v_org5_key]->org5_name}：0 / 0 (0.0%)\n";
                                }

                                // ARの情報を表示
                                if (isset($viewRates['AR'][$key])) {
                                    foreach ($viewRates['AR'][$key] as $v_org4_key => $v_o4) {
                                        if (isset($viewRates['AR'][$key][$v_org4_key]->count)) {
                                            if ($viewRates['DS'][$key][$v_org3_key]->org3_name == $viewRates['AR'][$key][$v_org4_key]->org3_name) {
                                                $messageContent .= "・{$viewRates['AR'][$key][$v_org4_key]->org4_name}：{$viewRates['AR'][$key][$v_org4_key]->read_count} / {$viewRates['AR'][$key][$v_org4_key]->count} ({$viewRates['AR'][$key][$v_org4_key]->view_rate}%)\n";
                                            }
                                        } else {
                                            $messageContent .= "・{$viewRates['AR'][$key][$v_org4_key]->org4_name}：0 / 0 (0.0%)\n";
                                        }
                                    }
                                }
                            }
                        } else {
                            // ARの情報を表示
                            if (isset($viewRates['AR'][$key])) {
                                foreach ($viewRates['AR'][$key] as $v_org4_key => $v_o4) {
                                    if (isset($viewRates['AR'][$key][$v_org4_key]->count)) {
                                        if ($viewRates['DS'][$key][$v_org3_key]->org3_name == $viewRates['AR'][$key][$v_org4_key]->org3_name) {
                                            $messageContent .= "・{$viewRates['AR'][$key][$v_org4_key]->org4_name}：{$viewRates['AR'][$key][$v_org4_key]->read_count} / {$viewRates['AR'][$key][$v_org4_key]->count} ({$viewRates['AR'][$key][$v_org4_key]->view_rate}%)\n";
                                        }
                                    } else {
                                        $messageContent .= "・{$viewRates['AR'][$key][$v_org4_key]->org4_name}：0 / 0 (0.0%)\n";
                                    }
                                }
                            }
                        }
                    }
                } else {
                    // BLの情報を表示
                    if (isset($viewRates['BL'][$key])) {
                        $displayedOrg5Names = [];
                        foreach ($viewRates['BL'][$key] as $v_org5_key => $v_o5) {
                            if (isset($viewRates['BL'][$key][$v_org5_key]->count)) {
                                if (!in_array($viewRates['BL'][$key][$v_org5_key]->org5_name, $displayedOrg5Names)) {
                                    if ($v_org5_key == 0) {
                                        $messageContent .= "\n・ {$viewRates['BL'][$key][$v_org5_key]->org5_name}：{$viewRates['BL'][$key][$v_org5_key]->read_count} / {$viewRates['BL'][$key][$v_org5_key]->count} ({$viewRates['BL'][$key][$v_org5_key]->view_rate}%)\n";
                                    } else {
                                        $messageContent .= "・ {$viewRates['BL'][$key][$v_org5_key]->org5_name}：{$viewRates['BL'][$key][$v_org5_key]->read_count} / {$viewRates['BL'][$key][$v_org5_key]->count} ({$viewRates['BL'][$key][$v_org5_key]->view_rate}%)\n";
                                    }
                                }
                                $displayedOrg5Names[] = $viewRates['BL'][$key][$v_org5_key]->org5_name;
                            } else {
                                $messageContent .= "・{$viewRates['BL'][$key][$v_org5_key]->org5_name}：0 / 0 (0.0%)\n";
                            }
                        }
                    }

                    // ARの情報を表示
                    if (isset($viewRates['AR'][$key])) {
                    $displayedOrg4Names = [];
                        foreach ($viewRates['AR'][$key] as $v_org4_key => $v_o4) {
                            if (isset($viewRates['AR'][$key][$v_org4_key]->count)) {
                                if (!in_array($viewRates['AR'][$key][$v_org4_key]->org4_name, $displayedOrg4Names)) {
                                    if ($v_org4_key == 0) {
                                        $messageContent .= "\n・{$viewRates['AR'][$key][$v_org4_key]->org4_name}：{$viewRates['AR'][$key][$v_org4_key]->read_count} / {$viewRates['AR'][$key][$v_org4_key]->count} ({$viewRates['AR'][$key][$v_org4_key]->view_rate}%)\n";
                                    } else {
                                        $messageContent .= "・{$viewRates['AR'][$key][$v_org4_key]->org4_name}：{$viewRates['AR'][$key][$v_org4_key]->read_count} / {$viewRates['AR'][$key][$v_org4_key]->count} ({$viewRates['AR'][$key][$v_org4_key]->view_rate}%)\n";
                                    }
                                }
                                $displayedOrg4Names[] = $viewRates['AR'][$key][$v_org4_key]->org4_name;
                            } else {
                                $messageContent .= "・{$viewRates['AR'][$key][$v_org4_key]->org4_name}：0 / 0 (0.0%)\n";
                            }
                        }
                    }
                }
            }
        } else {
            $messageContent .= "・配信業連：0件\n\n";
            $messageContent .= "※期間中に発行された業務連絡が集計対象。\n";
            $messageContent .= "※期間中に対象の業務連絡を閲覧したクルー数を集計。\n";
            $messageContent .= "※集約日時の時点で、本部システム上在籍している全クルーが対象。\n";
            $messageContent .= "（雇用手続きの関係上、実際の稼働人数と異なる場合があります。）\n\n";
        }

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
        // $to = array_merge($to, AdminRecipient::where('target', true)->pluck('email')->toArray());

        $subject =  $organization1['name'] . '_業連閲覧状況(' . date('n/j', strtotime($startOfLastWeek)) . '~' . date('n/j', strtotime($endOfLastWeek . ' -1 day')) . ')';
        $fromName = '業連・動画配信ツール';
        // メール送信
        $mailer = new SESMailer();
        if ($mailer->sendEmail($fromName, $to, $subject, $messageContent, $filePath)) {
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
        $fromName = 'システム管理者';
        $to = IncidentNotificationsRecipient::where('target', true)->pluck('email')->toArray();
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
        if ($mailer->sendEmail($fromName, $to, $subject, $message)) {
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
            'org1_name' => $organization1['name']
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
            'org1_name' => $organization1['name']
        ];
    }


    /**
     * エラーレスポンスを生成するメソッド
     *
     * @param Organization1 $organization1 組織オブジェクト
     * @param array $user_role_data 通知対象のユーザーデータ
     * @param string $errorMessage エラーメッセージ
     * @return array エラーレスポンス
     */
    private function createErrorResponse($organization1, $user_role_data, $errorMessage)
    {
        return [
            'status' => 'error',
            'org1_name' => $organization1['name'],
            'email' => $user_role_data[0]['DM_email'] . ',' . $user_role_data[0]['BM_email'] . ',' . $user_role_data[0]['AM_email'],
            'error_message' => $errorMessage
        ];
    }


    /**
     * エラーログを生成するメソッド
     *
     * @param Organization1 $organization1 組織オブジェクト
     * @param array $data メールデータ
     * @param string|null $messageContent 送信されたメッセージ内容
     * @return array エラーログ
     */
    private function createErrorLog($organization1, $data, $messageContent = null)
    {
        return [
            'type' => 'message_content_error',
            'org1_name' => $organization1['name'] ?? '',
            'DM_email' => $data['DM_email'] ?? '',
            'BM_email' => $data['BM_email'] ?? '',
            'AM_email' => $data['AM_email'] ?? '',
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
