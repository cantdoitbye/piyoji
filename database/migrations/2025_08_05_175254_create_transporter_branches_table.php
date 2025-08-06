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
      // Add branches support to existing logistic_companies table
        Schema::table('logistic_companies', function (Blueprint $table) {
            $table->boolean('has_branches')->default(false)->after('status');
        });

        // Create transporter branches table
        Schema::create('transporter_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logistic_company_id')->constrained('logistic_companies')->onDelete('cascade');
            $table->string('branch_name');
            $table->enum('city', ['Kolkata', 'Siliguri', 'Guwahati']); // Specific cities as per requirement
            $table->text('branch_address');
            $table->string('branch_contact_person');
            $table->string('branch_phone');
            $table->string('branch_email')->nullable();
            $table->text('services_offered')->nullable(); // JSON or text describing services
            $table->json('operational_hours')->nullable(); // Store working hours
            $table->decimal('handling_capacity_tons_per_day', 8, 2)->nullable();
            $table->boolean('is_main_branch')->default(false);
            $table->boolean('status')->default(true);
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            $table->index(['logistic_company_id', 'city']);
            $table->index(['logistic_company_id', 'status']);
            $table->index('city');
            $table->unique(['logistic_company_id', 'branch_name']);
        });

        // Branch service routes (which routes each branch handles)
        Schema::create('branch_service_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transporter_branch_id')->constrained('transporter_branches')->onDelete('cascade');
            $table->string('route_from');
            $table->string('route_to');
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->decimal('estimated_time_hours', 5, 2)->nullable();
            $table->decimal('rate_per_kg', 8, 2)->nullable();
            $table->decimal('minimum_charge', 8, 2)->nullable();
            $table->boolean('express_service_available')->default(false);
            $table->timestamps();
            
    $table->index(['transporter_branch_id', 'route_from', 'route_to'], 'idx_branch_route');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
   Schema::dropIfExists('branch_service_routes');
        Schema::dropIfExists('transporter_branches');
        
        Schema::table('logistic_companies', function (Blueprint $table) {
            $table->dropColumn('has_branches');
        });    }
};
