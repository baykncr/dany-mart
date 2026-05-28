<?php
// database/migrations/xxxx_create_product_stock_histories_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_stock_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnDelete();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete();
            $table->integer('added_stock')
                  ->comment('Positif = penambahan, Negatif = pengurangan (penjualan)');
            $table->unsignedInteger('current_stock')
                  ->comment('Stok setelah perubahan');
            $table->string('type')->default('manual')
                  ->comment('manual | sale | adjustment');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_stock_histories');
    }
};