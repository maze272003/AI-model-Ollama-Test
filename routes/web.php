<?php

use App\Http\Controllers\AiAssistantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/chat');
});

Route::get('/chat', [AiAssistantController::class, 'index'])->name('chat.index');
Route::post('/chat/ask', [AiAssistantController::class, 'ask'])->name('chat.ask');
Route::post('/chat/clear', [AiAssistantController::class, 'clearConversation'])->name('chat.clear'); // Now just redirects to new chat
Route::post('/chat/toggle-dark-mode', [AiAssistantController::class, 'toggleDarkMode'])->name('chat.toggle-dark-mode');
Route::get('/chat/server-stats', [AiAssistantController::class, 'getServerStats'])->name('chat.server-stats');
Route::get('/chat/history', [AiAssistantController::class, 'getFullChatHistory'])->name('chat.history'); // Get all chat summaries
Route::get('/chat/details/{chatId}', [AiAssistantController::class, 'getChatDetails'])->name('chat.details'); // Get messages for a specific chat ID