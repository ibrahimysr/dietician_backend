<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('subscription_id')
                  ->constrained('subscriptions')
                  ->name('payments_subscription_id_foreign')
                  ->onDelete('cascade')
                  ->index(); 
            $table->foreignId('client_id')
                  ->constrained('clients')
                  ->name('payments_client_id_foreign')
                  ->onDelete('cascade')
                  ->index();
            $table->decimal('amount', 10, 2); 
            $table->string('currency', 3)->default('TRY');
            $table->timestamp('payment_date'); 
            $table->string('payment_method', 50); 
            $table->string('transaction_id', 255)->index(); 
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded']); 
            $table->timestamp('refund_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
}