class AIFullscreenAssistant {
    constructor() {
        this.currentConversationId = null;
        this.conversations = [];
        this.attachEventListeners();
        this.loadConversations();
    }

    attachEventListeners() {
        const sendBtn = document.getElementById('chat-send-full');
        const input = document.getElementById('chat-input-full');
        const newChatBtn = document.getElementById('new-chat-btn');
        
        if (sendBtn) sendBtn.addEventListener('click', () => this.sendMessage());
        if (newChatBtn) newChatBtn.addEventListener('click', () => this.createNewConversation());
        if (input) {
            input.addEventListener('keypress', e => { 
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });
        }
    }

    async loadConversations() {
        try {
            const response = await fetch('/ai-assistant/conversations', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            
            this.conversations = data.conversations || [];
            this.renderConversationList();
            
            // Load active or most recent conversation
            if (this.conversations.length > 0) {
                const activeConv = this.conversations.find(c => c.is_active) || this.conversations[0];
                this.loadConversation(activeConv.id);
            } else {
                this.createNewConversation();
            }
        } catch (error) {
            console.error('Error loading conversations:', error);
        }
    }

    renderConversationList() {
        const sidebar = document.getElementById('conversation-list');
        if (!sidebar) return;

        if (this.conversations.length === 0) {
            sidebar.innerHTML = `
                <div class="text-center py-8 text-gray-400">
                    <i class="fas fa-comments text-3xl mb-2"></i>
                    <p class="text-sm">No conversations yet</p>
                </div>
            `;
            return;
        }

        sidebar.innerHTML = this.conversations.map(conv => {
            const isActive = conv.id === this.currentConversationId;
            const date = new Date(conv.updated_at);
            const timeAgo = this.getTimeAgo(date);
            
            return `
                <div class="mb-2 p-3 rounded-lg cursor-pointer transition ${
                    isActive 
                        ? 'bg-gray-900 text-white' 
                        : 'bg-gray-50 hover:bg-gray-100 text-gray-800'
                }" onclick="window.aiAssistant.loadConversation(${conv.id})">
                    <div class="flex justify-between items-start mb-1">
                        <p class="text-sm font-semibold truncate flex-1">${this.escapeHtml(conv.title || 'New Conversation')}</p>
                        <button onclick="event.stopPropagation(); window.aiAssistant.deleteConversation(${conv.id})" 
                                class="ml-2 text-xs ${isActive ? 'text-gray-300 hover:text-white' : 'text-gray-400 hover:text-red-600'} transition">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <p class="text-xs ${isActive ? 'text-gray-300' : 'text-gray-500'}">${timeAgo}</p>
                    ${conv.message_count ? `<p class="text-xs ${isActive ? 'text-gray-400' : 'text-gray-400'} mt-1">${conv.message_count} messages</p>` : ''}
                </div>
            `;
        }).join('');
    }

    async createNewConversation() {
        try {
            const response = await fetch('/ai-assistant/conversation', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ title: 'New Conversation' })
            });
            
