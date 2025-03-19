<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFoodsTable extends Migration
{
    public function up()
    {
        Schema::create('foods', function (Blueprint $table) {
            $table->id(); 
            $table->string('name', 255)->index();
            $table->string('category', 255); 
            $table->decimal('serving_size', 5, 2); 
            $table->integer('calories'); 
            $table->decimal('protein', 5, 2); 
            $table->decimal('fat', 5, 2); 
            $table->decimal('carbs', 5, 2); 
            $table->decimal('fiber', 5, 2)->nullable(); 
            $table->decimal('sugar', 5, 2)->nullable(); 
            $table->boolean('is_custom')->default(false); 
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->name('foods_created_by_foreign')
                  ->onDelete('set null')
                  ->index(); 
            $table->timestamps();
            $table->softDeletes(); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('foods');
    }
}