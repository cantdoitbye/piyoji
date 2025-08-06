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
      Schema::create('billing_companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('contact_person');
            $table->string('email')->unique();
            $table->string('phone');
            $table->text('billing_address');
            $table->string('billing_city');
            $table->string('billing_state');
            $table->string('billing_pincode');
            $table->string('gstin')->nullable();
            $table->string('pan')->nullable();
            $table->enum('type', ['seller', 'buyer', 'both'])->default('both');
            $table->boolean('status')->default(true);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'type']);
            $table->index('company_name');
            $table->index('type');
        });

        // Shipping addresses for buyers (one billing company can have multiple shipping addresses)
        Schema::create('billing_company_shipping_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_company_id')->constrained('billing_companies')->onDelete('cascade');
            $table->string('address_label')->default('Default'); // e.g., 'Warehouse 1', 'Main Office', etc.
            $table->text('shipping_address');
            $table->string('shipping_city');
            $table->string('shipping_state');
            $table->string('shipping_pincode');
            $table->string('contact_person')->nullable();
            $table->string('contact_phone')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('status')->default(true);
            $table->timestamps();
            
            // $table->index(['billing_company_id', 'status']);
            // $table->index('is_default');
        });

        // Pivot table for seller-billing company relationship (many-to-many)
        Schema::create('seller_billing_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('sellers')->onDelete('cascade');
            $table->foreignId('billing_company_id')->constrained('billing_companies')->onDelete('cascade');
            $table->boolean('is_primary')->default(false); // One primary billing company per seller
            $table->timestamps();
            
            $table->unique(['seller_id', 'billing_company_id']);
            $table->index(['seller_id', 'is_primary']);
        });

        // Update POC rules - POC can be assigned to billing company under specific seller
      Schema::create('poc_billing_company_assignments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('poc_id')->constrained('pocs')->onDelete('cascade');
    $table->foreignId('billing_company_id')->constrained('billing_companies')->onDelete('cascade');
    $table->foreignId('seller_id')->constrained('sellers')->onDelete('cascade');
    $table->boolean('is_primary')->default(false);
    $table->timestamps();

    // Ensure same POC cannot be assigned to multiple sellers (business rule)
    $table->unique('poc_id', 'unique_poc_per_seller');
    
    // Fix the long index name issue
    $table->unique(['poc_id', 'billing_company_id', 'seller_id'], 'unique_poc_bc_seller');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
 Schema::dropIfExists('poc_billing_company_assignments');
        Schema::dropIfExists('seller_billing_companies');
        Schema::dropIfExists('billing_company_shipping_addresses');
        Schema::dropIfExists('billing_companies');    }
};
