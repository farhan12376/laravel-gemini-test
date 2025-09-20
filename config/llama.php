<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Llama/Ollama Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk integrasi dengan Ollama (Llama LLM)
    |
    */

    'base_url' => env('LLAMA_BASE_URL', 'http://localhost:11434'),
    'model' => env('LLAMA_MODEL', 'llama3.2:1b'),
    'timeout' => env('LLAMA_TIMEOUT', 60),
    'temperature' => env('LLAMA_TEMPERATURE', 0.7),
    'max_tokens' => env('LLAMA_MAX_TOKENS', 1000),
    
    // Konfigurasi tambahan
    'connect_timeout' => env('LLAMA_CONNECT_TIMEOUT', 10),
    'retry_attempts' => env('LLAMA_RETRY_ATTEMPTS', 2),
    'retry_delay' => env('LLAMA_RETRY_DELAY', 1000), // milliseconds
];