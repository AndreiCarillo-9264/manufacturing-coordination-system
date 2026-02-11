<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('user_name')->nullable()->comment('Cached user name for records');
            $table->string('user_department')->nullable()->comment('Cached department for records');
            
            // Activity Details
            $table->string('log_name')->nullable()->comment('e.g., product, job_order');
            $table->text('description')->comment('What action was performed');
            $table->string('subject_type')->nullable()->comment('Model class name');
            $table->unsignedBigInteger('subject_id')->nullable()->comment('Model ID');
            $table->string('event')->nullable()->comment('created, updated, deleted, etc.');
            
            // Changes Tracking
            $table->json('properties')->nullable()->comment('Old and new values');
            $table->json('old_values')->nullable()->comment('Values before change');
            $table->json('new_values')->nullable()->comment('Values after change');
            
            // Request Information
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('method')->nullable()->comment('GET, POST, PUT, DELETE');
            $table->text('url')->nullable();
            
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('log_name');
            $table->index(['subject_type', 'subject_id']);
            $table->index('event');
            $table->index('created_at');
            $table->index(['user_id', 'created_at']);
            $table->index(['subject_type', 'subject_id', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};