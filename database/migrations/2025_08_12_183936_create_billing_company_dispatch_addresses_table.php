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
        Schema::create('billing_company_dispatch_addresses', function (Blueprint $table) {
              $table->id();
            $table->foreignId('billing_company_id')->constrained('billing_companies')->onDelete('cascade');
            $table->string('address_label');
            $table->text('dispatch_address');
            $table->string('dispatch_city');
            $table->string('dispatch_state');
            $table->string('dispatch_pincode', 10);
            $table->string('contact_person')->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('status')->default(true);
            $table->timestamps();

       
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_company_dispatch_addresses');
    }
};
