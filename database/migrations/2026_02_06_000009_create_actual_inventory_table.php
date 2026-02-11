<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actual_inventory', function (Blueprint $table) {
            $table->id();
            $table->string('tag_number')->unique()->comment('Physical inventory tag number');
            
            // References
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->foreignId('finished_good_id')->nullable()->constrained('finished_goods')->onDelete('set null');
            
            // Auto-filled from product
            $table->string('product_code');
            $table->string('customer_name');
            $table->string('model_name');
            $table->text('description');
            $table->string('dimension')->nullable();
            $table->string('uom')->default('PC/S');
            
            // Inventory Count
            $table->integer('fg_quantity')->default(0)->comment('Finished Good Quantity');
            $table->string('location')->nullable()->comment('Physical location');
            
            // Count Verification
            $table->string('counted_by')->nullable();
            $table->timestamp('counted_at')->nullable();
            $table->string('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->enum('status', ['Pending', 'Counted', 'Verified', 'Discrepancy'])->default('Pending');
            
            $table->text('remarks')->nullable();
            
            // Audit Fields
            $table->foreignId('encoded_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('date_encoded')->useCurrent();
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('tag_number');
            $table->index('product_id');
            $table->index('finished_good_id');
            $table->index('product_code');
            $table->index('location');
            $table->index('status');
            $table->index(['customer_name', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actual_inventory');
    }
};