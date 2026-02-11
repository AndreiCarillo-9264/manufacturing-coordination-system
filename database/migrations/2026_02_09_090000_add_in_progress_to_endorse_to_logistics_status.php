<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Alter enum to include 'in_progress'
        DB::statement("ALTER TABLE `endorse_to_logistics` MODIFY `status` ENUM('pending','approved','in_progress','completed') DEFAULT 'pending';");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `endorse_to_logistics` MODIFY `status` ENUM('pending','approved','completed') DEFAULT 'pending';");
    }
};
