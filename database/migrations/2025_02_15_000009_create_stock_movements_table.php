<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('stock_sessions')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('product_name');
            $table->integer('opening_grams');
            $table->integer('sold_grams')->default(0);
            $table->integer('closing_grams')->nullable();
            $table->integer('expected_closing_grams')->nullable();
            $table->integer('variance_grams')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
