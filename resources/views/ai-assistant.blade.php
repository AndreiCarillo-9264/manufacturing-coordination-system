@extends('layouts.app')

@section('title', 'AI Assistant')

@section('page-icon')
    <i class="fas fa-robot"></i>
@endsection

@section('page-title')
    AI Assistant
@endsection

@section('page-description')
    Chat with the system assistant for forecasting, job orders, inventory, and deliveries.
@endsection

@section('content')
<div class="h-[calc(100vh-160px)] bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl shadow-xl overflow-hidden flex">
    <!-- Sidebar -->
    <aside class="w-80 bg-white border-r border-gray-200 flex flex-col">
        <!-- Sidebar Header -->
        <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-white to-gray-50">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-100">
                        <i class="fas fa-comments text-amber-700 text-xs"></i>
                    </span>
                    Conversations
                </h3>

                <button id="new-chat-btn" 
                        class="bg-amber-600 hover:bg-amber-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2 shadow-sm hover:shadow-md">
                    <i class="fas fa-plus"></i>
                    <span class="hidden sm:inline">New</span>
                </button>
            </div>
        </div>

        <!-- Conversation List -->
        <div id="conversation-list" class="flex-1 overflow-y-auto p-2 space-y-1">
            @if($conversations?->isNotEmpty())
                @foreach($conversations as $conv)
                    <div class="group cursor-pointer rounded-lg border border-transparent bg-gray-50 p-3 transition-all duration-200 hover:border-amber-200 hover:bg-amber-50"
                         onclick="window.aiAssistant?.loadConversation({{ $conv->id }})">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-800 group-hover:text-amber-900">
                                    {{ $conv->title ?: 'New Conversation' }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $conv->updated_at?->diffForHumans() ?? '' }}
                                </p>
                            </div>

                            <span class="flex-shrink-0 rounded-full bg-gray-200 px-2 py-1 text-xs text-gray-400 group-hover:bg-amber-200 group-hover:text-amber-800">
                                {{ $conv->messages_count ?? $conv->messages?->count() ?? '0' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="py-12 text-center">
                    <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                        <i class="fas fa-comments text-3xl text-gray-300"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-500">No conversations yet</p>
                    <p class="mt-1 text-xs text-gray-400">Start a new chat to begin</p>
                </div>
            @endif
        </div>
    </aside>

    <!-- Main Chat Area -->
    <div class="flex flex-1 flex-col bg-white">
        <!-- Chat Header -->
        <div class="border-b border-gray-200 bg-gradient-to-r from-amber-50 via-white to-gray-50 px-6 py-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="flex items-center gap-2 text-lg font-semibold text-gray-800">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gradient-to-br from-amber-500 to-amber-700">
                            <i class="fas fa-robot text-xs text-white"></i>
                        </span>
                        <span class="truncate">{{ $activeConversation->title ?? 'New Conversation' }}</span>
                    </h3>
                    <p class="mt-1 text-xs text-gray-500">
                        <i class="fas fa-lightbulb text-amber-500 mr-1"></i>
                        AI-powered assistance powered by Groq API
                    </p>
                </div>

                <span class="flex items-center gap-2 rounded-full bg-gradient-to-r from-green-50 to-green-100 px-4 py-2 text-xs font-medium text-green-700 border border-green-200">
                    <span class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
                    <span>Ready</span>
                </span>
            </div>
        </div>

        <!-- Messages Area -->
        <div id="chat-messages-full" class="flex-1 overflow-y-auto p-6 space-y-6">
            @if($chatHistory?->isNotEmpty())
                @foreach($chatHistory as $msg)
                    <div class="space-y-4 animate-fade-in">
                        <!-- User Message -->
                        <div class="flex justify-end">
                            <div class="max-w-xl">
                                <div class="rounded-2xl rounded-tr-sm bg-gradient-to-br from-amber-600 to-amber-700 p-4 text-white shadow-md hover:shadow-lg transition-shadow">
                                    <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ $msg->user_message }}</p>
                                </div>
                                <p class="mt-2 text-right text-xs text-gray-500">{{ $msg->created_at?->format('M d, H:i') }}</p>
                            </div>
                        </div>

                        <!-- AI Response -->
                        <div class="flex justify-start">
                            <div class="max-w-xl">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-amber-500 to-amber-700 shadow-sm">
                                        <i class="fas fa-robot text-xs text-white"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="rounded-2xl rounded-tl-sm border border-gray-200 bg-white p-4 shadow-sm hover:shadow-md transition-shadow">
                                            <p class="text-sm leading-relaxed text-gray-800 whitespace-pre-wrap">{{ $msg->ai_response }}</p>
                                        </div>
                                        <p class="mt-2 text-xs text-gray-500">AI Assistant</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <!-- Welcome / Empty State -->
                <div class="flex h-full items-center justify-center p-6">
                    <div class="text-center max-w-2xl">
                        <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-amber-100 to-amber-200 shadow-lg ring-8 ring-amber-50">
                            <i class="fas fa-robot text-5xl text-amber-700"></i>
                        </div>

                        <h3 class="mb-2 text-3xl font-bold text-gray-800">Welcome to AI Advisor</h3>
                        <p class="mb-8 text-center text-gray-600 leading-relaxed">
                            Ask me questions about forecasting, job orders, inventory, deliveries, and more. I'm here to help you make better decisions.
                        </p>

                        <!-- Suggested Prompts -->
                        <div class="mt-10">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Try asking about:</p>
                            <div class="grid grid-cols-2 gap-3">
                                <button onclick="document.getElementById('chat-input-full').value = 'What is our current inventory status?'; window.aiAssistant.sendMessage();" 
                                        class="group rounded-lg border border-gray-200 bg-white p-4 text-left transition-all hover:border-amber-300 hover:bg-amber-50 hover:shadow-md">
                                    <i class="fas fa-chart-line mb-3 text-2xl text-amber-600"></i>
                                    <p class="text-sm font-medium text-gray-700 group-hover:text-amber-800">Inventory Status</p>
                                    <p class="text-xs text-gray-500 mt-1">Check current stock levels</p>
                                </button>

                                <button onclick="document.getElementById('chat-input-full').value = 'Show me today\'s job orders'; window.aiAssistant.sendMessage();" 
                                        class="group rounded-lg border border-gray-200 bg-white p-4 text-left transition-all hover:border-amber-300 hover:bg-amber-50 hover:shadow-md">
                                    <i class="fas fa-clipboard-list mb-3 text-2xl text-amber-600"></i>
                                    <p class="text-sm font-medium text-gray-700 group-hover:text-amber-800">Job Orders</p>
                                    <p class="text-xs text-gray-500 mt-1">View today's production</p>
                                </button>

                                <button onclick="document.getElementById('chat-input-full').value = 'Pending deliveries status'; window.aiAssistant.sendMessage();" 
                                        class="group rounded-lg border border-gray-200 bg-white p-4 text-left transition-all hover:border-amber-300 hover:bg-amber-50 hover:shadow-md">
                                    <i class="fas fa-truck mb-3 text-2xl text-amber-600"></i>
                                    <p class="text-sm font-medium text-gray-700 group-hover:text-amber-800">Deliveries</p>
                                    <p class="text-xs text-gray-500 mt-1">Check delivery schedule</p>
                                </button>

                                <button onclick="document.getElementById('chat-input-full').value = 'Inventory forecast for next month'; window.aiAssistant.sendMessage();" 
                                        class="group rounded-lg border border-gray-200 bg-white p-4 text-left transition-all hover:border-amber-300 hover:bg-amber-50 hover:shadow-md">
                                    <i class="fas fa-crystal-ball mb-3 text-2xl text-amber-600"></i>
                                    <p class="text-sm font-medium text-gray-700 group-hover:text-amber-800">Forecasting</p>
                                    <p class="text-xs text-gray-500 mt-1">Predict future trends</p>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Input Area -->
        <div class="border-t border-gray-200 bg-gradient-to-b from-white to-gray-50 px-4 py-4">
            <div class="mx-auto max-w-4xl">
                <div class="flex items-end gap-3">
                    <div class="relative flex-1">
                        <textarea id="chat-input-full"
                                  rows="1"
                                  class="w-full resize-none rounded-xl border border-gray-300 bg-white p-4 pr-12 text-gray-800 placeholder-gray-400 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-0 transition-all duration-200"
                                  placeholder="Type your question... (Try: 'What's our inventory status?' or 'Job orders for today')"
                                  style="min-height: 56px; max-height: 120px;"
                        ></textarea>

                        <button class="absolute bottom-4 right-3 text-gray-400 hover:text-amber-600 transition-colors duration-200" title="Attach file">
                            <i class="fas fa-paperclip"></i>
                        </button>
                    </div>

                    <button id="chat-send-full"
                            class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 px-6 py-4 font-medium text-white shadow-md transition-all duration-200 hover:shadow-lg hover:from-amber-700 hover:to-amber-800 active:scale-95 group">
                        <span>Send</span>
                        <i class="fas fa-paper-plane transition-transform duration-200 group-hover:translate-x-0.5"></i>
                    </button>
                </div>

                <p class="mt-3 text-center text-xs text-gray-400">
                    <i class="fas fa-keyboard mr-1"></i>Press <kbd class="bg-gray-200 px-2 py-1 rounded text-xs">Enter</kbd> to send, <kbd class="bg-gray-200 px-2 py-1 rounded text-xs">Shift+Enter</kbd> for new line
                </p>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/ai-assistant-fullscreen-enhanced.js') }}"></script>
@endsection