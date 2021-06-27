<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->index(['rating']);
        });

        Schema::table('holders', function (Blueprint $table) {
            $table->index(['project_id','date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['rating']);
        });

        Schema::table('holders', function (Blueprint $table) {
            $table->dropIndex(['date']);
        });
    }
}
