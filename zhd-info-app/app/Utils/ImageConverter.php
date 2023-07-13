<?php

namespace App\Utils;

class ImageConverter
{
    public static function movie2image($movie_path)
    {
        $shot_sec = 4;
        $dirname = dirname($movie_path);
        $filename = pathinfo($movie_path, PATHINFO_FILENAME);
        $output_path = $dirname . '/' . $filename . '.jpg';
        $cmd = 'python3 /var/www/zhd-info-app/py/m.py "' . $movie_path . '" ' . $shot_sec . ' "' . $output_path . '"';
        exec($cmd, $output);
        return $output_path;
    }

    public static function pdf2image($pdf_path)
    {
        $dirname = dirname($pdf_path);
        $filename = pathinfo($pdf_path, PATHINFO_FILENAME);
        $output_path = $dirname . '/' . $filename . '.jpg';
        $output_filename = $filename . '.jpg';
        $cmd = 'python3 /var/www/zhd-info-app/py/p.py "' . $pdf_path . '" "' . $dirname . '" "'. $output_filename .'"';
        exec($cmd, $output);
        return $output_path;
    }
}