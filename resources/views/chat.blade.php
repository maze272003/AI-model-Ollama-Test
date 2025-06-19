<!DOCTYPE html>
<html lang="en" class="{{ session('dark_mode') ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JM AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            600: '#4f46e5',
                            700: '#4338ca',
                        },
                        dark: {
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/github-dark.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/github.min.css">
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
        .dark .custom-scrollbar::-webkit-scrollbar-track {
            background: #1e293b;
        }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
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
        .dark .code-block-container {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
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
            position: fixed;
            height: 100vh;
            z-index: 40;
            transform: translateX(-100%);
        }
        @media (min-width: 768px) {
            .sidebar {
                transform: translateX(0);
            }
        }
        .sidebar-open {
            transform: translateX(0);
        }
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 30;
        }
        .sidebar-overlay-open {
            display: block;
        }
        .chat-item {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
        }
        .chat-item:hover {
            background-color: rgba(79, 70, 229, 0.1);
        }
        .dark .chat-item:hover {
            background-color: rgba(79, 70, 229, 0.2);
        }
        .active-chat {
            background-color: rgba(79, 70, 229, 0.2);
        }

        /* Server stats indicator */
        .server-stat {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.75rem;
        }
        .stat-bar {
            height: 6px;
            width: 40px;
            background-color: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }
        .dark .stat-bar {
            background-color: #334155;
        }
        .stat-bar-fill {
            height: 100%;
            border-radius: 3px;
        }
        .cpu-fill {
            background-color: #10b981;
        }
        .memory-fill {
            background-color: #3b82f6;
        }
        .signal-indicator {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .signal-bar {
            width: 3px;
            background-color: #e5e7eb;
            border-radius: 3px;
        }
        .dark .signal-bar {
            background-color: #334155;
        }
        .signal-bar.active {
            background-color: #10b981;
        }

        /* Disclaimer Modal */
        .disclaimer-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 100;
        }
        .disclaimer-content {
            background-color: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .dark .disclaimer-content {
            background-color: #1e293b;
            color: white;
        }
        .disclaimer-title {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #4f46e5;
        }
        .disclaimer-text {
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        .disclaimer-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }
        .disclaimer-btn {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            cursor: pointer;
        }
        .disclaimer-cancel {
            background-color: #f3f4f6;
            color: #4b5563;
        }
        .dark .disclaimer-cancel {
            background-color: #334155;
            color: #e5e7eb;
        }
        .disclaimer-accept {
            background-color: #4f46e5;
            color: white;
        }
        .dark .disclaimer-accept {
            background-color: #6366f1;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-dark-900">
    <!-- Disclaimer Modal -->
    <div id="disclaimerModal" class="disclaimer-modal">
        <div class="disclaimer-content">
            <div class="disclaimer-title">Important Notice</div>
            <div class="disclaimer-text">
                <p>This AI chatbot is currently under development and not fully functional yet. The system may have issues with:</p>
                <ul class="list-disc pl-5 mt-2 space-y-1">
                    <li>Server stability and response times.</li>
                    <li>Code generation accuracy.</li>
                    <li>General knowledge responses.</li>
                    <li>Maximum words can input is limited 2k words only.</li>
                    <li>Please phone only web responsiveness is under development.</li>
                </ul>
                <p class="mt-3">We're using a limited AI model (LLM) during this development phase. Please expect occasional errors or incomplete responses.</p>
            </div>
            <div class="disclaimer-buttons">
                <button id="disclaimerCancel" class="disclaimer-btn disclaimer-cancel">Cancel</button>
                <button id="disclaimerAccept" class="disclaimer-btn disclaimer-accept">Accept & Continue</button>
            </div>
        </div>
    </div>

    <div class="flex h-screen">
        <!-- Sidebar Overlay -->
        <div id="sidebarOverlay" class="sidebar-overlay"></div>

        <!-- Sidebar -->
        <div id="sidebar" class="sidebar bg-white dark:bg-dark-800 border-r border-gray-200 dark:border-gray-700 flex flex-col">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-lg font-semibold dark:text-white">Chat History</h2>
                <button id="toggleSidebar" class="p-1 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-2 space-y-1">
                <div id="chatHistoryList" class="space-y-1">
                    <!-- Chat history items will be loaded dynamically -->
                </div>
            </div>
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                <button id="newChatBtn" class="w-full bg-primary-600 dark:bg-primary-700 text-white py-2 rounded-md hover:bg-primary-700 dark:hover:bg-primary-600">
                    <i class="fas fa-plus mr-2"></i>New Chat
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300">
            <!-- Header -->
            <div class="bg-primary-600 dark:bg-dark-800 text-white p-4 flex items-center justify-between">
                <div class="flex items-center">
                    <button id="menuButton" class="mr-4 p-1 rounded-md hover:bg-primary-700 dark:hover:bg-gray-700 md:hidden">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="text-xl font-bold">
                        <i class="fas fa-robot mr-2"></i>JM AI
                    </h1>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-3">
                        <div class="server-stat text-white dark:text-gray-300">
                            <span class="hidden sm:inline">CPU:</span>
                            <span id="cpuUsage">{{ $serverStats['cpu'] ?? 0 }}%</span>
                            <div class="stat-bar">
                                <div id="cpuBar" class="stat-bar-fill cpu-fill" style="width: {{ $serverStats['cpu'] ?? 0 }}%"></div>
                            </div>
                        </div>
                        <div class="server-stat text-white dark:text-gray-300">
                            <span class="hidden sm:inline">RAM:</span>
                            <span id="memoryUsage">{{ $serverStats['memory'] ?? 0 }}%</span>
                            <div class="stat-bar">
                                <div id="memoryBar" class="stat-bar-fill memory-fill" style="width: {{ $serverStats['memory'] ?? 0 }}%"></div>
                            </div>
                        </div>
                        <div class="signal-indicator">
                            <div id="signal1" class="signal-bar h-2"></div>
                            <div id="signal2" class="signal-bar h-3"></div>
                            <div id="signal3" class="signal-bar h-4"></div>
                        </div>
                        <span id="lastUpdated" class="text-xs hidden sm:block">{{ $serverStats['updated_at'] ?? 'N/A' }}</span>
                    </div>
                    <button id="darkModeToggle" class="p-2 rounded-full hover:bg-primary-700 dark:hover:bg-gray-700">
                        <i class="fas {{ session('dark_mode') ? 'fa-sun' : 'fa-moon' }}"></i>
                    </button>
                    <form action="{{ route('chat.clear') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-xs bg-primary-700 hover:bg-primary-800 text-white px-3 py-1 rounded-full">
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
                            <div class="max-w-[90%] md:max-w-[80%] rounded-lg p-3 {{ $msg['role'] === 'user' ? 'bg-blue-50 dark:bg-blue-900/30' : 'bg-white dark:bg-dark-800 border dark:border-gray-700' }}">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">
                                    {{ $msg['role'] === 'user' ? 'You' : 'AI' }}
                                </div>
                                <div class="message-content dark:text-gray-200">
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
                <div class="p-4 border-t dark:border-gray-700">
                    <form id="chatForm" class="flex gap-2">
                        @csrf
                        <input type="hidden" name="chat_id" value="{{ $currentChatId }}">
                        <input
                            type="text"
                            id="promptInput"
                            name="prompt"
                            class="flex-1 p-3 border rounded-lg focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-600 bg-white dark:bg-dark-800 border-gray-300 dark:border-gray-700 dark:text-white"
                            placeholder="Type your message..."
                            autocomplete="off"
                            required
                            maxlength="2000"
                        >
                        <button
                            type="submit"
                            id="submitBtn"
                            class="bg-primary-600 dark:bg-primary-700 text-white p-3 rounded-lg hover:bg-primary-700 dark:hover:bg-primary-600"
                        >
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Typing Indicator -->
    <div id="typingIndicator" class="hidden fixed bottom-20 left-1/2 transform -translate-x-1/2 bg-white dark:bg-dark-800 p-3 rounded-lg shadow-lg dark:shadow-gray-900 border dark:border-gray-700">
        <div class="flex items-center gap-2 dark:text-white">
            <div class="typing-indicator flex gap-1">
                <span class="w-2 h-2 bg-gray-400 dark:bg-gray-300 rounded-full"></span>
                <span class="w-2 h-2 bg-gray-400 dark:bg-gray-300 rounded-full"></span>
                <span class="w-2 h-2 bg-gray-400 dark:bg-gray-300 rounded-full"></span>
            </div>
            <span>AI is Thinking...</span>
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
        // Check if disclaimer has been accepted
        const disclaimerAccepted = localStorage.getItem('disclaimerAccepted');
        const disclaimerModal = document.getElementById('disclaimerModal');
        
        if (!disclaimerAccepted) {
            disclaimerModal.style.display = 'flex';
        } else {
            disclaimerModal.style.display = 'none';
        }
        
        document.getElementById('disclaimerCancel').addEventListener('click', function() {
            window.location.href = 'https://github.com/maze272003';
        });
        
        document.getElementById('disclaimerAccept').addEventListener('click', function() {
            localStorage.setItem('disclaimerAccepted', 'true');
            disclaimerModal.style.display = 'none';
        });

        const chatForm = document.getElementById('chatForm');
        const chatContainer = document.getElementById('chatContainer');
        const promptInput = document.getElementById('promptInput');
        const submitBtn = document.getElementById('submitBtn');
        const typingIndicator = document.getElementById('typingIndicator');
        const sidebar = document.getElementById('sidebar');
        const toggleSidebar = document.getElementById('toggleSidebar');
        const menuButton = document.getElementById('menuButton');
        const newChatBtn = document.getElementById('newChatBtn');
        const chatHistoryList = document.getElementById('chatHistoryList');
        const darkModeToggle = document.getElementById('darkModeToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const cpuUsage = document.getElementById('cpuUsage');
        const memoryUsage = document.getElementById('memoryUsage');
        const cpuBar = document.getElementById('cpuBar');
        const memoryBar = document.getElementById('memoryBar');
        const lastUpdated = document.getElementById('lastUpdated');
        const signal1 = document.getElementById('signal1');
        const signal2 = document.getElementById('signal2');
        const signal3 = document.getElementById('signal3');

        // Initialize the app
        async function init() {
            loadFullChatHistory();
            updateSignalStrength();
            startServerStatsPolling();
            processExistingMessages();
            
            if ('{{ $currentChatId }}') {
                loadChatDetails('{{ $currentChatId }}');
            }
        }

        // Process existing AI messages on page load
        function processExistingMessages() {
            document.querySelectorAll('.message-content[data-raw-content]').forEach(aiMessageDiv => {
                const rawContent = aiMessageDiv.dataset.rawContent;
                renderAiResponse(aiMessageDiv, rawContent);
                aiMessageDiv.removeAttribute('data-raw-content');
            });
        }

        // Update signal strength indicator
        function updateSignalStrength() {
            const strength = Math.min(3, Math.max(1, Math.floor(Math.random() * 4)));
            
            signal1.classList.toggle('active', strength >= 1);
            signal2.classList.toggle('active', strength >= 2);
            signal3.classList.toggle('active', strength >= 3);
            
            setTimeout(updateSignalStrength, 3000);
        }

        // Poll server stats every 30 seconds
        function startServerStatsPolling() {
            fetchServerStats();
            setInterval(fetchServerStats, 30000);
        }

        // Fetch server stats
        async function fetchServerStats() {
            try {
                const response = await fetch('/chat/server-stats');
                if (response.ok) {
                    const data = await response.json();
                    updateServerStatsDisplay(data);
                }
            } catch (error) {
                console.error('Error fetching server stats:', error);
            }
        }

        // Update server stats display
        function updateServerStatsDisplay(stats) {
            cpuUsage.textContent = `${stats.cpu}%`;
            memoryUsage.textContent = `${stats.memory}%`;
            cpuBar.style.width = `${stats.cpu}%`;
            memoryBar.style.width = `${stats.memory}%`;
            lastUpdated.textContent = stats.updated_at;
        }

        // Load full chat history from server
        async function loadFullChatHistory() {
            try {
                const response = await fetch('/chat/history');
                if (response.ok) {
                    const data = await response.json();
                    renderChatHistory(data);
                }
            } catch (error) {
                console.error('Error loading chat history:', error);
            }
        }

        // Load chat details
        async function loadChatDetails(chatId) {
            try {
                const response = await fetch(`/chat/details/${chatId}`);
                if (response.ok) {
                    const data = await response.json();
                    displayChatMessages(data);
                }
            } catch (error) {
                console.error('Error loading chat details:', error);
            }
        }

        // Render chat history in sidebar
        function renderChatHistory(chats) {
            chatHistoryList.innerHTML = '';
            
            const groupedChats = groupChatsByDate(chats);
            
            for (const [date, dateChats] of Object.entries(groupedChats)) {
                const dateHeader = document.createElement('div');
                dateHeader.className = 'text-xs text-gray-500 dark:text-gray-400 px-2 py-1';
                dateHeader.textContent = formatDateHeader(date);
                chatHistoryList.appendChild(dateHeader);
                
                dateChats.forEach(chat => {
                    const lastMessage = chat.messages[chat.messages.length - 1];
                    const preview = lastMessage?.question || lastMessage?.ai_answer || 'Empty chat';
                    const truncatedPreview = preview.length > 30 ? preview.substring(0, 30) + '...' : preview;
                    
                    const chatItem = document.createElement('div');
                    chatItem.className = `chat-item px-2 py-1 rounded-md ${chat.chat_id === '{{ $currentChatId }}' ? 'active-chat' : ''}`;
                    chatItem.dataset.chatId = chat.chat_id;
                    chatItem.innerHTML = `
                        <i class="fas fa-comment-alt mr-2 text-gray-400"></i>
                        <div class="flex flex-col">
                            <span class="dark:text-gray-300">Chat ${chat.chat_id.substring(0, 8)}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 truncate">${truncatedPreview}</span>
                        </div>
                    `;
                    chatItem.addEventListener('click', () => loadChatDetails(chat.chat_id));
                    chatHistoryList.appendChild(chatItem);
                });
            }
        }

        // Display chat messages in main view
        function displayChatMessages(chatData) {
            chatContainer.innerHTML = '';
            
            chatData.messages.forEach(msg => {
                const msgDiv = document.createElement('div');
                msgDiv.className = `flex ${msg.role === 'user' ? 'justify-end' : 'justify-start'}`;
                
                msgDiv.innerHTML = `
                    <div class="max-w-[90%] md:max-w-[80%] rounded-lg p-3 ${msg.role === 'user' ? 'bg-blue-50 dark:bg-blue-900/30' : 'bg-white dark:bg-dark-800 border dark:border-gray-700'}">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">
                            ${msg.role === 'user' ? 'You' : 'AI'} 
                            <span class="text-gray-400 dark:text-gray-500">(${new Date(msg.created_at).toLocaleTimeString()})</span>
                            
                        </div>
                        <div class="message-content dark:text-gray-200">
                            ${msg.role === 'user' ? nl2br(escapeHtml(msg.content)) : ''}
                        </div>
                    </div>
                `;
                
                chatContainer.appendChild(msgDiv);
                
                if (msg.role === 'assistant') {
                    const contentDiv = msgDiv.querySelector('.message-content');
                    renderAiResponse(contentDiv, msg.content);
                }
            });
            
            scrollToBottom();
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
                const chatDate = chat.messages[0]?.created_at.split('T')[0] || today;
                
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

        // Function to detect programming language from code block
        function detectLanguage(code) {
            const langMatch = code.match(/^```(\w+)\n/);
            if (langMatch && langMatch[1]) {
                return langMatch[1].toLowerCase();
            }

            const firstLine = code.split('\n')[0].trim();

            if (firstLine.startsWith("<" + "?php") || firstLine.includes("<" + "?=")) return 'php';
            if (firstLine.includes('<html') || firstLine.includes('<!DOCTYPE html')) return 'html';
            if (firstLine.includes('import ') || firstLine.includes('export ')) return 'javascript';
            if (firstLine.includes('def ') || firstLine.startsWith('class ')) return 'python';
            if (firstLine.includes('function ') || firstLine.includes('=>')) return 'javascript';
            if (firstLine.includes('package ') || firstLine.includes('import ')) return 'java';
            if (firstLine.includes('#include ') || firstLine.includes('using namespace')) return 'cpp';
            if (firstLine.includes('using ') && firstLine.includes(';')) return 'csharp';
            if (firstLine.includes('fn ') || firstLine.includes('let ')) return 'rust';
            if (firstLine.includes('<' + "?xml")) return 'xml';
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
            let partialResponse = '';
            
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

        // Handle form submission
        chatForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const prompt = promptInput.value.trim();
            if (!prompt) return;

            const userMsgHtml = `
                <div class="flex justify-end">
                    <div class="max-w-[90%] md:max-w-[80%] rounded-lg p-3 bg-blue-50 dark:bg-blue-900/30">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">You</div>
                        <div class="dark:text-white">${nl2br(escapeHtml(prompt))}</div>
                    </div>
                </div>
            `;
            chatContainer.insertAdjacentHTML('beforeend', userMsgHtml);
            promptInput.value = '';
            scrollToBottom();

            typingIndicator.classList.remove('hidden');
            scrollToBottom();

            try {
                promptInput.disabled = true;
                submitBtn.disabled = true;

                const aiMsgWrapper = document.createElement('div');
                aiMsgWrapper.className = 'flex justify-start';
                aiMsgWrapper.innerHTML = `
                    <div class="max-w-[90%] md:max-w-[80%] rounded-lg p-3 bg-white dark:bg-dark-800 border dark:border-gray-700">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">AI</div>
                        <div class="message-content dark:text-gray-200"></div>
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
                        chat_id: '{{ $currentChatId }}'
                    })
                });

                if (!response.ok) throw new Error('Network error');

                const data = await response.json();
                const aiResponseText = data.response;

                typingIndicator.classList.add('hidden');

                typeWriter(aiMessageContentDiv, aiResponseText, 20, () => {
                    scrollToBottom();
                    loadFullChatHistory();
                });

                if (data.serverStats) {
                    updateServerStatsDisplay(data.serverStats);
                }

            } catch (error) {
                console.error('Error:', error);
                typingIndicator.classList.add('hidden');
                
                const errorMsg = document.createElement('div');
                errorMsg.className = 'flex justify-start';
                errorMsg.innerHTML = `
                    <div class="max-w-[90%] md:max-w-[80%] rounded-lg p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800">
                        <div class="text-xs text-red-500 dark:text-red-300 mb-1">Error</div>
                        <div class="text-red-700 dark:text-red-300">Sorry, there was an error processing your request. Please try again.</div>
                    </div>
                `;
                chatContainer.appendChild(errorMsg);
                scrollToBottom();
            } finally {
                promptInput.disabled = false;
                submitBtn.disabled = false;
                promptInput.focus();
            }
        });

        // Auto-scroll to bottom
        function scrollToBottom() {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        // Toggle sidebar
        toggleSidebar.addEventListener('click', () => {
            sidebar.classList.remove('sidebar-open');
            sidebarOverlay.classList.remove('sidebar-overlay-open');
        });

        menuButton.addEventListener('click', () => {
            sidebar.classList.add('sidebar-open');
            sidebarOverlay.classList.add('sidebar-overlay-open');
        });

        // Close sidebar when clicking overlay
        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('sidebar-open');
            sidebarOverlay.classList.remove('sidebar-overlay-open');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth < 768) {
                const isClickInsideSidebar = sidebar.contains(event.target);
                const isClickOnMenuButton = menuButton.contains(event.target);
                
                if (!isClickInsideSidebar && !isClickOnMenuButton && sidebar.classList.contains('sidebar-open')) {
                    sidebar.classList.remove('sidebar-open');
                    sidebarOverlay.classList.remove('sidebar-overlay-open');
                }
            }
        });

        // New chat button
        newChatBtn.addEventListener('click', () => {
            window.location.href = '/chat';
        });

        // Dark mode toggle
        darkModeToggle.addEventListener('click', () => {
            fetch('{{ route("chat.toggle-dark-mode") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(() => {
                window.location.reload();
            });
        });

        // Initialize the app
        init();
    });
</script>
</body>
</html>