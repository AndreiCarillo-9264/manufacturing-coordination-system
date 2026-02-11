<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_code')->unique()->comment('e.g., 45994C-ASABA-001-');
            $table->string('ptt_number')->nullable()->comment('PTT#');
            $table->enum('section', ['IMPORTED', 'LOCAL', 'EXPORT'])->default('LOCAL');
            $table->enum('status', ['Balance', 'Complete', 'Pending', 'Cancelled'])->default('Pending');
            
            // References
            $table->foreignId('job_order_id')->nullable()->constrained('job_orders')->onDelete('set null');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            
            // Auto-filled from product
            $table->string('product_code');
            $table->string('customer_name');
            $table->string('model_name');
            $table->text('description');
            $table->string('dimension')->nullable();
            $table->string('grade')->nullable()->comment('e.g., AB-FLUTE');
            $table->string('uom')->default('PC/S');
            
            // Transfer Details
            $table->string('jo_number')->nullable();
            $table->date('date_transferred')->nullable();
            $table->time('time_transferred')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('jo_balance')->default(0);
            $table->date('delivery_date')->nullable();
            $table->string('transfer_by')->nullable()->comment('Person who transferred');
            
            // Receiving Details
            $table->string('received_by')->nullable();
            $table->date('date_received')->nullable();
            $table->time('time_received')->nullable();
            $table->integer('quantity_received')->default(0);
            
            // Additional Info
            $table->string('jit')->nullable()->comment('Just In Time indicator');
            $table->integer('days')->nullable()->comment('Number of days');
            $table->string('ds_status')->nullable()->comment('Delivery Schedule Status');
            $table->string('week_number')->nullable();
            $table->string('category')->nullable()->comment('e.g., IMPORTED');
            $table->string('currency')->default('PHP');
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->text('remarks')->nullable();
            
            // Audit Fields
            $table->foreignId('encoded_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('date_encoded')->useCurrent();
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('transfer_code');
            $table->index('ptt_number');
            $table->index('status');
            $table->index('section');
            $table->index('product_id');
            $table->index('job_order_id');
            $table->index('product_code');
            $table->index('date_transferred');
            $table->index('week_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transfers');
    }
};