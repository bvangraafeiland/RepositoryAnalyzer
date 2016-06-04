<?php
namespace App\Export;

use App\Repository;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 03-06-2016
 * Time: 16:22
 */
class WarningsDataExporter extends DataExporter
{
    protected function getFileHeaders()
    {
        return ['full_name', 'language', 'average_count', 'median_count', 'max_count', 'min_count'];
    }

    protected function getItems()
    {
        //TODO figure out
        $repositories = DB::table('repositories')->join('results', 'repositories.id', '=', 'repository_id')->join('warnings', 'results.id', '=', 'result_id');
    }
}
