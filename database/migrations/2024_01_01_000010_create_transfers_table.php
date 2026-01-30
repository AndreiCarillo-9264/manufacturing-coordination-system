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
            $table->string('ptt_number')->unique();
            $table->string('section');
            $table->date('date_transferred');
            $table->foreignId('jo_id')->constrained('job_orders')->restrictOnDelete();
            $table->integer('qty');
            $table->date('delivery_date');
            $table->text('remarks')->nullable();
            $table->time('transfer_time');
            $table->enum('transfer_status', ['balance', 'complete'])->default('balance');
            $table->integer('jo_balance')->default(0);
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('grade')->nullable();
            $table->string('dimension')->nullable();
            $table->foreignId('received_by_user_id')->constrained('users')->restrictOnDelete();
            $table->date('date_received');
            $table->time('time_received');
            $table->integer('qty_received');
            $table->integer('jit_days')->default(0);
            $table->string('ds_status')->nullable();
            $table->integer('week_num');
            $table->string('category');
            $table->decimal('selling_price', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('jo_id');
            $table->index('product_id');
            $table->index('transfer_status');
            $table->index('date_transferred');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};