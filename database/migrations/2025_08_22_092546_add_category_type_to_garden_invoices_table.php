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
        Schema::table('garden_invoices', function (Blueprint $table) {
             $table->string('category_type')->after('invoice_number'); // fannings, brokens, dust
            $table->json('variables')->after('category_type'); // Selected variables for the category
            $table->string('grade')->after('variables'); // Grade selection
            
            // Add indexes for better performance
            $table->index('category_type');
            $table->index('grade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('garden_invoices', function (Blueprint $table) {
                $table->dropIndex(['category_type']);
            $table->dropIndex(['grade']);
            $table->dropColumn(['category_type', 'variables', 'grade']);
        
        });
    }
};
