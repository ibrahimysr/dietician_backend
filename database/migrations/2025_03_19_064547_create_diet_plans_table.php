<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDietPlansTable extends Migration
{
    public function up()
    {
        Schema::create('diet_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')
                  ->constrained('clients')
                  ->name('diet_plans_client_id_foreign')
                  ->onDelete('cascade')
                  ->index();
            $table->foreignId('dietitian_id')
                  ->constrained('dietitians')
                  ->name('diet_plans_dietitian_id_foreign')
                  ->onDelete('cascade')
                  ->index(); 
            $table->string('title', 255); 
            $table->date('start_date'); 
            $table->date('end_date')->nullable(); 
            $table->integer('daily_calories'); 
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'completed', 'paused']); 
            $table->boolean('is_ongoing')->default(false); 
            $table->timestamps();
            $table->softDeletes(); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('diet_plans');
    }
}