<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHoldersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->integer('coinmarketcap_project_id');
            $table->integer('cmc_rank')->default(0);
            $table->integer('rating')->default(0);
            $table->string('name');
            $table->string('slug', 100)->unique();
            $table->timestamps();
        });

        Schema::create('projects_growth', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('key');
            $table->float('value', 8, 2);
            $table->timestamps();

            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::create('holders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->integer('count');
            $table->date('date');
            $table->timestamps();

            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('holders');
        Schema::dropIfExists('projects_growth');
        Schema::dropIfExists('projects');
    }
}
