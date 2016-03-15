<?php
use App\Commands\AddRepositoryCommand;
use App\Commands\AutoCollectCommand;
use App\Commands\CloneRepositoryCommand;
use App\Commands\ProcessProjectsCommand;
use App\Commands\CheckRateLimitCommand;
use App\Commands\MigrateDatabaseCommand;
use App\Commands\SearchRepositoriesCommand;
use App\Commands\TinkerCommand;
use Symfony\Component\Console\Application;

define('PROJECT_DIR', __DIR__);

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
    new ProcessProjectsCommand,
    new AddRepositoryCommand,
    new CloneRepositoryCommand,
    new TinkerCommand
]);
