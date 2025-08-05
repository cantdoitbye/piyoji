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
            $table->string('selected_category')->nullable()->after('tea_ids');
            $table->string('selected_tea_type')->nullable()->after('selected_category');
            $table->json('filtered_grade_codes')->nullable()->after('selected_tea_type');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
           Schema::table('gardens', function (Blueprint $table) {
            $table->dropColumn(['selected_category', 'selected_tea_type', 'filtered_grade_codes']);
        });
    }
};
