// ===CHATBOT: Enhanced Floating Widget Implementation (START)===
class AIAssistant {
    constructor() {
        this.isOpen = false;
        this.currentConversationId = null;
        this.init();
    }

    init() {
        // Only create floating button if NOT on the AI Assistant fullscreen page
        if (!window.location.pathname.includes('/ai-assistant')) {
            this.createFloatingButton();
            this.createChatWidget();
            this.attachEventListeners();
            this.loadOrCreateConversation();
            this.makeDraggable();
        }
    }

    createFloatingButton() {
        const button = document.createElement('button');
        button.id = 'ai-assistant-btn';
        button.className = 'fixed bottom-5 right-5 w-14 h-14 rounded-full bg-gray-900 text-white shadow-lg z-50 flex items-center justify-center cursor-pointer transition hover:bg-gray-700 hover:scale-110';
        button.innerHTML = '<i class="fas fa-robot text-xl"></i>';
        button.style.right = '20px';
        button.style.bottom = '20px';
        document.body.appendChild(button);
    }

    createChatWidget() {
        const widget = document.createElement('div');
        widget.id = 'ai-chat-widget';
        widget.className = 'fixed bg-white rounded-lg shadow-2xl z-40 flex flex-col hidden transition-all duration-300';
        widget.style.width = '420px';
        widget.style.height = '600px';
        widget.style.right = '20px';
        widget.style.bottom = '90px';
        widget.innerHTML = `
            <div class="bg-gray-900 text-white p-4 rounded-t-lg flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <i class="fas fa-robot text-lg"></i>
                    <span class="font-bold">AI Assistant</span>
                </div>
                <div class="flex items-center gap-2">
                    <button id="fullscreen-btn" class="text-white hover:text-gray-300 transition" title="Open Fullscreen">
                        <i class="fas fa-expand text-sm"></i>
                    </button>
                    <button id="clear-chat" class="text-white hover:text-gray-300 transition" title="Clear Chat">
                        <i class="fas fa-trash text-sm"></i>
                    </button>
                    <button class="chat-close text-white hover:text-gray-300 text-xl transition">&times;</button>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto p-4 bg-gray-50" id="chat-messages">
                <div class="text-center text-gray-400 text-sm py-8">
                    <i class="fas fa-comments text-3xl mb-2"></i>
                    <p>Ask me anything about CPC Nexboard!</p>
                    <p class="text-xs mt-2">I can help with forecasting, job orders, inventory, and more.</p>
                </div>
            </div>
            <div class="p-3 border-t border-gray-200 bg-white rounded-b-lg">
                <div class="flex gap-2">
                    <input id="chat-input" type="text" placeholder="Type your question..." 
                           class="flex-1 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-900 text-sm">
                    <button id="chat-send" class="px-4 bg-gray-900 text-white rounded-lg hover:bg-gray-700 transition flex items-center gap-2">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(widget);
    }

    attachEventListeners() {
        document.getElementById('ai-assistant-btn').addEventListener('click', () => this.toggleWidget());
        document.querySelector('.chat-close').addEventListener('click', () => this.toggleWidget());
        document.getElementById('chat-send').addEventListener('click', () => this.sendMessage());
        document.getElementById('chat-input').addEventListener('keypress', e => { 
            if (e.key === 'Enter') this.sendMessage(); 
        });
        document.getElementById('clear-chat').addEventListener('click', () => this.clearChat());
        document.getElementById('fullscreen-btn').addEventListener('click', () => {
            window.location.href = '/ai-assistant';
        });
    }

    toggleWidget() {
        this.isOpen = !this.isOpen;
        const widget = document.getElementById('ai-chat-widget');
        
        if (this.isOpen) {
            widget.classList.remove('hidden');
            document.getElementById('chat-input').focus();
        } else {
            widget.classList.add('hidden');
        }
    }

    async loadOrCreateConversation() {
        try {
            // Get or create active conversation
            const response = await fetch('/ai-assistant/conversation/active', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            
            if (data.conversation) {
                this.currentConversationId = data.conversation.id;
                this.loadMessages(data.conversation.id);
            }
        } catch (error) {
            console.error('Error loading conversation:', error);
        }
    }

    async loadMessages(conversationId) {
        try {
            const response = await fetch(`/ai-assistant/conversation/${conversationId}/messages`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            const messages = await response.json();
            
            const container = document.getElementById('chat-messages');
            const emptyState = container.querySelector('.text-center');
            
            if (messages.length > 0 && emptyState) {
                emptyState.remove();
            }
            
            // Clear existing messages first to prevent duplicates
            const existingMessages = container.querySelectorAll('.mb-3');
            existingMessages.forEach(msg => msg.remove());
            
            // Load messages in correct order (oldest first)
            messages.forEach(msg => {
                this.addMessage('user', msg.user_message, false);
                this.addMessage('ai', msg.ai_response, false);
            });
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }

    async sendMessage() {
        const input = document.getElementById('chat-input');
        const message = input.value.trim();
        if (!message) return;

        this.addMessage('user', message);
        input.value = '';
        this.showTypingIndicator();

        try {
            console.log('Sending message:', message);
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

            console.log('Response status:', response.status);
            const data = await response.json();
            console.log('Response data:', data);
            
            this.hideTypingIndicator();

            if (data.success) {
                this.addMessage('ai', data.message);
                if (data.conversation_id) {
                    this.currentConversationId = data.conversation_id;
                }
            } else {
                const errorMsg = data.error || data.message || 'Sorry, an error occurred. Please try again.';
                console.error('AI Error:', errorMsg);
                this.addMessage('ai', errorMsg || 'Failed to get response. Check browser console for details.');
            }
        } catch (error) {
            console.error('Chat error:', error);
            this.hideTypingIndicator();
            this.addMessage('ai', 'Connection error: ' + error.message + '. Check browser console for details.');
        }
    }

    addMessage(sender, text, scroll = true) {
        const container = document.getElementById('chat-messages');
        const emptyState = container.querySelector('.text-center');
        if (emptyState) emptyState.remove();

        const messageDiv = document.createElement('div');
        messageDiv.className = `mb-3 ${sender === 'user' ? 'flex justify-end' : 'flex justify-start'}`;
        
        const bubble = document.createElement('div');
        bubble.className = `max-w-[80%] p-3 rounded-lg ${
            sender === 'user' 
                ? 'bg-gray-900 text-white rounded-br-none' 
                : 'bg-white text-gray-900 border border-gray-200 rounded-bl-none'
        }`;
        bubble.style.wordWrap = 'break-word';
        bubble.style.whiteSpace = 'pre-wrap';
        bubble.textContent = text;
        
        messageDiv.appendChild(bubble);
        container.appendChild(messageDiv);
        
        if (scroll) {
            container.scrollTop = container.scrollHeight;
        }
    }

    showTypingIndicator() {
        const container = document.getElementById('chat-messages');
        const indicator = document.createElement('div');
        indicator.id = 'typing-indicator';
        indicator.className = 'flex justify-start mb-3';
        indicator.innerHTML = `
            <div class="bg-white border border-gray-200 p-3 rounded-lg rounded-bl-none flex gap-1">
                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0s"></span>
                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></span>
            </div>
        `;
        container.appendChild(indicator);
        container.scrollTop = container.scrollHeight;
    }

    hideTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        if (indicator) indicator.remove();
    }

    async clearChat() {
        if (!confirm('Clear this conversation?')) return;

        try {
            if (this.currentConversationId) {
                await fetch(`/ai-assistant/conversation/${this.currentConversationId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
            }

            const container = document.getElementById('chat-messages');
            container.innerHTML = `
                <div class="text-center text-gray-400 text-sm py-8">
                    <i class="fas fa-comments text-3xl mb-2"></i>
                    <p>Ask me anything about CPC Nexboard!</p>
                    <p class="text-xs mt-2">I can help with forecasting, job orders, inventory, and more.</p>
                </div>
            `;
            
            // Create new conversation
            this.currentConversationId = null;
            await this.loadOrCreateConversation();
        } catch (error) {
            console.error('Error clearing chat:', error);
        }
    }

    makeDraggable() {
        const button = document.getElementById('ai-assistant-btn');
        let isDragging = false;
        let startX, startY, initialRight, initialBottom;

        const startDrag = (e) => {
            isDragging = true;
            startX = e.clientX || e.touches[0].clientX;
            startY = e.clientY || e.touches[0].clientY;
            
            const rect = button.getBoundingClientRect();
            initialRight = window.innerWidth - rect.right;
            initialBottom = window.innerHeight - rect.bottom;
            button.style.transition = 'none';
        };

        const doDrag = (e) => {
            if (!isDragging) return;
            e.preventDefault();
            
            const currentX = e.clientX || e.touches[0].clientX;
            const currentY = e.clientY || e.touches[0].clientY;
            const dx = currentX - startX;
            const dy = currentY - startY;
            
            let newRight = initialRight - dx;
            let newBottom = initialBottom - dy;
            
            newRight = Math.max(0, Math.min(newRight, window.innerWidth - button.offsetWidth));
            newBottom = Math.max(0, Math.min(newBottom, window.innerHeight - button.offsetHeight));
            
            button.style.right = `${newRight}px`;
            button.style.bottom = `${newBottom}px`;
            button.style.left = 'auto';
        };

        const endDrag = () => {
            if (!isDragging) return;
            isDragging = false;
            button.style.transition = 'all 0.3s ease';
            this.snapToSide();
        };

        button.addEventListener('mousedown', startDrag);
        button.addEventListener('touchstart', startDrag);
        document.addEventListener('mousemove', doDrag);
        document.addEventListener('touchmove', doDrag);
        document.addEventListener('mouseup', endDrag);
        document.addEventListener('touchend', endDrag);
    }

    snapToSide() {
        const button = document.getElementById('ai-assistant-btn');
        const rect = button.getBoundingClientRect();
        const halfWidth = window.innerWidth / 2;
        const snapRight = rect.left < halfWidth 
            ? window.innerWidth - button.offsetWidth - 20 
            : 20;
        
        button.style.right = `${snapRight}px`;
        button.style.bottom = '20px';
        button.style.left = 'auto';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.aiAssistant = new AIAssistant();
});
// ===CHATBOT: Enhanced Floating Widget Implementation (END)===