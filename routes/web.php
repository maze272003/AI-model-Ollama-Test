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

// Image upload route
Route::post('/chat/upload-image', [AiAssistantController::class, 'uploadImage'])->name('chat.uploadImage');
