<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actual_inventories', function (Blueprint $table) {
            $table->id();
            $table->string('tag_number')->unique();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->integer('fg_qty');
            $table->string('uom');
            $table->string('location')->nullable();
            $table->foreignId('counted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actual_inventories');
    }
};