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
        Schema::create('garden_invoice_samples', function (Blueprint $table) {
           $table->id();
            $table->foreignId('garden_invoice_id')->constrained()->onDelete('cascade');
            $table->string('sample_code')->nullable(); // Optional sample identification
            $table->decimal('sample_weight', 8, 3); // Individual sample weight
            $table->integer('number_of_sets')->default(1); // Number of sets for this sample
            $table->decimal('total_sample_weight', 10, 3); // Auto calculated: sample_weight * number_of_sets
            $table->text('sample_notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('garden_invoice_id');
            $table->index('sample_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garden_invoice_samples');
    }
};
