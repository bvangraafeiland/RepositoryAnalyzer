<?php
namespace RepoFinder\Commands;

use RepoFinder\Repository;
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
class SearchRepositoriesCommand extends GithubApiCommand
{
    protected function configure()
    {
        $this->setName('search:repositories')
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
        $result = $this->github->searchRepositories($queryString);

        $numResults = $result['total_count'];
        $output->writeln("<comment>$numResults found.</comment>");

        if ($numResults > 1000 || $result['incomplete_results']) {
            $output->writeln('<error>Too many or incomplete results!</error>');
            exit(1);
        };

        $this->handleResult($result);

        $this->github->getAllPages([$this, 'handleResult']);
    }

    public function handleResult(array $result)
    {
        // save to db
        foreach ($result['items'] as $item) {
            if (! Repository::query()->find($item['id']))
                Repository::create($item);
        }
    }
}
