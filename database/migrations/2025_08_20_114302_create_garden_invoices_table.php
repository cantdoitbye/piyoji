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
        Schema::create('garden_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('garden_id')->constrained()->onDelete('cascade');
            $table->string('mark_name'); // Garden name at time of invoice creation
            $table->string('invoice_prefix', 10);
            $table->string('invoice_number', 50)->unique();
            $table->integer('bags_packages')->default(0);
            $table->decimal('total_invoice_weight', 10, 3)->default(0); // Auto calculated from samples
            $table->date('packaging_date');
            $table->enum('status', ['draft', 'finalized', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Indexes
            $table->index(['garden_id', 'status']);
            $table->index('invoice_number');
            $table->index('packaging_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garden_invoices');
    }
};
