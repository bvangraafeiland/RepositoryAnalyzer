<?php
namespace App\Export;

use Exception;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 06-05-2016
 * Time: 11:56
 */
abstract class DataExporter
{
    protected abstract function getFileHeaders();
    protected abstract function getItems();
    protected abstract function getFileName();

    public function export()
    {
        $this->writeToCSV($this->getFileName(), $this->getItems(), $this->getFileHeaders());
    }
    
    protected function writeToCSV($fileName, $data, array $headers = [])
    {
        $file = fopen(PROJECT_DIR . "/results/$fileName.csv", 'w');
        if (!$file) {
            throw new Exception('File could not be opened for writing');
        }
        if ($headers) {
            fputcsv($file, $headers);
        }
        foreach ($data as $item) {
            fputcsv($file, (array) $item);
        }
        fclose($file);
    }
}
