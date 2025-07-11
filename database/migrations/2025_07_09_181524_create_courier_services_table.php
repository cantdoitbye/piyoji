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
        Schema::create('courier_services', function (Blueprint $table) {
              $table->id();
            $table->string('company_name');
            $table->string('contact_person');
            $table->string('email');
            $table->string('phone');
            $table->json('service_areas');
            $table->string('api_endpoint')->nullable();
            $table->text('api_token')->nullable();
            $table->string('api_username')->nullable();
            $table->text('api_password')->nullable();
            $table->string('webhook_url')->nullable();
            $table->string('tracking_url_template')->nullable();
            $table->boolean('status')->default(true);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'created_at']);
            $table->index('company_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courier_services');
    }
};
