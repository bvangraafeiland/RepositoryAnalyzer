<?php
namespace App\Commands;

use App\GitHubClient;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 19-02-2016
 * Time: 22:35
 */
abstract class ApiUsingCommand extends Command
{
    /**
     * @var GitHubClient
     */
    protected $github;

    /**
     * @var Client
     */
    protected $travis;

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

        $this->github = new GitHubClient($this->output);

        $this->travis = new Client([
            'base_uri' => 'https://api.travis-ci.org'
        ]);
    }
}
