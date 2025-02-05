<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Message;
use App\Models\Manual;
use App\Models\WowTalkNotificationLog;
use App\Models\WowtalkRecipient;
use App\Utils\SESMailer;
use App\Utils\SendWowTalkApi;

class SendWowtalkNotificationJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;
    protected $type;
    protected $mode;


    /**
     * Create a new job instance.
     */
    public function __construct($id, $type, $mode)
    {
        $this->id = $id;
        $this->type = $type;
        $this->mode = $mode;
    }


    public function uniqueId()
    {
        return $this->id . '_' . $this->type . '_' . $this->mode;
    }


    /**
     * Execute the job.
     */
    public function handle()
    {
        // メモリ制限を無効にする
        ini_set('memory_limit', '-1');

        Log::info('WowTalk通知送信開始');

        try {
            // ログを作成
            $messageLog = new WowTalkNotificationLog();
            $messageLog->log_type = $this->type;
            $messageLog->command_name = 'wowtalk:send-notifications-job';
            $messageLog->started_at = Carbon::now();
            $messageLog->status = true;
            $messageLog->attempts = 1;
            $messageLog->save();

            // メイン処理を実行
            $this->sendNotifications($messageLog, $this->id, $this->type);

            // 成功時の処理
            $messageLog->finished_at = Carbon::now();
            $messageLog->save();

        } catch (\Throwable $th) {
            // エラー発生時にログを更新し、エラーメッセージを記録
            $this->finalizeLog($messageLog, false, $th->getMessage());
        } finally {

            // 処理後にメモリ制限を元に戻す
            ini_restore('memory_limit');

            Log::info('WowTalk通知送信完了');
        }
    }


    /**
     * ログを完了させるメソッド
     * 処理の終了時に呼び出され、ログの終了時刻とステータスを更新します。
     *
     * @param WowTalkNotificationLog $messageLog ログオブジェクト
     * @param bool $status 処理が成功したかどうかのステータス
     * @param string|null $errorMessage エラーメッセージ（エラーが発生した場合）
     */
    private function finalizeLog($messageLog, $status, $errorMessage = null)
    {
        $messageLog->status = $status;
        if (!$status && $errorMessage) {
            $messageLog->error_message = $errorMessage;
        }
        $messageLog->finished_at = Carbon::now();
        $messageLog->save();
    }


    /**
     * メインの通知送信処理
     * データベースからすべてのメッセージを取得し、それぞれについて送信処理を行います。
     * 結果に応じてログを分類し、最終的にログを出力します。
     *
     * @param WowTalkNotificationLog $messageLog ログオブジェクト
     * @param string $type ログのタイプ ('message' または 'manual')
     */
    private function sendNotifications($messageLog, $id, $type)
    {
        // 現在の東京時刻を取得
        $currentDate = Carbon::now('Asia/Tokyo');

        // 現在掲載中と掲載終了を取得（is_broadcast_notificationが1:待ちの場合）
        $dataType = null;
        if ($type === 'message') {
            $dataType = Message::where('id', $id)->where('editing_flg', false)->where('is_broadcast_notification', 1)->first();
        } elseif ($type === 'manual') {
            $dataType = Manual::where('id', $id)->where('editing_flg', false)->where('is_broadcast_notification', 1)->first();
        }

        // $dataTypesがnullまたはfalseでないことを確認
        if (!$dataType) {
            return $this->createErrorResponse('', 'データが見つかりませんでした。タイプ: ' . $type, 1);
        }

        // 各種ログ用の配列を初期化
        $successLogs = [];
        $failureLogs = [];
        $errorLogs = [];

        $sendResult = $this->processItem($dataType, $currentDate, $type);

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

        // ログの出力
        $this->logResults($messageLog, $successLogs, $failureLogs, $errorLogs, $type);
    }


    /**
     * 個別メッセージの処理
     * 各メッセージが送信対象かを確認し、対象であれば送信処理を実行します。
     *
     * @param mixed $dataType メッセージまたはマニュアルオブジェクト
     * @param Carbon $currentDate 現在の日時
     * @param string $type ログのタイプ ('message' または 'manual')
     * @return array 処理結果のレスポンス（成功、失敗、エラーのいずれか）
     */
    private function processItem($dataType, $currentDate, $type)
    {
        // 各種日付の取得
        $startDatetime = $dataType->start_datetime; // 掲載開始日
        $createdAt     = $dataType->created_at;     // 登録日
        $endDatetime   = $dataType->end_datetime;   // 掲載終了日

        // 掲載開始日または登録日が存在しない場合の処理
        if (!$startDatetime || !$createdAt) {
            return $this->createFailureResponse($dataType);
        }

        // 掲載開始日が今日よりも新しい場合の処理
        if ($startDatetime->gt($currentDate)) {
            return $this->createFailureResponse($dataType);
        }

        if ($endDatetime) {
            // 掲載終了日が今日よりも古い場合の処理
            if ($currentDate->gt(Carbon::parse($endDatetime))) {
                $this->updateBroadcastNotification($dataType);
                return $this->createFailureResponse($dataType);
            }
        }

        // 掲載開始日が現在日時以前の場合に通知
        if ($startDatetime->lessThanOrEqualTo($currentDate)) {
            // メッセージ送信処理の実行
            return $this->sendWowTalkMessages($dataType, $type);
        }

        return $this->createFailureResponse($dataType);
    }


    /**
     * WowTalkメッセージを送信するメソッド
     * 各店舗に対してWowTalk APIを介して未読メッセージ通知を送信します。
     *
     * @param mixed $dataType メッセージまたはマニュアルオブジェクト
     * @param string $type ログのタイプ ('message' または 'manual')
     * @return array メッセージ送信結果のレスポンス（成功、失敗、エラーのいずれか）
     */
    private function sendWowTalkMessages($dataType, $type)
    {
        // 通知対象の店舗とWowTalk IDを取得
        $wowtalk_data = [];
        if ($type === 'message') {
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
                ->select('wowtalk_shops.wowtalk1_id', 'wowtalk_shops.wowtalk2_id', 'wowtalk_shops.business_notification1', 'wowtalk_shops.business_notification2')
                ->get()
                ->map(function ($result) {
                    return [
                        'wowtalk1_id' => $result->business_notification1 ? $result->wowtalk1_id : null,
                        'wowtalk2_id' => $result->business_notification2 ? $result->wowtalk2_id : null,
                    ];
                })
                ->toArray();

        } elseif ($type === 'manual') {
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
                ->select('wowtalk_shops.wowtalk1_id', 'wowtalk_shops.wowtalk2_id', 'wowtalk_shops.business_notification1', 'wowtalk_shops.business_notification2')
                ->get()
                ->map(function ($result) {
                    return [
                        'wowtalk1_id' => $result->business_notification1 ? $result->wowtalk1_id : null,
                        'wowtalk2_id' => $result->business_notification2 ? $result->wowtalk2_id : null,
                    ];
                })
                ->toArray();
        }

        // 通知対象のWowTalkIDがない場合
        if (empty($wowtalk_data)) {
            $this->updateBroadcastNotification($dataType);
            return $this->createFailureResponse($dataType);
        }

        // 通知対象のWowTalkIDがある場合
        $errorLogs = [];

        // メッセージ内容を生成
        $messageContent = null;
        try {
            $messageContent = $this->calculateAndGenerateMessageContent($dataType, $type);
        } catch (\Exception $e) {
            // メッセージ内容生成に失敗した場合のエラー処理
            $errorLog = $this->createErrorLog($dataType, $type, ['type' => 'message_content_error', 'response_result' => 'メッセージ生成に失敗しました', 'response_status' => $e->getMessage(), 'attempts' => 1], null, $messageContent);

            if ($errorLog) {
                $this->notifySystemAdmin(
                    $errorLog['type'] ?? 'unknown',
                    [
                        'message_id' => $errorLog['message_id'] ?? '',
                        'message_title' => $errorLog['message_title'] ?? '',
                        'request_message' => $errorLog['request_message'] ?? '',
                        'request_target' => $errorLog['request_target'] ?? '',
                        'log_type' => $type
                    ],
                    [
                        'error_message' => $errorLog['error_message'] ?? 'エラーメッセージがありません',
                        'status' => 'error',
                        'response_target' => $errorLog['response_target'] ?? '不明'
                    ]
                );
            }

            return $this->createErrorResponse($dataType, $e->getMessage(), 1);
        }

        // WowTalk IDを一括で収集
        $wowtalkIds = [];
        foreach ($wowtalk_data as $data) {
            foreach (['wowtalk1_id', 'wowtalk2_id'] as $wowtalkIdKey) {
                if (!empty($data[$wowtalkIdKey])) {
                    $wowtalkIds[] = $data[$wowtalkIdKey];
                }
            }
        }

        // WowTalk IDを20件ずつのバッチに分割してAPIを呼び出す
        $chunkedWowtalkIds = array_chunk($wowtalkIds, 20);
        foreach ($chunkedWowtalkIds as $batch) {
            try {
                $apiResult = SendWowTalkApi::sendWowTalkApiRequest($batch, $messageContent);
                if (is_array($apiResult)) {
                    if (isset($apiResult['type']) && ($apiResult['type'] === 'WowTalkAPI_error' || $apiResult['type'] === 'unexpected_response')) {
                        // エラーがある場合のみログを記録
                        $requestTarget = is_array($apiResult['request_target'])
                            ? implode(', ', $apiResult['request_target'])
                            : $apiResult['request_target'];
                        $responseResult = is_array($apiResult['response_result'])
                            ? implode(', ', $apiResult['response_result'])
                            : $apiResult['response_result'];

                        // エラーログを生成
                        $errorLogs[] = $this->createErrorLog($dataType, $type, [
                            'type' => $apiResult['type'],
                            'request_target' => $requestTarget,
                            'response_result' => $responseResult,
                            'response_status' => $apiResult['response_status'],
                            'attempts' => $apiResult['attempts']
                        ], $batch, $messageContent);
                    }
                }
            } catch (\Exception $e) {
                // エラーログを生成
                $errorLogs[] = $this->createErrorLog($dataType, $type, [
                    'type' => 'api_error',
                    'response_result' => 'API呼び出しに失敗しました',
                    'response_status' => $e->getMessage(),
                    'attempts' => 1
                ], $batch, $messageContent);
            }
        }

        // すべてのAPI処理が終了した後でエラーログを集約
        if (!empty($errorLogs)) {
            if ($type === 'message') {
                foreach ($errorLogs as $errorLog) {
                    $this->notifySystemAdmin(
                        $errorLog['type'] ?? 'unknown',
                        [
                            'message_id' => $errorLog['message_id'] ?? '',
                            'message_title' => $errorLog['message_title'] ?? '',
                            'request_message' => $errorLog['request_message'] ?? '',
                            'request_target' => $errorLog['request_target'] ?? '',
                            'log_type' => $type
                        ],
                        [
                            'error_message' => $errorLog['error_message'] ?? 'エラーメッセージがありません',
                            'status' => 'error',
                            'response_target' => $errorLog['response_target'] ?? '不明'
                        ]
                    );
                }
            } elseif ($type === 'manual') {
                foreach ($errorLogs as $errorLog) {
                    $this->notifySystemAdmin(
                        $errorLog['type'] ?? 'unknown',
                        [
                            'manual_id' => $errorLog['manual_id'] ?? '',
                            'manual_title' => $errorLog['manual_title'] ?? '',
                            'request_message' => $errorLog['request_message'] ?? '',
                            'request_target' => $errorLog['request_target'] ?? '',
                            'log_type' => $type
                        ],
                        [
                            'error_message' => $errorLog['error_message'] ?? 'エラーメッセージがありません',
                            'status' => 'error',
                            'response_target' => $errorLog['response_target'] ?? '不明'
                        ]
                    );
                }
            }

            // エラー時のレスポンスを返す
            $errorMessageString = implode('; ', array_unique(array_column($errorLogs, 'error_message')));
            return $this->createErrorResponse($dataType, $errorMessageString, 1);
        }

        // 成功時のレスポンスを返す
        return $this->createSuccessResponse($dataType);
    }


    /**
     * 未読メッセージ数を計算し、メッセージ内容を生成するメソッド
     * クルーの総メッセージ数と既読数を基に未読メッセージ数を算出し、それに基づいてメッセージ内容を作成します。
     *
     * @param mixed $dataType メッセージまたはマニュアルオブジェクト
     * @param string $type ログのタイプ ('message' または 'manual')
     * @return string 生成されたメッセージ内容
     * @throws \Exception メッセージ内容が800文字を超える場合にスロー
     */
    private function calculateAndGenerateMessageContent($dataType, $type)
    {
        if ($type === 'message') {
            // メッセージ内容を生成
            $messageContent = "業連：{$dataType->title}が配信されました。確認してください。\n";
            $messageContent .= "https://stag-innerstreaming.zensho-i.net/message/?search_period=all\n";
            // $messageContent .= "https://innerstreaming.zensho-i.net/message/?search_period=all\n";
        } elseif ($type === 'manual') {
            // メッセージ内容を生成
            $messageContent = "マニュアル：{$dataType->title}が配信されました。確認してください。\n";
            $messageContent .= "https://stag-innerstreaming.zensho-i.net/manual?keyword=&search_period=all\n";
            // $messageContent .= "https://innerstreaming.zensho-i.net/manual?keyword=&search_period=all\n";
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
            Log::info("システム管理者にエラーメールを送信しました。");
        } else {
            Log::error("メール送信中にエラーが発生しました。");
        }
    }


    /**
     * 業連配信通知フラグを更新するメソッド
     *
     * @param mixed $dataType メッセージまたはマニュアルオブジェクト
     * @return void
     */
    private function updateBroadcastNotification($dataType)
    {
        // 業連配信通知フラグを更新（0:なし）
        $dataType->timestamps = false; // タイムスタンプを無効にする
        $dataType->is_broadcast_notification = 0;
        $dataType->save();
        $dataType->timestamps = true; // タイムスタンプを再度有効にする
    }


    /**
     * 成功時のレスポンスを生成するメソッド
     *
     * @param mixed $dataType メッセージまたはマニュアルオブジェクト
     * @return array 成功レスポンス
     */
    private function createSuccessResponse($dataType)
    {
        // 業連配信通知フラグを更新（2:済み）
        $dataType->timestamps = false; // タイムスタンプを無効にする
        $dataType->is_broadcast_notification = 2;
        $dataType->save();
        $dataType->timestamps = true; // タイムスタンプを再度有効にする

        return [
            'status' => 'success',
            'title' => $dataType->title
        ];
    }


    /**
     * 失敗時のレスポンスを生成するメソッド
     *
     * @param mixed $dataType メッセージまたはマニュアルオブジェクト
     * @return array 失敗レスポンス
     */
    private function createFailureResponse($dataType)
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
     * @param string $errorMessage エラーメッセージ
     * @param int $attempts リトライ回数
     * @return array エラーレスポンス
     */
    private function createErrorResponse($dataType, $errorMessage, $attempts = 1)
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
     * @param string $type ログのタイプ ('message' または 'manual')
     * @param string|null $wowtalkId WowTalk ID
     * @param array $apiResult APIリクエストの結果
     * @param string|null $messageContent 送信されたメッセージ内容
     * @return array エラーログ
     */
    private function createErrorLog($dataType, $type, $apiResult, $wowtalkId = null, $messageContent = null)
    {
        if ($type === 'message') {
            return [
                'type' => $apiResult['type'],
                'message_id' => $dataType->id ?? '',
                'message_title' => $dataType->title ?? '',
                'error_message' => $apiResult['response_result'] . ' : ' . $apiResult['response_status'],
                'request_message' => $messageContent ?? '',
                'request_target' => $wowtalkId ?? '',
                'response_target' => $apiResult['response_target'] ?? '',
                'attempts' => $apiResult['attempts'] ?? 1
            ];
        } elseif ($type === 'manual') {
            return [
                'type' => $apiResult['type'],
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
     * @param WowTalkNotificationLog $messageLog ログオブジェクト
     * @param array $successLogs 成功ログ
     * @param array $failureLogs 失敗ログ
     * @param array $errorLogs エラーログ
     * @param string $type ログのタイプ ('message' または 'manual')
     */
    private function logResults($messageLog, $successLogs, $failureLogs, $errorLogs, $type)
    {
        if ($type === 'message') {
            Log::info("---業務連絡---");
        } elseif ($type === 'manual') {
            Log::info("---マニュアル---");
        }

        foreach ($successLogs as $log) {
            $label = "業務連絡：";
            Log::info($label . $log['title']);
        }

        Log::info("---送信しない---");
        foreach ($failureLogs as $log) {
            $label = "業務連絡：";
            Log::info($label . $log['title']);
        }

        Log::info("---送信エラー---");
        foreach ($errorLogs as $log) {
            $label = "業務連絡：";
            Log::error($label . $log['title']);
            Log::error("エラー内容：" . $log['error_message']);

            // エラーログをデータベースに保存
            $this->storeErrorLogsInDatabase($messageLog, $log, $type);
        }
    }


    /**
     * エラーログをデータベースに格納する関数
     * @param WowTalkNotificationLog $messageLog ログオブジェクト
     * @param array $log エラーログ
     * @param string $type ログのタイプ ('message' または 'manual')
     */
    private function storeErrorLogsInDatabase($messageLog, $log, $type)
    {
        try {
            $messageLog->log_type = $type;
            $messageLog->command_name = 'wowtalk:send-notifications-job';
            $messageLog->started_at = Carbon::now();
            $messageLog->status = false;

            // エラーメッセージを文にして保存
            $messageLog->error_message = $log['title'] . "：" . $log['error_message'];
            $messageLog->attempts = $log['attempts'];

            $messageLog->finished_at = Carbon::now();
            $messageLog->save();
        } catch (\Exception $e) {
            Log::error("エラーログのデータベース保存に失敗しました: " . $e->getMessage());
        }
    }
}
