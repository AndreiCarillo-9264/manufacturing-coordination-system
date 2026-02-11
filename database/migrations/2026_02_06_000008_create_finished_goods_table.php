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
            $table->string('fg_code')->unique()->comment('Unique Finished Good Code - auto-generated');
            $table->integer('count')->default(0);
            $table->string('pc')->nullable()->comment('PC identifier');
            $table->string('area')->nullable()->comment('Storage area');
            
            // References
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            
            // Auto-filled from product
            $table->string('product_code');
            $table->string('customer_name');
            $table->string('model_name');
            $table->text('description');
            $table->string('dimension')->nullable();
            $table->string('uom')->default('PC/S');
            
            // Inventory Quantities
            $table->integer('beginning_qty')->default(0)->comment('Beginning inventory');
            $table->integer('in_qty')->default(0)->comment('Incoming quantity');
            $table->integer('out_qty')->default(0)->comment('Outgoing quantity');
            $table->integer('theoretical_end_qty')->default(0)->comment('Calculated ending inventory');
            $table->integer('buffer_stocks')->default(0);
            $table->integer('current_qty')->default(0)->comment('Current actual quantity');
            $table->integer('ending_count')->default(0);
            $table->string('uom3')->nullable()->comment('Alternative UOM');
            
            // Variance Tracking
            $table->decimal('variance_amount', 12, 2)->nullable();
            $table->integer('variance_qty')->nullable()->comment('Variance in quantity');
            
            // Financial
            $table->string('currency')->default('PHP');
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->decimal('beginning_amount', 12, 2)->default(0);
            $table->decimal('in_amount', 12, 2)->default(0);
            $table->decimal('out_amount', 12, 2)->default(0);
            $table->decimal('end_amount', 12, 2)->default(0);
            
            // Age Tracking
            $table->date('last_in_date')->nullable();
            $table->date('older_date')->nullable();
            $table->integer('number_of_days')->nullable();
            $table->string('range')->nullable()->comment('Age range category');
            $table->integer('age_1_30_days')->default(0)->comment('1-30 days old');
            $table->integer('age_31_60_days')->default(0)->comment('31-60 days old');
            $table->integer('age_61_90_days')->default(0)->comment('61-90 days old');
            $table->integer('age_91_120_days')->default(0)->comment('91-120 days old');
            $table->integer('age_over_120_days')->default(0)->comment('Over 120 days old');
            
            $table->text('remarks')->nullable();
            
            // Audit Fields
            $table->foreignId('encoded_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('date_encoded')->useCurrent();
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('fg_code');
            $table->index('product_id');
            $table->index('product_code');
            $table->index('area');
            $table->index('current_qty');
            $table->index(['customer_name', 'current_qty']);
            $table->index('last_in_date');
            $table->index('range');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finished_goods');
    }
};