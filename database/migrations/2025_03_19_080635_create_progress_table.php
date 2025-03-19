<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgressTable extends Migration
{
    public function up()
    {
        Schema::create('progress', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('client_id')
                  ->constrained('clients')
                  ->name('progress_client_id_foreign')
                  ->onDelete('cascade')
                  ->index(); 
            $table->date('date')->index(); 
            $table->decimal('weight', 5, 2); 
            $table->decimal('waist', 5, 2)->nullable(); 
            $table->decimal('arm', 5, 2)->nullable(); 
            $table->decimal('chest', 5, 2)->nullable(); 
            $table->decimal('hip', 5, 2)->nullable(); 
            $table->decimal('body_fat_percentage', 5, 2)->nullable(); 
            $table->text('notes')->nullable();
            $table->string('photo_url', 255)->nullable(); 
            $table->timestamps();
            $table->softDeletes(); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('progress');
    }
}