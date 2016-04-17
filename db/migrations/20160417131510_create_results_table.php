<?php

use App\Database\Migration;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class CreateResultsTable extends Migration
{
    public function up()
    {
        DB::schema()->create('results', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('repository_id')->unsigned()->index();
            $table->foreign('repository_id')->references('id')->on('repositories')->onDelete('cascade');
            $table->string('hash', 40);
            $table->integer('analysis_tool_id')->unsigned()->index();
            $table->foreign('analysis_tool_id')->references('id')->on('analysis_tools')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['repository_id', 'hash', 'analysis_tool_id']);
        });
    }

    public function down()
    {
        DB::schema()->drop('results');
    }
}
