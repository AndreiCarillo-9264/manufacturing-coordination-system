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
            $table->string('ds_code')->unique()->comment('Delivery Code e.g., 45995C-JTC-033-00-2');
            $table->enum('ds_status', ['BACKLOG', 'ON SCHEDULE', 'DELIVERED', 'CANCELLED'])->default('ON SCHEDULE');
            
            // References
            $table->foreignId('job_order_id')->nullable()->constrained('job_orders')->onDelete('set null');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            
            // Auto-filled from product
            $table->string('product_code');
            $table->string('customer_name');
            $table->string('model_name');
            $table->text('description');
            $table->string('dimension')->nullable();
            $table->string('uom')->default('PC/S');
            
            // Delivery Schedule Specific
            $table->string('jo_number')->nullable();
            $table->string('po_number')->nullable();
            $table->date('delivery_date')->nullable();
            $table->integer('quantity')->default(0)->comment('DS Quantity');
            $table->integer('max_quantity')->default(0);
            $table->integer('fg_stocks')->default(0)->comment('Finished Goods Stocks');
            $table->integer('jo_balance')->default(0);
            $table->integer('transfer_quantity')->default(0);
            $table->integer('delivered_quantity')->default(0);
            
            // Status & Commitment
            $table->string('status')->nullable()->comment('e.g., Urgent');
            $table->string('pmp_commitment')->nullable()->comment('e.g., WITH RM');
            $table->string('ppqc_commitment')->nullable()->comment('e.g., COMPLETE');
            $table->string('ppqc_status')->nullable()->comment('e.g., Urgent');
            $table->string('jo_status')->nullable()->comment('e.g., Balance');
            
            // Additional Info
            $table->string('week_number')->nullable();
            $table->string('dsd')->nullable()->comment('Delivery Schedule Details');
            $table->integer('buffer_stocks')->default(0);
            $table->text('remarks')->nullable();
            $table->text('delivery_remarks')->nullable();
            $table->text('jo_remarks')->nullable();
            
            // Audit Fields
            $table->foreignId('encoded_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('date_encoded')->useCurrent();
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('ds_code');
            $table->index('ds_status');
            $table->index('product_id');
            $table->index('job_order_id');
            $table->index('product_code');
            $table->index('delivery_date');
            $table->index('week_number');
            $table->index(['customer_name', 'delivery_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_schedules');
    }
};