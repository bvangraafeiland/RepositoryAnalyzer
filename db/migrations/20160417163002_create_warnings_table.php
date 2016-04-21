<?php

use App\Database\Migration;
use App\WarningClassification;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class CreateWarningsTable extends Migration
{
    protected $gcdCategories = [
        'Functional Defects' => [
            'Check',
            'Concurrency',
            'Error Handling',
            'Interface',
            'Logic',
            'Migration',
            'Resource',
        ],
        'Maintainability Defects' => [
            'Best Practices',
            'Code Structure',
            'Documentation Conventions',
            'Metric',
            'Naming Conventions',
            'Object Oriented Design',
            'Redundancies',
            'Simplifications',
            'Style Conventions',
        ],
        'Other' => [
            'Regular Expressions',
            'Tool Specific',
        ]
    ];

    public function up()
    {
        $classificationCategories = array_keys($this->gcdCategories);
        DB::schema()->create('warning_classifications', function (Blueprint $table) use ($classificationCategories) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->enum('category', $classificationCategories);
        });

        foreach ($this->gcdCategories as $category => $classifications) {
            foreach ($classifications as $name) {
                WarningClassification::create(compact('name', 'category'));
            }
        }

        DB::schema()->create('warnings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('file');
            $table->integer('line');
            $table->integer('column')->nullable();
            $table->text('message');
            $table->string('rule');
            $table->integer('classification_id')->unsigned()->index()->nullable();
            $table->foreign('classification_id')->references('id')->on('warning_classifications')->onDelete('cascade');
            $table->integer('result_id')->unsigned()->index();
            $table->foreign('result_id')->references('id')->on('results')->onDelete('cascade');
            $table->integer('analysis_tool_id')->unsigned()->index();
            $table->foreign('analysis_tool_id')->references('id')->on('analysis_tools')->onDelete('cascade');
        });
    }

    public function down()
    {
        DB::schema()->drop('warnings');
        DB::schema()->drop('warning_classifications');
    }
}
