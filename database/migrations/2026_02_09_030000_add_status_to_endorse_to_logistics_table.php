<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('endorse_to_logistics', function (Blueprint $table) {
            // Add status field to track approval workflow
            // pending: waiting for logistics approval
            // approved: approved by logistics, ready for delivery
            // completed: delivery completed and marked as complete
            $table->enum('status', ['pending', 'approved', 'completed'])->default('pending')->after('si_number');
            
            // Add timestamps for approval workflow tracking
            $table->timestamp('approved_at')->nullable()->after('status');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')->after('approved_at');
            $table->timestamp('completed_at')->nullable()->after('approved_by');
            $table->foreignId('completed_by')->nullable()->constrained('users')->onDelete('set null')->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('endorse_to_logistics', function (Blueprint $table) {
            $table->dropColumn(['status', 'approved_at', 'approved_by', 'completed_at', 'completed_by']);
        });
    }
};