            const data = await response.json();
            if (data.conversation) {
                this.conversations.unshift(data.conversation);
                this.loadConversation(data.conversation.id);
            }
        } catch (error) {
            console.error('Error creating conversation:', error);
        }
    }

    async loadConversation(conversationId) {
        this.currentConversationId = conversationId;
        this.renderConversationList();
        
        try {
            const response = await fetch(`/ai-assistant/conversation/${conversationId}/messages`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            const messages = await response.json();
            
            const container = document.getElementById('chat-messages-full');
            container.innerHTML = '';
            
            if (messages.length === 0) {
                container.innerHTML = `
                    <div class="flex items-center justify-center h-full">
                        <div class="text-center text-gray-400">
                            <i class="fas fa-robot text-6xl mb-4"></i>
                            <p class="text-lg font-semibold">Start a conversation</p>
                            <p class="text-sm mt-2">Ask about forecasting, job orders, inventory, or system operations</p>
                        </div>
                    </div>
                `;
            } else {
                messages.forEach(msg => {
                    this.addMessage('user', msg.user_message, msg.created_at);
                    this.addMessage('ai', msg.ai_response, msg.created_at);
                });
            }
            
            // Enable input
            const input = document.getElementById('chat-input-full');
            if (input) {
                input.disabled = false;
                input.focus();
            }
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }

    async deleteConversation(conversationId) {
        if (!confirm('Delete this conversation?')) return;
        
        try {
            await fetch(`/ai-assistant/conversation/${conversationId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            
            this.conversations = this.conversations.filter(c => c.id !== conversationId);
            
            if (this.currentConversationId === conversationId) {
                if (this.conversations.length > 0) {
                    this.loadConversation(this.conversations[0].id);
                } else {
                    this.createNewConversation();
                }
            } else {
                this.renderConversationList();
            }
        } catch (error) {
            console.error('Error deleting conversation:', error);
        }
    }

    async sendMessage() {
        const input = document.getElementById('chat-input-full');
        const message = input.value.trim();
        if (!message || !this.currentConversationId) return;

        // Remove empty state if present
        const emptyState = document.querySelector('.flex.items-center.justify-center.h-full');
        if (emptyState) emptyState.remove();

        this.addMessage('user', message);
        input.value = '';
        input.disabled = true;

        this.showTypingIndicator();

        try {
            const response = await fetch('/ai-assistant/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    message,
                    conversation_id: this.currentConversationId
                }),
            });

            const data = await response.json();
            this.hideTypingIndicator();

            if (data.success) {
                this.addMessage('ai', data.message);
                
                // Update conversation title if it's the first message
                const conv = this.conversations.find(c => c.id === this.currentConversationId);
                if (conv && (!conv.title || conv.title === 'New Conversation')) {
                    conv.title = message.substring(0, 50);
                    conv.message_count = (conv.message_count || 0) + 1;
                    this.renderConversationList();
                }
            } else {
                this.addMessage('ai', data.message || 'Sorry, an error occurred. Please try again.');
            }
        } catch (error) {
            console.error('Chat error:', error);
            this.hideTypingIndicator();
            this.addMessage('ai', 'Connection error. Please check your internet connection and try again.');
        } finally {
            input.disabled = false;
            input.focus();
        }
    }

    addMessage(sender, text, timestamp = null) {
        const container = document.getElementById('chat-messages-full');
        const messageDiv = document.createElement('div');
        messageDiv.className = 'mb-4 chat-item';

        const displayTime = timestamp 
            ? new Date(timestamp).toLocaleString('en-US', { 
                month: 'short', day: 'numeric', year: 'numeric', 
                hour: 'numeric', minute: 'numeric', hour12: true 
              })
            : new Date().toLocaleString('en-US', { 
                month: 'short', day: 'numeric', year: 'numeric', 
                hour: 'numeric', minute: 'numeric', hour12: true 
              });

        if (sender === 'user') {
            messageDiv.innerHTML = `
                <div class="flex justify-end mb-2">
                    <div class="max-w-[70%] p-4 rounded-lg bg-gray-900 text-white rounded-br-none shadow">
                        <p class="text-sm whitespace-pre-wrap">${this.escapeHtml(text)}</p>
                        <p class="text-xs text-gray-300 mt-2">${displayTime}</p>
                    </div>
                </div>
            `;
        } else {
            messageDiv.innerHTML = `
                <div class="flex justify-start">
                    <div class="max-w-[70%] p-4 rounded-lg bg-white border border-gray-200 rounded-bl-none shadow">
                        <p class="text-sm text-gray-800 whitespace-pre-wrap">${this.escapeHtml(text)}</p>
                    </div>
                </div>
            `;
        }

        container.appendChild(messageDiv);
        container.scrollTop = container.scrollHeight;
    }

    showTypingIndicator() {
        const container = document.getElementById('chat-messages-full');
        const indicator = document.createElement('div');
        indicator.id = 'typing-indicator-full';
        indicator.className = 'mb-4 flex justify-start';
        indicator.innerHTML = `
            <div class="max-w-[70%] p-4 rounded-lg bg-white border border-gray-200 rounded-bl-none shadow flex gap-2">
                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0s"></span>
                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></span>
            </div>
        `;
        container.appendChild(indicator);
        container.scrollTop = container.scrollHeight;
    }

    hideTypingIndicator() {
        const indicator = document.getElementById('typing-indicator-full');
        if (indicator) indicator.remove();
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    getTimeAgo(date) {
        const seconds = Math.floor((new Date() - date) / 1000);
        
        if (seconds < 60) return 'just now';
        if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
        if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
        if (seconds < 604800) return `${Math.floor(seconds / 86400)}d ago`;
        
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }
}

// Initialize and expose globally
document.addEventListener('DOMContentLoaded', () => {
    window.aiAssistant = new AIFullscreenAssistant();
});