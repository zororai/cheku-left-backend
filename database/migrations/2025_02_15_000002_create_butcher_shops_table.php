<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('butcher_shops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('api_key')->unique()->nullable();
            $table->foreignId('subscription_plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->date('subscription_start_date')->nullable();
            $table->date('subscription_end_date')->nullable();
            $table->enum('subscription_status', ['active', 'expired', 'suspended'])->default('expired');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('butcher_shops');
    }
};
