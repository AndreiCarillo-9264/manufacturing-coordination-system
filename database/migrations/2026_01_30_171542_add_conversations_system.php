<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // First, create the conversations table
        if (!Schema::hasTable('conversations')) {
            Schema::create('conversations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('title')->default('New Conversation');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade');
                
                $table->index('user_id');
                $table->index(['user_id', 'is_active']);
            });
        }
        
        // Then add conversation_id to chat_histories if it doesn't exist
        if (!Schema::hasColumn('chat_histories', 'conversation_id')) {
            Schema::table('chat_histories', function (Blueprint $table) {
                $table->unsignedBigInteger('conversation_id')->nullable()->after('user_id');
                
                $table->index('conversation_id');
            });
            
            // Add foreign key separately to avoid issues
            Schema::table('chat_histories', function (Blueprint $table) {
                $table->foreign('conversation_id')
                      ->references('id')
                      ->on('conversations')
                      ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('chat_histories', 'conversation_id')) {
            Schema::table('chat_histories', function (Blueprint $table) {
                $table->dropForeign(['conversation_id']);
                $table->dropColumn('conversation_id');
            });
        }
        
        Schema::dropIfExists('conversations');
    }
};

