<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Organic Certified, Fair Trade, Export Grade A, dst
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_certifications');
    }
};