<?php
namespace App\Export;

use App\Repository;
use App\WarningClassification;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 02-06-2016
 * Time: 15:08
 */
class WarningCountsExporter extends DataExporter
{
    /**
     * @var Repository
     */
    protected $repository;

    public function __construct($repositoryName)
    {
        $this->repository = $repo = Repository::where('full_name', $repositoryName)->firstOrFail();
    }

    protected function getFileHeaders()
    {
        return ['result_id', 'commit_hash', 'committed_at', 'warnings_count'];
    }

    protected function getItems()
    {
        return (array) DB::table('results')->select(['results.id', 'results.hash', 'results.committed_at', DB::raw('count(warnings.id)')])->leftJoin('warnings', 'results.id', '=', 'warnings.result_id')->where('repository_id', $this->repository->id)->groupBy('results.id')->orderBy('results.id', 'desc')->get();
    }

    protected function getFileName()
    {
        return 'warning_counts/' . $this->repository->full_name;
    }

    private function countsPerCategory()
    {
        $classifications = WarningClassification::pluck('name', 'id');

        return $this->repository->results->map(function ($result) use ($classifications) {
            $warnings = $result->warnings->pluck('classification_id')->map(function ($classificationId) use ($classifications) {
                return $classifications[$classificationId];
            })->all();
            return array_count_values($warnings);
        });
    }
}
