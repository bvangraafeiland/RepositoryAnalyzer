<?php
namespace App\Commands;

use App\Exceptions\TooManyResultsException;
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

        if ($input->getOption('just-count')) {
            $numResults = $this->github->searchRepositories(buildSearchQuery($lang, $year, $lastPush, $numStars), true);
            $output->writeln("<comment>$numResults found</comment>");
        }
        else {
            $results = $this->getAllRepositories($lang, $year, $lastPush, $numStars);
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

    /**
     * @param $lang
     * @param $year
     * @param $lastPush
     * @param $numStars
     *
     * @return array
     * @throws TooManyResultsException
     * @throws \App\Exceptions\GitHubException
     */
    protected function getAllRepositories($lang, $year, $lastPush, $numStars)
    {
        try {
            $query = buildSearchQuery($lang, $year, $lastPush, $numStars);
            return $this->github->searchRepositories($query);
        } catch (TooManyResultsException $e) {
            $this->output->writeln("<error>Too many search results!</error>");
            $this->output->writeln("<comment>Splitting in two separate searches...</comment>");

            $firstQuery = buildSearchQuery($lang, $year, $lastPush, $numStars, '01-01', '06-30');
            $firstResults = $this->github->searchRepositories($firstQuery);

            $secondQuery = buildSearchQuery($lang, $year, $lastPush, $numStars, '07-01', '12-31');
            $secondResults = $this->github->searchRepositories($secondQuery);

            $results = array_merge($firstResults, $secondResults);

            return $results;
        }
    }
}
