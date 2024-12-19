<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Message;
use App\Models\Manual;
use App\Models\Shop;
use App\Models\Organization1;
use App\Models\WowTalkNotificationLog;
use App\Models\WowtalkRecipient;
use Illuminate\Support\Facades\Mail;
use App\Utils\SESMailer;
use App\Utils\SendWowTalkApi;

class WowTalkNotificationSenderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wowtalk:send-notifications';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'WowTalkで通知を送信するコマンドです。';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        // メモリ制限を無効にする
        ini_set('memory_limit', '-1');

        $this->info('WowTalk通知送信開始');

        try {
            // 業務連絡メッセージ送信
            try {
                $this->processNotification('message');
            } catch (\Throwable $th) {
                $this->error('業務連絡メッセージ送信中にエラーが発生しました: ' . $th->getMessage());
            }

            // マニュアル送信
            try {
                $this->processNotification('manual');
            } catch (\Throwable $th) {
                $this->error('マニュアル送信中にエラーが発生しました: ' . $th->getMessage());
            }
        } finally {
            // 処理後にメモリ制限を元に戻す
            ini_restore('memory_limit');
        }
    }


    /**
     * 通知処理を実行するメソッド
     *
     * @param string $logType ログのタイプ ('message' または 'manual')
     */
    private function processNotification($logType)
    {
        try {
            // ログを作成
            $messageLog = new WowTalkNotificationLog();
            $messageLog->log_type = $logType;
            $messageLog->command_name = $this->signature;
            $messageLog->started_at = Carbon::now();
            $messageLog->status = true;
            $messageLog->attempts = 1;
            $messageLog->save();

            // メイン処理を実行
            $this->sendNotifications($logType);

            // 成功時の処理
            $messageLog->finished_at = Carbon::now();
            $messageLog->save();

            $this->info('WowTalk通知送信完了');
        } catch (\Throwable $th) {
            // エラー発生時にログを更新し、エラーメッセージを記録
            $this->finalizeLog($messageLog, false, $th->getMessage());
        }
    }


    /**
     * ログを完了させるメソッド
     * 処理の終了時に呼び出され、ログの終了時刻とステータスを更新します。
     *
     * @param WowTalkNotificationLog $log ログオブジェクト
     * @param bool $status 処理が成功したかどうかのステータス
     * @param string|null $errorMessage エラーメッセージ（エラーが発生した場合）
     */
    private function finalizeLog($log, $status, $errorMessage = null)
    {
        $log->status = $status;
        if (!$status && $errorMessage) {
            $log->error_message = $errorMessage;
        }
        $log->finished_at = Carbon::now();
        $log->save();
    }


    /**
     * メインの通知送信処理
     * データベースからすべてのメッセージを取得し、それぞれについて送信処理を行います。
     * 結果に応じてログを分類し、最終的にログを出力します。
     *
     * @param string $logType ログのタイプ ('message' または 'manual')
     */
    private function sendNotifications($logType)
    {
        // 現在の東京時刻を取得
        $currentDate = Carbon::now('Asia/Tokyo');

        // 現在掲載中と掲載終了を取得
        if ($logType === 'message') {
            $dataTypes = Message::where('editing_flg', false)->where('is_broadcast_notification', true)->get();
        } elseif ($logType === 'manual') {
            $dataTypes = Manual::where('editing_flg', false)->where('is_broadcast_notification', true)->get();
        }

        // 各種ログ用の配列を初期化
        $successLogs = [];
        $failureLogs = [];
        $errorLogs = [];

        foreach ($dataTypes as $dataType) {
            $sendResult = $this->processItem($dataType, $currentDate, $logType);

            // 処理結果に応じてログを分類
            switch ($sendResult['status']) {
                case 'success':
                    $successLogs[] = [
                        'title' => $dataType->title,
                    ];
                    break;
                case 'failure':
                    $failureLogs[] = [
                        'title' => $dataType->title,
                    ];
                    break;
                case 'error':
                    $errorLogs[] = [
                        'title' => $dataType->title,
                        'error_message' => $sendResult['error_message'] ?? '不明なエラー',
                        'attempts' => $sendResult['attempts'] ?? 1
                    ];
                    break;
            }
        }

        // ログの出力
        $this->logResults($successLogs, $failureLogs, $errorLogs, $logType);
    }


    /**
     * 個別メッセージの処理
     * 各メッセージが送信対象かを確認し、対象であれば送信処理を実行します。
     *
     * @param mixed $dataType メッセージまたはマニュアルオブジェクト
     * @param Carbon $currentDate 現在の日時
     * @param string $logType ログのタイプ ('message' または 'manual')
     * @return array 処理結果のレスポンス（成功、失敗、エラーのいずれか）
     */
    private function processItem($dataType, $currentDate, $logType)
    {
        // 各種日付の取得
        $startDatetime = $dataType->start_datetime; // 掲載開始日
        $createdAt     = $dataType->created_at;     // 登録日
        $endDatetime   = $dataType->end_datetime;   // 掲載終了日

        // 掲載開始日または登録日が存在しない場合の処理
        if (!$startDatetime || !$createdAt) {
            return $this->createFailureResponse($dataType, $logType);
        }

        // 掲載開始日が今日よりも新しい場合の処理
        if ($startDatetime->gt($currentDate)) {
            return $this->createFailureResponse($dataType, $logType);
        }

        if ($endDatetime) {
            // 掲載終了日が今日よりも古い場合の処理
            if ($currentDate->gt(Carbon::parse($endDatetime))) {
                return $this->createFailureResponse($dataType, $logType);
            }
        }

        // 掲載開始日が現在日時以前の場合に通知
        if ($startDatetime->lessThanOrEqualTo($currentDate)) {
            // メッセージ送信処理の実行
            return $this->sendWowTalkMessages($dataType, $logType);
        }

        return $this->createFailureResponse($dataType, $logType);
    }


    /**
     * WowTalkメッセージを送信するメソッド
     * 各店舗に対してWowTalk APIを介して未読メッセージ通知を送信します。
     *
     * @param mixed $dataType メッセージまたはマニュアルオブジェクト
     * @param string $logType ログのタイプ ('message' または 'manual')
     * @return array メッセージ送信結果のレスポンス（成功、失敗、エラーのいずれか）
     */
    private function sendWowTalkMessages($dataType, $logType)
    {
        // 通知対象の店舗とWowTalk IDを取得
        $wowtalk_data = [];
        if ($logType === 'message') {
            $wowtalk_data = DB::table('wowtalk_shops')
                ->join('message_shop', 'wowtalk_shops.shop_id', '=', 'message_shop.shop_id')
                ->where('message_shop.message_id', $dataType->id)
                ->where(function ($query) {
                    $query->where(function ($subQuery) {
                        $subQuery->whereNotNull('wowtalk_shops.wowtalk1_id')
                                ->where('wowtalk_shops.business_notification1', true);
                    })
                    ->orWhere(function ($subQuery) {
                        $subQuery->whereNotNull('wowtalk_shops.wowtalk2_id')
                                ->where('wowtalk_shops.business_notification2', true);
                    });
                })
                ->select('wowtalk_shops.shop_id', 'wowtalk_shops.wowtalk1_id', 'wowtalk_shops.wowtalk2_id')
                ->get()
                ->map(function ($result) {
                    return [
                        'shop_id' => $result->shop_id,
                        'wowtalk1_id' => $result->wowtalk1_id,
                        'wowtalk2_id' => $result->wowtalk2_id,
                    ];
                })
                ->toArray();

        } elseif ($logType === 'manual') {
            $wowtalk_data = DB::table('wowtalk_shops')
                ->join('manual_shop', 'wowtalk_shops.shop_id', '=', 'manual_shop.shop_id')
                ->where('manual_shop.manual_id', $dataType->id)
                ->where(function ($query) {
                    $query->where(function ($subQuery) {
                        $subQuery->whereNotNull('wowtalk_shops.wowtalk1_id')
                                ->where('wowtalk_shops.business_notification1', true);
                    })
                    ->orWhere(function ($subQuery) {
                        $subQuery->whereNotNull('wowtalk_shops.wowtalk2_id')
                                ->where('wowtalk_shops.business_notification2', true);
                    });
                })
                ->select('wowtalk_shops.shop_id', 'wowtalk_shops.wowtalk1_id', 'wowtalk_shops.wowtalk2_id')
                ->get()
                ->map(function ($result) {
                    return [
                        'shop_id' => $result->shop_id,
                        'wowtalk1_id' => $result->wowtalk1_id,
                        'wowtalk2_id' => $result->wowtalk2_id,
                    ];
                })
                ->toArray();
        }

        // 通知対象のWowTalkIDがない場合
        if (empty($wowtalk_data)) {
            return $this->createFailureResponse($dataType, $logType);
        }

        // エラーログのための配列
        $errorLogs = [];
        foreach ($wowtalk_data as $data) {
            try {
                // メッセージ内容を生成
                $messageContent = $this->calculateAndGenerateMessageContent($dataType, $logType);

                // メッセージ内容が800文字を超える場合はエラーをスロー
                if (mb_strlen($messageContent) > 800) {
                    throw new \Exception("Message content exceeds 800 characters.");
                }
            } catch (\Exception $e) {
                // メッセージ内容生成に失敗した場合のエラー処理
                $errorLog = $this->createErrorLog($dataType, $logType, $data, null, ['type' => 'message_content_error', 'response_result' => 'メッセージ生成に失敗しました', 'response_status' => $e->getMessage(), 'attempts' => 1], '');

                $this->notifySystemAdmin(
                    $errorLog['type'],
                    $errorLog,
                    ['error_message' => $errorLog['error_message'],'status' => 'error', 'response_target' => $errorLog['response_target']]
                );

                return $this->createErrorResponse($dataType, $logType, $e->getMessage(), 1);
            }

            // WowTalk APIを呼び出す
            foreach (['wowtalk1_id', 'wowtalk2_id'] as $wowtalkIdKey) {
                if (!empty($data[$wowtalkIdKey])) {
                    try {
                        $apiResult = SendWowTalkApi::sendWowTalkApiRequest([$data[$wowtalkIdKey]], $messageContent);
                        if (is_array($apiResult)) {
                            $errorLogs[] = $this->createErrorLog($dataType, $logType, $data, $data[$wowtalkIdKey], $apiResult, $messageContent);
                        }
                    } catch (\Exception $e) {
                        // エラーログを生成
                        $errorLogs[] = $this->createErrorLog($dataType, $logType, $data, $data[$wowtalkIdKey], ['type' => 'api_error', 'response_result' => 'API呼び出しに失敗しました', 'response_status' => $e->getMessage(), 'attempts' => 1], $messageContent);
                    }
                }
            }
        }

        // すべてのAPI処理が終了した後でエラーログを集約
        if (!empty($errorLogs)) {
            if ($logType === 'message') {
                foreach ($errorLogs as $errorLog) {
                    $this->notifySystemAdmin(
                        $errorLog['type'],
                        [
                            'org1_name' => $errorLog['org1_name'],
                            'shop_code' => $errorLog['shop_code'],
                            'shop_name' => $errorLog['shop_name'],
                            'message_id' => $errorLog['message_id'],
                            'message_title' => $errorLog['message_title'],
                            'request_message' => $errorLog['request_message'],
                            'request_target' => $errorLog['request_target'],
                            'log_type' => $logType
                        ],
                        [
                            'error_message' => $errorLog['error_message'],
                            'status' => 'error',
                            'response_target' => $errorLog['response_target']
                        ]
                    );
                }
            } elseif ($logType === 'manual') {
                foreach ($errorLogs as $errorLog) {
                    $this->notifySystemAdmin(
                        $errorLog['type'],
                        [
                            'org1_name' => $errorLog['org1_name'],
                            'shop_code' => $errorLog['shop_code'],
                            'shop_name' => $errorLog['shop_name'],
                            'manual_id' => $errorLog['manual_id'],
                            'manual_title' => $errorLog['manual_title'],
                            'request_message' => $errorLog['request_message'],
                            'request_target' => $errorLog['request_target'],
                            'log_type' => $logType
                        ],
                        [
                            'error_message' => $errorLog['error_message'],
                            'status' => 'error',
                            'response_target' => $errorLog['response_target']
                        ]
                    );
                }
            }

            // エラー時のレスポンスを返す
            $errorMessageString = implode('; ', array_unique(array_column($errorLogs, 'error_message')));
            return $this->createErrorResponse($dataType, $logType, $errorMessageString, $errorLog['attempts']);
        }

        // 成功時のレスポンスを返す
        return $this->createSuccessResponse($dataType, $logType);
    }


    /**
     * 未読メッセージ数を計算し、メッセージ内容を生成するメソッド
     * クルーの総メッセージ数と既読数を基に未読メッセージ数を算出し、それに基づいてメッセージ内容を作成します。
     *
     * @param mixed $dataType メッセージまたはマニュアルオブジェクト
     * @param string $logType ログのタイプ ('message' または 'manual')
     * @return string 生成されたメッセージ内容
     * @throws \Exception メッセージ内容が800文字を超える場合にスロー
     */
    private function calculateAndGenerateMessageContent($dataType, $logType)
    {
        if ($logType === 'message') {
            // メッセージ内容を生成
            $messageContent = "業連が配信されました。確認してください。\n";
            $messageContent .= "・業連名：{$dataType->title}\n";
            // $messageContent .= "https://stag-innerstreaming.zensho-i.net/message/?search_period=all\n";
            $messageContent .= "https://innerstreaming.zensho-i.net/message/?search_period=all\n";
        } elseif ($logType === 'manual') {
            // メッセージ内容を生成
            $messageContent = "マニュアルが配信されました。確認してください。\n";
            $messageContent .= "・業連名：{$dataType->title}\n";
            // $messageContent .= "・URL：https://stag-innerstreaming.zensho-i.net/manual?keyword=&search_period=all\n";
            $messageContent .= "・URL：https://innerstreaming.zensho-i.net/manual?keyword=&search_period=all\n";
        }

        return $messageContent;
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
        $subject = '【業連・動画配信システム】業連配信通知 WowTalk連携エラー';

        $message = "WowTalk連携でエラーが発生しました。ご確認ください。\n\n";
        $message .= "■エラー内容\n" . ucfirst($errorType) . "が発生しました。\n\n";

        // リクエストデータ
        if (is_array($requestData)) {
            // 基本情報
            $message .= "■基本情報\n";
            $message .= "業態コード : " . ($requestData['org1_name'] ?? '') . "\n";
            $message .= "店舗コード : " . ($requestData['shop_code'] ?? '') . "\n";
            $message .= "店舗名 : " . ($requestData['shop_name'] ?? '') . "\n";
            if ($requestData['log_type'] === 'message') {
                $message .= "業連ID : " . ($requestData['message_id'] ?? '') . "\n";
                $message .= "業連名 : " . ($requestData['message_title'] ?? '') . "\n\n";
            } elseif ($requestData['log_type'] === 'manual') {
                $message .= "マニュアルID : " . ($requestData['manual_id'] ?? '') . "\n";
                $message .= "マニュアル名 : " . ($requestData['manual_title'] ?? '') . "\n\n";
            }
            $message .= "■リクエスト\n";
            $message .= "message : " . ($requestData['request_message'] ?? '') . "\n";
            $message .= "target : " . (is_array($requestData['request_target']) ? implode(', ', $requestData['request_target']) : $requestData['request_target']) . "\n\n";
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
        }
    }


    /**
     * 成功時のレスポンスを生成するメソッド
     *
     * @param mixed $dataType メッセージまたはマニュアルオブジェクト
     * @param string $logType ログのタイプ ('message' または 'manual')
     * @return array 成功レスポンス
     */
    private function createSuccessResponse($dataType, $logType)
    {
        // 業連配信通知フラグを更新
        $dataType->is_broadcast_notification = false;
        $dataType->save();

        return [
            'status' => 'success',
            'title' => $dataType->title
        ];
    }


    /**
     * 失敗時のレスポンスを生成するメソッド
     *
     * @param mixed $dataType メッセージまたはマニュアルオブジェクト
     * @param string $logType ログのタイプ ('message' または 'manual')
     * @param string $errorMessage エラーメッセージ
     * @return array 失敗レスポンス
     */
    private function createFailureResponse($dataType, $logType)
    {
        return [
            'status' => 'failure',
            'title' => $dataType->title
        ];
    }


    /**
     * エラーレスポンスを生成するメソッド
     *
     * @param mixed $dataType メッセージまたはマニュアルオブジェクト
     * @param string $logType ログのタイプ ('message' または 'manual')
     * @param string $errorMessage エラーメッセージ
     * @param int $attempts リトライ回数
     * @return array エラーレスポンス
     */
    private function createErrorResponse($dataType, $logType, $errorMessage, $attempts = 1)
    {
        return [
            'status' => 'error',
            'title' => $dataType->title,
            'error_message' => $errorMessage,
            'attempts' => $attempts
        ];
    }


    /**
     * エラーログを生成するメソッド
     *
     * @param mixed $dataType メッセージまたはマニュアルオブジェクト
     * @param string $logType ログのタイプ ('message' または 'manual')
     * @param array $data 店舗データ
     * @param string|null $wowtalkId WowTalk ID
     * @param array $apiResult APIリクエストの結果
     * @param string|null $messageContent 送信されたメッセージ内容
     * @return array エラーログ
     */
    private function createErrorLog($dataType, $logType, $data, $wowtalkId = null, $apiResult, $messageContent = null)
    {
        $shop = Shop::where('id', $data['shop_id'] ?? 0)
            ->select('shop_code', 'display_name', 'organization1_id')
            ->first();

        $org1 = Organization1::where('id', $shop->organization1_id ?? 0)
            ->select('name')
            ->first();

        if ($logType === 'message') {
            return [
                'type' => $apiResult['type'],
                'org1_name' => $org1->name ?? '',
                'shop_code' => $shop->shop_code ?? '',
                'shop_name' => $shop->display_name ?? '',
                'message_id' => $dataType->id ?? '',
                'message_title' => $dataType->title ?? '',
                'error_message' => $apiResult['response_result'] . ' : ' . $apiResult['response_status'],
                'request_message' => $messageContent ?? '',
                'request_target' => $wowtalkId ?? '',
                'response_target' => $apiResult['response_target'] ?? '',
                'attempts' => $apiResult['attempts'] ?? 1
            ];

        } elseif ($logType === 'manual') {
            return [
                'type' => $apiResult['type'],
                'org1_name' => $org1->name ?? '',
                'shop_code' => $shop->shop_code ?? '',
                'shop_name' => $shop->display_name ?? '',
                'manual_id' => $dataType->id ?? '',
                'manual_title' => $dataType->title ?? '',
                'error_message' => $apiResult['response_result'] . ' : ' . $apiResult['response_status'],
                'request_message' => $messageContent ?? '',
                'request_target' => $wowtalkId ?? '',
                'response_target' => $apiResult['response_target'] ?? '',
                'attempts' => $apiResult['attempts'] ?? 1
            ];
        }
    }


    /**
     * 成功、失敗、エラーログを出力する関数
     */
    private function logResults($successLogs, $failureLogs, $errorLogs, $logType)
    {
        $this->info("---送信する---");
        foreach ($successLogs as $log) {
            $label = "業務連絡：";
            $this->info($label . $log['title']);
        }

        $this->info("---送信しない---");
        foreach ($failureLogs as $log) {
            $label = "業務連絡：";
            $this->warn($label . $log['title']);
        }

        $this->info("---送信エラー---");
        foreach ($errorLogs as $log) {
            $label = "業務連絡：";
            $this->error($label . $log['title']);
            $this->error("エラー内容：" . $log['error_message']);

            // エラーログをデータベースに保存
            $this->storeErrorLogsInDatabase($log, $logType);
        }
    }


    /**
     * エラーログをデータベースに格納する関数
     */
    private function storeErrorLogsInDatabase($log, $logType)
    {
        try {
            $errorLog = new WowTalkNotificationLog();
            $errorLog->log_type = $logType;
            $errorLog->command_name = $this->signature;
            $errorLog->started_at = Carbon::now();
            $errorLog->status = false;

            // エラーメッセージを文にして保存
            $errorLog->error_message = $log['title'] . "：" . $log['error_message'];
            $errorLog->attempts = $log['attempts'];

            $errorLog->finished_at = Carbon::now();
            $errorLog->save();
        } catch (\Exception $e) {
            $this->error("エラーログのデータベース保存に失敗しました: " . $e->getMessage());
        }
    }
}
