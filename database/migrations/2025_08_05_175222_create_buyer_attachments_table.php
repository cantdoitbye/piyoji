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
         Schema::create('buyer_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('buyers')->onDelete('cascade');
            $table->string('file_name'); // Original filename
            $table->string('file_path'); // Storage path
            $table->string('file_type'); // MIME type
            $table->bigInteger('file_size'); // File size in bytes
            $table->string('document_type')->nullable(); // e.g., 'license', 'agreement', 'certificate'
            $table->text('description')->nullable();
            $table->string('uploaded_by')->nullable(); // User who uploaded
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->string('verified_by')->nullable();
            $table->timestamps();
            
            $table->index(['buyer_id', 'document_type']);
            $table->index(['buyer_id', 'is_verified']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyer_attachments');
    }
};
