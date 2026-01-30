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
            $table->string('ds_delivery_code')->unique();
            $table->enum('ds_status', ['pending', 'urgent', 'backlog', 'complete'])
                  ->default('pending');
            $table->date('date');
            $table->foreignId('jo_id')->constrained('job_orders')->restrictOnDelete();
            $table->string('po_number');
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->integer('qty');
            $table->string('uom');
            $table->text('remarks')->nullable();
            $table->text('pmp_commitment')->nullable();
            $table->text('ppqc_commitment')->nullable();
            $table->integer('fg_stocks')->default(0);
            $table->string('status')->nullable();
            $table->text('delivery_remarks')->nullable();
            $table->text('jo_remarks')->nullable();
            $table->string('ppqc_status')->nullable();
            $table->integer('jo_balance')->default(0);
            $table->integer('transfer')->nullable();
            $table->integer('delivered_dsd')->nullable();
            $table->integer('ds_qty');
            $table->integer('week_num');
            $table->date('date_encoded');
            $table->integer('max_qty')->nullable();
            $table->integer('buffer_stocks')->default(0);
            $table->integer('backlog')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('jo_id');
            $table->index('product_id');
            $table->index('ds_status');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_schedules');
    }
};