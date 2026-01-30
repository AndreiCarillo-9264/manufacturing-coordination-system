class AIAssistant {
    constructor() {
        this.isOpen = false;
        this.init();
    }

    init() {
        this.createFloatingButton();
        this.createChatWidget();
        this.attachEventListeners();
        this.loadHistory();
        this.makeDraggable();
    }

    createFloatingButton() {
        const button = document.createElement('button');
        button.id = 'ai-assistant-btn';
        button.className = 'fixed bottom-5 left-5 w-14 h-14 rounded-full bg-gray-900 text-white shadow-lg z-50 flex items-center justify-center cursor-pointer transition hover:bg-gray-700 hover:scale-110';
        button.innerHTML = '<i class="fas fa-robot text-xl"></i>';
        document.body.appendChild(button);
    }

    createChatWidget() {
        const widget = document.createElement('div');
        widget.id = 'ai-chat-widget';
        widget.className = 'fixed bg-white rounded-lg shadow-2xl z-40 flex flex-col hidden transition-all duration-300';
        widget.style.width = '420px';
        widget.style.height = '600px';
        widget.innerHTML = `
            <div class="bg-gray-900 text-white p-4 rounded-t-lg flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <i class="fas fa-robot text-lg"></i>
                    <span class="font-bold">AI Assistant</span>
                </div>
                <div class="flex items-center gap-2">
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
    }

    toggleWidget() {
        this.isOpen = !this.isOpen;
        const widget = document.getElementById('ai-chat-widget');
        
        if (this.isOpen) {
            widget.classList.remove('hidden');
            this.updateWidgetPosition();
            document.getElementById('chat-input').focus();
        } else {
            widget.classList.add('hidden');
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
            } else {
                this.addMessage('ai', data.message || 'Sorry, an error occurred. Please try again.');
            }
        } catch (error) {
            console.error('Chat error:', error);
            this.hideTypingIndicator();
            this.addMessage('ai', 'Connection error. Please check your internet connection.');
        }
    }

    addMessage(sender, text) {
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
        bubble.textContent = text;
        
        messageDiv.appendChild(bubble);
        container.appendChild(messageDiv);
        container.scrollTop = container.scrollHeight;
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

    async loadHistory() {
        try {
            const response = await fetch('/ai-assistant/history', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            const history = await response.json();
            
            const container = document.getElementById('chat-messages');
            const emptyState = container.querySelector('.text-center');
            
            if (history.length > 0 && emptyState) {
                emptyState.remove();
            }
            
            history.reverse().forEach(chat => {
                this.addMessage('user', chat.user_message);
                this.addMessage('ai', chat.ai_response);
            });
        } catch (error) {
            console.error('Error loading history:', error);
        }
    }

    async clearChat() {
        if (!confirm('Clear all chat history?')) return;

        try {
            await fetch('/ai-assistant/history', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const container = document.getElementById('chat-messages');
            container.innerHTML = `
                <div class="text-center text-gray-400 text-sm py-8">
                    <i class="fas fa-comments text-3xl mb-2"></i>
                    <p>Ask me anything about CPC Nexboard!</p>
                </div>
            `;
        } catch (error) {
            console.error('Error clearing chat:', error);
        }
    }

    makeDraggable() {
        const button = document.getElementById('ai-assistant-btn');
        let isDragging = false;
        let startX, startY, initialLeft, initialBottom;

        const startDrag = (e) => {
            isDragging = true;
            startX = e.clientX || e.touches[0].clientX;
            startY = e.clientY || e.touches[0].clientY;
            initialLeft = button.getBoundingClientRect().left;
            initialBottom = window.innerHeight - button.getBoundingClientRect().bottom;
            button.style.transition = 'none';
        };

        const doDrag = (e) => {
            if (!isDragging) return;
            e.preventDefault();
            const dx = (e.clientX || e.touches[0].clientX) - startX;
            const dy = (e.clientY || e.touches[0].clientY) - startY;
            let newLeft = initialLeft + dx;
            let newBottom = initialBottom - dy;
            newLeft = Math.max(0, Math.min(newLeft, window.innerWidth - button.offsetWidth));
            newBottom = Math.max(0, Math.min(newBottom, window.innerHeight - button.offsetHeight));
            button.style.left = `${newLeft}px`;
            button.style.bottom = `${newBottom}px`;
            if (this.isOpen) this.updateWidgetPosition();
        };

        const endDrag = () => {
            if (!isDragging) return;
            isDragging = false;
            button.style.transition = 'all 0.3s ease';
            this.snapToSide();
            if (this.isOpen) this.updateWidgetPosition();
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
        const halfWidth = window.innerWidth / 2;
        const currentLeft = button.getBoundingClientRect().left;
        const snapLeft = currentLeft < halfWidth ? 20 : window.innerWidth - button.offsetWidth - 20;
        button.style.left = `${snapLeft}px`;
        button.style.bottom = '20px';
    }

    updateWidgetPosition() {
        const button = document.getElementById('ai-assistant-btn');
        const widget = document.getElementById('ai-chat-widget');
        if (!widget) return;

        const buttonRect = button.getBoundingClientRect();
        const halfWidth = window.innerWidth / 2;
        const isRight = buttonRect.left > halfWidth;

        widget.style.bottom = `${window.innerHeight - buttonRect.top + 10}px`;
        widget.style.left = isRight 
            ? `${buttonRect.right - widget.offsetWidth}px` 
            : `${buttonRect.left}px`;
    }
}

document.addEventListener('DOMContentLoaded', () => new AIAssistant());