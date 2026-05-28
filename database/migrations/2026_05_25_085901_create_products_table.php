<?php
// database/migrations/xxxx_create_products_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                  ->constrained('categories')
                  ->restrictOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('unit')->default('pcs')
                  ->comment('e.g. pcs, botol, kg, dus');
            $table->unsignedBigInteger('purchase_price')->default(0);
            $table->unsignedBigInteger('selling_price');
            $table->unsignedInteger('stock')->default(0);
            $table->string('photo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};