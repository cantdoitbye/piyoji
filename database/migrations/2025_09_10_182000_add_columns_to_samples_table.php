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
        Schema::table('samples', function (Blueprint $table) {
               $table->string('garden_name')->nullable()->after('sample_name');
            $table->string('grade')->nullable()->after('garden_name');
            $table->string('invoice_prefix')->nullable()->after('grade');
            $table->integer('inv_no')->nullable()->after('invoice_prefix');
            $table->string('source_type')->nullable()->after('updated_by');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            
            $table->index(['garden_name', 'grade']);
            $table->index(['invoice_prefix', 'inv_no']);
            $table->index(['source_type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('samples', function (Blueprint $table) {
             $table->dropIndex(['garden_name', 'grade']);
            $table->dropIndex(['invoice_prefix', 'inv_no']);
            $table->dropIndex(['source_type', 'source_id']);
            
            $table->dropColumn([
                'garden_name', 'grade', 'invoice_prefix', 'inv_no', 'source_type', 'source_id'
            ]);
        });
    }
};
