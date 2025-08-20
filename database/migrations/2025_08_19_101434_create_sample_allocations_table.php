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
        Schema::create('sample_allocations', function (Blueprint $table) {
           $table->id();
            $table->foreignId('sample_id')->constrained('samples')->onDelete('cascade');
            $table->foreignId('batch_group_id')->nullable()->constrained('sample_batches')->onDelete('set null');
            $table->string('batch_id')->nullable();
            $table->decimal('allocated_weight', 8, 2)->default(0.01); // Fixed 10gm = 0.01kg
            $table->enum('allocation_type', [
                'batch_testing', 
                'retesting', 
                'quality_check', 
                'additional_evaluation'
            ])->default('batch_testing');
            $table->string('allocation_reason')->nullable();
            $table->timestamp('allocation_date')->default(now());
            $table->foreignId('allocated_by')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['allocated', 'used', 'returned', 'cancelled'])->default('allocated');
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['sample_id', 'allocation_type']);
            $table->index(['batch_group_id', 'status']);
            $table->index(['allocation_date']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_allocations');
    }
};
