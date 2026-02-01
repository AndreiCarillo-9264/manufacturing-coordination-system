class AIFullscreenAssistant {
    constructor() {
        this.attachEventListeners();
    }

    attachEventListeners() {
        const sendBtn = document.getElementById('chat-send-full');
        const input = document.getElementById('chat-input-full');
        
        if (sendBtn) sendBtn.addEventListener('click', () => this.sendMessage());
        if (input) {
            input.addEventListener('keypress', e => { 
                if (e.key === 'Enter') this.sendMessage(); 
            });
        }
    }

    async sendMessage() {
        const input = document.getElementById('chat-input-full');
        const message = input.value.trim();
        if (!message) return;

        // Clear empty state if present
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
                body: JSON.stringify({ message }),
            });

            const data = await response.json();
            this.hideTypingIndicator();

            if (data.success) {
                this.addMessage('ai', data.message);
                this.updateSidebar(message, data.message);
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

    addMessage(sender, text) {
        const container = document.getElementById('chat-messages-full');
        const messageDiv = document.createElement('div');
        messageDiv.className = 'mb-4 chat-item';

        const timestamp = new Date().toLocaleString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric', 
            hour: 'numeric', 
            minute: 'numeric',
            hour12: true 
        });

        if (sender === 'user') {
            messageDiv.innerHTML = `
                <div class="flex justify-end mb-2">
                    <div class="max-w-[70%] p-4 rounded-lg bg-gray-900 text-white rounded-br-none shadow">
                        <p class="text-sm">${this.escapeHtml(text)}</p>
                        <p class="text-xs text-gray-300 mt-2">${timestamp}</p>
                    </div>
                </div>
            `;
        } else {
            messageDiv.innerHTML = `
                <div class="flex justify-start">
                    <div class="max-w-[70%] p-4 rounded-lg bg-white border border-gray-200 rounded-bl-none shadow">
                        <p class="text-sm text-gray-800" style="white-space: pre-wrap;">${this.escapeHtml(text)}</p>
                    </div>
                </div>
            `;
        }

        container.appendChild(messageDiv);
        container.scrollTop = container.scrollHeight;
    }

    updateSidebar(userMessage, aiResponse) {
        const sidebar = document.querySelector('.w-80.bg-white .flex-1.overflow-y-auto');
        if (!sidebar) return;

        // Remove "No chat history yet" message if exists
        const emptyMessage = sidebar.querySelector('.text-center.py-8');
        if (emptyMessage) emptyMessage.remove();

        // Create new chat item for sidebar
        const chatItem = document.createElement('div');
        chatItem.className = 'mb-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 cursor-pointer transition';
        chatItem.onclick = () => {
            // Scroll to the last message
            const container = document.getElementById('chat-messages-full');
            container.scrollTop = container.scrollHeight;
        };
        
        const now = new Date();
        chatItem.innerHTML = `
            <p class="text-sm font-semibold text-gray-800 truncate">${this.escapeHtml(userMessage.substring(0, 50))}</p>
            <p class="text-xs text-gray-500 mt-1">just now</p>
        `;

        // Insert at the top
        sidebar.insertBefore(chatItem, sidebar.firstChild);
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
}

document.addEventListener('DOMContentLoaded', () => new AIFullscreenAssistant());