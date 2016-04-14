<?php
namespace App\Commands;

use App\BuildTool;
use Illuminate\Database\Schema\Blueprint;
use App\AnalysisTool;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 19-02-2016
 * Time: 21:26
 */
class MigrateDatabaseCommand extends Command
{
    protected function configure()
    {
        $this->setName('db:migrate')
            ->setDescription('Create the database tables needed to store the repository data');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->dropTables();
        $output->writeln("Creating repos table");

        DB::schema()->create('repositories', function (Blueprint $table) {
            $table->integer('id')->unsigned()->primary();
            $table->string('full_name')->index();
            $table->string('default_branch');
            $table->mediumInteger('stargazers_count')->unsigned();
            $table->boolean('has_issues');
            $table->mediumInteger('open_issues_count')->unsigned()->nullable();
            $table->dateTime('created_at');
            $table->dateTime('pushed_at');
            $table->string('language')->index();
            $table->boolean('uses_asats')->index()->nullable();
            $table->boolean('uses_travis')->index()->nullable();
            $table->boolean('asat_in_travis')->nullable();
            $table->boolean('asat_in_build_tool')->nullable();
        });

        $output->writeln("Creating analysis tools table");
        DB::schema()->create('analysis_tools', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
        });

        foreach (['checkstyle', 'pmd', 'jshint', 'jscs', 'eslint', 'rubocop', 'pylint'] as $tool) {
            AnalysisTool::create(['name' => $tool]);
        }

        $output->writeln("Creating build tools table");
        DB::schema()->create('build_tools', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
        });

        foreach (['grunt', 'gulp', 'maven', 'ant', 'gradle', 'rake', 'tox'] as $tool) {
            BuildTool::create(['name' => $tool]);
        }

        $output->writeln("Creating pull requests table");
        DB::schema()->create('pull_requests', function (Blueprint $table) {
            $table->integer('id')->unsigned()->primary();
            $table->integer('number')->unsigned();
            $table->string('state', 100);
            $table->string('title');
            $table->integer('user_id')->unsigned()->index();
            $table->integer('repository_id')->unsigned()->index();
            $table->foreign('repository_id')->references('id')->on('repositories')->onDelete('cascade');
            $table->timestamps();
            $table->timestamp('merged_at')->nullable();
            $table->timestamp('closed_at')->nullable();
        });

        $output->writeln("Creating pivot tables");
        DB::schema()->create('analysis_tool_repository', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('repository_id')->unsigned()->index();
            $table->foreign('repository_id')->references('id')->on('repositories')->onDelete('cascade');
            $table->integer('analysis_tool_id')->unsigned()->index();
            $table->foreign('analysis_tool_id')->references('id')->on('analysis_tools')->onDelete('cascade');
            $table->boolean('config_file_present')->index();
            $table->boolean('in_dev_dependencies')->index();
            $table->boolean('in_build_tool')->index();
            $table->timestamps();
        });
        DB::schema()->create('build_tool_repository', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('repository_id')->unsigned()->index();
            $table->foreign('repository_id')->references('id')->on('repositories')->onDelete('cascade');
            $table->integer('build_tool_id')->unsigned()->index();
            $table->foreign('build_tool_id')->references('id')->on('build_tools')->onDelete('cascade');
            $table->timestamps();
        });
    }

    protected function dropTables()
    {
        DB::statement('SET foreign_key_checks = 0');
        DB::schema()->dropIfExists('repositories');
        DB::schema()->dropIfExists('analysis_tools');
        DB::schema()->dropIfExists('analysis_tool_repository');
        DB::statement('SET foreign_key_checks = 1');
    }
}
