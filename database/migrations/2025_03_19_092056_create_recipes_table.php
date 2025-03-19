<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecipesTable extends Migration
{
    public function up()
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('dietitian_id')
                  ->constrained('dietitians')
                  ->name('recipes_dietitian_id_foreign')
                  ->onDelete('cascade')
                  ->index(); 
            $table->string('title', 255)->index(); 
            $table->text('description'); 
            $table->text('ingredients');
            $table->text('instructions'); 
            $table->integer('prep_time'); 
            $table->integer('cook_time'); 
            $table->integer('servings'); 
            $table->integer('calories'); 
            $table->decimal('protein', 5, 2); 
            $table->decimal('fat', 5, 2); 
            $table->decimal('carbs', 5, 2); 
            $table->text('tags')->nullable(); 
            $table->string('photo_url', 255)->nullable(); 
            $table->boolean('is_public')->default(false); 
            $table->timestamps(); 
            $table->softDeletes(); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('recipes');
    }
}