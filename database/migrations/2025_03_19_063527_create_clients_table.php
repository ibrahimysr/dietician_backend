<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('user_id')
                  ->constrained('users') 
                  ->name('clients_user_id_foreign') 
                  ->onDelete('cascade')
                  ->index();
            $table->foreignId('dietitian_id')
                  ->nullable()
                  ->constrained('dietitians') 
                  ->name('clients_dietitian_id_foreign') 
                  ->onDelete('set null')
                  ->index();
            $table->date('birth_date');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->decimal('height', 5, 2);
            $table->decimal('weight', 5, 2);
            $table->enum('activity_level', ['sedentary', 'light', 'moderate', 'active', 'very_active']);
            $table->string('goal', 255);
            $table->text('allergies')->nullable();
            $table->text('preferences')->nullable();
            $table->text('medical_conditions')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clients');
    }
}