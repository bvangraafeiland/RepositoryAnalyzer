<?php

use App\Database\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as DB;

class CreatePullRequestsTable extends Migration
{
    public function up()
    {
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
    }

    public function down()
    {
        DB::schema()->drop('pull_requests');
    }
}
