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
        Schema::table('inventory_transfers', function (Blueprint $table) {
            // Rename the old string column
            DB::statement('ALTER TABLE `inventory_transfers` CHANGE COLUMN `received_by` `received_by_name` VARCHAR(255) NULL');
            
            // Add new foreign key column
            $table->foreignId('received_by_user_id')->nullable()->after('time_received')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_transfers', function (Blueprint $table) {
            // Drop the foreign key
            $table->dropForeignIdFor('received_by_user_id');
            $table->dropColumn('received_by_user_id');
            
            // Rename back
            DB::statement('ALTER TABLE `inventory_transfers` CHANGE COLUMN `received_by_name` `received_by` VARCHAR(255) NULL');
        });
    }
};
