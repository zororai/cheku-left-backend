<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('butcher_id')->constrained('butcher_shops')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('device_id')->unique();
            $table->string('device_name')->nullable();
            $table->string('device_model')->nullable();
            $table->string('os_version', 100)->nullable();
            $table->string('app_version', 50)->nullable();
            $table->dateTime('last_active_at')->nullable();
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
