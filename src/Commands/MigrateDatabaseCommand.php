<?php
namespace RepoFinder\Commands;

use Illuminate\Database\Schema\Blueprint;
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
            $table->integer('id')->unsigned()->unique();
            $table->string('full_name');
            $table->string('html_url');
            $table->mediumInteger('stargazers_count')->unsigned();
            $table->dateTime('created_at');
            $table->dateTime('pushed_at');
            $table->string('language');
            $table->boolean('asat_in_travis')->nullable();
            $table->boolean('asat_in_build_tool')->nullable();
        });

        $output->writeln("Creating tools table");
        DB::schema()->create('analysis_tools', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        $output->writeln("Creating pivot table");
        DB::schema()->create('analysis_tool_repository', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('repository_id')->unsigned()->index();
            $table->foreign('repository_id')->references('id')->on('repositories')->onDelete('cascade');
            $table->integer('analysis_tool_id')->unsigned()->index();
            $table->foreign('analysis_tool_id')->references('id')->on('analysis_tools')->onDelete('cascade');
        });

        $output->writeln("Creating completed jobs table");
        DB::schema()->dropIfExists('completed_jobs');
        DB::schema()->create('completed_jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('analysis_tool_id')->unsigned()->index();
            $table->date('created_from');
            $table->date('created_until');
            $table->smallInteger('last_page')->unsigned();
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
