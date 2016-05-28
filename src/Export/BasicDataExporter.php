<?php
namespace App\Export;

use App\Repository;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 28-05-2016
 * Time: 19:19
 */
class BasicDataExporter extends DataExporter
{
    protected $languages = ['Java', 'JavaScript', 'Python', 'Ruby'];

    protected function getFileHeaders()
    {
        return ['language', 'count', 'uses_build_tool', 'uses_asats', 'asat_in_build_tool', 'uses_travis'];
    }

    //public function export()
    //{
    //    $this->latexTable();
    //}

    protected function getItems()
    {
        $results = [];
        foreach ($this->languages as $language) {
            $repos = Repository::where(compact('language'));
            $totalCount = $repos->count();
            $otherColumns = array_slice($this->getFileHeaders(), array_search('uses_asats', $this->getFileHeaders()));

            $usesBuildTool = $repos->has('buildTools')->count();
            $usesBuildTool = $this->getCountWithPercentage($usesBuildTool, $totalCount);

            $rest = array_map(function ($column) use ($totalCount, $language) {
                $asatCount = Repository::where(compact('language'))->where($column, 1)->count();
                return $this->getCountWithPercentage($asatCount, $totalCount);
            }, $otherColumns);

            $results[] = array_merge([$language, $totalCount, $usesBuildTool], $rest);
        }

        return $results;
    }

    protected function getCountWithPercentage($count, $total)
    {
        return $count . ' (' . round($count / $total, 2) * 100 . '%)';
    }

    protected function getFileName()
    {
        return 'basic_data';
    }
}
