<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('endorse_to_logistics', function (Blueprint $table) {
            $table->id();
            $table->string('etl_code')->unique()->comment('ETL Delivery Code e.g., 45992C-ASABA-001-14-1');
            
            // References
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->foreignId('delivery_schedule_id')->nullable()->constrained('delivery_schedules')->onDelete('set null');
            
            // Auto-filled from product
            $table->string('product_code');
            $table->string('customer_name');
            $table->string('model_name');
            $table->text('description');
            $table->string('uom')->default('PC/S');
            
            // Endorsement Details
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->integer('total_out')->default(0)->comment('Total quantity out');
            $table->integer('quantity')->nullable()->comment('Endorsed quantity');
            $table->integer('quantity_delivered')->default(0);
            $table->date('delivery_date')->nullable();
            
            // Logistics Details
            $table->string('dr_number')->nullable()->comment('Delivery Receipt Number');
            $table->string('si_number')->nullable()->comment('Sales Invoice Number');
            $table->string('received_by')->nullable();
            $table->date('date_received')->nullable();
            
            // Packaging
            $table->string('stretch_film_code')->nullable()->comment('Common Stretch Film Code');
            
            $table->text('remarks')->nullable();
            
            // Audit Fields
            $table->foreignId('encoded_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('date_encoded')->useCurrent();
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('etl_code');
            $table->index('product_id');
            $table->index('delivery_schedule_id');
            $table->index('product_code');
            $table->index('dr_number');
            $table->index('si_number');
            $table->index('delivery_date');
            $table->index(['customer_name', 'delivery_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('endorse_to_logistics');
    }
};