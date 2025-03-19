<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDietitiansTable extends Migration
{
    public function up()
    {
        Schema::create('dietitians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->index(); 
            $table->string('specialty', 255); 
            $table->text('bio'); 
            $table->decimal('hourly_rate', 10, 2)->nullable(); 
            $table->integer('experience_years')->nullable(); 
            $table->boolean('is_active')->default(true); 
            $table->timestamps(); 
            $table->softDeletes(); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('dietitians');
    }
}