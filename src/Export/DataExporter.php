<?php
namespace App\Export;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 06-05-2016
 * Time: 11:56
 */
abstract class DataExporter
{
    protected function writeToCSV($fileName, $data, array $headers = [])
    {
        $file = fopen(PROJECT_DIR . "/results/$fileName.csv", 'w');
        if ($headers) {
            fputcsv($file, $headers);
        }
        foreach ($data as $item) {
            fputcsv($file, (array) $item);
        }
        fclose($file);
    }
}
