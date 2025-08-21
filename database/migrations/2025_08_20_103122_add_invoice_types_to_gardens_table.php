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
        Schema::table('gardens', function (Blueprint $table) {
                  // Add garden type field
            $table->enum('garden_type', ['garden', 'mark'])->default('garden')->after('garden_name');
            
            // Add location coordinates if they don't exist
            if (!Schema::hasColumn('gardens', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('pincode');
            }
            if (!Schema::hasColumn('gardens', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
            
            // Add new column for invoice type variables
            $table->json('invoice_type_variables')->nullable()->after('acceptable_invoice_types');
       
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gardens', function (Blueprint $table) {
                        $table->dropColumn(['garden_type', 'invoice_type_variables']);

        });
    }
};
