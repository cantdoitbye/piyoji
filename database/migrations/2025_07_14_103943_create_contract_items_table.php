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
        Schema::create('contract_items', function (Blueprint $table) {
           $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->onDelete('cascade');
            $table->string('tea_grade');
            $table->string('tea_grade_description')->nullable();
            $table->decimal('price_per_kg', 10, 2);
            $table->string('currency', 3)->default('INR');
            $table->decimal('minimum_quantity', 10, 2)->nullable();
            $table->decimal('maximum_quantity', 10, 2)->nullable();
            $table->text('quality_parameters')->nullable();
            $table->text('special_terms')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['contract_id', 'is_active']);
            $table->index('tea_grade');
            $table->unique(['contract_id', 'tea_grade']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_items');
    }
};
