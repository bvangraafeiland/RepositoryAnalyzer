<?php

use App\GitHubClient;
use App\PullRequest;
use App\Repository;
use Carbon\Carbon;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

function progressBar(OutputInterface $output, $max) {
    $bar = new ProgressBar($output, $max);
    $bar->setBarCharacter('<info>=</info>');
    $bar->setProgressCharacter('|');

    return $bar;
}

function buildSearchQuery($lang, $year, $lastPush, $numStars, $start = '01-01', $end = '12-31') {
    return "language:$lang created:\"$year-$start .. $year-$end\" pushed:>=$lastPush stars:>=$numStars";
}

function codeContains($code, $string, $regex = false, $comment = "//") {
    $codeWithoutComments = preg_replace("%$comment.+%", "", $code);

    if ($regex)
        return (bool) preg_match($string, $code);

    return str_contains($codeWithoutComments, $string);
}

function cloneRepository($name) {
    $directory = absoluteRepositoriesDir();

    if (!file_exists($directory)) {
        mkdir($directory);
    }
    exec("cd $directory && git clone git://github.com/$name.git $name", $output, $returnCode);
    return $returnCode;
}

function absoluteRepositoriesDir() {
    return getenv('HOME') . '/' . getenv('REPOSITORIES_DIR');
}

function fetchPullRequests($repositoryName, $state = 'closed') {
    $repo = Repository::whereFullName($repositoryName)->firstOrFail();
    $pulls = GitHubClient::getInstance()->getPullRequests($repo->full_name, $state);
    foreach ($pulls as $apiAttributes) {
        $timestamps = collect(['created_at', 'updated_at', 'closed_at', 'merged_at'])->flatMap(function ($column) use ($apiAttributes) {
            return [$column => array_get($apiAttributes, $column) ? new Carbon($apiAttributes[$column]) : null];
        })->toArray();

        $pullRequest = PullRequest::findOrNew($apiAttributes['id']);
        $attributes = array_only($apiAttributes, ['id', 'number', 'state', 'title']) + ['user_id' => $apiAttributes['user']['id']] + $timestamps;
        $pullRequest->fill($attributes);
        $repo->pullRequests()->save($pullRequest);
    }
}
