<?php

namespace App\Utils;

use Illuminate\Support\Facades\Log;

class SendWowTalkApi
{
    /**
     * WowTalkメッセージを送信するAPIリクエストを行う関数
     */
    public static function sendWowTalkApiRequest($wowtalkIds, $messageContent)
    {
        // WowTalk API
        $url = 'https://wow-talk.zensho.com/message';

        // 送信するデータ
        $data = array(
            'message' => $messageContent, // メッセージ本文 最大800文字 改行コードは\nで挿入できる
            'target'  => $wowtalkIds     // 送信先のWowtalkID（ユーザーID）最大20件 20件を超えた分は送信しない
        );

        $retryCount = 1;
        $maxRetries = 3;
        $retryInterval = 60;
        $response_data = [];

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
                Log::error("JSONデコードエラー: $jsonError");
                return $jsonError;
            }

            // レスポンスの内容に基づいて処理を分岐
            if ($httpCode === 200) {
                // 成功レスポンス
                return 'success';
            } elseif (in_array($httpCode, [400, 403])) {
                // 400または403エラー
                return self::createApiErrorResponse($messageContent, $wowtalkIds, $response_data, $httpCode, $retryCount, 'WowTalkAPI_error');
            } elseif ($httpCode === 500 && $retryCount < $maxRetries) {
                // 500エラー
                $retryCount++;
                sleep($retryInterval);
            } else {
                // 想定外のレスポンス処理
                return self::createApiErrorResponse($messageContent, $wowtalkIds, $response_data, $httpCode, $retryCount, 'unexpected_response');
            }
        } while ($retryCount < $maxRetries);
        // リトライ回数を超えた場合のエラーハンドリング
        return self::createApiErrorResponse($messageContent, $wowtalkIds, $response_data, $httpCode, $retryCount, 'WowTalkAPI_error');
    }

    /**
     * APIエラーレスポンスまたは想定外のレスポンスを生成するメソッド
     *
     * @param string $messageContent メッセージ内容
     * @param array $wowtalkIds WowTalk IDの配列
     * @param array $response_data APIからのレスポンスデータ
     * @param int $httpCode HTTPステータスコード
     * @param int $retryCount リトライ回数（デフォルトは1）
     * @param string $type エラーのタイプ ('WowTalkAPI_error' または 'unexpected_response')
     * @return array エラーレスポンス
     */
    private static function createApiErrorResponse($messageContent, $wowtalkIds, $response_data, $httpCode, $retryCount, $type)
    {
        return [
            'type' => $type,
            'message' => $messageContent,
            'request_target' => $wowtalkIds,
            'response_result' => $response_data['result'] ?? 'unexpected_response',
            'response_status' => $httpCode,
            'response_target' => $response_data['target'] ?? [],
            'attempts' => $retryCount
        ];
    }
}
