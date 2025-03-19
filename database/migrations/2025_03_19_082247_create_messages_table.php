<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')
                  ->constrained('users')
                  ->name('messages_sender_id_foreign')
                  ->onDelete('cascade')
                  ->index();
            $table->foreignId('receiver_id')
                  ->constrained('users')
                  ->name('messages_receiver_id_foreign')
                  ->onDelete('cascade')
                  ->index();
            $table->text('message'); 
            $table->string('attachment_url', 255)->nullable(); 
            $table->timestamp('sent_at'); 
            $table->timestamp('read_at')->nullable(); 
            $table->boolean('is_delivered')->default(false); 
            $table->timestamps(); 
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('messages');
    }
}