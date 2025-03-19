<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up()
    {
        Schema::create('diet_plan_meals', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('diet_plan_id')
                  ->constrained('diet_plans')
                  ->name('diet_plan_meals_diet_plan_id_foreign')
                  ->onDelete('cascade')
                  ->index(); 
            $table->integer('day_number'); 
            $table->enum('meal_type', ['breakfast', 'lunch', 'dinner', 'snack']); 
            $table->text('description'); 
            $table->integer('calories');
            $table->decimal('protein', 5, 2); 
            $table->decimal('fat', 5, 2);
            $table->decimal('carbs', 5, 2); 
            $table->string('photo_url', 255)->nullable(); 
            $table->timestamps(); 
            $table->softDeletes(); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('diet_plan_meals');
    }
};