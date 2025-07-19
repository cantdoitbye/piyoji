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
        Schema::create('samples', function (Blueprint $table) {
        $table->id();
            
            // Basic sample information (Module 2.1)
            $table->string('sample_id')->unique();
            $table->string('sample_name');
            $table->foreignId('seller_id')->constrained('sellers')->onDelete('cascade');
            $table->string('batch_id');
            $table->decimal('sample_weight', 8, 2)->nullable();
            $table->date('arrival_date');
            $table->foreignId('received_by')->constrained('users')->onDelete('cascade');
            $table->enum('status', [
                'received', 
                'pending_evaluation', 
                'evaluated', 
                'approved', 
                'rejected', 
                'assigned_to_buyers'
            ])->default('received');
            $table->text('remarks')->nullable();
            
            // Evaluation information (Module 2.2)
            $table->decimal('aroma_score', 3, 1)->nullable();
            $table->decimal('liquor_score', 3, 1)->nullable();
            $table->decimal('appearance_score', 3, 1)->nullable();
            $table->decimal('overall_score', 3, 1)->nullable();
            $table->text('evaluation_comments')->nullable();
            $table->enum('evaluation_status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('evaluated_at')->nullable();
            
            // Audit fields
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better performance
            $table->index(['seller_id', 'status']);
            $table->index(['status', 'evaluation_status']);
            $table->index(['overall_score']);
            $table->index(['arrival_date']);
            $table->index('sample_id');
            $table->index(['evaluation_status', 'evaluated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('samples');
    }
};
