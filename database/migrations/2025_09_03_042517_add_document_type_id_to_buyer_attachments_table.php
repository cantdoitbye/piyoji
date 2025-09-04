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
        Schema::table('buyer_attachments', function (Blueprint $table) {
                        $table->foreignId('document_type_id')->nullable()->after('file_size')->constrained('document_types')->onDelete('set null');
              $table->dropColumn('document_type');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buyer_attachments', function (Blueprint $table) {
            $table->dropForeign(['document_type_id']);
            $table->dropColumn('document_type_id');
            $table->string('document_type')->after('file_size')->nullable();
        });
    }
};
