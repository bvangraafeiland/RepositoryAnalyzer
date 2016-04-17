<?php
use App\Commands\AddRepositoryCommand;
use App\Commands\AnalyzePullRequestsCommand;
use App\Commands\BatchExecuteCommand;
use App\Commands\CheckRateLimitCommand;
use App\Commands\CloneRepositoryCommand;
use App\Commands\ProcessProjectsCommand;
use App\Commands\RunAsatCommand;
use App\Commands\SearchRepositoriesCommand;
use App\Commands\TinkerCommand;
use App\GitHubClient;
use Symfony\Component\Console\Application;

define('PROJECT_DIR', __DIR__);

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();
$dotenv->required('REPOSITORIES_DIR');

$db = new Illuminate\Database\Capsule\Manager();
$db->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'github_repos',
    'username'  => 'root',
    'password'  => 'secret',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);
$db->setAsGlobal();
$db->bootEloquent();

GitHubClient::setInstance();

$application = new Application('GitHub repository miner');
$application->addCommands([
    new SearchRepositoriesCommand,
    new CheckRateLimitCommand,
    new BatchExecuteCommand,
    new ProcessProjectsCommand,
    new AddRepositoryCommand,
    new CloneRepositoryCommand,
    new RunAsatCommand,
    new AnalyzePullRequestsCommand,
    new TinkerCommand
]);
