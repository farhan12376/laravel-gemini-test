<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Academic Research Assistant - AI Powered</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .main-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            margin: 20px 0;
        }
        
        .chat-container {
            height: 600px;
            border-radius: 15px;
            overflow: hidden;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid rgba(0,0,0,0.1);
        }
        
        .chat-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
        }
        
        .chat-messages {
            height: 450px;
            overflow-y: auto;
            padding: 20px;
            background: white;
        }
        
        .chat-input-area {
            padding: 20px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
            border-radius: 0 0 15px 15px;
        }
        
        .message {
            margin-bottom: 20px;
            max-width: 80%;
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .message.user {
            margin-left: auto;
        }
        
        .message.assistant {
            margin-right: auto;
        }
        
        .message-content {
            padding: 15px 20px;
            border-radius: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .message.user .message-content {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-bottom-right-radius: 8px;
        }
        
        .message.assistant .message-content {
            background: white;
            color: #333;
            border: 1px solid #e9ecef;
            border-bottom-left-radius: 8px;
        }
        
        .message-meta {
            font-size: 0.8rem;
            color: #666;
            margin-top: 8px;
            opacity: 0.7;
        }
        
        .message.user .message-meta {
            text-align: right;
            color: rgba(255,255,255,0.8);
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
            gap: 6px;
            padding: 15px 20px;
        }
        
        .typing-dots span {
            width: 10px;
            height: 10px;
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
            border-radius: 25px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 20px;
            font-weight: 500;
        }
        
        .mode-selector:focus {
            border-color: rgba(255, 255, 255, 0.6);
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }
        
        .mode-selector option {
            background: #333;
            color: white;
        }
        
        .suggestions {
            margin-top: 15px;
        }
        
        .suggestion-btn {
            display: inline-block;
            margin: 5px;
            padding: 8px 15px;
            background: rgba(102, 126, 234, 0.1);
            border: 1px solid rgba(102, 126, 234, 0.3);
            border-radius: 20px;
            color: #667eea;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .suggestion-btn:hover {
            background: rgba(102, 126, 234, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .welcome-message {
            text-align: center;
            color: #666;
            margin: 80px 0;
        }
        
        .welcome-message i {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 20px;
        }
        
        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #28a745;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .btn-send {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-send:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }
        
        .btn-send:disabled {
            opacity: 0.6;
            transform: none;
        }
        
        .header-actions .btn {
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(5px);
            transition: all 0.3s ease;
        }
        
        .header-actions .btn:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-1px);
        }
        
        .templates-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-top: 30px;
        }
        
        .template-btn {
            transition: all 0.3s ease;
            border-radius: 10px;
        }
        
        .template-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container my-4">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="main-container p-4">
                    <div class="text-center mb-4">
                        <h1 class="text-primary mb-2">
                            <i class="fas fa-graduation-cap me-3"></i>
                            Academic Research Assistant
                        </h1>
                        <p class="text-muted fs-5">AI-powered research companion untuk membantu perjalanan akademik Anda</p>
                    </div>

                    <div class="chat-container">
                        <!-- Chat Header -->
                        <div class="chat-header">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="status-indicator">
                                        <div class="status-dot" id="status-dot"></div>
                                        <span id="status-text" class="fw-bold">Checking...</span>
                                        <small id="model-info" class="ms-2 opacity-75"></small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="row g-2 header-actions">
                                        <div class="col-md-6">
                                            <select class="form-select mode-selector" id="chatMode">
                                                @if(isset($available_modes) && !empty($available_modes))
                                                    @foreach($available_modes as $key => $mode)
                                                        <option value="{{ $key }}" {{ $key === 'general' ? 'selected' : '' }}>
                                                            {{ $mode['name'] }}
                                                        </option>
                                                    @endforeach
                                                @else
                                                    <option value="general">General</option>
                                                    <option value="brainstorm">Brainstorm</option>
                                                    <option value="analysis">Analysis</option>
                                                    <option value="writing">Writing</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <button class="btn btn-sm w-100" onclick="clearChat()" title="Clear Chat">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <div class="col-md-3">
                                            <button class="btn btn-sm w-100" onclick="exportChat()" title="Export Chat">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Chat Messages -->
                        <div class="chat-messages" id="chatMessages">
                            <div class="welcome-message" id="welcomeMessage">
                                <i class="fas fa-robot"></i>
                                <h4 class="mt-3 mb-3">Selamat datang di Academic Research Assistant!</h4>
                                <p class="text-muted mb-4">Pilih mode percakapan dan mulai diskusi tentang penelitian Anda. Saya siap membantu dari brainstorming hingga analisis data.</p>
                                <div class="mt-4">
                                    <button class="suggestion-btn" onclick="sendSuggestion('Help me brainstorm research topics in computer science')">
                                        <i class="fas fa-lightbulb me-2"></i>Research Ideas
                                    </button>
                                    <button class="suggestion-btn" onclick="sendSuggestion('How do I conduct a comprehensive literature review?')">
                                        <i class="fas fa-books me-2"></i>Literature Review
                                    </button>
                                    <button class="suggestion-btn" onclick="sendSuggestion('Explain statistical significance in research methodology')">
                                        <i class="fas fa-chart-line me-2"></i>Data Analysis
                                    </button>
                                    <button class="suggestion-btn" onclick="sendSuggestion('Help me improve my academic writing structure')">
                                        <i class="fas fa-pen-fancy me-2"></i>Writing Help
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
                            <div class="row g-3">
                                <div class="col">
                                    <div class="input-group">
                                        <input type="text" 
                                               class="form-control form-control-lg" 
                                               id="messageInput" 
                                               placeholder="Ketik pertanyaan tentang penelitian Anda..."
                                               style="border-radius: 25px 0 0 25px;">
                                        <button class="btn-send" id="sendButton" onclick="sendMessage()" title="Send Message">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="suggestions mt-3" id="suggestions">
                                <!-- Dynamic suggestions will be added here -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Templates Section -->
                    <div class="templates-section">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-templates me-2"></i>
                                Research Templates & Quick Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3" id="templates">
                                <div class="col-md-3">
                                    <button class="btn btn-outline-primary template-btn w-100" onclick="sendSuggestion('Help me structure a research proposal with background, objectives, methodology, and expected outcomes.')">
                                        <i class="fas fa-file-alt me-2"></i>
                                        <div>Research Proposal</div>
                                        <small class="text-muted">Structure & Guide</small>
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-outline-success template-btn w-100" onclick="sendSuggestion('Guide me through conducting a comprehensive literature review for my research topic.')">
                                        <i class="fas fa-search me-2"></i>
                                        <div>Literature Review</div>
                                        <small class="text-muted">Search & Synthesis</small>
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-outline-info template-btn w-100" onclick="sendSuggestion('Help me choose and design appropriate research methodology for my study.')">
                                        <i class="fas fa-cog me-2"></i>
                                        <div>Methodology</div>
                                        <small class="text-muted">Design & Methods</small>
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-outline-warning template-btn w-100" onclick="sendSuggestion('Assist me with statistical analysis and interpretation of my research data.')">
                                        <i class="fas fa-chart-bar me-2"></i>
                                        <div>Data Analysis</div>
                                        <small class="text-muted">Stats & Interpretation</small>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Service Status Footer -->
                    <div class="text-center mt-4">
                        <small class="text-muted" id="serviceStatus">
                            <i class="fas fa-circle text-secondary me-1"></i>
                            Initializing service connection...
                        </small>
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

        // Initialize application
        document.addEventListener('DOMContentLoaded', function() {
            checkServiceStatus();
            
            // Setup input event listeners
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });

            messageInput.addEventListener('input', function() {
                // Auto-resize input if needed
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
            });
        });

        async function sendMessage() {
            const message = messageInput.value.trim();
            if (!message || isTyping) return;

            const mode = document.getElementById('chatMode').value;
            
            // Add user message to chat
            addMessage('user', message);
            messageInput.value = '';
            messageInput.style.height = 'auto';
            
            if (!conversationStarted) {
                welcomeMessage.style.display = 'none';
                conversationStarted = true;
            }
            
            // Show typing indicator
            showTyping();
            
            try {
                const response = await fetch('/content/send', {
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
                new Date(meta.timestamp).toLocaleTimeString('id-ID') : 
                new Date().toLocaleTimeString('id-ID');
            
            let metaInfo = '';
            if (role === 'assistant') {
                const modeText = meta.mode ? `Mode: ${meta.mode.toUpperCase()}` : '';
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
            // Enhanced markdown-like formatting
            return content
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/```(.*?)```/gs, '<pre class="bg-light p-2 rounded"><code>$1</code></pre>')
                .replace(/`(.*?)`/g, '<code class="bg-light px-1 rounded">$1</code>')
                .replace(/\n\n/g, '</p><p>')
                .replace(/\n/g, '<br>')
                .replace(/^(.*)/, '<p>$1</p>');
        }

        function showTyping() {
            isTyping = true;
            sendButton.disabled = true;
            sendButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            typingIndicator.classList.add('active');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function hideTyping() {
            isTyping = false;
            sendButton.disabled = false;
            sendButton.innerHTML = '<i class="fas fa-paper-plane"></i>';
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
            if (confirm('Hapus semua percakapan? Tindakan ini tidak dapat dibatalkan.')) {
                try {
                    const response = await fetch('/content/clear', {
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
                        
                        // Show success message
                        showNotification('Chat history berhasil dihapus', 'success');
                    }
                } catch (error) {
                    console.error('Clear chat error:', error);
                    showNotification('Gagal menghapus chat history', 'error');
                }
            }
        }

        async function exportChat() {
            try {
                const response = await fetch('/content/export?format=txt');
                
                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `academic_research_chat_${Date.now()}.txt`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                    
                    showNotification('Chat berhasil diekspor', 'success');
                } else {
                    throw new Error('Export failed');
                }
            } catch (error) {
                console.error('Export error:', error);
                showNotification('Gagal mengekspor chat', 'error');
            }
        }

        async function checkServiceStatus() {
            try {
                const response = await fetch('/content/status');
                const data = await response.json();
                
                const statusDot = document.getElementById('status-dot');
                const statusText = document.getElementById('status-text');
                const modelInfo = document.getElementById('model-info');
                const serviceStatus = document.getElementById('serviceStatus');
                
                if (data.success && data.service_running) {
                    statusDot.style.background = '#28a745';
                    statusText.textContent = 'Online';
                    modelInfo.textContent = `(${data.config.model})`;
                    serviceStatus.innerHTML = '<i class="fas fa-circle text-success me-1"></i>Service connected and ready';
                } else {
                    statusDot.style.background = '#dc3545';
                    statusText.textContent = 'Offline';
                    modelInfo.textContent = '';
                    serviceStatus.innerHTML = '<i class="fas fa-circle text-danger me-1"></i>Service unavailable';
                }
            } catch (error) {
                console.error('Status check error:', error);
                const statusDot = document.getElementById('status-dot');
                const statusText = document.getElementById('status-text');
                const serviceStatus = document.getElementById('serviceStatus');
                
                statusDot.style.background = '#ffc107';
                statusText.textContent = 'Error';
                serviceStatus.innerHTML = '<i class="fas fa-circle text-warning me-1"></i>Connection error';
            }
        }

        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
            notification.style.cssText = `
                top: 20px;
                right: 20px;
                z-index: 9999;
                max-width: 400px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            `;
            
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }

        // Auto-check status every 30 seconds
        setInterval(checkServiceStatus, 30000);

        // Focus on input when page loads
        window.addEventListener('load', function() {
            messageInput.focus();
        });
    </script>
</body>
</html>