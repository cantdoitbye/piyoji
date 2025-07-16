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
        Schema::table('users', function (Blueprint $table) {
              $table->string('phone')->nullable()->after('email');
            $table->enum('role', ['data_entry', 'supervisor', 'viewer'])->default('data_entry')->after('password');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('role');
            $table->string('department')->nullable()->after('status');
            $table->text('permissions')->nullable()->after('department');
            $table->timestamp('last_login_at')->nullable()->after('permissions');
            $table->foreignId('created_by')->nullable()->constrained('admin_users')->onDelete('set null')->after('last_login_at');
            $table->foreignId('updated_by')->nullable()->constrained('admin_users')->onDelete('set null')->after('created_by');
            $table->softDeletes()->after('updated_at');
            
            $table->index(['status', 'role']);
            $table->index('department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
