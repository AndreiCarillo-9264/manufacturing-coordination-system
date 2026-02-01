@extends('layouts.app')

@section('title', 'AI Assistant')
@section('page-icon') <i class="fas fa-robot"></i> @endsection
@section('page-title', 'AI Assistant')
@section('page-description', 'Ask questions about CPC Nexboard operations, reports, inventory, job orders, and more')

@section('content')
<div class="h-full flex flex-col lg:flex-row gap-6 overflow-hidden">
    <!-- Sidebar - Chat History -->
    <div class="lg:w-80 bg-white rounded-xl shadow-md border border-gray-100 flex flex-col overflow-hidden lg:shrink-0">
        <div class="shrink-0 p-5 border-b bg-gray-900 text-white">
            <h2 class="font-semibold text-lg flex items-center gap-2">
                <i class="fas fa-history"></i>
                Recent Conversations
            </h2>
            <p class="text-xs text-gray-400 mt-1">Your chat history</p>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-3">
            @forelse($chatHistory as $chat)
                <div class="p-3.5 bg-gray-50 hover:bg-gray-100 rounded-lg cursor-pointer transition-all duration-150 border border-transparent hover:border-gray-200"
                     onclick="scrollToChat({{ $chat->id }})">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ Str::limit($chat->user_message, 45) }}</p>
                    <p class="text-xs text-gray-500 mt-1.5">{{ $chat->created_at->diffForHumans() }}</p>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center h-full text-gray-400 py-10">
                    <i class="fas fa-comment-slash text-5xl mb-4 opacity-60"></i>
                    <p class="text-base font-medium">No conversations yet</p>
                    <p class="text-sm mt-2">Start asking questions above</p>
                </div>
            @endforelse
        </div>

        <div class="shrink-0 p-4 border-t bg-gray-50">
            <button onclick="clearAllHistory()"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-700 font-medium rounded-lg transition border border-red-200">
                <i class="fas fa-trash-alt"></i>
                Clear All History
            </button>
        </div>
    </div>

    <!-- Main Chat Area -->
    <div class="flex-1 flex flex-col bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden min-h-0">
        <!-- Header -->
        <div class="shrink-0 p-5 border-b bg-gray-900 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center text-xl">
                    <i class="fas fa-robot"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold">AI Assistant</h1>
                    <p class="text-sm text-gray-300">Helping with CPC Nexboard operations</p>
                </div>
            </div>

            <button onclick="exportChat()"
                    class="flex items-center gap-2 px-4 py-2 bg-gray-800 hover:bg-gray-700 rounded-lg transition text-sm font-medium">
                <i class="fas fa-download"></i>
                Export Chat
            </button>
        </div>

        <!-- Messages - this is the scrollable part -->
        <div class="flex-1 overflow-y-auto p-6 bg-gray-50 space-y-8" id="chat-messages-full">
            @forelse($chatHistory as $chat)
                <div class="chat-item" data-chat-id="{{ $chat->id }}">
                    <!-- User Message -->
                    <div class="flex justify-end mb-5">
                        <div class="max-w-[75%] lg:max-w-[65%]">
                            <div class="inline-block p-4 rounded-2xl bg-gray-900 text-white rounded-br-none shadow-sm">
                                <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ $chat->user_message }}</p>
                            </div>
                            <p class="text-xs text-gray-500 mt-2 text-right">
                                {{ $chat->created_at->format('M d, Y • h:i A') }}
                            </p>
                        </div>
                    </div>

                    <!-- AI Response -->
                    <div class="flex justify-start">
                        <div class="max-w-[75%] lg:max-w-[65%]">
                            <div class="flex gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white shrink-0 mt-1">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div class="flex-1 p-4 rounded-2xl bg-white border border-gray-200 rounded-bl-none shadow-sm">
                                    <p class="text-sm text-gray-800 leading-relaxed whitespace-pre-wrap">{{ $chat->ai_response }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center h-full text-gray-400">
                    <div class="text-center">
                        <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gray-100 flex items-center justify-center">
                            <i class="fas fa-comments text-4xl text-gray-400"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">Welcome to AI Assistant</h3>
                        <p class="text-base">Ask me anything about job orders, inventory, deliveries, production, reports, or system usage.</p>
                        <p class="text-sm mt-3 text-gray-500">Type your question below to begin</p>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Input Area - always at bottom -->
        <div class="shrink-0 p-5 border-t border-gray-200 bg-white">
            <div class="flex gap-3">
                <textarea id="chat-input-full"
                          placeholder="Ask about job orders, inventory, deliveries, production... (Shift + Enter for new line)"
                          class="flex-1 p-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent text-base resize-none min-h-[56px] max-h-[140px]"
                          onkeydown="if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); document.getElementById('chat-send-full').click(); }"></textarea>
                <button id="chat-send-full"
                        class="px-7 bg-gray-900 hover:bg-gray-800 text-white rounded-xl transition flex items-center gap-2 font-medium shadow-sm">
                    <i class="fas fa-paper-plane"></i>
                    Send
                </button>
            </div>
            <p class="text-xs text-gray-500 mt-3 text-center">
                Responses are generated by AI • May contain inaccuracies
            </p>
        </div>
    </div>
</div>

<script>
// Scroll to specific chat message
function scrollToChat(chatId) {
    const el = document.querySelector(`[data-chat-id="${chatId}"]`);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        el.classList.add('ring-2', 'ring-indigo-500', 'ring-offset-2');
        setTimeout(() => el.classList.remove('ring-2', 'ring-indigo-500', 'ring-offset-2'), 2500);
    }
}

// Clear all chat history
async function clearAllHistory() {
    if (!confirm('Clear ALL chat history? This action cannot be undone.')) return;

    try {
        const res = await fetch('/ai-assistant/history', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        if (res.ok) location.reload();
        else alert('Failed to clear history');
    } catch (err) {
        console.error(err);
        alert('Error clearing history');
    }
}

// Export current chat as text file
function exportChat() {
    const messages = document.querySelectorAll('.chat-item');
    let content = 'CPC Nexboard AI Assistant - Chat Export\n';
    content += '=====================================\n\n';

    messages.forEach(msg => {
        const userMsg = msg.querySelector('.bg-gray-900 p')?.textContent?.trim();
        const aiMsg   = msg.querySelector('.bg-white p')?.textContent?.trim();
        const time    = msg.querySelector('.text-gray-500')?.textContent?.trim();

        if (userMsg && aiMsg) {
            content += `[${time || '—'}]\n`;
            content += `User: ${userMsg}\n`;
            content += `AI  : ${aiMsg}\n\n`;
        }
    });

    const blob = new Blob([content], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `nexboard-ai-chat-${new Date().toISOString().split('T')[0]}.txt`;
    link.click();
    URL.revokeObjectURL(url);
}
</script>
@endsection

@push('scripts')
<<<<<<< HEAD
    <script src="{{ asset('js/ai-assistant-fullscreen-enchanced.js') }}"></script>
=======
    <script src="{{ asset('js/ai-assistant-fullscreen.js') }}"></script>
>>>>>>> parent of 4b3ed60 (enhanced the chatbot capability)
@endpush