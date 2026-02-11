<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->text('deactivation_remarks')->nullable()->after('remarks');
            $table->foreignId('deactivated_by')->nullable()->constrained('users')->onDelete('set null')->after('deactivation_remarks');
            $table->timestamp('deactivated_at')->nullable()->after('deactivated_by');
        });

        Schema::table('inventory_transfers', function (Blueprint $table) {
            $table->text('deactivation_remarks')->nullable()->after('remarks');
            $table->foreignId('deactivated_by')->nullable()->constrained('users')->onDelete('set null')->after('deactivation_remarks');
            $table->timestamp('deactivated_at')->nullable()->after('deactivated_by');
        });
    }

    public function down(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->dropForeign(['deactivated_by']);
            $table->dropColumn(['deactivation_remarks', 'deactivated_by', 'deactivated_at']);
        });

        Schema::table('inventory_transfers', function (Blueprint $table) {
            $table->dropForeign(['deactivated_by']);
            $table->dropColumn(['deactivation_remarks', 'deactivated_by', 'deactivated_at']);
        });
    }
};
