<?php
namespace App\Analyzers;

use App\Repository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
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

    /**
     * @var Collection
     */
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
        })->average();
    }

    public function recentDensity()
    {
        $min = Carbon::parse($this->pullRequests->min('created_at'));
        $max = Carbon::parse($this->pullRequests->max('created_at'));

        return $this->pullRequests->count() / $min->diffInHours($max);
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

    public function getData($fields)
    {
        return array_map(function ($field) {
            $attribute = $this->repository[$field];
            return is_null($attribute) ? call_user_func([$this, camel_case($field)]) : $attribute;
        }, $fields);
    }
}
