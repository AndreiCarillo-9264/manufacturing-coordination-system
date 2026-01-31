<?php
/**
 * Updated Database Fix Script for AI Assistant Chatbot
 * Handles foreign key constraints properly
 * 
 * USAGE:
 * 1. Delete the old migration file: 2026_01_31_000000_fix_ai_assistant_tables.php (if exists)
 * 2. Copy this file to: database/migrations/
 * 3. Rename it to: 2026_01_31_000001_fix_ai_assistant_tables_v2.php
 * 4. Run: php artisan migrate
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        try {
            // Step 1: Drop the chat_histories foreign key constraint if it exists
            if (Schema::hasTable('chat_histories')) {
                // Get all foreign keys for chat_histories
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'chat_histories' 
                    AND REFERENCED_TABLE_NAME = 'conversations'
                ");
                
                foreach ($foreignKeys as $fk) {
                    DB::statement("ALTER TABLE chat_histories DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
                }
                
                // Drop conversation_id column if exists
                if (Schema::hasColumn('chat_histories', 'conversation_id')) {
                    Schema::table('chat_histories', function (Blueprint $table) {
                        $table->dropIndex(['conversation_id']);
                        $table->dropColumn('conversation_id');
                    });
                }
            }
            
            // Step 2: Drop the conversations table
            Schema::dropIfExists('conversations');
            
            // Step 3: Create conversations table with proper structure
            Schema::create('conversations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('title', 255)->default('New Conversation');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                // Add indexes
                $table->index('user_id');
                $table->index(['user_id', 'is_active']);
                $table->index('updated_at');
            });
            
            // Step 4: Add foreign key to conversations table
            Schema::table('conversations', function (Blueprint $table) {
                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade');
            });
            
            // Step 5: Add conversation_id to chat_histories
            if (Schema::hasTable('chat_histories')) {
                Schema::table('chat_histories', function (Blueprint $table) {
                    $table->unsignedBigInteger('conversation_id')->nullable()->after('user_id');
                    $table->index('conversation_id');
                });
                
                // Step 6: Add foreign key to chat_histories
                Schema::table('chat_histories', function (Blueprint $table) {
                    $table->foreign('conversation_id')
                          ->references('id')
                          ->on('conversations')
                          ->onDelete('cascade');
                });
            }
            
            // Step 7: Migrate existing chat histories
            $this->migrateExistingChats();
            
        } finally {
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        try {
            // Remove foreign key and column from chat_histories
            if (Schema::hasTable('chat_histories')) {
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'chat_histories' 
                    AND REFERENCED_TABLE_NAME = 'conversations'
                ");
                
                foreach ($foreignKeys as $fk) {
                    DB::statement("ALTER TABLE chat_histories DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
                }
                
                if (Schema::hasColumn('chat_histories', 'conversation_id')) {
                    Schema::table('chat_histories', function (Blueprint $table) {
                        $table->dropIndex(['conversation_id']);
                        $table->dropColumn('conversation_id');
                    });
                }
            }
            
            // Drop conversations table
            Schema::dropIfExists('conversations');
            
        } finally {
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
    
    /**
     * Migrate existing chat histories to conversations
     */
    private function migrateExistingChats(): void
    {
        try {
            // Check if chat_histories table exists
            if (!Schema::hasTable('chat_histories')) {
                return;
            }
            
            // Get all unique users who have chat history
            $users = DB::table('chat_histories')
                ->select('user_id')
                ->distinct()
                ->pluck('user_id');
            
            foreach ($users as $userId) {
                // Check if user exists in users table
                $userExists = DB::table('users')->where('id', $userId)->exists();
                
                if (!$userExists) {
                    continue; // Skip if user doesn't exist
                }
                
                // Create a default conversation for each user
                $conversationId = DB::table('conversations')->insertGetId([
                    'user_id' => $userId,
                    'title' => 'Chat History',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Update all chat histories for this user
                DB::table('chat_histories')
                    ->where('user_id', $userId)
                    ->whereNull('conversation_id')
                    ->update(['conversation_id' => $conversationId]);
            }
            
        } catch (\Exception $e) {
            // Log error but don't fail the migration
            \Log::error('Error migrating existing chats: ' . $e->getMessage());
        }
    }
};