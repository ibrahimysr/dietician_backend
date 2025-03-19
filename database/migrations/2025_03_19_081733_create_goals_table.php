<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoalsTable extends Migration
{
    public function up()
    {
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')
                  ->constrained('clients')
                  ->name('goals_client_id_foreign')
                  ->onDelete('cascade')
                  ->index(); 
            $table->foreignId('dietitian_id')
                  ->constrained('dietitians')
                  ->name('goals_dietitian_id_foreign')
                  ->onDelete('cascade')
                  ->index(); 
            $table->string('title', 255);
            $table->text('description'); 
            $table->decimal('target_value', 10, 2)->nullable();
            $table->decimal('current_value', 10, 2)->nullable(); 
            $table->string('unit', 20)->nullable();
            $table->enum('category', ['weight', 'measurement', 'nutrition', 'habit', 'other']); 
            $table->date('start_date'); 
            $table->date('target_date'); 
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'failed']); 
            $table->enum('priority', ['low', 'medium', 'high']);
            $table->decimal('progress_percentage', 5, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('goals');
    }
}