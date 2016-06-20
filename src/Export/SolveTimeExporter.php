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
            return true;
            //return !file_exists($this->getFileLocation($repository));
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
                return implode(':', [$warning->classification_id, $warning->file, $warning->rule, $this->getUniquePart($warning)]);
            })->all();

            if (is_null($initialWarnings)) {
                $initialWarnings = $currentSet;
            }

            // check if warnings have been solved
            foreach ($warningsPresent as $warning => $count) {
                if (!in_array($warning, $currentSet)) {
                    if ($count < $repository->results->count()) {
                        // invalid if solved after more than total count
                        $parts = explode(':', $warning);
                        $result_id = $result->id;
                        $solveTimes[$parts[0]][] = compact('warning', 'count', 'result_id');
                    }
                    unset($warningsPresent[$warning]);
                }
            }

            // increase counts
            foreach (array_diff($currentSet, $initialWarnings) as $warning) {
                $currentCount = array_get($warningsPresent, $warning, 0);
                $warningsPresent[$warning] = $currentCount + 1;
            }
        }

        return $solveTimes;
    }

    protected function getUniquePart($warning)
    {
        $uniquePart = $warning->code ?: $warning->line;
        if ($warning->rule == 'unused-wildcard-import') {
            $uniquePart .= $warning->message;
        }

        if ($warning->rule == 'AvoidCatchingGenericException') {
            $uniquePart .= $warning->line;
        }

        return $uniquePart;
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
