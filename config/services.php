<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'ollama' => [
        'url' => env('OLLAMA_URL', 'http://localhost:11434'),
        'model' => env('OLLAMA_MODEL', 'llama3.2:1b'),
        'timeout' => env('OLLAMA_TIMEOUT', 30),
    ],

    'rag' => [
        'url' => env('RAG_SERVICE_URL', 'http://localhost:8001'),
        'timeout' => env('RAG_SERVICE_TIMEOUT', 60),
    ],

    'ocr' => [
        'url' => env('OCR_SERVICE_URL', 'http://localhost:8002'),
        'timeout' => env('OCR_SERVICE_TIMEOUT', 30),
    ],

    'summarizer' => [
        'url' => env('SUMMARIZER_SERVICE_URL', 'http://localhost:8003'),
        'timeout' => env('SUMMARIZER_SERVICE_TIMEOUT', 30),
    ],
];
