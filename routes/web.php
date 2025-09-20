<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContentGeneratorController;
use App\Http\Controllers\ChatbotController;

// Chatbot routes
Route::prefix('chatbot')->name('chatbot.')->group(function() {
    Route::get('/', [ChatbotController::class, 'index'])->name('index');
    Route::post('/send', [ChatbotController::class, 'sendMessage'])->name('send');
    Route::get('/history', [ChatbotController::class, 'getHistory'])->name('history');
    Route::post('/clear', [ChatbotController::class, 'clearHistory'])->name('clear');
    Route::get('/export', [ChatbotController::class, 'exportConversation'])->name('export');
    Route::get('/templates', [ChatbotController::class, 'getTemplates'])->name('templates');
    Route::get('/status', [ChatbotController::class, 'status'])->name('status');
});

// Root redirect to chatbot
Route::get('/', function () {
    return redirect()->route('chatbot.index');
});

// Keep existing content generator routes for comparison
Route::prefix('content')->name('content.')->group(function() {
    Route::get('/generator', [ContentGeneratorController::class, 'index'])->name('generator');
    Route::post('/generate', [ContentGeneratorController::class, 'generate'])->name('generate');
    Route::get('/check-service', [ContentGeneratorController::class, 'checkService'])->name('check-service');
    Route::get('/test', [ContentGeneratorController::class, 'test'])->name('test');
});