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
            $table->string('jo_number')->unique()->comment('e.g., C-25-10422');
            $table->enum('jo_status', ['JO Full', 'Approved', 'Pending', 'In Progress', 'Cancelled'])->default('Pending');
            
            // Product Reference (Source of Truth)
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            
            // These fields will be auto-filled from product but can be overridden
            $table->string('product_code');
            $table->string('customer_name');
            $table->string('model_name');
            $table->text('description');
            $table->string('dimension')->nullable();
            $table->string('uom')->default('PC/S');
            
            // Job Order Specific Fields
            $table->string('po_number')->nullable()->comment('Purchase Order Number');
            $table->date('date_needed')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('jo_balance')->default(0);
            $table->integer('ppqc_transfer')->default(0);
            $table->integer('ds_quantity')->default(0)->comment('Delivery Schedule Quantity');
            $table->string('withdrawal_status')->nullable()->comment('e.g., APPROVED');
            $table->string('withdrawal_number')->nullable()->comment('e.g., DEC0020');
            $table->string('week_number')->nullable()->comment('e.g., 49');
            $table->text('remarks')->nullable();
            
            // Audit Fields
            $table->foreignId('encoded_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('date_encoded')->useCurrent();
            $table->timestamp('date_approved')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('jo_number');
            $table->index('jo_status');
            $table->index('product_id');
            $table->index('product_code');
            $table->index('po_number');
            $table->index('date_needed');
            $table->index('week_number');
            $table->index(['customer_name', 'date_needed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_orders');
    }
};