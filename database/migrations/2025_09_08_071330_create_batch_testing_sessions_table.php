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
        Schema::create('batch_testing_sessions', function (Blueprint $table) {
             $table->id();
            $table->foreignId('batch_group_id')->constrained('sample_batches')->onDelete('cascade');
            $table->string('batch_id');
            $table->json('testers'); // Store tester information as JSON
            $table->integer('total_samples');
            $table->integer('current_sample_index')->default(0);
            $table->enum('status', ['initiated', 'in_progress', 'completed'])->default('initiated');
            $table->timestamp('session_started_at')->nullable();
            $table->timestamp('session_completed_at')->nullable();
            $table->foreignId('initiated_by')->constrained('users')->onDelete('cascade');
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['batch_group_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_testing_sessions');
    }
};
