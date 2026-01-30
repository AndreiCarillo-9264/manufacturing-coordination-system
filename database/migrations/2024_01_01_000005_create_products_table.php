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
            $table->string('customer')->nullable();
            $table->foreignId('encoded_by_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->string('product_code')->unique();
            $table->string('model_name');
            $table->text('description')->nullable();
            $table->date('date_encoded');
            $table->string('specs')->nullable();
            $table->string('dimension')->nullable();
            $table->integer('moq')->default(1);
            $table->string('uom');
            $table->string('currency')->default('PHP');
            $table->decimal('selling_price', 12, 2)->default(0.00);
            $table->string('rsqf_number')->nullable();
            $table->text('remarks_po')->nullable();
            $table->decimal('mc', 12, 2)->nullable();
            $table->decimal('diff', 12, 2)->nullable();
            $table->decimal('mu', 12, 2)->nullable();
            $table->string('location')->nullable();
            $table->string('pc')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('customer');
            $table->index('product_code');
            $table->index('date_encoded');
            $table->index('encoded_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};