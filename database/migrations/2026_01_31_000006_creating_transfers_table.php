<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            
            // Transfer identification
            $table->string('ptt_number')->unique();
            
            // References
            $table->foreignId('job_order_id')
                  ->constrained('job_orders')
                  ->restrictOnDelete();
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->restrictOnDelete();
            
            // Transfer details
            $table->string('section');
            $table->string('category');
            $table->enum('status', [
                'balance',
                'complete'
            ])->default('balance');
            $table->string('delivery_schedule_status')->nullable();
            
            // Dates and times
            $table->date('date_transferred');
            $table->time('time_transferred');
            $table->date('date_delivery_scheduled');
            $table->integer('week_number');
            $table->integer('jit_days')->default(0);
            
            // Quantities
            $table->integer('qty_transferred');
            $table->integer('qty_jo_balance')->default(0);
            
            // Quality details
            $table->string('grade')->nullable();
            $table->string('dimension')->nullable();
            
            // Financial
            $table->decimal('unit_selling_price', 12, 2);
            $table->decimal('total_amount', 12, 2);
            
            // Receiving details
            $table->foreignId('received_by_user_id')
                  ->constrained('users')
                  ->restrictOnDelete();
            $table->date('date_received');
            $table->time('time_received');
            $table->integer('qty_received');
            
            // Notes
            $table->text('remarks')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('ptt_number');
            $table->index('job_order_id');
            $table->index('product_id');
            $table->index('status');
            $table->index('section');
            $table->index('date_transferred');
            $table->index('received_by_user_id');
            $table->index('week_number');
            $table->index(['job_order_id', 'status']);
            $table->index(['product_id', 'date_transferred']);
            $table->index(['date_transferred', 'section']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};