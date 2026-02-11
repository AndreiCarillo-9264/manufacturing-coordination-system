<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('profile_picture');
            }
            if (!Schema::hasColumn('users', 'deactivation_remarks')) {
                $table->text('deactivation_remarks')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('users', 'deactivated_by')) {
                $table->unsignedBigInteger('deactivated_by')->nullable()->after('deactivation_remarks');
            }
            if (!Schema::hasColumn('users', 'deactivated_at')) {
                $table->timestamp('deactivated_at')->nullable()->after('deactivated_by');
            }
        });

        // Backfill any existing NULLs just in case
        DB::table('users')->whereNull('is_active')->update(['is_active' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'deactivated_at')) {
                $table->dropColumn('deactivated_at');
            }
            if (Schema::hasColumn('users', 'deactivated_by')) {
                $table->dropColumn('deactivated_by');
            }
            if (Schema::hasColumn('users', 'deactivation_remarks')) {
                $table->dropColumn('deactivation_remarks');
            }
            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
