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
        Schema::create('teas', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // e.g., Black Tea, Green Tea, White Tea
            $table->string('tea_type'); // e.g., Orthodox, CTC, Specialty
            $table->string('sub_title'); // e.g., Earl Grey, Assam Bold
            $table->string('grade'); // e.g., BP, BOP, PD, Dust, FTGFOP
            $table->text('description')->nullable();
            $table->json('characteristics')->nullable(); // flavor notes, aroma, etc.
            $table->boolean('status')->default(true);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'category']);
            $table->index('category');
            $table->index('tea_type');
            $table->index('grade');
            $table->unique(['category', 'tea_type', 'sub_title', 'grade']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teas');
    }
};
