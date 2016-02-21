<?php

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

function progressBar(OutputInterface $output, $max) {
    $bar = new ProgressBar($output, $max);
    $bar->setBarCharacter('<info>=</info>');
    $bar->setProgressCharacter('|');

    return $bar;
}
