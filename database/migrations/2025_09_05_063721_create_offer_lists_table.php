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
        Schema::create('offer_lists', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->nullable();
            $table->string('awr_no')->nullable();
            $table->date('date');
            $table->enum('for', ['GTPP', 'GTFP']);
            $table->string('garden_name');
            $table->foreignId('garden_id')->nullable()->constrained('gardens')->nullOnDelete();
            $table->string('grade');
            $table->enum('inv_pretx', ['C', 'EX', 'PR'])->default('C');
            $table->integer('inv_no')->nullable();
            $table->string('party_1')->nullable();
            $table->string('party_2')->nullable();
            $table->string('party_3')->nullable();
            $table->string('party_4')->nullable();
            $table->string('party_5')->nullable();
            $table->string('party_6')->nullable();
            $table->string('party_7')->nullable();
            $table->string('party_8')->nullable();
            $table->string('party_9')->nullable();
            $table->string('party_10')->nullable();
            $table->decimal('pkgs', 8, 2)->nullable();
            $table->decimal('net1', 8, 2)->nullable();
            $table->decimal('ttl_kgs', 10, 3)->nullable();
            $table->date('d_o_packing')->nullable();
            $table->enum('type', ['BROKENS', 'FANNINGS', 'D'])->nullable();
            $table->string('key')->nullable();
            $table->text('name_of_upload')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['garden_name', 'grade']);
            $table->index(['date', 'for']);
            $table->index('garden_id');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_lists');
    }
};
