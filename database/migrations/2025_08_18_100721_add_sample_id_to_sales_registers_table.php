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
        Schema::table('sales_registers', function (Blueprint $table) {
            $table->foreignId('sample_id')->nullable()->after('id')->constrained('samples')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_registers', function (Blueprint $table) {
              $table->dropForeign(['sample_id']);
            $table->dropColumn('sample_id');
        });
    }
};
