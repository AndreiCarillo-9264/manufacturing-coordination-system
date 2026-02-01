<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\ChatHistory;

class AIAssistantController extends Controller
{
    public function fullscreen()
    {
        try {
            $chatHistory = ChatHistory::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->take(50)
                ->get();
                
<<<<<<< HEAD
            // Determine active conversation (create default if none)
            $activeConversation = Conversation::where('user_id', Auth::id())
                ->where('is_active', true)
                ->first();

            if (! $activeConversation && $conversations->isNotEmpty()) {
                $activeConversation = $conversations->first();
            }

            // Load chat history for the active conversation or an empty collection
            if ($activeConversation) {
                $chatHistory = ChatHistory::where('conversation_id', $activeConversation->id)
                    ->orderBy('created_at', 'asc')
                    ->get();
            } else {
                $chatHistory = collect([]);
            }

            return view('ai-assistant', compact('conversations', 'chatHistory', 'activeConversation'));
        } catch (\Exception $e) {
            Log::error('Error loading AI assistant: ' . $e->getMessage());
            return view('ai-assistant', ['conversations' => collect([]), 'chatHistory' => collect([])]);
=======
            return view('ai-assistant', compact('chatHistory'));
        } catch (\Exception $e) {
            Log::error('Error loading chat history: ' . $e->getMessage());
            return view('ai-assistant', ['chatHistory' => collect([])]);
>>>>>>> parent of 4b3ed60 (enhanced the chatbot capability)
        }
    }
    
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        try {
            $groqKey = env('GROQ_API_KEY');
            $openaiKey = env('OPENAI_API_KEY');
            
            if (!empty($groqKey)) {
                return $this->chatWithGroq($request->message);
            } elseif (!empty($openaiKey)) {
                return $this->chatWithOpenAI($request->message);
            } else {
                return $this->chatMockResponse($request->message);
            }
            
        } catch (\Exception $e) {
            Log::error('AI Assistant Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    private function chatWithGroq($userMessage)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama-3.3-70b-versatile',
                'messages' => [
                    [
                        'role' => 'system', 
                        'content' => 'You are a helpful AI assistant for CPC Nexboard, a manufacturing coordination system. Assist with questions on sales, production, inventory, logistics, dashboards, job orders, distributions, and operations. Provide clear, step-by-step guidance. Keep responses concise and professional.'
                    ],
                    [
                        'role' => 'user', 
                        'content' => $userMessage
                    ],
                ],
                'temperature' => 0.7,
                'max_tokens' => 800,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $aiResponse = trim($data['choices'][0]['message']['content']);
                
                ChatHistory::create([
                    'user_id' => Auth::id(),
                    'user_message' => $userMessage,
                    'ai_response' => $aiResponse,
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => $aiResponse,
                ]);
            }

            Log::error('Groq API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Groq API Error: ' . $response->body(),
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Groq Connection Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    private function chatWithOpenAI($userMessage)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system', 
                        'content' => 'You are a helpful AI assistant for CPC Nexboard, a manufacturing coordination system. Assist with questions on sales, production, inventory, logistics, dashboards, job orders, distributions, and operations. Provide clear, step-by-step guidance. Keep responses concise and professional.'
                    ],
                    [
                        'role' => 'user', 
                        'content' => $userMessage
                    ],
                ],
                'temperature' => 0.7,
                'max_tokens' => 800,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $aiResponse = trim($data['choices'][0]['message']['content']);
                
                // Save to database
                ChatHistory::create([
                    'user_id' => Auth::id(),
                    'user_message' => $userMessage,
                    'ai_response' => $aiResponse,
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => $aiResponse,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'OpenAI API Error: ' . $response->body(),
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('OpenAI Connection Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    private function chatMockResponse($userMessage)
    {
        $responses = [
            'hello' => 'Hello! Welcome to CPC Nexboard AI Assistant. How can I help you today?',
            'help' => 'I can assist you with Sales, Production, Inventory, Logistics, Job Orders, and Distributions. What would you like to know?',
            'sales' => 'The Sales module helps you manage customer orders and job orders. You can create, track, and manage sales transactions.',
            'production' => 'The Production module tracks manufacturing processes and finished goods inventory.',
            'inventory' => 'The Inventory module helps you manage stock levels, track products, and monitor inventory movements.',
            'logistics' => 'The Logistics module handles distribution and inventory transfers between locations.',
        ];
        
        $lowerMessage = strtolower($userMessage);
        $aiResponse = 'I can help you with CPC Nexboard operations. Please ask about Sales, Production, Inventory, or Logistics.';
        
        foreach ($responses as $key => $response) {
            if (strpos($lowerMessage, $key) !== false) {
                $aiResponse = $response;
                break;
            }
        }
        
        ChatHistory::create([
            'user_id' => Auth::id(),
            'user_message' => $userMessage,
            'ai_response' => $aiResponse . ' (Note: Using demo mode - configure GROQ_API_KEY or OPENAI_API_KEY in .env for AI responses)',
        ]);
        
        return response()->json([
            'success' => true,
            'message' => $aiResponse . "\n\n💡 Demo Mode: Configure GROQ_API_KEY in your .env file for AI-powered responses.",
        ]);
    }
    
    public function history()
    {
        try {
            $history = ChatHistory::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->take(50)
                ->get();
                
            return response()->json($history);
        } catch (\Exception $e) {
            Log::error('Error fetching history: ' . $e->getMessage());
            return response()->json([]);
        }
    }
    
    public function clearHistory()
    {
        try {
            ChatHistory::where('user_id', Auth::id())->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Chat history cleared successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error clearing history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear history.'
            ], 500);
        }
    }
}