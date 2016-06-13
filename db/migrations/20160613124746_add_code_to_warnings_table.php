<?php

use App\Database\Migration;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class AddCodeToWarningsTable extends Migration
{
    public function up()
    {
        DB::schema()->table('warnings', function (Blueprint $table) {
            $table->string('code')->nullable();
        });
    }

    public function down()
    {
        DB::schema()->table('warnings', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
}
