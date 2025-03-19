<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')
                  ->constrained('clients')
                  ->name('subscriptions_client_id_foreign')
                  ->onDelete('cascade')
                  ->index(); 
            $table->foreignId('dietitian_id')
                  ->constrained('dietitians')
                  ->name('subscriptions_dietitian_id_foreign')
                  ->onDelete('cascade')
                  ->index();
            $table->foreignId('subscription_plan_id')
                  ->constrained('subscription_plans')
                  ->name('subscriptions_subscription_plan_id_foreign')
                  ->onDelete('cascade')
                  ->index(); 
            $table->date('start_date');
            $table->date('end_date'); 
            $table->enum('status', ['active', 'expired', 'canceled']); 
            $table->boolean('auto_renew')->default(false); 
            $table->enum('payment_status', ['paid', 'unpaid'])->default('unpaid');
            $table->timestamps(); 
            $table->softDeletes(); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}