<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['status', 'sort_order']);
        });

        // Insert default document types
        $defaultTypes = [
            ['name' => 'Business License', 'description' => 'Business License Document', 'sort_order' => 1],
            ['name' => 'Agreement', 'description' => 'Agreement Document', 'sort_order' => 2],
            ['name' => 'Certificate', 'description' => 'Certificate Document', 'sort_order' => 3],
            ['name' => 'Registration Document', 'description' => 'Registration Document', 'sort_order' => 4],
            ['name' => 'Tax Document', 'description' => 'Tax Document', 'sort_order' => 5],
            ['name' => 'Bank Statement', 'description' => 'Bank Statement', 'sort_order' => 6],
            ['name' => 'Other Document', 'description' => 'Other Document', 'sort_order' => 7],
        ];

        foreach ($defaultTypes as $type) {
            DB::table('document_types')->insert(array_merge($type, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};
