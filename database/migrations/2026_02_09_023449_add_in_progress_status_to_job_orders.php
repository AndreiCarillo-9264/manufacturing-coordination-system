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
        Schema::table('job_orders', function (Blueprint $table) {
            // Modify jo_status ENUM to rename 'Partial' to 'Approved' and add 'In Progress'
            $table->enum('jo_status', ['JO Full', 'Approved', 'Pending', 'In Progress', 'Cancelled'])
                ->default('Pending')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First convert data back to old values
        DB::update("UPDATE job_orders SET jo_status = 'Partial' WHERE jo_status = 'Approved'");
        DB::update("UPDATE job_orders SET jo_status = 'Pending' WHERE jo_status = 'In Progress'");

        Schema::table('job_orders', function (Blueprint $table) {
            // Revert to original ENUM values
            $table->enum('jo_status', ['JO Full', 'Partial', 'Pending', 'Cancelled'])
                ->default('Pending')
                ->change();
        });
    }
};
