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
        Schema::create('sample_buyer_assignments', function (Blueprint $table) {
             $table->id();
            $table->foreignId('sample_id')->constrained('samples')->onDelete('cascade');
            $table->foreignId('buyer_id')->constrained('buyers')->onDelete('cascade');
            $table->text('assignment_remarks')->nullable();
            $table->enum('dispatch_status', [
                'awaiting_dispatch',
                'dispatched',
                'delivered',
                'feedback_received'
            ])->default('awaiting_dispatch');
            $table->datetime('assigned_at');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');
            $table->datetime('dispatched_at')->nullable();
            $table->string('tracking_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['sample_id', 'dispatch_status']);
            $table->index(['buyer_id', 'dispatch_status']);
            $table->index('assigned_at');
            
            // Unique constraint to prevent duplicate assignments
            $table->unique(['sample_id', 'buyer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_buyer_assignments');
    }
};
