<?php

use App\Database\Migration;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class CreateRepositoriesTable extends Migration
{
    public function up()
    {
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
    }

    public function down()
    {
        DB::schema()->drop('repositories');
    }
}
