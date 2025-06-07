<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class AiAssistantController extends Controller
{
    private const DEFAULT_MODEL = 'deepseek-r1:14b';
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

        $conversations = Session::get(self::SESSION_KEY, []);
        $conversations[] = ['role' => 'user', 'content' => $request->prompt];
        Session::put(self::SESSION_KEY, $conversations);

        try {
            $response = Http::timeout(120)->post(self::OLLAMA_API_URL, [
                'model' => self::DEFAULT_MODEL,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                    ['role' => 'user', 'content' => $request->prompt]
                ],
                'stream' => false
            ]);

            $aiResponse = $response->json()['message']['content'] ?? 'Sorry, I could not process that.';

            $conversations[] = ['role' => 'assistant', 'content' => $aiResponse];
            Session::put(self::SESSION_KEY, $conversations);

            return response()->json(['response' => $aiResponse]);

        } catch (\Exception $e) {
            Log::error('AI Error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function clear()
    {
        Session::forget(self::SESSION_KEY);
        return redirect()->route('chat.index');
    }
}