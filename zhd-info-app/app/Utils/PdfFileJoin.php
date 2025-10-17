<?php

namespace App\Utils;

class PdfFileJoin
{
    public static function recompressPdf($filePath, $outputPath)
    {
        $command = "qpdf --stream-data=uncompress --force-version=1.4 " . escapeshellarg($filePath) . " " . escapeshellarg($outputPath);
        exec($command, $output, $returnVar);
        if ($returnVar !== 0) {
            throw new \Exception('Failed to recompress PDF using qpdf.');
        }
    }
}
