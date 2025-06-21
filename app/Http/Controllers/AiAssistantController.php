<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Models\ResponseAi;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\IOFactory;
use Exception;

class AiAssistantController extends Controller
{
    private const OLLAMA_API_URL = 'http://localhost:11434/api/chat';
    private const MODEL = 'qwen2.5-coder:3b'; // You can change this to a multimodal model like 'llava' if available
    private const SERVER_STATS_CACHE_KEY = 'server_stats';
    private const SERVER_STATS_CACHE_TIME = 30; // seconds

    public function index(Request $request)
    {
        $chatId = $request->query('chat_id', uniqid()); // Generate new chat_id if not present

        // Fetch conversations for the current chat_id
        $conversations = ResponseAi::query()
            ->where('chat_id', $chatId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->flatMap(function($item) {
                return [
                    [
                        'role' => 'user',
                        'content' => $item->question,
                        'created_at' => $item->created_at // Use created_at for individual messages
                    ],
                    [
                        'role' => 'assistant',
                        'content' => $item->ai_answer,
                        'created_at' => $item->created_at // Use created_at for individual messages
                    ]
                ];
            })
            ->toArray();

        $serverStats = $this->getServerStats();

        return view('chat', [
            'conversations' => $conversations,
            'serverStats' => $serverStats,
            'darkMode' => Session::get('dark_mode', false),
            'currentChatId' => $chatId // Pass the current chat ID to the view
        ]);
    }

    public function ask(Request $request)
    {
        $request->validate([
            'prompt' => 'nullable|string|max:2000',
            'chat_id' => 'required|string',
            'file' => 'nullable|file|mimes:jpeg,png,jpg,gif,docx|max:10240' // 10MB limit
        ]);

        $userQuestion = $request->prompt;
        $chatId = $request->chat_id;
        $fileContent = null;
        $fileName = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileExtension = $file->getClientOriginalExtension();

            try {
                if (in_array($fileExtension, ['jpeg', 'png', 'jpg', 'gif'])) {
                    // This is a placeholder for actual image-to-text processing.
                    // Option 1: Use an Ollama multimodal model (if configured)
                    // You'd need to convert the image to base64 and include it in the messages array
                    // as part of the 'images' field for a multimodal model.
                    // For example:
                    // $base64Image = base64_encode(file_get_contents($file->getRealPath()));
                    // $messages[] = ['role' => 'user', 'content' => $userQuestion, 'images' => [$base64Image]];
                    //
                    // Option 2: Use an external OCR service (e.g., Google Cloud Vision AI)
                    // For demonstration, we'll just acknowledge the image.
                    $fileContent = "[Image: {$fileName}]";
                    // In a real app, you'd send this image to an OCR API and get text.
                    // Example (conceptual): $fileContent = $this->ocrService->recognize($file->getRealPath());

                } elseif ($fileExtension === 'docx') {
                    // Process DOCX file
                    $phpWord = IOFactory::load($file->getRealPath());
                    $text = '';
                    foreach ($phpWord->getSections() as $section) {
                        foreach ($section->getElements() as $element) {
                            if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                                foreach ($element->getElements() as $textElement) {
                                    if ($textElement instanceof \PhpOffice\PhpWord\Element\Text) {
                                        $text .= $textElement->getText() . ' ';
                                    }
                                }
                            } elseif ($element instanceof \PhpOffice\PhpWord\Element\TextBreak) {
                                $text .= "\n";
                            } elseif ($element instanceof \PhpOffice\PhpWord\Element\Table) {
                                foreach ($element->getRows() as $row) {
                                    foreach ($row->getCells() as $cell) {
                                        $cellText = '';
                                        foreach ($cell->getElements() as $cellElement) {
                                            if ($cellElement instanceof \PhpOffice\PhpWord\Element\TextRun) {
                                                foreach ($cellElement->getElements() as $cellTextElement) {
                                                    if ($cellTextElement instanceof \PhpOffice\PhpWord\Element\Text) {
                                                        $cellText .= $cellTextElement->getText() . ' ';
                                                    }
                                                }
                                            }
                                        }
                                        $text .= $cellText . "\t"; // Tab for columns
                                    }
                                    $text .= "\n"; // Newline for rows
                                }
                            }
                            // Add more element types as needed (lists, etc.)
                        }
                    }
                    $fileContent = $text;
                }
            } catch (Exception $e) {
                Log::error("File processing error: " . $e->getMessage());
                return response()->json(['error' => 'Failed to process file.'], 400);
            }

            // Prepend file content to the user question
            if ($fileContent) {
                $userQuestion = "Regarding the uploaded file '{$fileName}':\n" . $fileContent . "\n\n" . ($userQuestion ?: "Please analyze this content.");
            }
        }

