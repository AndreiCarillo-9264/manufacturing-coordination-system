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
            $table->string('jo_number')->unique();
            $table->enum('status', ['pending', 'approved', 'in_progress', 'completed', 'cancelled'])
                  ->default('pending');
            $table->enum('jo_status', ['jo_full', 'balance', 'excess'])->nullable();
            $table->date('date_needed');
            $table->string('po_number');
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->integer('qty');
            $table->string('uom');
            $table->foreignId('encoded_by_user_id')->constrained('users')->restrictOnDelete();
            $table->text('remarks')->nullable();
            $table->integer('jo_balance')->default(0);
            $table->integer('ppqc_transfer')->nullable();
            $table->integer('ds_quantity')->nullable();
            $table->enum('withdrawal', ['approved', 'with_fg_stocks'])->nullable();
            $table->string('withdrawal_number')->nullable();
            $table->integer('week_number');
            $table->date('date_encoded');
            $table->date('date_approved')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('product_id');
            $table->index('status');
            $table->index('jo_status');
            $table->index('date_needed');
            $table->index('week_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_orders');
    }
};