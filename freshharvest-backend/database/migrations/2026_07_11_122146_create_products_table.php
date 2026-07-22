<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // supplier
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('unit')->default('kg'); // kg, buah, ikat, gram, liter, dst
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('min_order_qty')->default(1);
            $table->enum('export_grade', ['A', 'B', 'C'])->nullable();
            $table->string('origin_region')->nullable();
            $table->decimal('rating_avg', 2, 1)->default(0);
            $table->unsignedInteger('views_count')->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};