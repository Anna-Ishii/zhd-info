<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Message;
use App\Models\WowtalkShop;
use App\Models\User;
use App\Models\ImsSyncLog;
use Illuminate\Support\Facades\Mail;

class WowTalkUnreadNotificationSender extends Command
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

        // ログ出力
        $this->info('start');
        $ims_log = new ImsSyncLog();
        $ims_log->import_at = new Carbon('now');
        $ims_log->save();

        $this->info('WowTalk未読通知送信開始');

        try {
            // メッセージ送信のメイン処理を実行
            $this->sendMessageNotifications();
            $this->info('WowTalk未読通知送信完了');

        } catch (\Throwable $th) {
            $th_msg = $th->getMessage();
            $this->info("エラーが発生しました: $th_msg");
        }
        // ログ出力
        $ims_log->save();
        $this->info('end');
    }


    /**
    * メッセージ通知を送信するメインの処理
    */
    public function sendMessageNotifications()
    {
        // // 現在の東京時刻を取得
        // $currentDate = Carbon::now('Asia/Tokyo');

        // テスト
        // 今日を月曜日に設定(2024-07-22 10:00)
        $currentDate = Carbon::create(2024, 7, 22, 10, 0, 0, 'Asia/Tokyo');

        // 現在掲載中のメッセージと掲載終了メッセージを取得
        $allMessages = Message::where('editing_flg', false)->get();

        // 各メッセージを処理
        foreach ($allMessages as $message) {
            $this->processMessage($message, $currentDate);
        }
    }


    /**
    * メッセージを処理する関数
    *
    * @param Message $message
    * @param Carbon $currentDate
    */
    private function processMessage(Message $message, Carbon $currentDate)
    {
        // メッセージの開始日時、作成日時、および終了日時を取得
        $startDatetime = $message->start_datetime;
        $createdAt = $message->created_at;
        $endDatetime = $message->end_datetime;

        // 開始日時または作成日時がnullの場合の処理
        if (!$startDatetime || !$createdAt) {
            $missingFields = [];
            if (!$startDatetime) {
                $missingFields[] = "開始日時";
            }
            if (!$createdAt) {
                $missingFields[] = "作成日時";
            }
            $this->info("---" . implode("と", $missingFields) . "なし---");
            return;
        }

        // 掲載開始日と作成日に基づくメッセージ送信日時を取得
        if ($startDatetime->gte($createdAt)) {
            $messageSendDate = $this->getNextWeekDate($startDatetime);
            $this->info("掲載開始日が作成日よりも後または同じです。掲載開始日: " . $messageSendDate->toDateString());
        } else {
            $messageSendDate = $this->getNextWeekDate($createdAt);
            $this->info("作成日が掲載開始日よりも後です。掲載開始日: " . $messageSendDate->toDateString());
        }

        // 掲載終了日-7日の日時を取得
        $sendMessage = true;
        if ($endDatetime) {
            $sevenDaysBeforeEnd = Carbon::parse($endDatetime)->subDays(7);
            $this->info("掲載終了日: " . $endDatetime->toDateString() . " の7日前の日付: " . $sevenDaysBeforeEnd->toDateString());

            if ($currentDate->gt($sevenDaysBeforeEnd)) {
                $sendMessage = false;
                $this->info("現在の日付が掲載終了日の7日前を過ぎています。メッセージは送信されません。");
            }
        }

        // 今日が翌週かどうか、かつ現在の日付が掲載終了日-7日を過ぎていない場合にメッセージを送信
        if ($currentDate->isSameDay($messageSendDate) && $sendMessage) {
            $this->info("今日がメッセージ送信日です。メッセージを送信します。");

            // // wowtalk 配信版
            // $this->sendWowTalkMessageWithRetry($message);

            // テスト
            $messageContent = $this->createMessageContent($message);
            echo $messageContent;

        } else {
            if (!$currentDate->isSameDay($messageSendDate)) {
                $this->info("今日はメッセージ送信日ではありません。");
            }
            if (!$sendMessage) {
                $this->info("現在の日付が掲載終了日の7日前を過ぎているため、メッセージは送信されません。");
            }
        }
    }


    /**
    * 翌週の日付を取得する関数
    *
    * @param Carbon $date
    * @return Carbon
    */
    private function getNextWeekDate($date)
    {
        // 翌週の日付
        // return (new Carbon($date))->addWeek();

        // 翌週月曜日の日付
        return (new Carbon($date))->next(Carbon::MONDAY);
    }


    /**
    * WowTalkメッセージ送信処理
    *
    * @param Message $message
    */
    private function sendWowTalkMessageWithRetry($message)
    {
        $retryCount = 0;
        $maxRetries = 3; // 最大リトライ回数
        // $retryInterval = 60; // 1分

        do {
            $this->info("WowTalkメッセージ送信試行回数: " . ($retryCount + 1));
            $result = $this->sendWowTalkMessage($message);

            if ($result === 'success') {
                $this->info("WowTalkメッセージ送信に成功しました。");
                return;
            }

            if (strpos($result, 'message_content_error') !== false) {
                $this->error("メッセージ内容の生成に失敗したため、リトライを中止します。");
                $this->notifySystemAdmin($result);
                return;
            }

            $retryCount++;
            $this->info("WowTalkメッセージ送信に失敗しました。リトライ回数: " . $retryCount);
            // sleep($retryInterval);

        } while ($retryCount < $maxRetries);

        // 最大リトライ回数を超えた場合、システム管理者に通知
        $this->error("WowTalkメッセージ送信が最大リトライ回数に達しました。システム管理者に通知します。");
        $this->notifySystemAdmin($result);
    }


    /**
    * WowTalkメッセージを送信する関数
    *
    * @param Message $message
    * @return string
    */
    private function sendWowTalkMessage($message)
    {
        $this->info("WowTalkメッセージ送信開始");

        // wowtalk_id取得
        // $shop_ids = MessageShop::where('message_id', $message->id)->pluck('shop_id')->toArray();

        // テスト
        $shop_ids = [0 => 111111, 1 => 111112, 2 => 111113, 3 => 111114];

        // wowtalkID取得
        $chunkSize = 100;
        $chunked_shop_ids = array_chunk($shop_ids, $chunkSize);

        $wowtalk_ids = [];
        foreach ($chunked_shop_ids as $chunk) {
            $ids = WowtalkShop::whereIn('shop_id', $chunk)
                ->where('notification_target', true)
                ->pluck('wowtalk_id')
                ->toArray();
            $wowtalk_ids = array_merge($wowtalk_ids, $ids);
        }

        // wowtalkIDなし
        if (empty($wowtalk_ids)) {
            $this->info("通知対象のWowTalkIDが見つかりませんでした。");
            return 'no_target';
        }
        $this->info("通知対象のWowTalkID: " . implode(', ', $wowtalk_ids));

        // メッセージ内容を生成
        try {
            $messageContent = $this->createMessageContent($message);
            $this->info("メッセージ内容: " . $messageContent);
        } catch (\Exception $e) {
            $this->error("メッセージ内容の生成に失敗しました: " . $e->getMessage());
            return 'message_content_error: ' . $e->getMessage();
        }

        // メッセージ送信のロジック
        $url = 'https://wow-talk.zensho.com/message';

        // 送信するデータ
        $data = array(
            'message' => $messageContent, // メッセージ本文 最大800文字 改行コードは\nで挿入できる
            'target' => $wowtalk_ids      // 送信先のWowtalkID（ユーザーID）最大20件 20件を超えた分は送信しない
        );

        // JSON形式にエンコード
        $json_data = json_encode($data);

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
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

        $this->info("メッセージ送信リクエストを送信します。");

        // リクエストを実行してレスポンスを取得
        $response = curl_exec($ch);

        // エラーが発生した場合の処理
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            $this->error("cURLエラー: " . $error);
            die('curl_error: ' . $error);
        }

        // cURLセッションを終了
        curl_close($ch);

        $this->info("メッセージ送信リクエストが完了しました。レスポンス: " . $response);

        // レスポンスをデコード
        $response_data = json_decode($response, true);

        // レスポンスの存在と形式を確認
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = 'json_decode_error: ' . json_last_error_msg();
            $this->error($error);
            return $error;
        }

        // レスポンスを表示
        if (isset($response_data['result']) && $response_data['result'] == 'success') {
            $this->info("メッセージ送信が成功しました。");
            return 'success';
        } elseif (isset($response_data['result'])) {
            $error = 'api_error: ' . $response_data['result'];
            $this->error($error);
            return $error;
        } else {
            $error = 'unexpected_response: ' . $response;
            $this->error($error);
            return $error;
        }
    }


    /**
    * メッセージコンテンツを作成する関数
    *
    * @param Message $message
    * @return string
    */
    private function createMessageContent($message)
    {
        // 未読者リストを取得
        $unreadUsers = $message->user()->wherePivot('read_flg', false)->orderBy('name', 'asc')->take(10)->get();

        // 未読者の名前を配列に格納
        $unreadUserNames = $unreadUsers->pluck('name')->toArray();
        $unreadUserNamesString = implode("\n　", $unreadUserNames);
        $additionalUnreadCount = $message->user()->wherePivot('read_flg', false)->count() - 10;


        // // メッセージ内容をフォーマット
        // $messageContent = "配信した業連で{$message->user()->wherePivot('read_flg', false)->count()}名の未読者がいます。\n"
        //     . "・業連名：{$message->title}\n"
        //     . "・配信日：" . $message->start_datetime->format('Y/m/d H:i') . "\n"
        //     . "・カテゴリ：{$message->category->name}\n"
        //     . "・URL：https://innerstreaming.zensho-i.net\n"
        //     . "・未読者：\n　{$unreadUserNamesString}";


        // メッセージ内容をフォーマット
        $messageContent = "{$message->title}（" . $message->start_datetime->format('Y/m/d H:i') . "配信）の未読者が{$message->user()->wherePivot('read_flg', false)->count()}名います。確認してください。\n";


        if ($additionalUnreadCount > 0) {
            $messageContent .= "\n　他{$additionalUnreadCount}名";
        }

        return mb_strimwidth($messageContent, 0, 800, "...");
    }


    /**
    * システム管理者に通知する関数
    *
    * @param string $error
    */
    private function notifySystemAdmin($error)
    {
        $this->info("システム管理者にエラーを通知します。エラーメッセージ: $error");
        $to = ['xxx@example.com', 'xxx@example.com', 'xxx@example.com'];
        $subject = 'WowTalk API エラー通知';
        $message = "WowTalk APIでエラーが発生しました。\nエラーメッセージ: $error";

        Mail::raw($message, function ($msg) use ($to, $subject) {
            $msg->to($to)->subject($subject);
        });
    }
}
