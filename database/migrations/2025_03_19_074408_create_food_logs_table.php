<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFoodLogsTable extends Migration
{
    public function up()
    {
        Schema::create('food_logs', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('client_id')
                  ->constrained('clients')
                  ->name('food_logs_client_id_foreign')
                  ->onDelete('cascade')
                  ->index(); 
            $table->foreignId('food_id')
                  ->nullable()
                  ->constrained('foods')
                  ->name('food_logs_food_id_foreign')
                  ->onDelete('set null')
                  ->index(); 
            $table->date('date')->index(); 
            $table->enum('meal_type', ['breakfast', 'lunch', 'dinner', 'snack']); 
            $table->text('food_description')->nullable(); 
            $table->decimal('quantity', 5, 2)->default(1); 
            $table->integer('calories');
            $table->decimal('protein', 5, 2)->nullable(); 
            $table->decimal('fat', 5, 2)->nullable(); 
            $table->decimal('carbs', 5, 2)->nullable(); 
            $table->string('photo_url', 255)->nullable(); 
            $table->timestamp('logged_at'); 
            $table->timestamps(); 
            $table->softDeletes(); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('food_logs');
    }
}