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
               $table->decimal('catalog_weight', 8, 2)->default(0)->after('sample_weight');
            $table->decimal('allocated_weight', 8, 2)->default(0)->after('catalog_weight');
            $table->decimal('available_weight', 8, 2)->default(0)->after('allocated_weight');
            $table->integer('allocation_count')->default(0)->after('available_weight');
            $table->boolean('has_sufficient_weight')->default(true)->after('allocation_count');
            
            // Add index for better performance
            $table->index(['has_sufficient_weight', 'available_weight']);
       
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('samples', function (Blueprint $table) {
              $table->dropIndex(['has_sufficient_weight', 'available_weight']);
            $table->dropColumn([
                'catalog_weight',
                'allocated_weight', 
                'available_weight',
                'allocation_count',
                'has_sufficient_weight'
            ]);
        });
    }
};
