<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_orders', function (Blueprint $table) {
            $table->id();
            
            // Job order identification
            $table->string('jo_number')->unique();
            $table->string('po_number');
            
            // Status tracking
            $table->enum('status', [
                'pending',
                'approved',
                'in_progress',
                'completed',
                'cancelled'
            ])->default('pending');
            $table->enum('fulfillment_status', [
                'full',
                'balance',
                'excess'
            ])->nullable();
            
            // Product reference
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->restrictOnDelete();
            
            // Quantities
            $table->integer('qty_ordered');
            $table->integer('qty_balance')->default(0);
            $table->integer('qty_transferred_to_ppqc')->nullable();
            $table->integer('qty_in_delivery_schedule')->nullable();
            
            // Withdrawal tracking
            $table->enum('withdrawal_status', [
                'approved',
                'with_fg_stocks'
            ])->nullable();
            $table->string('withdrawal_number')->nullable();
            
            // Scheduling
            $table->integer('week_number');
            $table->date('date_needed');
            $table->date('date_encoded');
            $table->date('date_approved')->nullable();
            
            // Audit and notes
            $table->foreignId('encoded_by_user_id')
                  ->constrained('users')
                  ->restrictOnDelete();
            $table->text('remarks')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('jo_number');
            $table->index('po_number');
            $table->index('product_id');
            $table->index('status');
            $table->index('fulfillment_status');
            $table->index('date_needed');
            $table->index('week_number');
            $table->index('encoded_by_user_id');
            $table->index(['product_id', 'status']);
            $table->index(['date_needed', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_orders');
    }
};