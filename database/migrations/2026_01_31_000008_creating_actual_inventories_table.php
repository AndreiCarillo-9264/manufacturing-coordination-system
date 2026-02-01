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
            
            // Identification
            $table->string('tag_number')->unique();
            
            // Product reference
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->restrictOnDelete();
            
            // Physical count
            $table->integer('qty_counted');
            $table->string('location')->nullable();
            
            // Verification
            $table->foreignId('counted_by_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->foreignId('verified_by_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('counted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            
            // Notes
            $table->text('remarks')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('tag_number');
            $table->index('product_id');
            $table->index('location');
            $table->index('counted_by_user_id');
            $table->index('verified_by_user_id');
            $table->index(['product_id', 'location']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actual_inventories');
    }
};