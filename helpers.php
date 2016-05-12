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

    if (strtolower($repository->language) == 'javascript') {
        system("cd $directory/$name && npm install");
    }
    elseif (strtolower($repository->language) == 'ruby') {
        system("cd $directory/$name && bundler install");
    }
    //elseif (strtolower($repository->language) == 'python') {
    //    system("cd $directory/$name && \$WORKON_HOME/python2/bin/python setup.py install");
    //}

    return $returnCode;
}

function absoluteRepositoriesDir() {
    return getenv('HOME') . '/' . getenv('REPOSITORIES_DIR');
}
