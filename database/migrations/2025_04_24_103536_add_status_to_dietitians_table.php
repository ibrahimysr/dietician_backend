<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  
    public function up(): void
    {
        Schema::table('dietitians', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('is_active');
            $table->boolean('is_active')->default(false)->change();
            $table->text('rejection_reason')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('dietitians', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('rejection_reason');
            $table->boolean('is_active')->default(true)->change(); 
        });
    }
};