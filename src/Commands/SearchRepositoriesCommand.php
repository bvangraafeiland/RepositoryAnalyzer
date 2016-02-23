<?php
namespace App\Commands;

use App\Repository;
use Symfony\Component\Console\Command\Command;
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
class SearchRepositoriesCommand extends Command
{
    use GithubApi;

    protected function configure()
    {
        $this->setName('search')
            ->setDescription('Search for repositories on GitHub')
            ->addArgument('language', InputArgument::REQUIRED, "Filter repositories based on language")
            ->addArgument('year', InputArgument::REQUIRED, "Search for repositories created in this year")
            ->addOption('stars', null, InputOption::VALUE_REQUIRED, "Minimum amount of stars a repository should have", 200);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lastPush = '2016-01-01';
        $year = $input->getArgument('year');
        $numStars = $input->getOption('stars');
        $lang = $input->getArgument('language');

        $queryString = "language:$lang created:\"$year-01-01 .. $year-12-31\" pushed:>=$lastPush stars:>=$numStars";
        $output->writeln("Search query: <info>'$queryString'</info>");

        // int total_count, bool incomplete_results, array items
        $results = $this->github->searchRepositories($queryString);
        $this->storeRepositories($results);
    }

    protected function storeRepositories(array $items)
    {
        // save to db
        foreach ($items as $item) {
            if (! Repository::query()->find($item['id']))
                Repository::create($item);
        }
    }
}
