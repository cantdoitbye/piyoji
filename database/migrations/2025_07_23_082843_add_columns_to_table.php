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
        Schema::table('buyers', function (Blueprint $table) {
            $table->json('poc_ids')->nullable()->after('status');
        });

        Schema::table('sellers', function (Blueprint $table) {
            $table->json('poc_ids')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('table', function (Blueprint $table) {
              Schema::table('buyers', function (Blueprint $table) {
            $table->dropColumn('poc_ids');
        });

        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn('poc_ids');
        });
        });
    }
};
