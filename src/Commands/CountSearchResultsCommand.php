<?php
namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 26-02-2016
 * Time: 16:12
 */
class CountSearchResultsCommand extends Command
{
    use GithubApi;

    protected function configure()
    {
        $this->setName('count:results')
            ->addArgument('language', InputArgument::REQUIRED)
            ->addArgument('year', InputArgument::REQUIRED)
            ->addOption('stars', null, InputOption::VALUE_REQUIRED, "Minimum amount of stars a repository should have", 200);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $count = $this->github->countRepositories($input->getArgument('language'), $input->getArgument('year'), $input->getOption('stars'));
        $output->writeln("<info>$count results found</info>");
    }
}
