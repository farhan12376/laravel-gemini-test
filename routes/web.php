<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContentGeneratorController;

// Main application routes
Route::get('/', function () {
    return redirect('/content/generator');
});

Route::prefix('content')->name('content.')->group(function() {
    // Main interface
    Route::get('/generator', [ContentGeneratorController::class, 'index'])->name('generator');
    
    // Content generation
    Route::post('/generate', [ContentGeneratorController::class, 'generate'])->name('generate');
    
    // Service status and health
    Route::get('/check-service', [ContentGeneratorController::class, 'checkService'])->name('check-service');
    Route::get('/status', [ContentGeneratorController::class, 'checkService'])->name('status');
    
    // Academic templates
    Route::get('/templates', [ContentGeneratorController::class, 'getTemplates'])->name('templates');
    
    // Chat functionality
    Route::post('/send', [ContentGeneratorController::class, 'sendMessage'])->name('send');
    Route::post('/clear', [ContentGeneratorController::class, 'clearHistory'])->name('clear');
    Route::get('/export', [ContentGeneratorController::class, 'exportConversation'])->name('export');
});

// Test routes
Route::get('/test-gemini', function() {
    $service = new App\Services\GeminiService();
    return $service->getServiceHealth();
});