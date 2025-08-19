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
        Schema::create('sample_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('original_sample_id')->constrained('samples')->onDelete('cascade');
            $table->foreignId('new_sample_id')->constrained('samples')->onDelete('cascade');
            $table->foreignId('from_batch_group_id')->nullable()->constrained('sample_batches')->onDelete('set null');
            $table->foreignId('to_batch_group_id')->nullable()->constrained('sample_batches')->onDelete('set null');
            $table->string('from_batch_id')->nullable();
            $table->string('to_batch_id')->nullable();
            $table->decimal('transferred_weight', 8, 2);
            $table->integer('transferred_quantity')->default(1);
            $table->decimal('remaining_weight', 8, 2)->nullable();
            $table->integer('remaining_quantity')->nullable();
            $table->enum('transfer_reason', ['retesting', 'quality_check', 'additional_evaluation', 'other'])->default('retesting');
            $table->text('transfer_remarks')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
            $table->timestamp('transfer_date')->default(now());
            $table->foreignId('transferred_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['original_sample_id', 'status']);
            $table->index(['transfer_date']);
            $table->index(['transfer_reason']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_transfers');
    }
};