        if (!$userQuestion) {
            return response()->json(['error' => 'No prompt or file content provided.'], 400);
        }

        try {
            $messages = $this->prepareMessages($userQuestion);

            $response = Http::timeout(2000)->post(self::OLLAMA_API_URL, [
                'model' => self::MODEL,
                'messages' => $messages,
                'stream' => false
            ]);

            $aiResponse = $response->json()['message']['content'] ?? 'Sorry, I could not process that.';

            // Store both question and answer in the database
            ResponseAi::create([
                'chat_id' => $chatId,
                'question' => $userQuestion,
                'ai_answer' => $aiResponse,
                'model_used' => self::MODEL
            ]);

            return response()->json([
                'response' => $aiResponse,
                'serverStats' => $this->getServerStats()
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Ollama connection error: ' . $e->getMessage());
            return response()->json(['error' => 'Could not connect to AI model. Please ensure Ollama is running and accessible.'], 503);
        } catch (\Exception $e) {
            Log::error('AI Error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error processing AI request.'], 500);
        }
    }

    public function clearConversation()
    {
        // This clears the current chat history shown by redirecting to a new chat
        // If you want to delete the actual database entries, you would do:
        // ResponseAi::where('chat_id', $request->input('chat_id'))->delete();
        // However, given the chat history sidebar, this button likely means "start a new chat".
        return redirect()->route('chat.index');
    }

    public function toggleDarkMode()
    {
        Session::put('dark_mode', !Session::get('dark_mode', false));
        return response()->json(['success' => true]); // Return JSON response for AJAX
    }

