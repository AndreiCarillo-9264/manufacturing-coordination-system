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
        Schema::table('finished_goods', function (Blueprint $table) {
            $table->foreignId('job_order_id')->nullable()->after('product_id')->constrained('job_orders')->onDelete('set null');
            $table->index('job_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('finished_goods', function (Blueprint $table) {
            $table->dropForeignIdFor('job_orders');
            $table->dropIndex(['job_order_id']);
            $table->dropColumn('job_order_id');
        });
    }
};
