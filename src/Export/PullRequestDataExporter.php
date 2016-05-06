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
    public function pullRequestCounts()
    {
        $counts = Repository::all('pull_request_count', 'uses_asats')->toArray();
        $this->writeToCSV('pull_request_counts', $counts, ['count', 'uses_asats']);
    }

    public function pullRequestsData()
    {
        $start = microtime(true);
        $repositories = Repository::has('pullRequests', '>=', 100)->get();
        $fetched = microtime(true);
        var_dump('fetching: ' . ($fetched - $start));

        $pulls = DB::select('select * from pull_requests WHERE pull_requests.repository_id IN (' . implode(',', $repositories->modelKeys()) . ')');
        $pullRequests = [];
        foreach ($pulls as $pr) {
            $pullRequests[$pr->repository_id][] = $pr;
        }

        $repositories = $repositories->map(function (Repository $repository) use ($pullRequests) {
            $repository->pullRequests = new Collection($pullRequests[$repository->id]);
            return $repository;
        });

        $loaded = microtime(true);
        var_dump('loading: ' . ($loaded - $fetched));

        $repoData = $this->gatherPullRequestStatistics($repositories);

        $processed = microtime(true);
        var_dump('processing: ' . ($processed - $loaded));

        var_dump('total: ' . (microtime(true) - $start));

        $this->writeToCSV("pull_request_stats", $repoData, ['name', 'uses_asats', 'total_count', 'merged_count', 'time_to_close', 'user_count']);
    }

    protected function gatherPullRequestStatistics(Collection $repositories)
    {
        return $repositories->map(function (Repository $repository) {
            $analyzer = new PullRequestsAnalyzer($repository);
            return [
                $repository->full_name,
                $repository->uses_asats,
                $repository->pull_request_count,
                $analyzer->mergedCount(),
                $analyzer->timeToClose()->average(),
                $analyzer->uniqueUserCount()
            ];
        })->toArray();
    }
}
