<?php
namespace App\Commands;

use App\Repository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 19-02-2016
 * Time: 17:13
 */
class SearchRepositoriesCommand extends ApiUsingCommand
{
    protected function configure()
    {
        $this->setName('search')
            ->setDescription('Search for repositories on GitHub')
            ->addArgument('language', InputArgument::REQUIRED, "Filter repositories based on language")
            ->addArgument('year', InputArgument::REQUIRED, "Search for repositories created in this year")
            ->addOption('stars', null, InputOption::VALUE_REQUIRED, "Minimum amount of stars a repository should have", 200)
            ->addOption('just-count', null, InputOption::VALUE_NONE, 'When provided, returns just the number of projects that would be processed');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lastPush = '2016-01-01';
        $year = $input->getArgument('year');
        $numStars = $input->getOption('stars');
        $lang = $input->getArgument('language');

        $queryString = buildSearchQuery($lang, $year, $lastPush, $numStars);
        $output->writeln("Search query: <info>'$queryString'</info>");

        if ($input->getOption('just-count')) {
            $numResults = $this->github->countRepositories($queryString);
            $output->writeln("<comment>$numResults found</comment>");
        }
        else {
            // int total_count, bool incomplete_results, array items
            $results = $this->github->searchRepositories($queryString);
            $this->storeRepositories($results);
        }
    }

    protected function storeRepositories(array $items)
    {
        // save to db
        foreach ($items as $item) {
            Repository::addIfNew($item);
        }
    }
}
