<?php
use App\Http\Controllers\AiAssistantController;
use Illuminate\Support\Facades\Route;

// Default route to chat
Route::get('/', function () {
    return redirect('/chat');
});

// Chat routes
Route::get('/chat', [AiAssistantController::class, 'index']);
Route::post('/chat/ask', [AiAssistantController::class, 'ask'])->name('chat.ask');
Route::post('/chat/change-model', [AiAssistantController::class, 'changeModel'])->name('chat.change-model');
Route::post('/chat/clear', [AiAssistantController::class, 'clearConversation'])->name('chat.clear');