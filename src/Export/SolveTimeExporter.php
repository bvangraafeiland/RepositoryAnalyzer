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
class SolveTimeExporter
{
    public function export()
    {
        $repositories = Repository::has('results')->with(['results' => function ($query) {
            $query->orderBy('id', 'desc');
        }])->get()->filter(function (Repository $repository) {
            return !file_exists($this->getFileLocation($repository));
        });

        foreach ($repositories as $repository) {
            var_dump("checking $repository->full_name");
            $solveTimes = $this->getSolveTimes($repository);
            $this->writeToFile($repository, $solveTimes);
        }
    }

    protected function getSolveTimes(Repository $repository)
    {
        $solveTimes = [];
        $initialWarnings = null;
        $warningsPresent = [];
        foreach ($repository->results as $result) {
            $warnings = collect(DB::table('warnings')->where('result_id', $result->id)->get());
            $currentSet = $warnings->map(function ($warning) {
                return "$warning->classification_id:" . $warning->file . $warning->column . $warning->rule . $warning->message;
            })->all();

            if (is_null($initialWarnings)) {
                $initialWarnings = $currentSet;
            }

            // check if warnings have been solved
            foreach ($warningsPresent as $warning => $count) {
                if (!in_array($warning, $currentSet)) {
                    $parts = explode(':', $warning);
                    $solveTimes[$parts[0]][] = $count;
                    unset($warningsPresent[$warning]);
                }
            }

            // increase counts
            foreach (array_diff($currentSet, $initialWarnings) as $warning) {
                $warningsPresent[$warning] = array_get($warningsPresent, $warning, 0) + 1;
            }
        }

        return $solveTimes;
    }

    protected function writeToFile(Repository $repository, array $solveTimes)
    {
        $location = $this->getFileLocation($repository);
        file_put_contents($location, json_encode($solveTimes));
    }

    /**
     * @param Repository $repository
     *
     * @return string
     */
    protected function getFileLocation(Repository $repository)
    {
        return PROJECT_DIR . '/results/solve_times/' . str_replace('/', '-', $repository->full_name) . '.json';
    }
}
