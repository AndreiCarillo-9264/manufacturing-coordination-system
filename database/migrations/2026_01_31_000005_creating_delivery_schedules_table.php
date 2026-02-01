<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_schedules', function (Blueprint $table) {
            $table->id();
            
            // Delivery identification
            $table->string('delivery_code')->unique();
            
            // References
            $table->foreignId('job_order_id')
                  ->constrained('job_orders')
                  ->restrictOnDelete();
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->restrictOnDelete();
            
            // Status tracking
            $table->enum('status', [
                'pending',
                'urgent',
                'backlog',
                'complete'
            ])->default('pending');
            $table->string('ppqc_status')->nullable();
            
            // Scheduling
            $table->date('delivery_date');
            $table->integer('week_number');
            $table->date('date_encoded');
            
            // Quantities 
            $table->integer('qty_scheduled');
            $table->integer('qty_delivered')->nullable();
            $table->integer('qty_transferred')->nullable();
            $table->integer('qty_max')->nullable();
            $table->integer('qty_fg_stocks')->default(0);
            $table->integer('qty_buffer_stock')->default(0);
            $table->integer('qty_backlog')->default(0);
            $table->integer('qty_jo_balance')->default(0);
            
            // Commitments and notes
            $table->text('pmp_commitment')->nullable();
            $table->text('ppqc_commitment')->nullable();
            $table->text('remarks')->nullable();
            $table->text('delivery_remarks')->nullable();
            $table->text('jo_remarks')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('delivery_code');
            $table->index('job_order_id');
            $table->index('product_id');
            $table->index('status');
            $table->index('delivery_date');
            $table->index('week_number');
            $table->index(['job_order_id', 'status']);
            $table->index(['delivery_date', 'status']);
            $table->index(['product_id', 'delivery_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_schedules');
    }
};