    public function getServerStats()
    {
        return Cache::remember(self::SERVER_STATS_CACHE_KEY, self::SERVER_STATS_CACHE_TIME, function() {
            try {
                // Default values
                $cpuUsage = 0;
                $memoryUsage = 0;

                // CPU Usage (only if available and on Linux)
                // Using 'sys_getloadavg' for a simple load average (might not directly translate to % usage)
                // For more accurate CPU %, you'd often parse /proc/stat
                if (function_exists('sys_getloadavg')) {
                    $cpuLoad = sys_getloadavg();
                    // Assume 1-minute load average as a proxy for current usage relative to cores
                    // This is a rough estimate and not true instantaneous CPU usage.
                    $cpuCores = shell_exec('nproc'); // Get number of processing units
                    $cpuCores = $cpuCores ? (int)trim($cpuCores) : 1;
                    $cpuUsage = round(($cpuLoad[0] / $cpuCores) * 100, 2);
                    $cpuUsage = min(100, max(0, $cpuUsage)); // Cap between 0 and 100
                }

                // Memory Usage (Linux only - requires `free -m` command)
                if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') { // Not Windows
                    $freeOutput = @shell_exec('free -m');
                    if ($freeOutput) {
                        preg_match('/Mem:\s+(\d+)\s+(\d+)\s+(\d+)/', $freeOutput, $matches);
                        if (isset($matches[1], $matches[2])) {
                            $memoryTotal = (int)$matches[1];
                            $memoryUsed = (int)$matches[2];
                            if ($memoryTotal > 0) {
                                $memoryUsage = round(($memoryUsed / $memoryTotal) * 100, 2);
                            }
                        }
                    }
                } elseif (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    // Basic Windows memory usage (less precise and might require admin rights for some commands)
                    // This is very basic; real world needs WMI or other tools.
                    $output = shell_exec('wmic OS get FreePhysicalMemory,TotalPhysicalMemory /value');
                    if ($output) {
                        preg_match('/FreePhysicalMemory=(\d+)/', $output, $freeMatches);
                        preg_match('/TotalPhysicalMemory=(\d+)/', $output, $totalMatches);
                        if (isset($freeMatches[1]) && isset($totalMatches[1])) {
                            $freeMemKB = (int)$freeMatches[1];
                            $totalMemKB = (int)$totalMatches[1];
                            if ($totalMemKB > 0) {
                                $memoryUsage = round((($totalMemKB - $freeMemKB) / $totalMemKB) * 100, 2);
                            }
                        }
                    }
                }


                return [
                    'cpu' => $cpuUsage,
                    'memory' => $memoryUsage,
                    'updated_at' => now()->format('H:i:s')
                ];
            } catch (Exception $e) {
                Log::error('Server stats error: ' . $e->getMessage());
                return [
                    'cpu' => 0,
                    'memory' => 0,
                    'updated_at' => 'N/A'
                ];
            }
        });
    }

    private function prepareMessages(string $userQuestion): array
    {
        // In a real multimodal scenario with Ollama, if your model supports it (e.g., LLaVA),
        // the 'messages' array would look something like this for an image:
        /*
        [
            ['role' => 'system', 'content' => 'You are a helpful assistant.'],
            ['role' => 'user', 'content' => $userQuestion, 'images' => [$base64Image]]
        ]
        */
        // For text-only processing (current MODEL `phi3`), we just send the text.
        return [
            ['role' => 'system', 'content' => 'You are a helpful AI assistant. Provide concise and accurate answers. For code, always use markdown code blocks.'],
            ['role' => 'user', 'content' => $userQuestion]
        ];
    }

    public function getFullChatHistory()
    {
        // Get all unique chat_ids with their latest message info for display in sidebar
        $chatSummaries = ResponseAi::select('chat_id', 'question', 'ai_answer', 'created_at', 'updated_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('chat_id')
            ->map(function ($messagesForChat, $chatId) {
                // Get the first user question as the chat title/summary
                $firstQuestion = $messagesForChat->sortBy('created_at')->first()->question ?? 'New Chat';
                $latestMessage = $messagesForChat->sortByDesc('created_at')->first();

                // Flatten messages for the full chat history display
                $messages = $messagesForChat->flatMap(function($item) {
                    return [
                        [
                            'role' => 'user',
                            'content' => $item->question,
                            'created_at' => $item->created_at,
                            'model_used' => $item->model_used // Keep model_used for full details if needed
                        ],
                        [
                            'role' => 'assistant',
                            'content' => $item->ai_answer,
                            'created_at' => $item->created_at,
                            'model_used' => $item->model_used
                        ]
                    ];
                })->values()->sortBy('created_at')->toArray(); // Sort flattened messages by time

                return [
                    'id' => $chatId,
                    'title' => strlen($firstQuestion) > 50 ? substr($firstQuestion, 0, 50) . '...' : $firstQuestion,
                    'createdAt' => $messagesForChat->min('created_at'),
                    'updatedAt' => $latestMessage->created_at, // Use created_at of the last message as updated time
                    'messages' => $messages // Include full messages for details loading
                ];
            })
            ->values()
            ->sortByDesc('updatedAt') // Sort chats by their last message time
            ->toArray();

        return response()->json($chatSummaries);
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
            })->values()->toArray();

        return response()->json([
            'chat_id' => $chatId,
            'messages' => $messages
        ]);
    }
}