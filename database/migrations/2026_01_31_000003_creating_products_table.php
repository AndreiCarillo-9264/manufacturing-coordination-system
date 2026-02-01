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
            
            // Product identification
            $table->string('product_code')->unique();
            $table->string('model_name');
            $table->text('description')->nullable();
            
            // Customer information
            $table->string('customer')->nullable();
            
            // Technical specifications
            $table->string('specs')->nullable();
            $table->string('dimension')->nullable();
            $table->string('location')->nullable();
            $table->string('pc')->nullable();
            
            // Units and pricing
            $table->string('uom');
            $table->integer('moq')->default(1);
            $table->string('currency')->default('PHP');
            $table->decimal('selling_price', 12, 2)->default(0.00);
            
            // Manufacturing cost details
            $table->decimal('mc', 12, 2)->nullable();
            $table->decimal('diff', 12, 2)->nullable();
            $table->decimal('mu', 12, 2)->nullable();
            
            // Reference documents
            $table->string('rsqf_number')->nullable();
            $table->text('remarks')->nullable();
            
            // Audit trail
            $table->foreignId('encoded_by_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->date('date_encoded');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('product_code');
            $table->index('customer');
            $table->index('model_name');
            $table->index('date_encoded');
            $table->index('encoded_by_user_id');
            $table->index(['customer', 'model_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};