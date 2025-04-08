<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiAssistantController extends Controller
{
    public function ask(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string',
            'model' => 'required|string',
        ]);

        $model = $request->input('model');
        $prompt = $request->input('prompt');

        try {
            // Send the request to Ollama's API with the selected model
            $response = Http::timeout(60)->post('http://localhost:11434/api/generate', [
                'model' => $model,
                'prompt' => $prompt,
                'stream' => false,
            ]);

            // Check if the API call was successful
            if ($response->failed()) {
                return response()->json(['error' => 'Error with the external API'], 500);
            }

            // Parse the response
            $aiResponse = $response->json()['response'] ?? 'No response from AI';

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error communicating with the AI. Please try again later.'], 500);
        }

        // Return response to frontend
        return response()->json(['response' => $aiResponse]);
    }

    public function index()
    {
        return view('chat', [
            'conversations' => session()->get('conversations', []),
            'selectedModel' => 'deepseek-r1:1.5b' // Default model when loading
        ]);
    }

    // Example of custom logging for the selected model
    private function logModelSelection($model)
    {
        // Add custom logging logic for specific model
        \Log::info("Model selected: " . $model);
    }
}
