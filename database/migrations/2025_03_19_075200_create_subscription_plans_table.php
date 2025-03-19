<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionPlansTable extends Migration
{
    public function up()
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('dietitian_id')
                  ->constrained('dietitians')
                  ->name('subscription_plans_dietitian_id_foreign')
                  ->onDelete('cascade')
                  ->index();
            $table->string('name', 255); 
            $table->text('description'); 
            $table->integer('duration'); 
            $table->decimal('price', 10, 2); 
            $table->text('features'); 
            $table->enum('status', ['active', 'inactive']); 
            $table->timestamps(); 
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscription_plans');
    }
}