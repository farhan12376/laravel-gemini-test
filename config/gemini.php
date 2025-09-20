<?php

// config/gemini.php
return [
    'api_key' => env('GEMINI_API_KEY'),
    'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
    'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
    'timeout' => env('GEMINI_TIMEOUT', 60),
    'max_tokens' => env('GEMINI_MAX_TOKENS', 1024),
    'temperature' => env('GEMINI_TEMPERATURE', 0.7),
];