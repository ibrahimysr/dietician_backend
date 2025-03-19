<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiTokensTable extends Migration
{
    public function up()
    {
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->name('api_tokens_user_id_foreign')
                  ->onDelete('cascade')
                  ->index(); 
            $table->string('name', 255); 
            $table->string('token', 255)->unique()->index(); 
            $table->text('abilities'); 
            $table->timestamp('last_used_at')->nullable(); 
            $table->timestamp('expires_at')->nullable(); 
            $table->timestamps(); 
            $table->softDeletes(); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('api_tokens');
    }
}