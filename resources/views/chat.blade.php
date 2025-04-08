<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Assistant</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f4ff',
                            100: '#e0e9ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        },
                        ai: {
                            bubble: '#ffffff',
                        },
                        user: {
                            bubble: '#f0f4ff',
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.3s ease-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                        'pulse-slow': 'pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            'from': { opacity: '0', transform: 'translateY(10px)' },
                            'to': { opacity: '1', transform: 'translateY(0)' },
                        },
                        slideUp: {
                            'from': { opacity: '0', transform: 'translate(-50%, 20px)' },
                            'to': { opacity: '1', transform: 'translate(-50%, 0)' },
                        }
                    }
                }
            }
        }
    </script>
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

        /* Typing indicator animation */
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
    </style>
</head>
<body class="bg-gray-50">
    <!-- Main Container -->
    <div class="flex flex-col max-w-3xl mx-auto h-screen bg-white shadow-lg">
        <!-- Header Section -->
        <div class="bg-primary-600 text-white p-4 text-center rounded-b-xl shadow-md">
            <h2 class="text-xl font-semibold m-0">
                <i class="fas fa-robot mr-2"></i>AI Assistant
            </h2>
        </div>

        <!-- Chat History Section -->
        <div
            id="chatContainer"
            class="flex-1 overflow-y-auto p-6 custom-scrollbar bg-gray-50 space-y-4"
            data-conversations='@json($conversations)'>
            <!-- Messages will be dynamically inserted here -->
        </div>

        <!-- Input Section -->
        <div class="p-4 bg-white border-t border-gray-200 relative">
            <!-- Model Selector Dropdown -->
            <div class="absolute -top-12 right-0 bg-white p-2 rounded-lg shadow-md border border-gray-200 z-10">
                <select
                    id="modelSelect"
                    class="text-sm rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                    <option value="deepseek-r1:1.5b" {{ $selectedModel === 'deepseek-r1:1.5b' ? 'selected' : '' }}>deepseek-r1:1.5b</option>
                    <option value="deepseek-r1:latest" {{ $selectedModel === 'deepseek-r1:latest' ? 'selected' : '' }}>deepseek-r1:latest</option>
                    <option value="qwen2.5:3b" {{ $selectedModel === 'qwen2.5:3b' ? 'selected' : '' }}>qwen2.5:3b</option>
                </select>
            </div>

            <!-- Input Form -->
            <form id="chatForm" class="relative">
                @csrf
                <div class="relative">
                    <input
                        type="text"
                        id="promptInput"
                        name="prompt"
                        class="w-full p-3 pr-12 rounded-full border border-gray-300 focus:border-primary-500 focus:ring-primary-500 shadow-sm"
                        placeholder="Ask something..."
                        required
                        autocomplete="off">
                    <button
                        type="submit"
                        id="submitBtn"
                        class="absolute right-2 bottom-2 bg-primary-600 text-white rounded-full w-9 h-9 flex items-center justify-center hover:bg-primary-700 transition-all duration-200 hover:scale-105">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- Error Message (if any) -->
        @if(isset($error))
            <div id="errorMessage" class="fixed bottom-20 left-1/2 transform -translate-x-1/2 z-50 animate-slide-up">
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-lg">
                    {{ $error }}
                </div>
            </div>
        @endif
    </div>

    <!-- Typing Indicator (hidden by default) -->
    <div id="typingIndicator" class="hidden">
        <div class="message ai-message animate-fade-in">
            <div class="message-meta">
                <i class="fas fa-robot"></i> Assistant
            </div>
            <div class="typing-indicator flex space-x-1 mt-1">
                <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatContainer = document.getElementById('chatContainer');
            const chatForm = document.getElementById('chatForm');
            const promptInput = document.getElementById('promptInput');
            const modelSelect = document.getElementById('modelSelect');
            const submitBtn = document.getElementById('submitBtn');
            const typingIndicator = document.getElementById('typingIndicator').innerHTML;

            // Load initial conversations
            const conversations = JSON.parse(chatContainer.dataset.conversations);
            renderConversations(conversations);

            // Auto-scroll to bottom
            scrollToBottom();

            // Handle form submission with AJAX
            chatForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const prompt = promptInput.value.trim();
                if (!prompt) return;

                const model = modelSelect.value;

                // Add user message immediately
                addMessage({
                    sender: 'user',
                    content: prompt
                });

                // Clear input
                promptInput.value = '';

                // Show typing indicator
                chatContainer.insertAdjacentHTML('beforeend', typingIndicator);
                scrollToBottom();

                try {
                    // Disable input while waiting for response
                    promptInput.disabled = true;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner animate-spin"></i>';

                    // Send request to server
                    const response = await fetch('{{ route("chat.ask") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            prompt: prompt,
                            model: model
                        })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.error || 'Something went wrong');
                    }

                    // Remove typing indicator
                    const typingElements = document.querySelectorAll('.typing-indicator');
                    typingElements.forEach(el => el.parentElement.remove());

                    // Add AI response
                    addMessage({
                        sender: 'ai',
                        content: data.response
                    });

                } catch (error) {
                    // Remove typing indicator
                    const typingElements = document.querySelectorAll('.typing-indicator');
                    typingElements.forEach(el => el.parentElement.remove());

                    // Show error message
                    showError(error.message);
                } finally {
                    // Re-enable input
                    promptInput.disabled = false;
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
                    promptInput.focus();
                }
            });

            // Render initial conversations
            function renderConversations(conversations) {
                if (!conversations || conversations.length === 0) {
                    // Show welcome message if no conversations
                    addMessage({
                        sender: 'ai',
                        content: "Hello! I'm your AI assistant. How can I help you today?"
                    });
                    return;
                }

                conversations.forEach(convo => {
                    addMessage({
                        sender: 'user',
                        content: convo.prompt
                    });

                    addMessage({
                        sender: 'ai',
                        content: convo.response
                    });
                });
            }

            // Add a new message to the chat
            function addMessage({sender, content}) {
                const isUser = sender === 'user';
                const messageHtml = `
                    <div class="flex ${isUser ? 'justify-end' : 'justify-start'} animate-fade-in">
                        <div class="max-w-[85%] lg:max-w-[75%]">
                            <div class="text-xs text-gray-500 mb-1 flex items-center">
                                <i class="fas ${isUser ? 'fa-user' : 'fa-robot'} mr-1"></i>
                                ${isUser ? 'You' : 'Assistant'}
                            </div>
                            <div class="p-3 rounded-lg ${isUser ?
                                'bg-user-bubble rounded-tr-none' :
                                'bg-ai-bubble border border-gray-200 rounded-tl-none'} shadow-sm">
                                ${content.replace(/\n/g, '<br>')}
                            </div>
                        </div>
                    </div>
                `;

                chatContainer.insertAdjacentHTML('beforeend', messageHtml);
                scrollToBottom();
            }

            // Show error message
            function showError(message) {
                const errorHtml = `
                    <div id="errorMessage" class="fixed bottom-20 left-1/2 transform -translate-x-1/2 z-50 animate-slide-up">
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-lg">
                            ${message}
                        </div>
                    </div>
                `;

                // Remove existing error if any
                const existingError = document.getElementById('errorMessage');
                if (existingError) existingError.remove();

                document.body.insertAdjacentHTML('beforeend', errorHtml);

                // Auto-hide after 5 seconds
                setTimeout(() => {
                    const error = document.getElementById('errorMessage');
                    if (error) error.remove();
                }, 5000);
            }

            // Scroll to bottom of chat
            function scrollToBottom() {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }

            // Focus input on page load
            promptInput.focus();
        });
    </script>
</body>
</html>
