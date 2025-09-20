<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Academic Research Assistant - AI Chatbot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .chat-container {
            height: 600px;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            overflow: hidden;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
        }
        
        .chat-header {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .chat-messages {
            height: 450px;
            overflow-y: auto;
            padding: 15px;
            background: rgba(255, 255, 255, 0.95);
        }
        
        .chat-input-area {
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .message {
            margin-bottom: 15px;
            max-width: 80%;
        }
        
        .message.user {
            margin-left: auto;
        }
        
        .message.assistant {
            margin-right: auto;
        }
        
        .message-content {
            padding: 12px 16px;
            border-radius: 18px;
            position: relative;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .message.user .message-content {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-bottom-right-radius: 8px;
        }
        
        .message.assistant .message-content {
            background: #f8f9fa;
            color: #333;
            border: 1px solid #e9ecef;
            border-bottom-left-radius: 8px;
        }
        
        .message-meta {
            font-size: 0.75rem;
            color: #666;
            margin-top: 5px;
            text-align: right;
        }
        
        .message.assistant .message-meta {
            text-align: left;
        }
        
        .typing-indicator {
            display: none;
        }
        
        .typing-indicator.active {
            display: block;
        }
        
        .typing-dots {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 10px 16px;
        }
        
        .typing-dots span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #667eea;
            animation: typing 1.4s infinite ease-in-out;
        }
        
        .typing-dots span:nth-child(1) { animation-delay: -0.32s; }
        .typing-dots span:nth-child(2) { animation-delay: -0.16s; }
        
        @keyframes typing {
            0%, 80%, 100% {
                transform: scale(0.8);
                opacity: 0.5;
            }
            40% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .mode-selector {
            border-radius: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding: 8px 15px;
        }
        
        .mode-selector:focus {
            border-color: rgba(255, 255, 255, 0.6);
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
            background: rgba(255, 255, 255, 0.15);
            color: white;
        }
        
        .mode-selector option {
            background: #333;
            color: white;
        }
        
        .suggestions {
            margin-top: 10px;
        }
        
        .suggestion-btn {
            display: inline-block;
            margin: 3px;
            padding: 6px 12px;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 15px;
            color: white;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .suggestion-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-1px);
        }
        
        .welcome-message {
            text-align: center;
            color: #666;
            margin: 50px 0;
        }
        
        .welcome-message i {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #28a745;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .btn-send {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-send:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-send:disabled {
            opacity: 0.6;
            transform: none;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container my-4">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="text-center mb-4">
                    <h2 class="text-primary">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Academic Research Assistant
                    </h2>
                    <p class="text-muted">AI-powered chatbot untuk membantu penelitian akademik Anda</p>
                </div>

                <div class="chat-container">
                    <!-- Chat Header -->
                    <div class="chat-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center text-white">
                                <div class="status-indicator">
                                    <div class="status-dot" id="status-dot"></div>
                                    <span id="status-text">Online</span>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <select class="mode-selector form-select form-select-sm" id="chatMode">
                                    @foreach($available_modes as $key => $mode)
                                        <option value="{{ $key }}" {{ $key === 'general' ? 'selected' : '' }}>
                                            {{ $mode['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                
                                <button class="btn btn-sm btn-outline-light" onclick="clearChat()">
                                    <i class="fas fa-trash"></i>
                                </button>
                                
                                <button class="btn btn-sm btn-outline-light" onclick="exportChat()">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Chat Messages -->
                    <div class="chat-messages" id="chatMessages">
                        <div class="welcome-message" id="welcomeMessage">
                            <i class="fas fa-robot"></i>
                            <h5>Selamat datang di Academic Research Assistant!</h5>
                            <p>Pilih mode percakapan dan mulai diskusi tentang penelitian Anda.</p>
                            <div class="mt-3">
                                <button class="suggestion-btn" onclick="sendSuggestion('Help me brainstorm research topics in computer science')">
                                    Research Ideas
                                </button>
                                <button class="suggestion-btn" onclick="sendSuggestion('How do I conduct a literature review?')">
                                    Literature Review
                                </button>
                                <button class="suggestion-btn" onclick="sendSuggestion('Explain statistical significance in research')">
                                    Data Analysis
                                </button>
                            </div>
                        </div>
                        
                        <div class="typing-indicator" id="typingIndicator">
                            <div class="message assistant">
                                <div class="message-content">
                                    <div class="typing-dots">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chat Input -->
                    <div class="chat-input-area">
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control" 
                                   id="messageInput" 
                                   placeholder="Ketik pertanyaan tentang penelitian Anda..."
                                   style="border: 1px solid rgba(255,255,255,0.3); background: rgba(255,255,255,0.9);">
                            <button class="btn-send" id="sendButton" onclick="sendMessage()">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        
                        <div class="suggestions mt-2" id="suggestions">
                            <!-- Dynamic suggestions will be added here -->
                        </div>
                    </div>
                </div>
                
                <!-- Templates Section -->
                <div class="mt-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-template me-2"></i>
                                Research Templates
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row" id="templates">
                                <!-- Templates will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const chatMessages = document.getElementById('chatMessages');
        const messageInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');
        const typingIndicator = document.getElementById('typingIndicator');
        const welcomeMessage = document.getElementById('welcomeMessage');
        const suggestions = document.getElementById('suggestions');
        
        let isTyping = false;
        let conversationStarted = false;

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            checkServiceStatus();
            loadTemplates();
            
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
        });

        async function sendMessage() {
            const message = messageInput.value.trim();
            if (!message || isTyping) return;

            const mode = document.getElementById('chatMode').value;
            
            // Add user message to chat
            addMessage('user', message);
            messageInput.value = '';
            
            if (!conversationStarted) {
                welcomeMessage.style.display = 'none';
                conversationStarted = true;
            }
            
            // Show typing indicator
            showTyping();
            
            try {
                const response = await fetch('/chatbot/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ message, mode })
                });

                const data = await response.json();
                
                hideTyping();
                
                if (data.success) {
                    addMessage('assistant', data.response, {
                        mode: data.mode,
                        model: data.model,
                        timestamp: data.timestamp
                    });
                    
                    // Update suggestions
                    if (data.suggestions) {
                        updateSuggestions(data.suggestions);
                    }
                } else {
                    addMessage('assistant', 'Maaf, terjadi kesalahan: ' + (data.message || 'Unknown error'), {
                        isError: true
                    });
                }
                
            } catch (error) {
                hideTyping();
                addMessage('assistant', 'Maaf, terjadi kesalahan koneksi. Silakan coba lagi.', {
                    isError: true
                });
                console.error('Chat error:', error);
            }
        }

        function addMessage(role, content, meta = {}) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${role}`;
            
            const timestamp = meta.timestamp ? 
                new Date(meta.timestamp).toLocaleTimeString() : 
                new Date().toLocaleTimeString();
            
            let metaInfo = '';
            if (role === 'assistant') {
                const modeText = meta.mode ? `Mode: ${meta.mode}` : '';
                const modelText = meta.model ? `Model: ${meta.model}` : '';
                metaInfo = `<div class="message-meta">${modeText} ${modelText ? '• ' + modelText : ''} • ${timestamp}</div>`;
            } else {
                metaInfo = `<div class="message-meta">${timestamp}</div>`;
            }
            
            const errorClass = meta.isError ? ' text-danger' : '';
            
            messageDiv.innerHTML = `
                <div class="message-content${errorClass}">
                    ${formatMessage(content)}
                </div>
                ${metaInfo}
            `;
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function formatMessage(content) {
            // Basic markdown-like formatting
            return content
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/```(.*?)```/gs, '<pre><code>$1</code></pre>')
                .replace(/`(.*?)`/g, '<code>$1</code>')
                .replace(/\n/g, '<br>');
        }

        function showTyping() {
            isTyping = true;
            sendButton.disabled = true;
            typingIndicator.classList.add('active');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function hideTyping() {
            isTyping = false;
            sendButton.disabled = false;
            typingIndicator.classList.remove('active');
        }

        function updateSuggestions(suggestionList) {
            suggestions.innerHTML = '';
            suggestionList.forEach(suggestion => {
                const btn = document.createElement('span');
                btn.className = 'suggestion-btn';
                btn.textContent = suggestion;
                btn.onclick = () => sendSuggestion(suggestion);
                suggestions.appendChild(btn);
            });
        }

        function sendSuggestion(text) {
            messageInput.value = text;
            sendMessage();
        }

        async function clearChat() {
            if (confirm('Hapus semua percakapan?')) {
                try {
                    const response = await fetch('/chatbot/clear', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (response.ok) {
                        chatMessages.innerHTML = '';
                        welcomeMessage.style.display = 'block';
                        conversationStarted = false;
                        suggestions.innerHTML = '';
                    }
                } catch (error) {
                    console.error('Clear chat error:', error);
                }
            }
        }

        async function exportChat() {
            try {
                const response = await fetch('/chatbot/export?format=txt');
                const blob = await response.blob();
                
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `academic_chat_${Date.now()}.txt`;
                a.click();
                window.URL.revokeObjectURL(url);
            } catch (error) {
                console.error('Export error:', error);
                alert('Gagal mengekspor percakapan');
            }
        }

        async function checkServiceStatus() {
            try {
                const response = await fetch('/chatbot/status');
                const data = await response.json();
                
                const statusDot = document.getElementById('status-dot');
                const statusText = document.getElementById('status-text');
                
                if (data.success && data.service_status === 'running') {
                    statusDot.style.background = '#28a745';
                    statusText.textContent = 'Online';
                } else {
                    statusDot.style.background = '#dc3545';
                    statusText.textContent = 'Offline';
                }
            } catch (error) {
                console.error('Status check error:', error);
            }
        }

        async function loadTemplates() {
            try {
                const response = await fetch('/chatbot/templates');
                const data = await response.json();
                
                if (data.success) {
                    const templatesDiv = document.getElementById('templates');
                    
                    Object.entries(data.templates).forEach(([key, template]) => {
                        const templateDiv = document.createElement('div');
                        templateDiv.className = 'col-md-6 mb-2';
                        templateDiv.innerHTML = `
                            <button class="btn btn-outline-primary btn-sm w-100" onclick="sendSuggestion('${template.content}')">
                                <i class="fas fa-file-alt me-2"></i>
                                ${template.title}
                            </button>
                        `;
                        templatesDiv.appendChild(templateDiv);
                    });
                }
            } catch (error) {
                console.error('Templates load error:', error);
            }
        }

        // Auto-check status every 30 seconds
        setInterval(checkServiceStatus, 30000);
    </script>
</body>
</html>