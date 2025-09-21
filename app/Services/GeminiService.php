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
        $this->baseUrl = config('gemini.base_url');
        $this->model = config('gemini.model');
    }

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
            return [$this->model];
        } catch (Exception $e) {
            Log::error('Failed to get Gemini models: ' . $e->getMessage());
            return [$this->model];
        }
    }

    public function generateContent($params)
    {
        try {
            $prompt = $this->buildPrompt($params);
            $result = $this->callGeminiAPI($prompt, $params);

            if ($result['success']) {
                return [
                    'success' => true,
                    'content' => [
                        'title' => $this->generateTitle($params['topic']),
                        'body' => $result['content'],
                        'type' => $params['content_type'],
                        'created_at' => now()->toISOString(),
                        'model_used' => $this->model
                    ]
                ];
            }
            return $result;
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Service error: ' . $e->getMessage()
            ];
        }
    }

    public function generateChatResponse($message, $mode = 'general')
    {
        try {
            $prompt = $this->buildChatPrompt($message, $mode);
            $result = $this->callGeminiAPI($prompt, ['content_type' => 'chat']);

            return [
                'success' => $result['success'],
                'content' => $result['content'] ?? '',
                'mode' => $mode,
                'model_used' => $this->model,
                'created_at' => now()->toISOString()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Chat error: ' . $e->getMessage()
            ];
        }
    }

    private function callGeminiAPI($prompt, $params)
    {
        try {
            $contents = [[
                'role' => 'user',
                'parts' => [['text' => $prompt]]
            ]];
            
            $generationConfig = [
                'temperature' => $this->getTemperatureForType($params['content_type'] ?? 'general'),
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => $this->getMaxTokensForType($params['content_type'] ?? 'general'),
            ];

            $requestData = [
                'contents' => $contents,
                'generationConfig' => $generationConfig,
                'safetySettings' => [[
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ]]
            ];

            $response = Http::timeout(60)
                ->withHeaders([
                    'x-goog-api-key' => $this->apiKey,
                    'Content-Type' => 'application/json'
                ])
                ->post($this->baseUrl . "/models/{$this->model}:generateContent", $requestData);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No response generated';
                
                return [
                    'success' => true,
                    'content' => $content
                ];
            }

            return [
                'success' => false,
                'error' => 'Gemini API Error: ' . $response->status()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'API call failed: ' . $e->getMessage()
            ];
        }
    }

    private function buildPrompt($params)
    {
        $contentType = $params['content_type'];
        $topic = $params['topic'];
        $tone = $params['tone'];
        $keywords = $params['keywords'] ?? '';

        $prompt = "Write a {$tone} {$contentType} about '{$topic}'.";
        
        if (!empty($keywords)) {
            $prompt .= " Include these keywords: {$keywords}.";
        }

        $instructions = match($contentType) {
            'article' => ' Make it informative and well-structured with introduction, main points, and conclusion. Aim for 400-600 words.',
            'blog' => ' Make it engaging and personal, suitable for blog audience. Use conversational tone and practical insights.',
            'social' => ' Keep it short and engaging for social media. Maximum 200 words with hashtags.',
            'email' => ' Format as professional email with greeting, clear message, and appropriate closing.',
            default => ' Provide comprehensive and helpful content.'
        };

        return $prompt . $instructions;
    }

    private function buildChatPrompt($message, $mode)
    {
        $modePrompts = [
            'brainstorm' => 'You are an academic research brainstorming assistant. Help generate and refine research ideas with creativity and academic rigor.',
            'analysis' => 'You are a data analysis expert. Help interpret research findings, suggest statistical methods, and explain complex analytical concepts.',
            'writing' => 'You are an academic writing coach. Help with structure, clarity, citation styles, and improving academic prose.',
            'literature' => 'You are a literature review specialist. Help identify key papers, synthesize findings, and suggest research gaps.',
            'general' => 'You are a helpful academic research assistant. Provide comprehensive support for all aspects of academic research.'
        ];

        $systemPrompt = $modePrompts[$mode] ?? $modePrompts['general'];
        
        return $systemPrompt . "\n\nUser question: " . $message;
    }

    private function getTemperatureForType($type)
    {
        return match($type) {
            'brainstorm' => 0.9,
            'social' => 0.8,
            'blog' => 0.7,
            'chat' => 0.7,
            'article' => 0.6,
            'email' => 0.5,
            'analysis' => 0.3,
            default => 0.7
        };
    }

    private function getMaxTokensForType($type)
    {
        return match($type) {
            'social' => 300,
            'email' => 500,
            'chat' => 800,
            'blog' => 800,
            'article' => 1200,
            default => 800
        };
    }

    private function generateTitle($topic)
    {
        return ucwords(trim($topic));
    }

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