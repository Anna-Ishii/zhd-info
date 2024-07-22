<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Message;
use App\Models\User;

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
        $this->info('WowTalk未読通知送信開始');

        try {
            // メッセージ送信のメイン処理を実行
            $this->sendMessageNotifications();

            $this->info('WowTalk未読通知送信完了');
        } catch (\Exception $e) {
            $this->error('エラーが発生しました: ' . $e->getMessage());
        }
    }

    public function sendMessageNotifications()
    {
        // 現在の東京時刻を取得
        $currentDate = Carbon::now('Asia/Tokyo');

        // 現在掲載中のメッセージと掲載終了メッセージを取得
        $allMessages = Message::where('editing_flg', false)->get();

        // 各メッセージを処理
        foreach ($allMessages as $message) {
            $this->processMessage($message, $currentDate);
        }
    }

    // メッセージを処理する関数
    private function processMessage(Message $message, Carbon $currentDate)
    {
        // メッセージの開始日時、作成日時、および終了日時を取得
        $startDatetime = $message->start_datetime;
        $createdAt = $message->created_at;
        $endDatetime = $message->end_datetime;

        // 開始日時と作成日時のどちらかがnullの場合の処理
        if (!$startDatetime || !$createdAt) {
            return;
        }

        // 掲載開始日と作成日に基づくメッセージ送信日時を取得
        if ($startDatetime->gte($createdAt)) {
            $messageSendDate = $this->getNextWeekDate($startDatetime);
        } else {
            $messageSendDate = $this->getNextWeekDate($createdAt);
        }

        // 掲載終了日-7日の日時を取得
        $sendMessage = true;
        if ($endDatetime) {
            $sevenDaysBeforeEnd = Carbon::parse($endDatetime)->subDays(7);
            if ($currentDate->gt($sevenDaysBeforeEnd)) {
                $sendMessage = false;
            }
        }

        // 今日が翌週かどうか、かつ現在の日付が掲載終了日-7日を過ぎていない場合にメッセージを送信
        if ($currentDate->isSameDay($messageSendDate) && $sendMessage) {
            $this->sendWowTalkMessage($message);
        }
    }

    // 翌週の日付を取得する関数
    private function getNextWeekDate($date)
    {
        return (new Carbon($date))->addWeek();
    }

    // WowTalkメッセージ送信処理
    private function sendWowTalkMessage($message)
    {
        // メッセージ内容を生成
        $messageContent = $this->createMessageContent($message);

        // メッセージ送信のロジック
        // APIのエンドポイントURL
        $url = 'https://wow-talk.zensho.com/message';

        // 送信するデータ
        $user_id = 'nssx020'; // ユーザーID

        //
        // 送信するデータ
        $data = array(
            'message' => $messageContent, // メッセージ本文 最大800文字 改行コードは\nで挿入できる
            'target' => array($user_id) // 送信先のWowtalkID（ユーザーID）最大20件 20件を超えた分は送信しない
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

        // リクエストを実行してレスポンスを取得
        $response = curl_exec($ch);

        // エラーが発生した場合の処理
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            die('cURLエラー: ' . $error);
        }

        // cURLセッションを終了
        curl_close($ch);

        // レスポンスをデコード
        $response_data = json_decode($response, true);

        // レスポンスの存在と形式を確認
        if (json_last_error() !== JSON_ERROR_NONE) {
            die('JSONデコードエラー: ' . json_last_error_msg());
        }

        // レスポンスを表示
        if (isset($response_data['result']) && $response_data['result'] == 'success') {
            echo 'メッセージ送信成功: ' . $response;
        } elseif (isset($response_data['result'])) {
            echo 'メッセージ送信失敗: ' . $response;
        } else {
            echo '予期しないレスポンス: ' . $response;
        }
    }

    // メッセージ内容を生成する関数
    private function createMessageContent($message)
    {
        // 未読者リストを取得
        $unreadUsers = $message->user()->wherePivot('read_flg', false)->orderBy('name', 'asc')->take(10)->get();

        // 未読者の名前を配列に格納
        $unreadUserNames = $unreadUsers->pluck('name')->toArray();
        $unreadUserNamesString = implode("\n　", $unreadUserNames);
        $additionalUnreadCount = $message->user()->wherePivot('read_flg', false)->count() - 10;

        // メッセージ内容をフォーマット
        $messageContent = "テスト\n 配信した業連で{$message->user()->wherePivot('read_flg', false)->count()}名の未読者がいます。\n"
            . "・業連名：{$message->title}\n"
            . "・配信日：" . $message->start_datetime->format('Y/m/d H:i') . "\n"
            . "・カテゴリ：{$message->category->name}\n"
            . "・URL：https://innerstreaming.zensho-i.net\n"
            . "・未読者：\n　{$unreadUserNamesString}";

        if ($additionalUnreadCount > 0) {
            $messageContent .= "\n　他{$additionalUnreadCount}名";
        }

        // メッセージは最大800文字に制限
        return mb_strimwidth($messageContent, 0, 800, "...");
    }
}
