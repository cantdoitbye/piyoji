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
        Schema::create('pocs', function (Blueprint $table) {
            $table->id();
            $table->string('poc_name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('designation')->nullable();
            $table->enum('poc_type', ['seller', 'buyer', 'both'])->default('both');
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode')->nullable();
            $table->boolean('status')->default(true);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'poc_type']);
            $table->index('poc_name');
            $table->index('poc_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pocs');
    }
};
