<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code')->unique()->comment('e.g., C-ADA-070-01-0CA');
            
            // Customer Information
            $table->string('customer_name');
            $table->string('customer_location')->nullable();
            
            // Product Details
            $table->string('model_name');
            $table->text('description');
            $table->string('specs')->nullable()->comment('e.g., C-175');
            $table->string('dimension')->nullable()->comment('e.g., S.T.S 221 X 207 X 187 mm');
            
            // Pricing & Commercial
            $table->integer('moq')->nullable()->comment('Minimum Order Quantity');
            $table->string('uom')->default('PC/S')->comment('Unit of Measure');
            $table->string('currency')->default('PHP');
            $table->decimal('selling_price', 10, 2)->nullable();
            
            // Additional Fields
            $table->string('rsqf_number')->nullable()->comment('RSQF#');
            $table->string('po_remarks')->nullable()->comment('Remarks - P.O.');
            $table->decimal('mc', 10, 2)->nullable()->comment('Manufacturing Cost');
            $table->decimal('diff', 10, 2)->nullable()->comment('Difference');
            $table->decimal('mu', 10, 4)->nullable()->comment('Markup');
            $table->string('pc')->nullable()->comment('Product Category');
            
            // Audit Fields
            $table->foreignId('encoded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('date_encoded')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index('product_code');
            $table->index('customer_name');
            $table->index('model_name');
            $table->index(['customer_name', 'model_name']);
            $table->fullText(['product_code', 'customer_name', 'model_name', 'description'])->name('products_ft_search');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};