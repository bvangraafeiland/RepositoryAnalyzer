<?php
namespace App\Export;

use App\WarningClassification;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 07-06-2016
 * Time: 15:21
 */
class SolveTimeCombiner
{
    public function export()
    {
        $result = collect(scandir(PROJECT_DIR . '/results/solve_times'))->filter(function ($file) {
            return ends_with($file, '.json');
        })->map(function ($file) {
            return json_decode(file_get_contents(PROJECT_DIR . "/results/solve_times/$file"), true);
        })->reduce(function ($carry, $projectTimes) {
            foreach ($projectTimes as $classificationId => $warnings) {
                $solveTimes = collect($warnings)->pluck('count')->all();
                $carry[$classificationId] = array_merge(array_get($carry, $classificationId, []), $solveTimes);
            }
            return $carry;
        }, []);

        $classifications = WarningClassification::pluck('name', 'id');

        foreach ($result as $classificationId => $solveTimes) {
            $fileName = snake_case($classifications[$classificationId]);
            file_put_contents(PROJECT_DIR . "/results/solve_time_per_category/$fileName.csv", implode(PHP_EOL, $solveTimes) . PHP_EOL);
        }
    }
}
