<?php
// database/migrations/xxxx_create_categories_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->json('association_categories')->nullable()
                  ->comment('Array of category IDs associated for recommendation');
            $table->unsignedTinyInteger('priority_order')->default(0)
                  ->comment('Lower number = higher priority in recommendation');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};