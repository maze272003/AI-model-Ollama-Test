<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Models\ResponseAi;

class AiAssistantController extends Controller
{
    private const DEFAULT_MODEL = 'qwen2.5-coder:0.5b';
    private const OLLAMA_API_URL = 'http://localhost:11434/api/chat';
    private const SESSION_KEY = 'ai_chat_history';
    private const AVAILABLE_MODELS = [
        'qwen2.5-coder:0.5b' => 'Qwen2.5 Coder 0.5B',
        'gemma3:1b' => 'Gemma3 1B',
        'qwen2.5-coder:3b' => 'Qwen2.5 Coder 3B',
        'deepseek-r1:14b' => 'Deepseek R1 14B',
        'deepseek-r1:14b' => 'Deepseek R1 14B',
    ];

    public function index()
    {
        $conversations = Session::get(self::SESSION_KEY, [
            ['role' => 'assistant', 'content' => 'Hello! How can I help you today?']
        ]);

        return view('chat', [
            'conversations' => $conversations,
            'selectedModel' => Session::get('current_model', self::DEFAULT_MODEL),
            'availableModels' => self::AVAILABLE_MODELS
        ]);
    }

    public function ask(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string',
            'model' => 'sometimes|string'
        ]);

        $userQuestion = $request->prompt;
        $selectedModel = $request->model ?? Session::get('current_model', self::DEFAULT_MODEL);

        // Get entire conversation history
        $conversations = Session::get(self::SESSION_KEY, []);
        $conversations[] = ['role' => 'user', 'content' => $userQuestion];
        Session::put(self::SESSION_KEY, $conversations);

        try {
            $response = Http::timeout(640)->post(self::OLLAMA_API_URL, [
                'model' => $selectedModel,
                'messages' => $this->prepareMessages($conversations),
                'stream' => false
            ]);

            $aiResponse = $response->json()['message']['content'] ?? 'Sorry, I could not process that.';

            // Save to database
            ResponseAi::create([
                'question' => $userQuestion,
                'ai_answer' => $aiResponse,
                'model_used' => $selectedModel
            ]);

            $conversations[] = ['role' => 'assistant', 'content' => $aiResponse];
            Session::put(self::SESSION_KEY, $conversations);

            return response()->json(['response' => $aiResponse]);

        } catch (\Exception $e) {
            Log::error('AI Error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function clearConversation()
    {
        Session::forget(self::SESSION_KEY);
        return redirect()->route('chat.index');
    }

    public function changeModel(Request $request)
    {
        $request->validate(['model' => 'required|string']);
        Session::put('current_model', $request->model);
        return response()->json(['success' => true]);
    }

    private function prepareMessages(array $conversations): array
    {
        // Convert our conversation format to Ollama's expected format
        $messages = [['role' => 'system', 'content' => 'You are a helpful assistant.']];
        
        foreach ($conversations as $message) {
            $messages[] = [
                'role' => $message['role'] === 'assistant' ? 'assistant' : 'user',
                'content' => $message['content']
            ];
        }
        
        return $messages;
    }
}