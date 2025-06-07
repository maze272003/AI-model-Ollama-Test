<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Models\ResponseAi; // Import the ResponseAi model

class AiAssistantController extends Controller
{
    private const DEFAULT_MODEL = 'qwen2.5-coder:0.5b';
    private const OLLAMA_API_URL = 'http://localhost:11434/api/chat';
    private const SESSION_KEY = 'ai_chat_history';

    public function index()
    {
        $conversations = Session::get(self::SESSION_KEY, [
            ['role' => 'assistant', 'content' => 'Hello! How can I help you today?']
        ]);

        return view('chat', [
            'conversations' => $conversations,
            'selectedModel' => self::DEFAULT_MODEL
        ]);
    }

    public function ask(Request $request)
    {
        $request->validate(['prompt' => 'required|string']);

        $userQuestion = $request->prompt; // Kunin ang tanong ng user

        $conversations = Session::get(self::SESSION_KEY, []);
        $conversations[] = ['role' => 'user', 'content' => $userQuestion];
        Session::put(self::SESSION_KEY, $conversations);

        try {
            $response = Http::timeout(640)->post(self::OLLAMA_API_URL, [
                'model' => self::DEFAULT_MODEL,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                    ['role' => 'user', 'content' => $userQuestion]
                ],
                'stream' => false
            ]);

            $aiResponse = $response->json()['message']['content'] ?? 'Sorry, I could not process that.';

            // I-save sa database ang tanong at sagot
            ResponseAi::create([
                'question' => $userQuestion,
                'ai_answer' => $aiResponse,
            ]);

            $conversations[] = ['role' => 'assistant', 'content' => $aiResponse];
            Session::put(self::SESSION_KEY, $conversations);

            return response()->json(['response' => $aiResponse]);

        } catch (\Exception $e) {
            Log::error('AI Error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    // RENAMED THIS METHOD TO clearConversation()
    public function clearConversation()
    {
        Session::forget(self::SESSION_KEY);
        return redirect()->route('chat.index');
    }
}