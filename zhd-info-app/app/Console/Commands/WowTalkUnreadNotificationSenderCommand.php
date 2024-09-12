<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Message;
use App\Models\Shop;
use App\Models\Organization1;
use App\Models\WowTalkNotificationLog;
use Illuminate\Support\Facades\Mail;

class WowTalkUnreadNotificationSenderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wowtalk:send-unread-notifications';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'WowTalkで未読通知を送信するコマンドです。';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        // メモリ制限を無効にする
        ini_set('memory_limit', '-1');

        $this->info('WowTalk未読通知送信開始');

        try {
            // 業務連絡メッセージ送信のログを作成
            $messageLog = new WowTalkNotificationLog();
            $messageLog->log_type = 'message';
            $messageLog->command_name = $this->signature;
            $messageLog->started_at = Carbon::now();
            $messageLog->status = true;
            $messageLog->attempts = 1;
            $messageLog->save();

            // 業務連絡メッセージ送信のメイン処理を実行
            $this->sendNotifications();

            // 成功時の処理
            $messageLog->finished_at = Carbon::now();
            $messageLog->save();

            $this->info('WowTalk未読通知送信完了');
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
     */
    private function sendNotifications()
    {
        // 現在の東京時刻を取得
        $currentDate = Carbon::now('Asia/Tokyo');

        // 現在掲載中と掲載終了を取得
        $messages = Message::where('editing_flg', false)->get();

        // 各種ログ用の配列を初期化
        $successLogs = [];
        $failureLogs = [];
        $errorLogs = [];

        foreach ($messages as $message) {
            $sendResult = $this->processItem($message, $currentDate);

            // 処理結果に応じてログを分類
            switch ($sendResult['status']) {
                case 'success':
                    $successLogs[] = [
                        'title' => $message->title,
                    ];
                    break;
                case 'failure':
                    $failureLogs[] = [
                        'title' => $message->title,
                    ];
                    break;
                case 'error':
                    $errorLogs[] = [
                        'title' => $message->title,
                        'error_message' => $sendResult['error_message'] ?? '不明なエラー',
                        'attempts' => $sendResult['attempts'] ?? 1
                    ];
                    break;
            }
        }

        // ログの出力
        $this->logResults($successLogs, $failureLogs, $errorLogs);
    }


    /**
     * 個別メッセージの処理
     * 各メッセージが送信対象かを確認し、対象であれば送信処理を実行します。
     *
     * @param Message $message メッセージオブジェクト
     * @param Carbon $currentDate 現在の日時
     * @return array 処理結果のレスポンス（成功、失敗、エラーのいずれか）
     */
    private function processItem($message, $currentDate)
    {
        // 各種日付の取得
        $startDatetime = $message->start_datetime; // 掲載開始日
        $createdAt     = $message->created_at;     // 登録日
        $endDatetime   = $message->end_datetime;   // 掲載終了日

        // 掲載開始日または登録日が存在しない場合の処理
        if (!$startDatetime || !$createdAt) {
            return $this->createFailureResponse($message);
        }

        // 掲載開始日が今日よりも新しい場合の処理
        if ($startDatetime->gt($currentDate)) {
            return $this->createFailureResponse($message);
        }

        if ($endDatetime) {
            // 掲載終了日が今日よりも古い場合の処理
            if ($currentDate->gt(Carbon::parse($endDatetime))) {
                return $this->createFailureResponse($message);
            }
        }

        // 掲載開始日と登録日に基づくメッセージ送信日時を取得
        if ($startDatetime->gte($createdAt)) {
            $sendDate  = $startDatetime;
        } else {
            $sendDate  = $createdAt;
        }

        // 今日から1週間前と1週間後の範囲を取得
        $oneWeekAgo = $currentDate->copy()->subWeek();
        $oneWeekLater = $currentDate->copy()->addWeek();

        // メッセージ送信日が今日から1週間前と1週間後の範囲内にあるか確認
        if ($sendDate->between($oneWeekAgo, $oneWeekLater)) {
            // メッセージ送信処理の実行
            return $this->sendWowTalkMessages($message);
        }

        return $this->createFailureResponse($message);
    }


    /**
     * WowTalkメッセージを送信するメソッド
     * 各店舗に対してWowTalk APIを介して未読メッセージ通知を送信します。
     *
     * @param Message $message メッセージオブジェクト
     * @return array メッセージ送信結果のレスポンス（成功、失敗、エラーのいずれか）
     */
    private function sendWowTalkMessages($message)
    {
        // 通知対象の店舗とWowTalk IDを取得
        $wowtalk_data = [];
        $wowtalk_data = DB::table('wowtalk_shops')
            ->join('message_shop', 'wowtalk_shops.shop_id', '=', 'message_shop.shop_id')
            ->where('message_shop.message_id', $message->id)
            ->where('wowtalk_shops.notification_target', true)
            ->select('wowtalk_shops.shop_id', 'wowtalk_shops.wowtalk_id')
            ->get()
            ->map(function ($result) {
                return [
                    'shop_id' => $result->shop_id,
                    'wowtalk_id' => $result->wowtalk_id,
                ];
            })
            ->toArray();

        // 通知対象のWowTalkIDがない場合
        if (empty($wowtalk_data)) {
            return $this->createFailureResponse($message);
        }

        // エラーログのための配列
        $errorLogs = [];
        foreach ($wowtalk_data as $data) {
            try {
                // メッセージ内容を生成
                $messageContent = $this->calculateAndGenerateMessageContent($message, $data['shop_id']);

                // メッセージ内容が800文字を超える場合はエラーをスロー
                if (mb_strlen($messageContent) > 800) {
                    throw new \Exception("Message content exceeds 800 characters.");
                }
            } catch (\Exception $e) {
                // メッセージ内容生成に失敗した場合のエラー処理
                $errorLog = $this->createErrorLog($message, $data, ['type' => 'message_content_error', 'response_result' => 'メッセージ生成に失敗しました', 'response_status' => $e->getMessage(), 'attempts' => 1], $messageContent = '');

                $this->notifySystemAdmin(
                    $errorLog['type'],
                    $errorLog,
                    ['error_message' => $errorLog['error_message'], 'status' => 'error', 'response_target' => $errorLog['response_target']]
                );

                return $this->createErrorResponse($message, $e->getMessage(), 1);
            }

            // APIリクエストを1件ずつ送信
            $apiResult = $this->sendWowTalkApiRequest([$data['wowtalk_id']], $messageContent);

            if (is_array($apiResult)) {
                // エラーログを生成
                $errorLogs[] = $this->createErrorLog($message, $data, $apiResult, $messageContent);
            }
        }

        // すべてのAPI処理が終了した後でエラーログを集約
        if (!empty($errorLogs)) {
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
                        'request_target' => $errorLog['request_target']
                    ],
                    [
                        'error_message' => $errorLog['error_message'],
                        'status' => 'error',
                        'response_target' => $errorLog['response_target']
                    ]
                );
            }

            // エラー時のレスポンスを返す
            $errorMessageString = implode('; ', array_unique(array_column($errorLogs, 'error_message')));
            return $this->createErrorResponse($message, $errorMessageString, $errorLog['attempts']);
        }

        // 成功時のレスポンスを返す
        return $this->createSuccessResponse($message);
    }


    /**
     * 未読メッセージ数を計算し、メッセージ内容を生成するメソッド
     * クルーの総メッセージ数と既読数を基に未読メッセージ数を算出し、それに基づいてメッセージ内容を作成します。
     *
     * @param Message $message メッセージオブジェクト
     * @param int $shopId 店舗ID
     * @return string 生成されたメッセージ内容
     * @throws \Exception メッセージ内容が800文字を超える場合にスロー
     */
    private function calculateAndGenerateMessageContent($message, $shopId)
    {
        // 該当クルーの総メッセージ数を取得
        $crewMessageCounts = DB::table('crews as c')
            ->join('message_user as mu', 'mu.user_id', '=', 'c.user_id')
            ->where('mu.message_id', $message->id)
            ->where('mu.shop_id', $shopId)
            ->count();

        // クルーの既読メッセージ数を取得
        $crewMessageReadCounts = DB::table('crew_message_logs as cml')
            ->join('crews as c', 'c.id', '=', 'cml.crew_id')
            ->join('message_user as mu', 'mu.user_id', '=', 'c.user_id')
            ->where('mu.message_id', $message->id)
            ->where('mu.shop_id', $shopId)
            ->where('mu.read_flg', true)
            ->where('cml.message_id', $message->id)
            ->count();

        // 未読メッセージ数を計算
        $unreadMessageCounts = $crewMessageCounts - $crewMessageReadCounts;

        // メッセージ内容を生成
        $messageContent = "{$message->title}（" . $message->start_datetime->format('Y/m/d H:i') . "配信）の未読者が{$unreadMessageCounts}名います。確認してください。\n";
        return $messageContent;
    }


    /**
     * WowTalkメッセージを送信するAPIリクエストを行う関数
     */
    private function sendWowTalkApiRequest($wowtalk_ids, $messageContent)
    {
        // WowTalk API
        $url = 'https://wow-talk.zensho.com/message';

        // 送信するデータ
        $data = array(
            'message' => $messageContent, // メッセージ本文 最大800文字 改行コードは\nで挿入できる
            'target'  => $wowtalk_ids     // 送信先のWowtalkID（ユーザーID）最大20件 20件を超えた分は送信しない
        );

        $retryCount = 1;
        $maxRetries = 3;
        $retryInterval = 60;
        do {
            // cURLセッションを初期化
            $ch = curl_init($url);

            // ヘッダーを設定
            $api_key = 'osKHSzS8682LsLcM6Yw0O6PSVIXY5UBJ745nUcNv';  // APIキー
            $headers = array(
                'x-api-key: ' . $api_key,
                'Content-Type: application/json'
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            // オプションを設定
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            // リクエストを実行してレスポンスを取得
            $response = curl_exec($ch);

            // リクエストが失敗した場合のエラーハンドリング
            if ($response === false) {
                $error = curl_error($ch);
                curl_close($ch);
                return 'curl_error: ' . $error;
            }
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            // cURLセッションを終了
            curl_close($ch);

            // レスポンスをデコード
            $response_data = json_decode($response, true);

            // レスポンスの存在と形式を確認
            if (json_last_error() !== JSON_ERROR_NONE) {
                $jsonError = 'json_decode_error: ' . json_last_error_msg();
                $this->error("JSONデコードエラー: $jsonError");
                return $jsonError;
            }

            // レスポンスの内容に基づいて処理を分岐
            if ($httpCode === 200) {
                // 成功レスポンス
                return 'success';
            } elseif (in_array($httpCode, [400, 403])) {
                // 400または403エラー
                return $this->createApiErrorResponse($messageContent, $wowtalk_ids, $response_data, $httpCode, $retryCount, 'WowTalkAPI_error');
            } elseif ($httpCode === 500 && $retryCount < $maxRetries) {
                // 500エラー
                $retryCount++;
                sleep($retryInterval);
            } else {
                // 想定外のレスポンス処理
                return $this->createApiErrorResponse($messageContent, $wowtalk_ids, $response_data, $httpCode, $retryCount, 'unexpected_response');
            }
        } while ($retryCount < $maxRetries);
        // リトライ回数を超えた場合のエラーハンドリング
        return $this->createApiErrorResponse($messageContent, $wowtalk_ids, $response_data, $httpCode, $retryCount, 'WowTalkAPI_error');
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
        $to = ['yhonda@nssx.co.jp'];
        $subject = '【業連・動画配信システム】WowTalk連携エラー';

        $message = "WowTalk連携でエラーが発生しました。ご確認ください。\n\n";
        $message .= "■エラー内容\n" . ucfirst($errorType) . "が発生しました。\n\n";

        // リクエストデータ
        if (is_array($requestData)) {
            // 基本情報
            $message .= "■基本情報\n";
            $message .= "業態コード : " . ($requestData['org1_name'] ?? '') . "\n";
            $message .= "店舗コード : " . ($requestData['shop_code'] ?? '') . "\n";
            $message .= "店舗名 : " . ($requestData['shop_name'] ?? '') . "\n";
            $message .= "業連ID : " . ($requestData['message_id'] ?? '') . "\n";
            $message .= "業連名 : " . ($requestData['message_title'] ?? '') . "\n\n";
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

        try {
            // 直接エンコードした送信者名とメールアドレスを設定
            $fromAddress = 'yhonda@nssx.co.jp';
            $fromName = '=?UTF-8?B?' . base64_encode('システム管理者（NSS様、IT担当（佐溝様、北川様））') . '?=';

            Mail::raw($message, function ($msg) use ($to, $subject, $fromAddress, $fromName) {
                $msg->to($to)
                    ->subject($subject)
                    ->from($fromAddress, $fromName);
            });

            // メール送信成功時のログ
            $this->info("システム管理者にエラーメールを送信しました。");
        } catch (\Exception $e) {
            // メール送信失敗時のログ
            $this->error("メール送信中にエラーが発生しました: " . $e->getMessage());
            $this->error("メール送信エラー: " . $e->getMessage());
        }
    }


    /**
     * 成功時のレスポンスを生成するメソッド
     *
     * @param Message $message メッセージオブジェクト
     * @return array 成功レスポンス
     */
    private function createSuccessResponse($message)
    {
        return [
            'status' => 'success',
            'title' => $message->title
        ];
    }


    /**
     * 失敗時のレスポンスを生成するメソッド
     *
     * @param Message $message メッセージオブジェクト
     * @param string $errorMessage エラーメッセージ
     * @return array 失敗レスポンス
     */
    private function createFailureResponse($message)
    {
        return [
            'status' => 'failure',
            'title' => $message->title
        ];
    }


    /**
     * エラーレスポンスを生成するメソッド
     *
     * @param Message $message メッセージオブジェクト
     * @param string $errorMessage エラーメッセージ
     * @param int $attempts リトライ回数
     * @return array エラーレスポンス
     */
    private function createErrorResponse($message, $errorMessage, $attempts = 1)
    {
        return [
            'status' => 'error',
            'title' => $message->title,
            'error_message' => $errorMessage,
            'attempts' => $attempts
        ];
    }


    /**
     * APIエラーレスポンスまたは想定外のレスポンスを生成するメソッド
     *
     * @param string $messageContent メッセージ内容
     * @param array $wowtalk_ids WowTalk IDの配列
     * @param array $response_data APIからのレスポンスデータ
     * @param int $httpCode HTTPステータスコード
     * @param int $retryCount リトライ回数（デフォルトは1）
     * @param string $type エラーのタイプ ('WowTalkAPI_error' または 'unexpected_response')
     * @return array エラーレスポンス
     */
    private function createApiErrorResponse($messageContent, $wowtalk_ids, $response_data, $httpCode, $retryCount, $type)
    {
        return [
            'type' => $type,
            'message' => $messageContent,
            'request_target' => $wowtalk_ids,
            'response_result' => $response_data['result'] ?? 'unexpected_response',
            'response_status' => $httpCode,
            'response_target' => $response_data['target'] ?? [],
            'attempts' => $retryCount
        ];
    }


    /**
     * エラーログを生成するメソッド
     *
     * @param Message $message メッセージオブジェクト
     * @param array $data 店舗データ
     * @param array $apiResult APIリクエストの結果
     * @param string $messageContent 送信されたメッセージ内容
     * @return array エラーログ
     */
    private function createErrorLog($message, $data, $apiResult, $messageContent = null)
    {
        $shop = Shop::where('id', $data['shop_id'] ?? 0)
            ->select('shop_code', 'display_name', 'organization1_id')
            ->first();

        $org1 = Organization1::where('id', $shop->organization1_id ?? 0)
            ->select('name')
            ->first();

        return [
            'type' => $apiResult['type'],
            'org1_name' => $org1->name ?? '',
            'shop_code' => $shop->shop_code ?? '',
            'shop_name' => $shop->display_name ?? '',
            'message_id' => $message->id,
            'message_title' => $message->title,
            'error_message' => $apiResult['response_result'] . ' : ' . $apiResult['response_status'],
            'request_message' => $messageContent ?? '',
            'request_target' => $data['wowtalk_id'] ?? '',
            'response_target' => $apiResult['response_target'] ?? '',
            'attempts' => $apiResult['attempts'] ?? 1
        ];
    }


    /**
     * 成功、失敗、エラーログを出力する関数
     */
    private function logResults($successLogs, $failureLogs, $errorLogs)
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
            $this->storeErrorLogsInDatabase($log);
        }
    }


    /**
     * エラーログをデータベースに格納する関数
     */
    private function storeErrorLogsInDatabase($log)
    {
        try {
            $errorLog = new WowTalkNotificationLog();
            $errorLog->log_type = 'message';
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
