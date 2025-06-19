<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Assistant</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/github-dark.min.css">
    <style>
        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #c7d2fe;
            border-radius: 3px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #4f46e5;
        }

        /* Typing indicator */
        .typing-indicator span {
            animation: bounce 1.5s infinite ease-in-out;
            display: inline-block;
        }
        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }
        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }
        @keyframes bounce {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-5px); }
        }

        /* Enhanced Code Block Styles */
        .code-block-container {
            background-color: #1e1e1e;
            color: #d4d4d4;
            border-radius: 0.5rem;
            padding: 0;
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            overflow-x: auto;
            margin: 1rem 0;
            font-size: 0.875rem;
            line-height: 1.5;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .code-block-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #252526;
            padding: 0.5rem 1rem;
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
            border-bottom: 1px solid #333;
        }

        .code-block-title {
            display: flex;
            align-items: center;
            color: #9e9e9e;
            font-size: 0.75rem;
        }

        .code-block-language {
            background-color: #3a3d41;
            color: #ce9178;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            margin-left: 0.5rem;
            font-size: 0.7rem;
        }

        .code-block-actions {
            display: flex;
            gap: 0.5rem;
        }

        .code-block-btn {
            background-color: #3a3d41;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: background-color 0.2s;
            font-size: 0.7rem;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .code-block-btn:hover {
            background-color: #4f5257;
        }

        .code-block-btn i {
            font-size: 0.7rem;
        }

        .code-block-content {
            padding: 1rem;
            white-space: pre;
        }

        /* Terminal-like scrollbar */
        .code-block-container::-webkit-scrollbar {
            height: 8px;
        }

        .code-block-container::-webkit-scrollbar-track {
            background: #252526;
            border-radius: 0 0 0.5rem 0.5rem;
        }

        .code-block-container::-webkit-scrollbar-thumb {
            background: #3a3d41;
            border-radius: 4px;
        }

        .code-block-container::-webkit-scrollbar-thumb:hover {
            background: #4f5257;
        }

        /* Sidebar styles */
        .sidebar {
            width: 280px;
            transition: transform 0.3s ease;
        }
        .sidebar-closed {
            transform: translateX(-100%);
        }
        .sidebar-open {
            transform: translateX(0);
        }
        .chat-item {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
        }
        .chat-item:hover {
            background-color: #f3f4f6;
        }
        .active-chat {
            background-color: #e0e7ff;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar bg-white border-r border-gray-200 flex flex-col sidebar-open">
            <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-semibold">Chat History</h2>
                <button id="toggleSidebar" class="p-1 rounded-md hover:bg-gray-100">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-2 space-y-1">
                <div class="text-xs text-gray-500 px-2 py-1">Today</div>
                <div id="chatHistoryList" class="space-y-1">
                    <!-- Chat history items will be added here by JavaScript -->
                </div>
            </div>
            <div class="p-4 border-t border-gray-200">
                <button id="newChatBtn" class="w-full bg-indigo-600 text-white py-2 rounded-md hover:bg-indigo-700">
                    <i class="fas fa-plus mr-2"></i>New Chat
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <div class="bg-indigo-600 text-white p-4 flex items-center justify-between">
                <div class="flex items-center">
                    <button id="menuButton" class="mr-4 p-1 rounded-md hover:bg-indigo-700">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="text-xl font-bold">
                        <i class="fas fa-robot mr-2"></i>AI Assistant
                    </h1>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm">
                        Model: 
                        <select id="modelSelect" class="bg-indigo-700 text-white border-none rounded-md px-2 py-1">
                            @foreach($availableModels as $id => $name)
                                <option value="{{ $id }}" {{ $id === $selectedModel ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <form action="{{ route('chat.clear') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-xs bg-indigo-700 hover:bg-indigo-800 text-white px-3 py-1 rounded-full">
                            Clear Chat
                        </button>
                    </form>
                </div>
            </div>

            <!-- Chat Container -->
            <div class="flex-1 overflow-hidden flex flex-col">
                <div id="chatContainer" class="flex-1 overflow-y-auto p-4 custom-scrollbar space-y-4">
                    @foreach($conversations as $msg)
                        <div class="flex {{ $msg['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[80%] rounded-lg p-3 {{ $msg['role'] === 'user' ? 'bg-blue-50' : 'bg-white border' }}">
                                <div class="text-xs text-gray-500 mb-1">
                                    {{ $msg['role'] === 'user' ? 'You' : 'AI' }}
                                </div>
                                <div class="message-content">
                                    @if ($msg['role'] === 'user')
                                        {!! nl2br(e($msg['content'])) !!}
                                    @else
                                        <div data-raw-content="{!! htmlspecialchars($msg['content']) !!}"></div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Input Area -->
                <div class="p-4 border-t">
                    <form id="chatForm" class="flex gap-2">
                        @csrf
                        <input
                            type="text"
                            id="promptInput"
                            name="prompt"
                            class="flex-1 p-3 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                            placeholder="Type your message..."
                            autocomplete="off"
                            required
                        >
                        <button
                            type="submit"
                            id="submitBtn"
                            class="bg-indigo-600 text-white p-3 rounded-lg hover:bg-indigo-700"
                        >
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Typing Indicator -->
    <div id="typingIndicator" class="hidden fixed bottom-20 left-1/2 transform -translate-x-1/2 bg-white p-3 rounded-lg shadow-lg">
        <div class="flex items-center gap-2">
            <div class="typing-indicator flex gap-1">
                <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
            </div>
            <span>AI is typing...</span>
        </div>
    </div>

    <!-- Highlight.js Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/languages/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/languages/python.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/languages/php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/languages/html.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/languages/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/languages/java.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/languages/cpp.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/languages/sql.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/languages/bash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/languages/json.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/languages/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/languages/markdown.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatForm = document.getElementById('chatForm');
            const chatContainer = document.getElementById('chatContainer');
            const promptInput = document.getElementById('promptInput');
            const submitBtn = document.getElementById('submitBtn');
            const typingIndicator = document.getElementById('typingIndicator');
            const modelSelect = document.getElementById('modelSelect');
            const sidebar = document.getElementById('sidebar');
            const toggleSidebar = document.getElementById('toggleSidebar');
            const menuButton = document.getElementById('menuButton');
            const newChatBtn = document.getElementById('newChatBtn');
            const chatHistoryList = document.getElementById('chatHistoryList');

            // Local storage keys
            const STORAGE_KEY = 'ai_chat_history';
            const CURRENT_CHAT_KEY = 'current_chat_id';
            const MODEL_KEY = 'selected_model';

            // Initialize variables
            let currentChatId = localStorage.getItem(CURRENT_CHAT_KEY) || Date.now().toString();
            let chats = JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};
            let isTyping = false;
            let typingInterval;
            let partialResponse = '';

            // Initialize the app
            function init() {
                loadChatHistory();
                
                if (chats[currentChatId]) {
                    // The server will handle the initial messages
                } else {
                    chats[currentChatId] = {
                        id: currentChatId,
                        title: 'New Chat',
                        messages: [],
                        createdAt: new Date().toISOString(),
                        updatedAt: new Date().toISOString()
                    };
                    saveChatHistory();
                }
                
                setActiveChat(currentChatId);
                
                const savedModel = localStorage.getItem(MODEL_KEY);
                if (savedModel) {
                    modelSelect.value = savedModel;
                }
                
                scrollToBottom();
            }

            // Save chat history to local storage
            function saveChatHistory() {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(chats));
                localStorage.setItem(CURRENT_CHAT_KEY, currentChatId);
                localStorage.setItem(MODEL_KEY, modelSelect.value);
            }

            // Load chat history from local storage
            function loadChatHistory() {
                chatHistoryList.innerHTML = '';
                
                const sortedChats = Object.values(chats).sort((a, b) => 
                    new Date(b.updatedAt) - new Date(a.updatedAt)
                );
                
                const groupedChats = groupChatsByDate(sortedChats);
                
                for (const [date, dateChats] of Object.entries(groupedChats)) {
                    const dateHeader = document.createElement('div');
                    dateHeader.className = 'text-xs text-gray-500 px-2 py-1';
                    dateHeader.textContent = formatDateHeader(date);
                    chatHistoryList.appendChild(dateHeader);
                    
                    dateChats.forEach(chat => {
                        const chatItem = document.createElement('div');
                        chatItem.className = `chat-item px-2 py-1 rounded-md ${chat.id === currentChatId ? 'active-chat' : ''}`;
                        chatItem.dataset.chatId = chat.id;
                        chatItem.innerHTML = `
                            <i class="fas fa-comment-alt mr-2 text-gray-400"></i>
                            <span>${chat.title}</span>
                        `;
                        chatItem.addEventListener('click', () => loadChat(chat.id));
                        chatHistoryList.appendChild(chatItem);
                    });
                }
            }

            // Group chats by date
            function groupChatsByDate(chats) {
                const today = new Date().toISOString().split('T')[0];
                const yesterday = new Date(Date.now() - 86400000).toISOString().split('T')[0];
                const sevenDaysAgo = new Date(Date.now() - 7 * 86400000).toISOString().split('T')[0];
                const thirtyDaysAgo = new Date(Date.now() - 30 * 86400000).toISOString().split('T')[0];
                
                const grouped = {
                    'Today': [],
                    'Yesterday': [],
                    'Last 7 Days': [],
                    'Last 30 Days': [],
                    'Older': []
                };
                
                chats.forEach(chat => {
                    const chatDate = chat.updatedAt.split('T')[0];
                    
                    if (chatDate === today) {
                        grouped['Today'].push(chat);
                    } else if (chatDate === yesterday) {
                        grouped['Yesterday'].push(chat);
                    } else if (chatDate >= sevenDaysAgo) {
                        grouped['Last 7 Days'].push(chat);
                    } else if (chatDate >= thirtyDaysAgo) {
                        grouped['Last 30 Days'].push(chat);
                    } else {
                        grouped['Older'].push(chat);
                    }
                });
                
                return Object.fromEntries(
                    Object.entries(grouped).filter(([_, chats]) => chats.length > 0)
                );
            }

            // Format date header
            function formatDateHeader(date) {
                if (date === 'Today') return 'Today';
                if (date === 'Yesterday') return 'Yesterday';
                if (date === 'Last 7 Days') return 'Last 7 Days';
                if (date === 'Last 30 Days') return 'Last 30 Days';
                return 'Older';
            }

            // Set active chat in sidebar
            function setActiveChat(chatId) {
                document.querySelectorAll('.chat-item').forEach(item => {
                    item.classList.toggle('active-chat', item.dataset.chatId === chatId);
                });
            }

            // Load a specific chat
            function loadChat(chatId) {
                currentChatId = chatId;
                saveChatHistory();
                setActiveChat(chatId);
                window.location.href = `/chat?chat_id=${chatId}`;
            }

            // Create a new chat
            function createNewChat() {
                currentChatId = Date.now().toString();
                chats[currentChatId] = {
                    id: currentChatId,
                    title: 'New Chat',
                    messages: [],
                    createdAt: new Date().toISOString(),
                    updatedAt: new Date().toISOString()
                };
                saveChatHistory();
                loadChat(currentChatId);
            }

            // Update chat title based on first message
            function updateChatTitle(message) {
                if (!chats[currentChatId] || chats[currentChatId].title !== 'New Chat') return;
                
                const firstMessage = message.trim();
                const title = firstMessage.length > 30 ? firstMessage.substring(0, 30) + '...' : firstMessage;
                chats[currentChatId].title = title;
                chats[currentChatId].updatedAt = new Date().toISOString();
                saveChatHistory();
                loadChatHistory();
            }

            // Auto-scroll to bottom
            function scrollToBottom() {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }

            // Function to detect programming language from code block
            function detectLanguage(code) {
                const langMatch = code.match(/^```(\w+)\n/);
                if (langMatch && langMatch[1]) {
                    return langMatch[1].toLowerCase();
                }

                const firstLine = code.split('\n')[0].trim();

                // Avoid Blade parsing issues by splitting up problematic strings
                if (firstLine.startsWith("<" + "?php") || firstLine.includes("<" + "?=")) return 'php';
                if (firstLine.includes('<html') || firstLine.includes('<!DOCTYPE html')) return 'html';
                if (firstLine.includes('import ') || firstLine.includes('export ')) return 'javascript';
                if (firstLine.includes('def ') || firstLine.startsWith('class ')) return 'python';
                if (firstLine.includes('function ') || firstLine.includes('=>')) return 'javascript';
                if (firstLine.includes('package ') || firstLine.includes('import ')) return 'java';
                if (firstLine.includes('#include ') || firstLine.includes('using namespace')) return 'cpp';
                if (firstLine.includes('using ') && firstLine.includes(';')) return 'csharp';
                if (firstLine.includes('fn ') || firstLine.includes('let ')) return 'rust';
                if (firstLine.includes('<' + '?xml')) return 'xml';
                if (firstLine.includes('SELECT ') || firstLine.includes('INSERT ')) return 'sql';

                return 'text';
            }

            // Function to handle file download
            function downloadCode(code, language) {
                let extension = 'txt';
                const extensionMap = {
                    'javascript': 'js',
                    'typescript': 'ts',
                    'python': 'py',
                    'java': 'java',
                    'cpp': 'cpp',
                    'c': 'c',
                    'csharp': 'cs',
                    'php': 'php',
                    'html': 'html',
                    'css': 'css',
                    'sql': 'sql',
                    'ruby': 'rb',
                    'go': 'go',
                    'rust': 'rs',
                    'swift': 'swift',
                    'kotlin': 'kt',
                    'bash': 'sh',
                    'shell': 'sh',
                    'json': 'json',
                    'xml': 'xml',
                    'markdown': 'md'
                };
                
                if (extensionMap[language]) {
                    extension = extensionMap[language];
                }
                
                const filename = `code-${Date.now()}.${extension}`;
                const blob = new Blob([code], { type: 'text/plain' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            }

            // Helper to escape HTML characters for plain text display
            function escapeHtml(text) {
                var map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, function(m) { return map[m]; });
            }

            // Function to process and display AI response with enhanced code blocks
            function renderAiResponse(targetElement, rawResponseText) {
                const codeBlockRegex = /```(\w+)?\n([\s\S]*?)\n```/g;
                let lastPos = 0;
                let htmlParts = '';

                let match;
                while ((match = codeBlockRegex.exec(rawResponseText)) !== null) {
                    const fullMatch = match[0];
                    const language = match[1] || '';
                    const codeContent = match[2];
                    const startPos = match.index;
                    const endPos = codeBlockRegex.lastIndex;

                    if (startPos > lastPos) {
                        const plainText = rawResponseText.substring(lastPos, startPos);
                        if (plainText.trim() !== '') {
                            htmlParts += `<p class="mb-2">${nl2br(escapeHtml(plainText))}</p>`;
                        }
                    }

                    const detectedLanguage = language || detectLanguage(codeContent);
                    
                    htmlParts += `
                        <div class="code-block-container">
                            <div class="code-block-header">
                                <div class="code-block-title">
                                    <i class="fas fa-terminal"></i>
                                    <span class="code-block-language">${detectedLanguage}</span>
                                </div>
                                <div class="code-block-actions">
                                    <button class="code-block-btn" onclick="copyCode(this, '${escapeHtml(codeContent)}')">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                    <button class="code-block-btn" onclick="downloadCodeBlock('${escapeHtml(codeContent)}', '${detectedLanguage}')">
                                        <i class="fas fa-download"></i> Download
                                    </button>
                                </div>
                            </div>
                            <pre class="code-block-content"><code class="language-${detectedLanguage}">${escapeHtml(codeContent)}</code></pre>
                        </div>
                    `;
                    lastPos = endPos;
                }

                if (lastPos < rawResponseText.length) {
                    const plainText = rawResponseText.substring(lastPos);
                    if (plainText.trim() !== '') {
                        htmlParts += `<p class="mb-2">${nl2br(escapeHtml(plainText))}</p>`;
                    }
                }

                if (htmlParts === '' && rawResponseText.trim() !== '') {
                    htmlParts = `<p class="mb-2">${nl2br(escapeHtml(rawResponseText))}</p>`;
                }

                targetElement.innerHTML = htmlParts;
                
                // Apply syntax highlighting
                document.querySelectorAll('pre code').forEach((block) => {
                    hljs.highlightElement(block);
                });
            }

            // Helper function to convert newlines to <br> tags
            function nl2br(str) {
                return str.replace(/(?:\r\n|\r|\n)/g, '<br>');
            }

            // Typewriter effect for AI response
            function typeWriter(element, text, speed = 20, callback) {
                let i = 0;
                partialResponse = '';
                
                function typing() {
                    if (i < text.length) {
                        partialResponse += text.charAt(i);
                        renderAiResponse(element, partialResponse);
                        i++;
                        scrollToBottom();
                        setTimeout(typing, speed);
                    } else if (callback) {
                        callback();
                    }
                }
                
                typing();
            }

            // Global function for downloading code blocks
            window.downloadCodeBlock = function(code, language) {
                downloadCode(code, language);
            };

            // Updated copy function with feedback
            window.copyCode = function(button, codeText) {
                navigator.clipboard.writeText(codeText).then(() => {
                    const originalText = button.innerHTML;
                    button.innerHTML = '<i class="fas fa-check"></i> Copied!';
                    setTimeout(() => {
                        button.innerHTML = originalText;
                    }, 2000);
                }).catch(err => {
                    console.error('Failed to copy text: ', err);
                    button.innerHTML = '<i class="fas fa-times"></i> Failed';
                    setTimeout(() => {
                        button.innerHTML = originalText;
                    }, 2000);
                });
            };

            // Process existing AI messages on page load
            document.querySelectorAll('.message-content[data-raw-content]').forEach(aiMessageDiv => {
                const rawContent = aiMessageDiv.dataset.rawContent;
                renderAiResponse(aiMessageDiv, rawContent);
                aiMessageDiv.removeAttribute('data-raw-content');
            });

            // Handle form submission
            chatForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const prompt = promptInput.value.trim();
                if (!prompt) return;

                const userMsgHtml = `
                    <div class="flex justify-end">
                        <div class="max-w-[80%] rounded-lg p-3 bg-blue-50">
                            <div class="text-xs text-gray-500 mb-1">You</div>
                            <div>${nl2br(escapeHtml(prompt))}</div>
                        </div>
                    </div>
                `;
                chatContainer.insertAdjacentHTML('beforeend', userMsgHtml);
                promptInput.value = '';
                scrollToBottom();

                if (chatContainer.querySelectorAll('.flex.justify-end').length === 1) {
                    updateChatTitle(prompt);
                }

                typingIndicator.classList.remove('hidden');
                scrollToBottom();

                try {
                    promptInput.disabled = true;
                    submitBtn.disabled = true;

                    const aiMsgWrapper = document.createElement('div');
                    aiMsgWrapper.className = 'flex justify-start';
                    aiMsgWrapper.innerHTML = `
                        <div class="max-w-[80%] rounded-lg p-3 bg-white border">
                            <div class="text-xs text-gray-500 mb-1">AI</div>
                            <div class="message-content"></div>
                        </div>
                    `;
                    chatContainer.appendChild(aiMsgWrapper);
                    scrollToBottom();

                    const aiMessageContentDiv = aiMsgWrapper.querySelector('.message-content');

                    const response = await fetch('{{ route("chat.ask") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ 
                            prompt: prompt,
                            model: modelSelect.value
                        })
                    });

                    if (!response.ok) throw new Error('Network error');

                    const data = await response.json();
                    const aiResponseText = data.response;

                    if (chats[currentChatId]) {
                        chats[currentChatId].messages.push(
                            { role: 'user', content: prompt },
                            { role: 'assistant', content: aiResponseText }
                        );
                        chats[currentChatId].updatedAt = new Date().toISOString();
                        saveChatHistory();
                        loadChatHistory();
                    }

                    typingIndicator.classList.add('hidden');

                    typeWriter(aiMessageContentDiv, aiResponseText, 20, () => {
                        scrollToBottom();
                    });

                } catch (error) {
                    alert('Error: ' + error.message);
                } finally {
                    typingIndicator.classList.add('hidden');
                    promptInput.disabled = false;
                    submitBtn.disabled = false;
                    promptInput.focus();
                    scrollToBottom();
                }
            });

            // Handle model change
            modelSelect.addEventListener('change', async function() {
                try {
                    const response = await fetch('{{ route("chat.change-model") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ 
                            model: modelSelect.value
                        })
                    });

                    if (!response.ok) throw new Error('Failed to change model');
                    
                    localStorage.setItem(MODEL_KEY, modelSelect.value);
                } catch (error) {
                    console.error('Error changing model:', error);
                    alert('Failed to change model');
                }
            });

            // Toggle sidebar
            toggleSidebar.addEventListener('click', () => {
                sidebar.classList.add('sidebar-closed');
            });

            menuButton.addEventListener('click', () => {
                sidebar.classList.remove('sidebar-closed');
            });

            // New chat button
            newChatBtn.addEventListener('click', createNewChat);

            // Initialize the app
            init();
        });
    </script>
</body>
</html>