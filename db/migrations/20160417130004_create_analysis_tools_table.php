<?php

use App\AnalysisTool;
use App\Database\Migration;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class CreateAnalysisToolsTable extends Migration
{
    public function up()
    {
        DB::schema()->create('analysis_tools', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
        });

        foreach (['checkstyle', 'pmd', 'jshint', 'jscs', 'eslint', 'rubocop', 'pylint'] as $tool) {
            AnalysisTool::create(['name' => $tool]);
        }

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
    }

    public function down()
    {
        DB::schema()->drop('analysis_tool_repository');
        DB::schema()->drop('analysis_tools');
    }
}
