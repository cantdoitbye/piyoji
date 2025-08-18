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
        Schema::create('sample_batches', function (Blueprint $table) {
           $table->id();
            $table->string('batch_number')->unique(); // e.g., BATCH20250811001
            $table->date('batch_date'); // Date for which batch is created
            $table->integer('batch_sequence'); // Sequence number for the day (1, 2, 3...)
            $table->integer('total_samples')->default(0); // Total samples in this batch
            $table->integer('max_samples')->default(48); // Maximum samples allowed (48)
            $table->enum('status', ['open', 'full', 'processing', 'completed'])->default('open');
            $table->text('remarks')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['batch_date', 'status']);
            $table->index(['status']);
            $table->index('batch_number');
            $table->unique(['batch_date', 'batch_sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_batches');
    }
};
