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
        Schema::create('sales_registers', function (Blueprint $table) {
          $table->id();
            $table->string('sales_entry_id')->unique(); // e.g., SLE202508110001
            $table->foreignId('buyer_id')->constrained('buyers')->onDelete('cascade');
            $table->string('product_name');
            $table->string('tea_grade');
            $table->decimal('quantity_kg', 10, 2); // Quantity in kg
            $table->decimal('rate_per_kg', 8, 2); // Rate per kg
            $table->decimal('total_amount', 12, 2); // Total amount
            $table->date('entry_date');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('remarks')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['status', 'entry_date']);
            $table->index(['buyer_id', 'status']);
            $table->index('sales_entry_id');
            $table->index('entry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_registers');
    }
};
