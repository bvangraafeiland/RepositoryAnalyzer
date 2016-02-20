<?php
use RepoFinder\Commands\AutoCollectCommand;
use RepoFinder\Commands\CheckRateLimitCommand;
use RepoFinder\Commands\MigrateDatabaseCommand;
use RepoFinder\Commands\SearchRepositoriesCommand;
use Symfony\Component\Console\Application;

require_once __DIR__ . '/vendor/autoload.php';

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
    new AutoCollectCommand
]);
