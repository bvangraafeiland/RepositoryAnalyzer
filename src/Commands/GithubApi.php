<?php
namespace App\Commands;

use App\GitHubClient;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 19-02-2016
 * Time: 22:35
 */
trait GithubApi
{
    /**
     * @var GitHubClient
     */
    protected $github;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->initGithub();
    }

    public function initGithub()
    {
        $this->github = new GitHubClient($this->output);
    }
}
