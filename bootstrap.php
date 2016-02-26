<?php
use App\Commands\AddRepositoryCommand;
use App\Commands\AutoCollectCommand;
use App\Commands\CheckASATUsageCommand;
use App\Commands\CheckRateLimitCommand;
use App\Commands\CheckTravisUsageCommand;
use App\Commands\CountSearchResultsCommand;
use App\Commands\MigrateDatabaseCommand;
use App\Commands\SearchRepositoriesCommand;
use App\Commands\TinkerCommand;
use Symfony\Component\Console\Application;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

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

$application = new Application('GitHub repository miner');
$application->addCommands([
    new SearchRepositoriesCommand,
    new MigrateDatabaseCommand,
    new CheckRateLimitCommand,
    new AutoCollectCommand,
    new CheckASATUsageCommand,
    new CheckTravisUsageCommand,
    new CountSearchResultsCommand,
    new AddRepositoryCommand,
    new TinkerCommand
]);
