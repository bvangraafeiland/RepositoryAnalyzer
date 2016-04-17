<?php

use App\BuildTool;
use App\Database\Migration;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class CreateBuildToolsTable extends Migration
{
    public function up()
    {
        DB::schema()->create('build_tools', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
        });

        foreach (['grunt', 'gulp', 'maven', 'ant', 'gradle', 'rake', 'tox'] as $tool) {
            BuildTool::create(['name' => $tool]);
        }

        DB::schema()->create('build_tool_repository', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('repository_id')->unsigned()->index();
            $table->foreign('repository_id')->references('id')->on('repositories')->onDelete('cascade');
            $table->integer('build_tool_id')->unsigned()->index();
            $table->foreign('build_tool_id')->references('id')->on('build_tools')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        DB::schema()->drop('buid_tool_repository');
        DB::schema()->drop('buid_tools');
    }
}
