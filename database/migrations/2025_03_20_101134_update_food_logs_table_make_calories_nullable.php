<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFoodLogsTableMakeCaloriesNullable extends Migration
{
    public function up()
    {
        Schema::table('food_logs', function (Blueprint $table) {
            $table->integer('calories')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('food_logs', function (Blueprint $table) {
            $table->integer('calories')->nullable(false)->change();
        });
    }
}