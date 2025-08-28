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
        Schema::table('samples', function (Blueprint $table) {
            $table->unsignedTinyInteger('color_score')
                ->default(0)
                ->after('overall_score'); 

            $table->unsignedTinyInteger('taste_score')
                ->default(0)
                ->after('color_score'); 

            $table->unsignedTinyInteger('strength_score')
                ->default(0)
                ->after('taste_score'); 

            $table->unsignedTinyInteger('briskness_score')
                ->default(0)
                ->after('strength_score'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('samples', function (Blueprint $table) {
            $table->dropColumn(['color_score', 'taste_score', 'strength_score', 'briskness_score']);
        });
    }
};
