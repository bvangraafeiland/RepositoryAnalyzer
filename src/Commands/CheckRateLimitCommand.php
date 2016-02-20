<?php
namespace RepoFinder\Commands;

use Carbon\Carbon;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 19-02-2016
 * Time: 22:33
 */
class CheckRateLimitCommand extends GithubApiCommand
{
    protected function configure()
    {
        $this->setName('check:ratelimit')
            ->setDescription('Check the number of requests remaining and when the limit resets');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $response = $this->github->getRateLimits();

        $this->displayInfo($response, 'core');
        $this->displayInfo($response, 'search');
    }

    protected function displayInfo($response, $category)
    {
        $this->output->writeln('<comment>' . ucfirst($category) . ' API:</comment>');
        $this->output->write('<info>' . $response[$category]['remaining'] . '/' . $response[$category]['limit'] . '</info> requests, ');
        $this->output->writeln('resetting <info>' . Carbon::createFromTimestamp($response[$category]['reset'])->diffForHumans() . '</info>');
    }
}
