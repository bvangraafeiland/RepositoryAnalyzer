<?php
namespace App\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 13-04-2016
 * Time: 18:48
 */
class AnalyzePullRequestsCommand extends ApiUsingCommand
{
    protected function configure()
    {
        $this->setName('analyze:pulls')->addArgument('repo', InputArgument::REQUIRED, 'Repository to check pull requests for')
            ->setDescription('Fetches the last 100 closed pull requests for the given repository and stores them in the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Fetching pull request data...');

        fetchPullRequests($input->getArgument('repo'));

        $output->writeln('<info>Done!</info>');
    }
}
