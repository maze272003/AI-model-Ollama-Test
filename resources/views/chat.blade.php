<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Assistant</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        /* Code blocks */
        .code-block-container {
            background-color: #2d2d2d;
            color: #f8f8f2;
            border-radius: 0.5rem;
            padding: 1rem;
            font-family: monospace;
            overflow-x: auto;
        }
        .code-block-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
            color: #9e9e9e;
            font-size: 0.75rem;
        }
        .copy-btn {
            background-color: #444;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            cursor: pointer;
        }
        .copy-btn:hover {
            background-color: #555;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex flex-col max-w-3xl mx-auto h-screen bg-white shadow-lg">
        <!-- Header -->
        <div class="bg-indigo-600 text-white p-4 text-center rounded-b-xl">
            <h1 class="text-xl font-bold">
                <i class="fas fa-robot mr-2"></i>AI Assistant
            </h1>
            <div class="text-sm mt-1">
                Model: <span id="currentModel">{{ $selectedModel }}</span>
            </div>
            <form action="{{ route('chat.clear') }}" method="POST" class="mt-2">
                @csrf
                <button type="submit" class="text-xs bg-indigo-700 hover:bg-indigo-800 text-white px-3 py-1 rounded-full">
                    Clear Chat
                </button>
            </form>
        </div>

        <!-- Chat Messages -->
        <div id="chatContainer" class="flex-1 overflow-y-auto p-4 custom-scrollbar space-y-4">
            @foreach($conversations as $msg)
                <div class="flex {{ $msg['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%] rounded-lg p-3 {{ $msg['role'] === 'user' ? 'bg-blue-50' : 'bg-white border' }}">
                        <div class="text-xs text-gray-500 mb-1">
                            {{ $msg['role'] === 'user' ? 'You' : 'AI' }}
                        </div>
                        <div class="whitespace-pre-wrap">{!! nl2br(e($msg['content'])) !!}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Input Box -->
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

    <!-- Typing Indicator (Hidden by default) -->
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatForm = document.getElementById('chatForm');
            const chatContainer = document.getElementById('chatContainer');
            const promptInput = document.getElementById('promptInput');
            const submitBtn = document.getElementById('submitBtn');
            const typingIndicator = document.getElementById('typingIndicator');

            // Auto-scroll to bottom
            function scrollToBottom() {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }

            // Handle form submission
            chatForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const prompt = promptInput.value.trim();
                if (!prompt) return;

                // Add user message
                const userMsgHtml = `
                    <div class="flex justify-end">
                        <div class="max-w-[80%] rounded-lg p-3 bg-blue-50">
                            <div class="text-xs text-gray-500 mb-1">You</div>
                            <div>${prompt.replace(/\n/g, '<br>')}</div>
                        </div>
                    </div>
                `;
                chatContainer.insertAdjacentHTML('beforeend', userMsgHtml);
                promptInput.value = '';
                scrollToBottom();

                // Show typing indicator
                typingIndicator.classList.remove('hidden');
                scrollToBottom();

                try {
                    // Disable input while waiting
                    promptInput.disabled = true;
                    submitBtn.disabled = true;

                    // Send to server
                    const response = await fetch('{{ route("chat.ask") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ prompt })
                    });

                    if (!response.ok) throw new Error('Network error');

                    const data = await response.json();

                    // Add AI response
                    const aiMsgHtml = `
                        <div class="flex justify-start">
                            <div class="max-w-[80%] rounded-lg p-3 bg-white border">
                                <div class="text-xs text-gray-500 mb-1">AI</div>
                                <div>${data.response.replace(/\n/g, '<br>')}</div>
                            </div>
                        </div>
                    `;
                    chatContainer.insertAdjacentHTML('beforeend', aiMsgHtml);

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
        });
    </script>
</body>
</html>