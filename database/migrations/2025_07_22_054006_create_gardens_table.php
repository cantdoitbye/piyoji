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
        Schema::create('gardens', function (Blueprint $table) {
           $table->id();
            $table->string('garden_name');
            $table->text('address');
            $table->string('contact_person_name');
            $table->string('mobile_no');
            $table->string('email')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode')->nullable();
            $table->json('tea_ids'); // Multi-select tea from tea master
            $table->decimal('altitude', 8, 2)->nullable(); // Garden altitude in meters
            $table->text('speciality')->nullable(); // Garden's speciality
            $table->boolean('status')->default(true);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'created_at']);
            $table->index('garden_name');
            $table->index('state');
            $table->index('contact_person_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gardens');
    }
};
