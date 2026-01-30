<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finished_goods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained('products')->cascadeOnDelete();
            $table->integer('count_pc_area')->nullable();
            $table->integer('beg')->default(0);
            $table->integer('in_qty')->default(0);
            $table->integer('out_qty')->default(0);
            $table->integer('theo_end')->default(0);
            $table->text('remarks')->nullable();
            $table->integer('buffer_stocks')->default(0);
            $table->decimal('cur_sell_price', 12, 2);
            $table->decimal('beg_amt', 12, 2)->default(0);
            $table->decimal('in_amt', 12, 2)->default(0);
            $table->decimal('out_amt', 12, 2)->default(0);
            $table->decimal('end_amt', 12, 2)->default(0);
            $table->integer('ending_count')->default(0);
            $table->string('uom3')->nullable();
            $table->integer('variance_count')->default(0);
            $table->decimal('variance_amount', 12, 2)->default(0);
            $table->date('last_in_date')->nullable();
            $table->date('older_date')->nullable();
            $table->integer('days')->default(0);
            $table->integer('range_1_30')->default(0);
            $table->integer('range_31_60')->default(0);
            $table->integer('range_61_90')->default(0);
            $table->integer('range_91_120')->default(0);
            $table->integer('range_over_120')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finished_goods');
    }
};