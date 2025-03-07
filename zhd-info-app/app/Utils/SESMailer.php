<?php

namespace App\Utils;

use Aws\Ses\SesClient;
use Aws\Ses\Exception\SesException;

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

    public function sendEmail($fromName, $to, $subject, $message, $filePath = null)
    {
        try {
            $fromAddress = 'zhd-gyoren-system@zensho.com';
            $from = '=?UTF-8?B?' . base64_encode($fromName) . '?= <' . $fromAddress . '>';

            $boundary = uniqid('np');

            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "From: $from\r\n";
            $headers .= "To: " . implode(',', $to) . "\r\n";
            $headers .= "Subject: $subject\r\n";
            $headers .= "Content-Type: multipart/mixed; boundary=\"" . $boundary . "\"\r\n";

            $body = "--" . $boundary . "\r\n";
            $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $body .= $message . "\r\n";

            if ($filePath) {
                $attachment = new \SplFileInfo($filePath);
                $attachmentPath = $attachment->getRealPath();
                $attachmentContent = file_get_contents($attachmentPath);
                $attachmentBase64 = base64_encode($attachmentContent);

                $body .= "--" . $boundary . "\r\n";
                $body .= "Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; name=\"" . $attachment->getFilename() . "\"\r\n";
                $body .= "Content-Transfer-Encoding: base64\r\n";
                $body .= "Content-Disposition: attachment; filename=\"" . $attachment->getFilename() . "\"\r\n\r\n";
                $body .= $attachmentBase64 . "\r\n";
            }

            $body .= "--" . $boundary . "--";

            $result = $this->sesClient->sendRawEmail([
                'RawMessage' => [
                    'Data' => $headers . "\r\n" . $body,
                ],
                'Source' => $from,
                'SourceArn' => 'arn:aws:ses:us-west-2:016712135282:identity/zhd-gyoren-system@zensho.com',
            ]);

            return true;
        } catch (SesException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
