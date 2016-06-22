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
        $solveTimesPerRepository = collect(scandir(PROJECT_DIR . '/results/solve_times'))->filter(function ($file) {
            return ends_with($file, '.json');// && !str_contains($file, ['nupic']);
        })->map(function ($file) {
            return json_decode(file_get_contents(PROJECT_DIR . "/results/solve_times/$file"), true);
        });

        $solveTimeAverages = [];

        foreach ($solveTimesPerRepository as $file) {
            foreach ($file as $categoryId => $solves) {
                $solveTimeAverages[$categoryId][] = collect($solves)->average('count');
            }
        }

        $classifications = WarningClassification::pluck('name', 'id');
        foreach ($solveTimeAverages as $classificationId => $solveTimes) {
            $fileName = snake_case($classifications[$classificationId]);
            file_put_contents(PROJECT_DIR . "/results/solve_times_per_category_normalized/$fileName.csv", implode(PHP_EOL, $solveTimes) . PHP_EOL);
        }

        //$solveTimeSets = $solveTimesPerRepository->reduce(function ($carry, $projectTimes) {
        //    foreach ($projectTimes as $classificationId => $warnings) {
        //        $solveTimes = collect($warnings)->pluck('count')->all();
        //        $carry[$classificationId] = array_merge(array_get($carry, $classificationId, []), $solveTimes);
        //    }
        //    return $carry;
        //}, []);
        //
        //
        //foreach ($solveTimeSets as $classificationId => $solveTimes) {
        //    $fileName = snake_case($classifications[$classificationId]);
        //    file_put_contents(PROJECT_DIR . "/results/solve_time_per_category/$fileName.csv", implode(PHP_EOL, $solveTimes) . PHP_EOL);
        //}
    }
}
