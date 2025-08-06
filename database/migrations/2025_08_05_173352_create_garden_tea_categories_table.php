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
       Schema::table('gardens', function (Blueprint $table) {
            // Add support for multiple category filtering
            $table->json('category_filters')->nullable()->after('tea_ids');
            
            // Remove single category fields as we now support multiple
            $table->dropColumn(['selected_category', 'selected_tea_type', 'filtered_grade_codes']);
        });

        // Create a new table to store garden tea category relationships for better querying
        Schema::create('garden_tea_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('garden_id')->constrained('gardens')->onDelete('cascade');
            $table->string('category');
            $table->json('tea_types'); // Array of tea types for this category
            $table->json('grade_codes')->nullable(); // Array of grade codes (optional filter)
            $table->timestamps();
            
            $table->index(['garden_id', 'category']);
            $table->unique(['garden_id', 'category']); // One entry per category per garden
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
 Schema::dropIfExists('garden_tea_categories');
        
        Schema::table('gardens', function (Blueprint $table) {
            $table->dropColumn('category_filters');
            
            // Add back single category fields
            $table->string('selected_category')->nullable();
            $table->string('selected_tea_type')->nullable();
            $table->json('filtered_grade_codes')->nullable();
        });    }
};
