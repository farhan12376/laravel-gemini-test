<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GeminiService
{
    private $apiKey;
    private $baseUrl;
    private $model;

    public function __construct()
    {
        $this->apiKey = config('gemini.api_key');
        $this->baseUrl = config('gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta');
        $this->model = config('gemini.model', 'gemini-1.5-flash');
    }

    /**
     * Check if Gemini API is accessible
     */
    public function isServiceRunning()
    {
        try {
            $response = Http::timeout(10)->get($this->baseUrl . '/models', [
                'key' => $this->apiKey
            ]);
            
            return $response->successful();
        } catch (Exception $e) {
            Log::warning('Gemini API check failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get available models
     */
    public function getAvailableModels()
    {
        try {
            $response = Http::timeout(10)->get($this->baseUrl . '/models', [
                'key' => $this->apiKey
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return collect($data['models'] ?? [])->pluck('name')->map(function($name) {
                    return str_replace('models/', '', $name);
                })->toArray();
            }
            
            return [$this->model]; // fallback
        } catch (Exception $e) {
            Log::error('Failed to get Gemini models: ' . $e->getMessage());
            return [$this->model];
        }
    }

    /**
     * Generate chat response using Gemini
     */
    public function generateChatResponse($messages, $config = [])
    {
        try {
            Log::info('Starting Gemini chat generation', [
                'message_count' => count($messages),
                'model' => $this->model
            ]);

            // Format messages for Gemini
            $contents = $this->formatMessagesForGemini($messages);
            
            // Build generation config
            $generationConfig = [
                'temperature' => $config['temperature'] ?? 0.7,
                'topK' => $config['top_k'] ?? 40,
                'topP' => $config['top_p'] ?? 0.95,
                'maxOutputTokens' => $config['max_tokens'] ?? 1024,
            ];

            // Add safety settings
            $safetySettings = [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ]
            ];

            $requestData = [
                'contents' => $contents,
                'generationConfig' => $generationConfig,
                'safetySettings' => $safetySettings
            ];

            Log::info('Sending request to Gemini API');

            $response = Http::timeout(60)
                ->post($this->baseUrl . "/models/{$this->model}:generateContent", $requestData)
                ->header('x-goog-api-key', $this->apiKey);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('Gemini response received', [
                    'candidates' => count($data['candidates'] ?? [])
                ]);

                $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No response generated';
                
                return [
                    'success' => true,
                    'content' => $content,
                    'model_used' => $this->model,
                    'usage' => $data['usageMetadata'] ?? null,
                    'created_at' => now()->toISOString()
                ];
            } else {
                Log::error('Gemini API error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return [
                    'success' => false,
                    'error' => 'Gemini API Error: ' . $response->status() . ' - ' . $response->body()
                ];
            }

        } catch (Exception $e) {
            Log::error('Gemini generation exception', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => 'Service error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate content based on type (untuk compatibility dengan existing code)
     */
    public function generateContent($params)
    {
        // Convert old format to chat format
        $systemPrompt = $this->buildSystemPrompt($params);
        $userMessage = "Generate {$params['content_type']} about: {$params['topic']}";
        
        if (!empty($params['keywords'])) {
            $userMessage .= "\nKeywords to include: {$params['keywords']}";
        }

        $messages = [
            ['role' => 'user', 'content' => $systemPrompt . "\n\n" . $userMessage]
        ];

        $config = [
            'temperature' => $this->getTemperatureForType($params['content_type']),
            'max_tokens' => $this->getMaxTokensForType($params['content_type'])
        ];

        $result = $this->generateChatResponse($messages, $config);

        if ($result['success']) {
            return [
                'success' => true,
                'content' => [
                    'title' => $this->generateTitle($params['topic']),
                    'body' => $result['content'],
                    'type' => $params['content_type'],
                    'created_at' => $result['created_at'],
                    'model_used' => $result['model_used']
                ]
            ];
        }

        return $result;
    }

    /**
     * Academic Research Assistant - Main chat function
     */
    public function generateAcademicResponse($userMessage, $conversationHistory = [], $mode = 'general')
    {
        $systemPrompts = [
            'brainstorm' => "You are an Academic Research Assistant specializing in helping students brainstorm research topics and methodologies. Be creative, suggest multiple angles, and ask probing questions to help refine ideas.",
            
            'analysis' => "You are a Data Analysis Expert helping researchers interpret their findings. Focus on statistical significance, methodology validation, and clear explanations of complex concepts.",
            
            'writing' => "You are an Academic Writing Coach. Help with structure, clarity, citation formatting, and academic tone. Provide specific suggestions for improvement.",
            
            'literature_review' => "You are a Literature Review Specialist. Help identify key papers, synthesize findings, and suggest research gaps.",
            
            'general' => "You are a friendly Academic Research Assistant. Help with all aspects of academic research while maintaining a supportive and encouraging tone."
        ];

        // Build conversation context
        $messages = [];
        
        // Add system prompt
        $messages[] = [
            'role' => 'user',
            'content' => $systemPrompts[$mode] ?? $systemPrompts['general']
        ];

        // Add conversation history (last 5 messages to avoid token limits)
        $recentHistory = array_slice($conversationHistory, -5);
        foreach ($recentHistory as $msg) {
            $messages[] = [
                'role' => $msg['role'] === 'user' ? 'user' : 'model',
                'content' => $msg['content']
            ];
        }

        // Add current user message
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];

        $config = [
            'temperature' => $this->getTemperatureForMode($mode),
            'max_tokens' => 1024
        ];

        return $this->generateChatResponse($messages, $config);
    }

    /**
     * Format messages for Gemini API format
     */
    private function formatMessagesForGemini($messages)
    {
        $contents = [];
        
        foreach ($messages as $message) {
            $role = $message['role'] === 'assistant' ? 'model' : 'user';
            
            $contents[] = [
                'role' => $role,
                'parts' => [
                    ['text' => $message['content']]
                ]
            ];
        }
        
        return $contents;
    }

    /**
     * Build system prompt for content generation
     */
    private function buildSystemPrompt($params)
    {
        $tone = $params['tone'] ?? 'professional';
        $contentType = $params['content_type'] ?? 'article';
        
        $prompts = [
            'article' => "Write a comprehensive, well-structured article with clear introduction, main points, and conclusion.",
            'blog' => "Write an engaging blog post with a conversational tone and practical insights.",
            'social' => "Create engaging social media content that's shareable and impactful.",
            'email' => "Compose a professional email with clear subject, greeting, body, and closing.",
            'research' => "Generate academic-style content with proper structure and scholarly tone."
        ];

        return "You are a professional content writer. " . ($prompts[$contentType] ?? $prompts['article']) . " Use a {$tone} tone throughout.";
    }

    private function getTemperatureForType($type)
    {
        return match($type) {
            'social' => 0.8,
            'blog' => 0.7,
            'email' => 0.5,
            'article' => 0.6,
            default => 0.7
        };
    }

    private function getTemperatureForMode($mode)
    {
        return match($mode) {
            'brainstorm' => 0.9,
            'analysis' => 0.3,
            'writing' => 0.6,
            'literature_review' => 0.4,
            default => 0.7
        };
    }

    private function getMaxTokensForType($type)
    {
        return match($type) {
            'social' => 300,
            'email' => 500,
            'blog' => 800,
            'article' => 1200,
            default => 800
        };
    }

    private function generateTitle($topic)
    {
        return ucwords(trim($topic));
    }

    /**
     * Get service health
     */
    public function getServiceHealth()
    {
        try {
            $isRunning = $this->isServiceRunning();
            $models = $this->getAvailableModels();
            
            return [
                'status' => $isRunning ? 'running' : 'offline',
                'url' => $this->baseUrl,
                'model' => $this->model,
                'available_models' => $models,
                'api_configured' => !empty($this->apiKey),
                'last_checked' => now()->toISOString()
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
                'last_checked' => now()->toISOString()
            ];
        }
    }
}