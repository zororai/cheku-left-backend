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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('butcher_id')->nullable()->after('id')->constrained('butcher_shops')->nullOnDelete();
            $table->string('username')->unique()->nullable()->after('email');
            $table->enum('role', ['super_admin', 'owner', 'manager', 'cashier'])->default('cashier')->after('password');
            $table->boolean('is_active')->default(true)->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['butcher_id']);
            $table->dropColumn(['butcher_id', 'username', 'role', 'is_active']);
        });
    }
};
