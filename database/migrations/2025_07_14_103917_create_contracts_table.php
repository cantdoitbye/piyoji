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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('sellers')->onDelete('cascade');
            $table->string('contract_number')->unique();
            $table->string('contract_title');
            $table->date('effective_date');
            $table->date('expiry_date');
            $table->enum('status', ['draft', 'active', 'expired', 'cancelled'])->default('draft');
            $table->text('terms_and_conditions')->nullable();
            $table->text('remarks')->nullable();
            $table->string('uploaded_file_path')->nullable();
            $table->foreignId('created_by')->constrained('admin_users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('admin_users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['seller_id', 'status']);
            $table->index(['effective_date', 'expiry_date']);
            $table->index('contract_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
