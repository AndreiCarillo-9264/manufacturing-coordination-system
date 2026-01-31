@extends('layouts.app')

@section('title', 'AI Assistant - CPC Nexboard')
@section('page-title', 'AI Assistant')
@section('page-description', 'Ask questions about CPC Nexboard operations, reports, inventory, job orders, and more')

@section('content')
<div class="flex h-[calc(100vh-180px)] gap-4">
    <!-- Conversation Sidebar -->
    <div class="w-80 bg-white rounded-lg shadow-sm flex flex-col">
        <div class="p-4 border-b border-gray-200">
            <button id="new-chat-btn" class="w-full px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-700 transition flex items-center justify-center gap-2">
                <i class="fas fa-plus"></i>
                <span>New Conversation</span>
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto p-4">
            <p class="text-xs text-gray-500 uppercase mb-3 tracking-wider">Recent Conversations</p>
            <div id="conversation-list">
                <!-- Conversations will be loaded here dynamically -->
                <div class="text-center py-8 text-gray-400">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                    <p class="text-sm">Loading conversations...</p>
                </div>
            </div>
        </div>
        
        <div class="p-4 border-t border-gray-200 bg-gray-50">
            <div class="text-xs text-gray-500 text-center">
                <i class="fas fa-robot mr-1"></i>
                Powered by {{ env('GROQ_API_KEY') ? 'Groq AI' : (env('OPENAI_API_KEY') ? 'OpenAI' : 'Demo Mode') }}
            </div>
        </div>
    </div>

    <!-- Chat Area -->
    <div class="flex-1 bg-white rounded-lg shadow-sm flex flex-col">
        <!-- Chat Header -->
        <div class="bg-gray-900 text-white p-4 rounded-t-lg flex justify-between items-center">
            <div class="flex items-center gap-3">
                <i class="fas fa-robot text-2xl"></i>
                <div>
                    <h2 class="font-bold text-lg">AI Assistant</h2>
                    <p class="text-xs text-gray-300">Helping with CPC Nexboard operations</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-green-600 text-white text-xs rounded-full flex items-center gap-1">
                    <i class="fas fa-circle text-xs"></i>
                    Online
                </span>
            </div>
        </div>

        <!-- Chat Messages -->
        <div class="flex-1 overflow-y-auto p-6 bg-gray-50" id="chat-messages-full">
            <div class="flex items-center justify-center h-full">
                <div class="text-center text-gray-400">
                    <i class="fas fa-robot text-6xl mb-4"></i>
                    <p class="text-lg font-semibold">Welcome to AI Assistant</p>
                    <p class="text-sm mt-2">Ask about forecasting, job orders, inventory, or system operations</p>
                    <div class="mt-6 grid grid-cols-2 gap-3 max-w-md mx-auto">
                        <div class="p-3 bg-white rounded-lg border border-gray-200 text-left cursor-pointer hover:bg-gray-50" onclick="document.getElementById('chat-input-full').value='Can you forecast sales for next month?'; document.getElementById('chat-send-full').click();">
                            <i class="fas fa-chart-line text-blue-600 mb-2"></i>
                            <p class="text-xs font-semibold text-gray-700">Forecast Sales</p>
                        </div>
                        <div class="p-3 bg-white rounded-lg border border-gray-200 text-left cursor-pointer hover:bg-gray-50" onclick="document.getElementById('chat-input-full').value='Show me inventory status'; document.getElementById('chat-send-full').click();">
                            <i class="fas fa-boxes text-green-600 mb-2"></i>
                            <p class="text-xs font-semibold text-gray-700">Inventory Status</p>
                        </div>
                        <div class="p-3 bg-white rounded-lg border border-gray-200 text-left cursor-pointer hover:bg-gray-50" onclick="document.getElementById('chat-input-full').value='How do I create a new job order?'; document.getElementById('chat-send-full').click();">
                            <i class="fas fa-clipboard-list text-purple-600 mb-2"></i>
                            <p class="text-xs font-semibold text-gray-700">Create Job Order</p>
                        </div>
                        <div class="p-3 bg-white rounded-lg border border-gray-200 text-left cursor-pointer hover:bg-gray-50" onclick="document.getElementById('chat-input-full').value='What are the pending deliveries?'; document.getElementById('chat-send-full').click();">
                            <i class="fas fa-truck text-orange-600 mb-2"></i>
                            <p class="text-xs font-semibold text-gray-700">Deliveries</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Input -->
        <div class="p-4 border-t border-gray-200 bg-white rounded-b-lg">
            <div class="flex gap-3">
                <input 
                    id="chat-input-full" 
                    type="text" 
                    placeholder="Ask about forecasting, job orders, inventory, deliveries..." 
                    class="flex-1 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-900 text-sm"
                    autocomplete="off"
                >
                <button 
                    id="chat-send-full" 
                    class="px-6 bg-gray-900 text-white rounded-lg hover:bg-gray-700 transition flex items-center gap-2 font-medium"
                >
                    <i class="fas fa-paper-plane"></i>
                    <span>Send</span>
                </button>
            </div>
            <div class="mt-2 text-xs text-gray-500 flex items-center gap-4">
                <span><i class="fas fa-lightbulb mr-1"></i> Try asking about trends or forecasts</span>
                <span><i class="fas fa-keyboard mr-1"></i> Press Enter to send</span>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/ai-assistant-fullscreen-enhanced.js') }}"></script>
@endsection