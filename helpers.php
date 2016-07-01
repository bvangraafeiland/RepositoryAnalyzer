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

function cloneRepository(Repository $repository) {
    $directory = absoluteRepositoriesDir();
    $name = $repository->full_name;

    if (!file_exists($directory)) {
        mkdir($directory);
    }
    exec("cd $directory && git clone git://github.com/$name.git $name", $output, $returnCode);

    //if (strtolower($repository->language) == 'javascript') {
    //    system("cd $directory/$name && npm install");
    //}
    //elseif (strtolower($repository->language) == 'ruby') {
    //    system("cd $directory/$name && bundler install");
    //}
    //elseif (strtolower($repository->language) == 'python') {
    //    system("cd $directory/$name && \$WORKON_HOME/python2/bin/python setup.py install");
    //}

    return $returnCode;
}

function absoluteRepositoriesDir() {
    return getenv('HOME') . '/' . getenv('REPOSITORIES_DIR');
}

function array_median(array $array) {
    // perhaps all non numeric values should filtered out of $array here?
    $arraySize = count($array);
    if ($arraySize == 0) {
        throw new LengthException('Median of an empty array is undefined');
    }
    // if we're down here it must mean $array
    // has at least 1 item in the array.
    $center = (int) floor($arraySize / 2);

    sort($array, SORT_NUMERIC);
    $median = $array[$center]; // assume an odd # of items
    // Handle the even case by averaging the middle 2 items
    if ($arraySize % 2 == 0) {
        $median = ($median + $array[$center - 1]) / 2;
    }
    return $median;
}
