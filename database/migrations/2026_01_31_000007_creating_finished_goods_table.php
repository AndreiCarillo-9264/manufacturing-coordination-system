<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finished_goods', function (Blueprint $table) {
            $table->id();
            
            // 1:1 relationship with products
            $table->foreignId('product_id')
                  ->unique()
                  ->constrained('products')
                  ->cascadeOnDelete();
            
            // Inventory counts
            $table->integer('qty_beginning')->default(0);
            $table->integer('qty_in')->default(0);
            $table->integer('qty_out')->default(0);
            $table->integer('qty_theoretical_ending')->default(0);
            $table->integer('qty_actual_ending')->default(0);
            $table->integer('qty_variance')->default(0);
            $table->integer('qty_buffer_stock')->default(0);
            $table->integer('qty_pc_area')->nullable();
            
            // Amount tracking
            $table->decimal('amount_beginning', 12, 2)->default(0.00);
            $table->decimal('amount_in', 12, 2)->default(0.00);
            $table->decimal('amount_out', 12, 2)->default(0.00);
            $table->decimal('amount_ending', 12, 2)->default(0.00);
            $table->decimal('amount_variance', 12, 2)->default(0.00);
            
            // Aging analysis
            $table->date('date_last_in')->nullable();
            $table->date('date_oldest')->nullable();
            $table->integer('days_aging')->default(0);
            $table->integer('aging_1_30_days')->default(0);
            $table->integer('aging_31_60_days')->default(0);
            $table->integer('aging_61_90_days')->default(0);
            $table->integer('aging_91_120_days')->default(0);
            $table->integer('aging_over_120_days')->default(0);
            
            // Notes
            $table->text('remarks')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('product_id');
            $table->index('date_last_in');
            $table->index('days_aging');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finished_goods');
    }
};