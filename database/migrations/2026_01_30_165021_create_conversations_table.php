<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
        
        // Update chat_histories table to include conversation_id
        Schema::table('chat_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('conversation_id')->nullable()->after('user_id');
            
            $table->foreign('conversation_id')
                  ->references('id')
                  ->on('conversations')
                  ->onDelete('cascade');
            
            $table->index('conversation_id');
        });
    }

    public function down(): void
    {
        Schema::table('chat_histories', function (Blueprint $table) {
            $table->dropForeign(['conversation_id']);
            $table->dropColumn('conversation_id');
        });
        
        Schema::dropIfExists('conversations');
    }
};