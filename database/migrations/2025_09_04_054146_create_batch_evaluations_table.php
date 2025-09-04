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
        Schema::create('batch_evaluations', function (Blueprint $table) {
          $table->id();
            $table->foreignId('batch_group_id')->constrained('sample_batches')->onDelete('cascade');
            $table->string('batch_id');
            $table->date('evaluation_date');
            $table->integer('total_samples')->default(0);
            $table->enum('evaluation_status', [
                'pending', 
                'in_progress', 
                'completed', 
                'cancelled'
            ])->default('pending');
            $table->text('overall_remarks')->nullable();
            $table->foreignId('evaluation_started_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('evaluation_completed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('evaluation_started_at')->nullable();
            $table->timestamp('evaluation_completed_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes
            $table->index(['batch_group_id', 'evaluation_status']);
            $table->index(['evaluation_date']);
            $table->index(['evaluation_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_evaluations');
    }
};
