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
            $table->timestamp('committed_at');
            $table->unique(['repository_id', 'hash']);
        });

        DB::schema()->create('analysis_tool_result', function (Blueprint $table) {
            $table->integer('analysis_tool_id')->unsigned()->index();
            $table->foreign('analysis_tool_id')->references('id')->on('analysis_tools')->onDelete('cascade');
            $table->integer('result_id')->unsigned()->index();
            $table->foreign('result_id')->references('id')->on('results')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['analysis_tool_id', 'result_id']);
        });
    }

    public function down()
    {
        DB::schema()->drop('analysis_tool_result');
        DB::schema()->drop('results');
    }
}
