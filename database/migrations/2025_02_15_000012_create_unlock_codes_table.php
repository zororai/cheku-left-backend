<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unlock_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->foreignId('butcher_id')->nullable()->constrained('butcher_shops')->nullOnDelete();
            $table->integer('additional_payments');
            $table->boolean('is_used')->default(false);
            $table->dateTime('used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unlock_codes');
    }
};
