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
        Schema::create('batch_tester_evaluations', function (Blueprint $table) {
           $table->id();
            $table->foreignId('batch_evaluation_id')->constrained('batch_evaluations')->onDelete('cascade');
            $table->foreignId('tester_poc_id')->constrained('pocs')->onDelete('cascade');
            $table->string('tester_name');
            $table->integer('c_score')->default(0)->comment('Color/Appearance score out of 100');
            $table->integer('t_score')->default(0)->comment('Taste score out of 100');
            $table->integer('s_score')->default(0)->comment('Strength score out of 100');
            $table->integer('b_score')->default(0)->comment('Body/Liquor score out of 100');
            $table->integer('total_samples')->default(0)->comment('Total samples evaluated by this tester');
            $table->string('color_shade')->default('RED')->comment('Color shade assessment');
            $table->string('brand')->default('WB')->comment('Brand classification');
            $table->text('remarks')->nullable();
            $table->enum('evaluation_status', ['pending', 'completed'])->default('pending');
            $table->timestamp('evaluated_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes
            $table->index(['batch_evaluation_id', 'tester_poc_id']);
            $table->index(['tester_poc_id']);
            $table->index(['evaluation_status']);
            
            // Constraints
            // $table->check('c_score >= 0 AND c_score <= 100');
            // $table->check('t_score >= 0 AND t_score <= 100');
            // $table->check('s_score >= 0 AND s_score <= 100');
            // $table->check('b_score >= 0 AND b_score <= 100');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_tester_evaluations');
    }
};
