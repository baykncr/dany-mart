<?php
// database/migrations/xxxx_create_orders_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete();
            $table->string('order_number')->unique();
            $table->unsignedBigInteger('total_amount');
            $table->enum('payment_method', ['cash', 'qris'])->default('cash');
            $table->unsignedBigInteger('payment_amount');
            $table->unsignedBigInteger('change_amount')->default(0)
                  ->comment('Kembalian = payment_amount - total_amount');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};