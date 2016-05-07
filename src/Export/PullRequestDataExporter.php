<?php
namespace App\Export;

use App\Analyzers\PullRequestsAnalyzer;
use Illuminate\Database\Capsule\Manager as DB;
use App\Repository;
use Illuminate\Support\Collection;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 06-05-2016
 * Time: 11:55
 */
class PullRequestDataExporter extends DataExporter
{
    protected function getFileHeaders()
    {
        return ['full_name', 'uses_asats', 'pull_request_count', 'merged_count', 'time_to_close', 'recent_density', 'lifetime_density', 'unique_user_count'];
    }

    protected function getFileName()
    {
        return 'pull_request_stats';
    }

    protected function getItems()
    {
        $repositories = Repository::has('pullRequests', '>=', 100)->get();

        $pulls = DB::select('select * from pull_requests WHERE pull_requests.repository_id IN (' . implode(',', $repositories->modelKeys()) . ')');
        $pullRequests = [];
        foreach ($pulls as $pr) {
            $pullRequests[$pr->repository_id][] = $pr;
        }

        return $repositories->map(function (Repository $repository) use ($pullRequests) {
            $repository->pullRequests = new Collection($pullRequests[$repository->id]);
            $analyzer = new PullRequestsAnalyzer($repository);

            return $analyzer->getData($this->getFileHeaders());
        });
    }
}
