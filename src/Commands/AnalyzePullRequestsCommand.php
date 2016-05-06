<?php
namespace App\Commands;

use App\GitHubClient;
use App\PullRequest;
use App\Repository;
use Carbon\Carbon;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        $this->setName('analyze:pulls')
            ->addArgument('language', InputArgument::OPTIONAL)
            ->addOption('repo', null, InputOption::VALUE_REQUIRED, 'Repository to check pull requests for')
            ->addOption('state', null, InputOption::VALUE_REQUIRED, 'PR state (open, closed, all)')
            ->addOption('count', null, InputOption::VALUE_NONE, 'Count total number of pull requests instead of fetching their data')
            ->setDescription('Fetches the last 100 closed pull requests for the given repositories and stores them in the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Fetching pull request data...');
        $count = $input->getOption('count');

        $query = Repository::query();

        if ($count) {
            $query->whereNull('pull_request_count');
        }

        if ($language = $input->getArgument('language')) {
            $query->where(compact('language'));
        }

        if ($repoName = $input->getOption('repo')) {
            $query->where('full_name', $repoName);
        }

        $repos = $query->get();

        foreach ($repos as $repo) {
            $output->writeln("Getting data for <comment>$repo->full_name</comment>...");
            if ($count) {
                $this->countPullRequests($repo);
            }
            else {
                $this->fetchPullRequests($repo);
            }
        }

        $output->writeln('<info>Done!</info>');
    }

    protected function fetchPullRequests(Repository $repo, $state = 'closed') {
        $existingPullRequestIds = $repo->pullRequests()->lists('id');
        $pulls = array_filter(GitHubClient::getInstance()->getPullRequests($repo->full_name, $state), function ($attributes) use ($existingPullRequestIds) {
            return !$existingPullRequestIds->contains($attributes['id']);
        });

        foreach ($pulls as $apiAttributes) {
            $timestamps = collect(['created_at', 'updated_at', 'closed_at', 'merged_at'])->flatMap(function ($column) use ($apiAttributes) {
                return [$column => array_get($apiAttributes, $column) ? new Carbon($apiAttributes[$column]) : null];
            })->toArray();

            $attributes = array_only($apiAttributes, ['id', 'number', 'state', 'title']) + ['user_id' => $apiAttributes['user']['id'], 'repository_id' => $repo->id] + $timestamps;

            $attributes['title'] = str_limit($attributes['title'], 255, '');

            PullRequest::insert($attributes);
        }
    }

    protected function countPullRequests(Repository $repository)
    {
        $repository->pull_request_count = GitHubClient::getInstance()->countPullRequests($repository->full_name);
        $repository->save();
    }
}
