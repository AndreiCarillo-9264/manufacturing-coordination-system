<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\ChatHistory;
use App\Models\Conversation;
use App\Models\JobOrder;
use App\Models\FinishedGood;
use App\Models\Product;
use App\Models\DeliverySchedule;

class AIAssistantController extends Controller
{
    public function fullscreen()
    {
        try {
            $conversations = Conversation::where('user_id', Auth::id())
                ->withCount('messages')
                ->orderBy('updated_at', 'desc')
                ->get();
                
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
        }
    }
    
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'conversation_id' => 'nullable|exists:conversations,id',
        ]);

        try {
            // Get or create conversation
            $conversationId = $request->conversation_id;
            if (!$conversationId) {
                $conversation = Conversation::create([
                    'user_id' => Auth::id(),
                    'title' => substr($request->message, 0, 50),
                    'is_active' => true,
                ]);
                $conversationId = $conversation->id;
            } else {
                $conversation = Conversation::findOrFail($conversationId);
                $conversation->touch(); // Update timestamp
            }

            // Get system context for AI
            $systemContext = $this->getSystemContext($request->message);
            
            $groqKey = env('GROQ_API_KEY');
            $openaiKey = env('OPENAI_API_KEY');
            
            if (!empty($groqKey)) {
                return $this->chatWithGroq($request->message, $systemContext, $conversationId);
            } elseif (!empty($openaiKey)) {
                return $this->chatWithOpenAI($request->message, $systemContext, $conversationId);
            } else {
                return $this->chatMockResponse($request->message, $conversationId);
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
    
    private function getSystemContext($userMessage)
    {
        $context = [];
        $lowerMessage = strtolower($userMessage);
        
        // Check if message is about forecasting
        if (preg_match('/forecast|predict|trend|future|projection/i', $userMessage)) {
            $context['forecasting_data'] = $this->getForecastingData();
        }
        
        // Check if message is about inventory
        if (preg_match('/inventory|stock|product|item/i', $userMessage)) {
            $context['inventory_summary'] = $this->getInventorySummary();
        }
        
        // Check if message is about job orders
        if (preg_match('/job order|order|jo|production/i', $userMessage)) {
            $context['job_order_summary'] = $this->getJobOrderSummary();
        }
        
        // Check if message is about deliveries
        if (preg_match('/delivery|shipping|logistics|distribution/i', $userMessage)) {
            $context['delivery_summary'] = $this->getDeliverySummary();
        }
        
        // Check if asking how to do something
        if (preg_match('/how to|how do i|guide|tutorial|help me/i', $userMessage)) {
            $context['system_guide'] = $this->getSystemGuide($userMessage);
        }
        
        return $context;
    }
    
    private function getForecastingData()
    {
        try {
            // Get historical data for the last 90 days
            $jobOrders = JobOrder::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(quantity) as total_quantity')
            )
            ->where('created_at', '>=', now()->subDays(90))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();
            
            $production = DB::table('transfers')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(quantity_transferred) as total_produced')
                )
                ->where('created_at', '>=', now()->subDays(90))
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->get();
            
            $deliveries = DeliverySchedule::select(
                DB::raw('DATE(delivery_date) as date'),
                DB::raw('COUNT(*) as delivery_count')
            )
            ->where('delivery_date', '>=', now()->subDays(90))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();
            
            // Calculate trends
            $avgOrdersPerDay = $jobOrders->avg('order_count') ?? 0;
            $avgProductionPerDay = $production->avg('total_produced') ?? 0;
            $avgDeliveriesPerDay = $deliveries->avg('delivery_count') ?? 0;
            
            return [
                'last_90_days' => [
                    'orders' => $jobOrders->count(),
                    'total_quantity_ordered' => $jobOrders->sum('total_quantity'),
                    'avg_orders_per_day' => round($avgOrdersPerDay, 2),
                ],
                'production' => [
                    'total_produced' => $production->sum('total_produced'),
                    'avg_production_per_day' => round($avgProductionPerDay, 2),
                ],
                'deliveries' => [
                    'total_deliveries' => $deliveries->sum('delivery_count'),
                    'avg_deliveries_per_day' => round($avgDeliveriesPerDay, 2),
                ],
                'top_products' => $this->getTopProducts(),
            ];
        } catch (\Exception $e) {
            Log::error('Error getting forecasting data: ' . $e->getMessage());
            return null;
        }
    }
    
    private function getTopProducts()
    {
        try {
            return JobOrder::select('product_code', DB::raw('SUM(quantity) as total_ordered'))
                ->where('created_at', '>=', now()->subDays(90))
                ->groupBy('product_code')
                ->orderBy('total_ordered', 'desc')
                ->limit(5)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
    
    private function getInventorySummary()
    {
        try {
            $lowStock = FinishedGood::where('current_qty', '<', 50)
                ->count();
            
            $outOfStock = FinishedGood::where('current_qty', '<=', 0)->count();
            
            $totalProducts = FinishedGood::count();
            
            $totalValue = FinishedGood::sum(DB::raw('current_qty * selling_price'));
            
            return [
                'total_products' => $totalProducts,
                'low_stock_items' => $lowStock,
                'out_of_stock_items' => $outOfStock,
                'total_inventory_value' => round($totalValue, 2),
                'stock_status' => [
                    'healthy' => $totalProducts - $lowStock - $outOfStock,
                    'low' => $lowStock,
                    'out' => $outOfStock,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error getting inventory summary: ' . $e->getMessage());
            return null;
        }
    }
    
    private function getJobOrderSummary()
    {
        try {
            $statuses = JobOrder::select('jo_status', DB::raw('COUNT(*) as count'))
                ->groupBy('jo_status')
                ->get()
                ->pluck('count', 'jo_status')
                ->toArray();
            
            $recentOrders = JobOrder::orderBy('created_at', 'desc')
                ->limit(5)
                ->get(['jo_number', 'product_code', 'quantity', 'jo_status', 'created_at'])
                ->toArray();
            
            return [
                'status_breakdown' => $statuses,
                'total_orders' => array_sum($statuses),
                'recent_orders' => $recentOrders,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting job order summary: ' . $e->getMessage());
            return null;
        }
    }
    
    private function getDeliverySummary()
    {
        try {
            $pending = DeliverySchedule::where('ds_status', 'ON SCHEDULE')->count();
            $completed = DeliverySchedule::where('ds_status', 'DELIVERED')->count();
            
            $upcomingDeliveries = DeliverySchedule::where('delivery_date', '>=', now())
                ->where('ds_status', 'ON SCHEDULE')
                ->orderBy('delivery_date')
                ->limit(5)
                ->get(['delivery_date', 'customer_name', 'product_code', 'quantity'])
                ->toArray();
            
            return [
                'pending_deliveries' => $pending,
                'completed_deliveries' => $completed,
                'upcoming_deliveries' => $upcomingDeliveries,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting delivery summary: ' . $e->getMessage());
            return null;
        }
    }
    
    private function getSystemGuide($message)
    {
        $guides = [
            'create order' => "To create a new job order:\n1. Navigate to 'Job Orders' in the sidebar\n2. Click 'Create New Job Order'\n3. Fill in customer details, product selection, and quantity\n4. Review and submit\n5. The order will be in 'pending' status awaiting approval",
            
            'track inventory' => "To track inventory:\n1. Go to 'Inventory Dashboard' for an overview\n2. Visit 'Finished Goods' to see detailed stock levels\n3. Use 'Actual Inventory' to perform physical counts\n4. Low stock items are automatically highlighted",
            
            'schedule delivery' => "To schedule a delivery:\n1. Go to 'Delivery Schedules'\n2. Click 'Create New Schedule'\n3. Select the job order (must be approved/in progress)\n4. Set delivery date and customer details\n5. Track status until marked as delivered",
            
            'view reports' => "To view reports:\n1. Check the dashboard for quick insights\n2. Sales Dashboard shows order trends\n3. Production Dashboard shows manufacturing data\n4. Inventory Dashboard shows stock levels\n5. Custom reports available in the Reports section",
        ];
        
        $lowerMessage = strtolower($message);
        foreach ($guides as $keyword => $guide) {
            if (strpos($lowerMessage, $keyword) !== false) {
                return $guide;
            }
        }
        
        return null;
    }
    
    private function chatWithGroq($userMessage, $systemContext, $conversationId)
    {
        try {
            // Build enhanced system prompt with context
            $systemPrompt = $this->buildSystemPrompt($systemContext);
            
            // Get conversation history for context
            $conversationHistory = $this->getConversationHistory($conversationId, 5);
            
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt]
            ];
            
            // Add conversation history
            foreach ($conversationHistory as $msg) {
                $messages[] = ['role' => 'user', 'content' => $msg->user_message];
                $messages[] = ['role' => 'assistant', 'content' => $msg->ai_response];
            }
            
            // Add current message
            $messages[] = ['role' => 'user', 'content' => $userMessage];
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama-3.3-70b-versatile',
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 1500,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $aiResponse = trim($data['choices'][0]['message']['content']);
                
                ChatHistory::create([
                    'user_id' => Auth::id(),
                    'conversation_id' => $conversationId,
                    'user_message' => $userMessage,
                    'ai_response' => $aiResponse,
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => $aiResponse,
                    'conversation_id' => $conversationId,
                ]);
            }

            Log::error('Groq API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'AI service temporarily unavailable. Please try again.',
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Groq Connection Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    private function buildSystemPrompt($context)
    {
        $basePrompt = "You are an AI assistant for CPC Nexboard, a manufacturing coordination system. You help users with:\n\n";
        $basePrompt .= "1. FORECASTING & ANALYTICS: Analyze trends, predict future demand, identify patterns\n";
        $basePrompt .= "2. SYSTEM OPERATIONS: Guide users on creating orders, managing inventory, scheduling deliveries\n";
        $basePrompt .= "3. DATA INSIGHTS: Provide summaries of job orders, production, inventory, and logistics\n";
        $basePrompt .= "4. PROBLEM SOLVING: Help troubleshoot issues and optimize workflows\n\n";
        
        if (!empty($context)) {
            $basePrompt .= "CURRENT SYSTEM DATA:\n";
            
            if (isset($context['forecasting_data'])) {
                $data = $context['forecasting_data'];
                $basePrompt .= "\nFORECASTING DATA (Last 90 days):\n";
                $basePrompt .= "- Total Orders: {$data['last_90_days']['orders']}\n";
                $basePrompt .= "- Avg Orders/Day: {$data['last_90_days']['avg_orders_per_day']}\n";
                $basePrompt .= "- Total Quantity Ordered: {$data['last_90_days']['total_quantity_ordered']}\n";
                $basePrompt .= "- Avg Production/Day: {$data['production']['avg_production_per_day']}\n";
                $basePrompt .= "- Avg Deliveries/Day: {$data['deliveries']['avg_deliveries_per_day']}\n";
                
                if (!empty($data['top_products'])) {
                    $basePrompt .= "\nTop Products:\n";
                    foreach ($data['top_products'] as $product) {
                        $basePrompt .= "  - {$product['product_code']}: {$product['total_ordered']} units\n";
                    }
                }
                
                $basePrompt .= "\nUse this data to provide forecasts, identify trends, and make recommendations.\n";
            }
            
            if (isset($context['inventory_summary'])) {
                $inv = $context['inventory_summary'];
                $basePrompt .= "\nINVENTORY STATUS:\n";
                $basePrompt .= "- Total Products: {$inv['total_products']}\n";
                $basePrompt .= "- Low Stock Items: {$inv['low_stock_items']}\n";
                $basePrompt .= "- Out of Stock: {$inv['out_of_stock_items']}\n";
                $basePrompt .= "- Total Value: \${$inv['total_inventory_value']}\n";
            }
            
            if (isset($context['job_order_summary'])) {
                $jo = $context['job_order_summary'];
                $basePrompt .= "\nJOB ORDER STATUS:\n";
                $basePrompt .= "- Total Orders: {$jo['total_orders']}\n";
                foreach ($jo['status_breakdown'] as $status => $count) {
                    $basePrompt .= "  - {$status}: {$count}\n";
                }
            }
            
            if (isset($context['delivery_summary'])) {
                $del = $context['delivery_summary'];
                $basePrompt .= "\nDELIVERY STATUS:\n";
                $basePrompt .= "- Pending: {$del['pending_deliveries']}\n";
                $basePrompt .= "- Completed: {$del['completed_deliveries']}\n";
            }
            
            if (isset($context['system_guide'])) {
                $basePrompt .= "\nSYSTEM GUIDE:\n{$context['system_guide']}\n";
            }
        }
        
        $basePrompt .= "\nProvide clear, actionable insights. When forecasting, explain your reasoning and methodology. Keep responses concise but informative.";
        
        return $basePrompt;
    }
    
    private function getConversationHistory($conversationId, $limit = 5)
    {
        return ChatHistory::where('conversation_id', $conversationId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse();
    }
    
    private function chatWithOpenAI($userMessage, $systemContext, $conversationId)
    {
        // Similar implementation to Groq but for OpenAI
        // Code structure same as chatWithGroq but different endpoint
        $systemPrompt = $this->buildSystemPrompt($systemContext);
        $conversationHistory = $this->getConversationHistory($conversationId, 5);
        
        $messages = [['role' => 'system', 'content' => $systemPrompt]];
        
        foreach ($conversationHistory as $msg) {
            $messages[] = ['role' => 'user', 'content' => $msg->user_message];
            $messages[] = ['role' => 'assistant', 'content' => $msg->ai_response];
        }
        
        $messages[] = ['role' => 'user', 'content' => $userMessage];
        
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 1500,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $aiResponse = trim($data['choices'][0]['message']['content']);
                
                ChatHistory::create([
                    'user_id' => Auth::id(),
                    'conversation_id' => $conversationId,
                    'user_message' => $userMessage,
                    'ai_response' => $aiResponse,
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => $aiResponse,
                    'conversation_id' => $conversationId,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'AI service temporarily unavailable.',
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('OpenAI Connection Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    private function chatMockResponse($userMessage, $conversationId)
    {
        $aiResponse = "I'm running in demo mode. To enable full AI capabilities with forecasting and system knowledge, please configure GROQ_API_KEY or OPENAI_API_KEY in your .env file.\n\n";
        $aiResponse .= "I can help you with:\n";
        $aiResponse .= "• Forecasting demand and trends\n";
        $aiResponse .= "• Creating and managing job orders\n";
        $aiResponse .= "• Tracking inventory levels\n";
        $aiResponse .= "• Scheduling deliveries\n";
        $aiResponse .= "• Analyzing system data\n\n";
        $aiResponse .= "Your question: " . substr($userMessage, 0, 100);
        
        ChatHistory::create([
            'user_id' => Auth::id(),
            'conversation_id' => $conversationId,
            'user_message' => $userMessage,
            'ai_response' => $aiResponse,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => $aiResponse,
            'conversation_id' => $conversationId,
        ]);
    }
    
    // Conversation management endpoints
    
    public function getConversations()
    {
        $conversations = Conversation::where('user_id', Auth::id())
            ->withCount('messages')
            ->orderBy('updated_at', 'desc')
            ->get();
            
        return response()->json(['conversations' => $conversations]);
    }
    
    public function getActiveConversation()
    {
        $conversation = Conversation::where('user_id', Auth::id())
            ->where('is_active', true)
            ->first();
            
        if (!$conversation) {
            $conversation = Conversation::create([
                'user_id' => Auth::id(),
                'title' => 'New Conversation',
                'is_active' => true,
            ]);
        }
        
        return response()->json(['conversation' => $conversation]);
    }
    
    public function createConversation(Request $request)
    {
        $conversation = Conversation::create([
            'user_id' => Auth::id(),
            'title' => $request->title ?? 'New Conversation',
            'is_active' => true,
        ]);
        
        // Deactivate other conversations
        Conversation::where('user_id', Auth::id())
            ->where('id', '!=', $conversation->id)
            ->update(['is_active' => false]);
        
        return response()->json(['conversation' => $conversation]);
    }
    
    public function getConversationMessages($conversationId)
    {
        $messages = ChatHistory::where('conversation_id', $conversationId)
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'asc')
            ->get();
            
        return response()->json($messages);
    }
    
    public function deleteConversation($conversationId)
    {
        $conversation = Conversation::where('id', $conversationId)
            ->where('user_id', Auth::id())
            ->firstOrFail();
            
        $conversation->messages()->delete();
        $conversation->delete();
        
        return response()->json(['success' => true]);
    }
    
    // Legacy endpoints
    public function history()
    {
        try {
            $history = ChatHistory::where('user_id', Auth::id())
                ->orderBy('created_at', 'asc')
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