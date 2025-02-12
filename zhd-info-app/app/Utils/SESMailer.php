<?php

namespace App\Utils;

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

class SESMailer
{
    private $sesClient;

    public function __construct()
    {
        $this->sesClient = new SesClient([
            'version' => 'latest',
            'region'  => 'us-west-2',
        ]);
    }

    public function sendEmail($fromName, $to, $subject, $message)
    {
        try {
            // 送信者名とメールアドレスを設定
            $fromAddress = 'zhd-gyoren-system@zensho.com';
            $fromName = 'システム管理者';
            $from = '=?UTF-8?B?' . base64_encode($fromName) . '?= <' . $fromAddress . '>';

            $this->sesClient->sendEmail([
                'Destination' => [
                    'ToAddresses' => $to,
                ],
                'Message' => [
                    'Body' => [
                        'Text' => [
                            'Charset' => 'UTF-8',
                            'Data' => $message,
                        ],
                    ],
                    'Subject' => [
                        'Charset' => 'UTF-8',
                        'Data' => $subject,
                    ],
                ],
                'Source' => $from,
                'SourceArn' => 'arn:aws:ses:us-west-2:016712135282:identity/zhd-gyoren-system@zensho.com',
            ]);

            return true;
        } catch (AwsException $e) {
            error_log($e->getMessage()); // エラーメッセージをログに出力
            return false;
        }
    }
}
