<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_schedules', function (Blueprint $table) {
            $table->time('delivery_time')->nullable()->after('delivery_date');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_schedules', function (Blueprint $table) {
            $table->dropColumn('delivery_time');
        });
    }
};
