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
             // Add new fields for batch management
                         $table->decimal('sample_weight', 8, 2)->nullable()->after('batch_id');
            $table->integer('number_of_samples')->default(1)->after('sample_weight');
            $table->decimal('weight_per_sample', 8, 2)->nullable()->after('number_of_samples');
            $table->foreignId('batch_group_id')->nullable()->constrained('sample_batches')->onDelete('set null')->after('batch_id');
            
            // Make batch_id nullable since it will be auto-generated during batching
            $table->string('batch_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('samples', function (Blueprint $table) {
                 $table->dropForeign(['batch_group_id']);
            $table->dropColumn(['number_of_samples', 'weight_per_sample', 'batch_group_id']);
            $table->string('batch_id')->nullable(false)->change();
       
        });
    }
};
