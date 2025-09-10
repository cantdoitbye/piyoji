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
        Schema::create('sample_testing_results', function (Blueprint $table) {
           $table->id();
            $table->foreignId('testing_session_id')->constrained('batch_testing_sessions')->onDelete('cascade');
            $table->foreignId('sample_id')->constrained('samples')->onDelete('cascade');
            $table->integer('sample_sequence'); // Position in batch (1, 2, 3...)
            $table->json('tester_results'); // Store all testers' scores for this sample
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamp('testing_completed_at')->nullable();
            $table->foreignId('tested_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('sample_remarks')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['testing_session_id', 'sample_sequence']);
            $table->index(['sample_id', 'status']);
            $table->unique(['testing_session_id', 'sample_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_testing_results');
    }
};
