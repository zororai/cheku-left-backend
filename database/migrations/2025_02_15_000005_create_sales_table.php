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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('butcher_id')->constrained('butcher_shops')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('device_sale_id')->nullable();
            $table->string('sale_number')->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->string('payment_method')->default('cash');
            $table->dateTime('sale_date');
            $table->dateTime('synced_at')->nullable();
            $table->timestamps();

            $table->index(['butcher_id', 'sale_date']);
            $table->unique(['butcher_id', 'device_sale_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
