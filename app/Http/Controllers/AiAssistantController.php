<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Models\ResponseAi;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AiAssistantController extends Controller
{
    private const OLLAMA_API_URL = 'http://localhost:11434/api/chat';
    private const MODEL = 'qwen2.5-coder:3b';
    private const SERVER_STATS_CACHE_KEY = 'server_stats';
    private const SERVER_STATS_CACHE_TIME = 30; // seconds

    public function index(Request $request)
    {
        $chatId = $request->query('chat_id');
        
        $conversations = ResponseAi::query()
            ->when($chatId, function($query) use ($chatId) {
                $query->where('chat_id', $chatId);
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->flatMap(function($item) {
                return [
                    [
                        'role' => 'user',
                        'content' => $item->question,
                        'created_at' => $item->created_at
                    ],
                    [
                        'role' => 'assistant',
                        'content' => $item->ai_answer,
                        'created_at' => $item->created_at
                    ]
                ];
            })
            ->toArray();

        if (empty($chatId)) {
            $latestChat = ResponseAi::latest()->first();
            if ($latestChat) {
                return redirect()->route('chat.index', ['chat_id' => $latestChat->chat_id]);
            }
        }

        
        $serverStats = $this->getServerStats();

        return view('chat', [
            'conversations' => $conversations,
            'serverStats' => $serverStats,
            'darkMode' => Session::get('dark_mode', false),
            'currentChatId' => $chatId ?? $this->generateChatId()
        ]);
    }

    public function ask(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string',
            'chat_id' => 'required|string'
        ]);

        $userQuestion = $request->prompt;
        $chatId = $request->chat_id;

        try {
            $response = Http::timeout(2000)->post(self::OLLAMA_API_URL, [
                'model' => self::MODEL,
                'messages' => $this->prepareMessages($userQuestion),
                'stream' => false
            ]);

            $aiResponse = $response->json()['message']['content'] ?? 'Sorry, I could not process that.';

            // Store both question and answer in the database
            $responseAi = ResponseAi::create([
                'chat_id' => $chatId,
                'question' => $userQuestion,
                'ai_answer' => $aiResponse,
                'model_used' => self::MODEL
            ]);

            return response()->json([
                'response' => $aiResponse,
                'serverStats' => $this->getServerStats()
            ]);

        } catch (\Exception $e) {
            Log::error('AI Error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function clearConversation()
    {
        // You might want to implement chat deletion logic here if needed
        return redirect()->route('chat.index');
    }

    public function toggleDarkMode()
    {
        Session::put('dark_mode', !Session::get('dark_mode', false));
        return redirect()->back();
    }

    public function getServerStats()
    {
        return Cache::remember(self::SERVER_STATS_CACHE_KEY, self::SERVER_STATS_CACHE_TIME, function() {
            try {
                // Default values
                $cpuUsage = 0;
                $memoryUsage = 0;

                // CPU Usage (only if available)
                if (function_exists('sys_getloadavg') && function_exists('sysconf')) {
                    $cpuLoad = sys_getloadavg();
                    $cpuCores = @sysconf(_SC_NPROCESSORS_ONLN) ?: 1;
                    $cpuUsage = round(($cpuLoad[0] / $cpuCores) * 100, 2);
                }

                // Memory Usage (Linux only)
                if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                    $free = @shell_exec('free');
                    if ($free) {
                        $freeArr = explode("\n", trim($free));
                        if (isset($freeArr[1])) {
                            $memArr = array_values(array_filter(explode(" ", $freeArr[1])));
                            if (isset($memArr[1], $memArr[2])) {
                                $memoryUsed = $memArr[2];
                                $memoryTotal = $memArr[1];
                                $memoryUsage = round(($memoryUsed / $memoryTotal) * 100, 2);
                            }
                        }
                    }
                }

                return [
                    'cpu' => $cpuUsage,
                    'memory' => $memoryUsage,
                    'updated_at' => now()->format('H:i:s')
                ];
            } catch (\Exception $e) {
                \Log::error('Server stats error: ' . $e->getMessage());
                return [
                    'cpu' => 0,
                    'memory' => 0,
                    'updated_at' => 'N/A'
                ];
            }
        });
    }

    private function generateChatId()
    {
        return uniqid();
    }

    private function prepareMessages(string $userQuestion): array
    {
        return [
            ['role' => 'system', 'content' => 'You are a helpful assistant.'],
            ['role' => 'user', 'content' => $userQuestion]
        ];
    }
    public function getChatHistory()
{
    // Get all unique chat_ids with their metadata
    $chatSummaries = DB::table('response_ai_table')
        ->select(
            'chat_id as id',
            DB::raw('MIN(created_at) as createdAt'),
            DB::raw('MAX(created_at) as updatedAt'),
            DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(question ORDER BY created_at DESC), ',', 1) as title")
        )
        ->groupBy('chat_id')
        ->orderByDesc('updatedAt')
        ->get();

    // For each chat, get all messages
    $fullChats = $chatSummaries->map(function ($chat) {
        $messages = ResponseAi::where('chat_id', $chat->id)
            ->orderBy('created_at')
            ->get()
            ->flatMap(function ($item) {
                return [
                    [
                        'role' => 'user',
                        'content' => $item->question,
                        'created_at' => $item->created_at
                    ],
                    [
                        'role' => 'assistant',
                        'content' => $item->ai_answer,
                        'created_at' => $item->created_at
                    ]
                ];
            })
            ->toArray();

        return [
            'id' => $chat->id,
            'title' => $chat->title,
            'createdAt' => $chat->createdAt,
            'updatedAt' => $chat->updatedAt,
            'messages' => $messages
        ];
    });

    return response()->json($fullChats);
}
public function getChatDetails($chatId)
    {
        $messages = ResponseAi::where('chat_id', $chatId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->flatMap(function($item) {
                return [
                    [
                        'role' => 'user',
                        'content' => $item->question,
                        'created_at' => $item->created_at,
                        'model_used' => $item->model_used
                    ],
                    [
                        'role' => 'assistant',
                        'content' => $item->ai_answer,
                        'created_at' => $item->created_at,
                        'model_used' => $item->model_used
                    ]
                ];
            });

        return response()->json([
            'chat_id' => $chatId,
            'messages' => $messages
        ]);
    }
    public function getFullChatHistory()
    {
        $chats = DB::table('response_ai_table')
            ->select('*')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('chat_id')
            ->map(function($messages, $chatId) {
                return [
                    'chat_id' => $chatId,
                    'messages' => $messages->map(function($msg) {
                        return [
                            'id' => $msg->id,
                            'question' => $msg->question,
                            'ai_answer' => $msg->ai_answer,
                            'model_used' => $msg->model_used,
                            'created_at' => $msg->created_at,
                            'updated_at' => $msg->updated_at
                        ];
                    })
                ];
            })
            ->values();

        return response()->json($chats);
    }
}