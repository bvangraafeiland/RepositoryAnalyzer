<?php

use App\Database\Migration;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class AddPullRequestCountToRepositoriesTable extends Migration
{
    public function up()
    {
        DB::schema()->table('repositories', function (Blueprint $table) {
            $table->integer('pull_request_count')->unsigned()->nullable();
        });
    }

    public function down()
    {
        DB::schema()->table('repositories', function (Blueprint $table) {
            $table->dropColumn('pull_request_count');
        });
    }
}
