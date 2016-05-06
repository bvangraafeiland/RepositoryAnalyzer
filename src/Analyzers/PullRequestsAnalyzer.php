<?php
namespace App\Analyzers;

use App\Repository;
use Carbon\Carbon;
use stdClass;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 14-04-2016
 * Time: 17:43
 */
class PullRequestsAnalyzer
{
    protected $repository;
    protected $pullRequests;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
        $this->pullRequests = $repository->pullRequests;
    }

    public function timeToClose()
    {
        return $this->pullRequests->map(function (stdClass $pullRequest) {
            return Carbon::parse($pullRequest->created_at)->diffInSeconds(Carbon::parse($pullRequest->closed_at));
        });
    }

    public function uniqueUserCount()
    {
        return $this->pullRequests->map(function (stdClass $pullRequest) {
            return $pullRequest->user_id;
        })->unique()->count();
    }

    public function timeToReject()
    {
        //return $this->rejectedPullRequests->map(function ($pr) {
        //    return $pr->created_at->diffInSeconds($pr->closed_at);
        //});
    }

    public function timeToMerge()
    {
        //return $this->mergedPullRequests->map(function ($pr) {
        //    return $pr->created_at->diffInSeconds($pr->merged_at);
        //});
    }

    public function mergedCount()
    {
        return $this->pullRequests->filter(function (stdClass $pullRequest) {
            return $pullRequest->merged_at;
        })->count();
    }
}
