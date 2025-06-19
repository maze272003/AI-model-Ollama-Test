<?php

use App\Http\Controllers\AiAssistantController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/chat');
});

Route::get('/chat', [AiAssistantController::class, 'index'])->name('chat.index');
Route::post('/chat/ask', [AiAssistantController::class, 'ask'])->name('chat.ask');
Route::post('/chat/clear', [AiAssistantController::class, 'clearConversation'])->name('chat.clear');
Route::post('/chat/toggle-dark-mode', [AiAssistantController::class, 'toggleDarkMode'])->name('chat.toggle-dark-mode');
Route::get('/chat/server-stats', [AiAssistantController::class, 'getServerStats'])->name('chat.server-stats');
Route::get('/chat/history', [AiAssistantController::class, 'getFullChatHistory'])->name('chat.history');
Route::get('/chat/details/{chatId}', [AiAssistantController::class, 'getChatDetails'])->name('chat.details');