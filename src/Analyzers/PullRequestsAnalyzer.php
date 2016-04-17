<?php
namespace App\Analyzers;

use App\PullRequest;
use App\Repository;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 14-04-2016
 * Time: 17:43
 */
class PullRequestsAnalyzer
{
    /**
     * @var Repository
     */
    protected $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function timeToClose()
    {
        return $this->repository->pullRequests->map(function (PullRequest $pr) {
            return $pr->created_at->diffInSeconds($pr->closed_at);
        });
    }

    public function timeToReject()
    {
        return $this->repository->rejectedPullRequests->map(function (PullRequest $pr) {
            return $pr->created_at->diffInSeconds($pr->closed_at);
        });
    }

    public function timeToMerge()
    {
        return $this->repository->mergedPullRequests->map(function (PullRequest $pr) {
            return $pr->created_at->diffInSeconds($pr->merged_at);
        });
    }

    public function mergedCount()
    {
        return $this->repository->mergedPullRequests()->count();
    }

    public function totalCount()
    {
        return $this->repository->pullRequests()->count();
    }
}
