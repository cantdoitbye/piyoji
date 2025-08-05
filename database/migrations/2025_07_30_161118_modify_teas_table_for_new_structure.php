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
         Schema::table('teas', function (Blueprint $table) {
            // Add new fields with proper naming
            $table->string('tea_type_id')->after('category');
            $table->string('sub_tea_type_id')->after('tea_type_id');
            $table->string('category_id')->after('sub_tea_type_id');
            $table->string('grade_code')->after('category_id');
            
            // Keep old fields temporarily for data migration
            // Will be removed in a separate migration after data migration
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('teas', function (Blueprint $table) {
            $table->dropColumn(['tea_type_id', 'sub_tea_type_id', 'category_id', 'grade_code']);
        });
    }
};
