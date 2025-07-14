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
        Schema::create('logistic_companies', function (Blueprint $table) {
             $table->id();
            $table->string('company_name');
            $table->string('contact_person');
            $table->string('email')->unique();
            $table->string('phone');
            $table->text('address');
            $table->string('city');
            $table->string('state');
            $table->string('pincode');
            $table->json('supported_routes');
            $table->json('supported_regions');
            $table->text('pricing_structure')->nullable();
            $table->enum('pricing_type', ['per_kg', 'per_km', 'flat_rate', 'custom'])->default('per_kg');
            $table->decimal('base_rate', 10, 2)->nullable();
            $table->decimal('per_kg_rate', 10, 2)->nullable();
            $table->decimal('per_km_rate', 10, 2)->nullable();
            $table->text('service_description')->nullable();
            $table->string('gstin')->nullable();
            $table->string('pan')->nullable();
            $table->boolean('status')->default(true);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'created_at']);
            $table->index('company_name');
            $table->index('state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logistic_companies');
    }
};
