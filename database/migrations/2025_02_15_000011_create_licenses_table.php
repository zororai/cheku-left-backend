<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('butcher_id')->unique()->constrained('butcher_shops')->cascadeOnDelete();
            $table->string('plan', 50)->default('free');
            $table->enum('status', ['active', 'locked', 'expired'])->default('active');
            $table->integer('payment_count')->default(0);
            $table->integer('payment_limit')->default(100);
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
