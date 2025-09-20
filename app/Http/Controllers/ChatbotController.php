<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    private $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Display chatbot interface
     */
    public function index(): View
    {
        $sessionId = Session::get('chatbot_session', Session::getId());
        Session::put('chatbot_session', $sessionId);

        return view('chatbot.index', [
            'session_id' => $sessionId,
            'available_modes' => $this->getAvailableModes()
        ]);
    }

    /**
     * Send message to chatbot
     */
    public function sendMessage(Request $request): JsonResponse
    {
        set_time_limit(120); // Prevent timeout
        
        try {
            $request->validate([
                'message' => 'required|string|max:2000',
                'mode' => 'required|in:brainstorm,analysis,writing,literature_review,general'
            ]);

            $userMessage = $request->input('message');
            $mode = $request->input('mode', 'general');
            $sessionId = Session::get('chatbot_session', Session::getId());

            Log::info('Chatbot message received', [
                'session' => $sessionId,
                'mode' => $mode,
                'message_length' => strlen($userMessage)
            ]);

            // Get conversation history from session
            $conversationHistory = Session::get("chat_history_{$sessionId}", []);

            // Generate response using Gemini
            $result = $this->geminiService->generateAcademicResponse(
                $userMessage, 
                $conversationHistory, 
                $mode
            );

            if ($result['success']) {
                // Add messages to history
                $conversationHistory[] = [
                    'role' => 'user',
                    'content' => $userMessage,
                    'timestamp' => now()->toISOString()
                ];

                $conversationHistory[] = [
                    'role' => 'assistant', 
                    'content' => $result['content'],
                    'timestamp' => now()->toISOString(),
                    'mode' => $mode,
                    'model' => $result['model_used'] ?? 'gemini-1.5-flash'
                ];

                // Keep only last 20 messages to prevent session bloat
                $conversationHistory = array_slice($conversationHistory, -20);
                
                // Save to session
                Session::put("chat_history_{$sessionId}", $conversationHistory);

                return response()->json([
                    'success' => true,
                    'response' => $result['content'],
                    'mode' => $mode,
                    'model' => $result['model_used'] ?? 'gemini-1.5-flash',
                    'timestamp' => now()->toISOString(),
                    'suggestions' => $this->getSuggestions($mode, $userMessage)
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Failed to generate response'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Chatbot error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get conversation history
     */
    public function getHistory(Request $request): JsonResponse
    {
        $sessionId = Session::get('chatbot_session', Session::getId());
        $history = Session::get("chat_history_{$sessionId}", []);

        return response()->json([
            'success' => true,
            'history' => $history,
            'session_id' => $sessionId
        ]);
    }

    /**
     * Clear conversation history
     */
    public function clearHistory(Request $request): JsonResponse
    {
        $sessionId = Session::get('chatbot_session', Session::getId());
        Session::forget("chat_history_{$sessionId}");

        return response()->json([
            'success' => true,
            'message' => 'Conversation history cleared'
        ]);
    }

    /**
     * Export conversation
     */
    public function exportConversation(Request $request)
    {
        try {
            $format = $request->input('format', 'txt'); // txt, json, pdf
            $sessionId = Session::get('chatbot_session', Session::getId());
            $history = Session::get("chat_history_{$sessionId}", []);

            if (empty($history)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No conversation to export'
                ], 400);
            }

            $filename = 'academic_chat_' . date('Y-m-d_H-i-s');

            switch ($format) {
                case 'json':
                    return response()->json($history)
                        ->header('Content-Disposition', "attachment; filename=\"{$filename}.json\"");

                case 'txt':
                default:
                    $content = $this->formatConversationAsText($history);
                    return response($content)
                        ->header('Content-Type', 'text/plain')
                        ->header('Content-Disposition', "attachment; filename=\"{$filename}.txt\"");
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get research templates
     */
    public function getTemplates(): JsonResponse
    {
        $templates = [
            'research_proposal' => [
                'title' => 'Research Proposal Structure',
                'content' => 'Help me structure a research proposal with: background, problem statement, objectives, methodology, and expected outcomes.'
            ],
            'literature_review' => [
                'title' => 'Literature Review Plan', 
                'content' => 'I need to conduct a literature review on [topic]. Help me identify key databases, search terms, and organization strategy.'
            ],
            'data_analysis' => [
                'title' => 'Data Analysis Guidance',
                'content' => 'I have [type] data and need help choosing appropriate statistical methods and interpretation techniques.'
            ],
            'thesis_outline' => [
                'title' => 'Thesis/Paper Outline',
                'content' => 'Help me create a detailed outline for my academic paper on [topic] including chapter structure and key points.'
            ]
        ];

        return response()->json([
            'success' => true,
            'templates' => $templates
        ]);
    }

    /**
     * Check service status
     */
    public function status(): JsonResponse
    {
        try {
            $health = $this->geminiService->getServiceHealth();
            
            return response()->json([
                'success' => true,
                'service_status' => $health['status'],
                'api_configured' => $health['api_configured'] ?? false,
                'model' => $health['model'] ?? 'unknown',
                'available_models' => $health['available_models'] ?? [],
                'last_checked' => $health['last_checked']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available conversation modes
     */
    private function getAvailableModes()
    {
        return [
            'general' => [
                'name' => 'General Assistant',
                'description' => 'General academic help and guidance',
                'icon' => 'fas fa-comments'
            ],
            'brainstorm' => [
                'name' => 'Research Brainstorm', 
                'description' => 'Generate and refine research ideas',
                'icon' => 'fas fa-lightbulb'
            ],
            'analysis' => [
                'name' => 'Data Analysis',
                'description' => 'Statistical analysis and interpretation',
                'icon' => 'fas fa-chart-line'
            ],
            'writing' => [
                'name' => 'Academic Writing',
                'description' => 'Writing assistance and style guidance',
                'icon' => 'fas fa-pen-fancy'
            ],
            'literature_review' => [
                'name' => 'Literature Review',
                'description' => 'Research synthesis and gap analysis',
                'icon' => 'fas fa-books'
            ]
        ];
    }

    /**
     * Get contextual suggestions based on mode and message
     */
    private function getSuggestions($mode, $userMessage)
    {
        $suggestions = [
            'general' => [
                'How do I start my research?',
                'What methodology should I use?',
                'Help me find relevant papers'
            ],
            'brainstorm' => [
                'What are current trends in this field?',
                'What research gaps exist?',
                'How can I narrow down my topic?'
            ],
            'analysis' => [
                'What statistical test should I use?',
                'How do I interpret these results?',
                'Is my sample size adequate?'
            ],
            'writing' => [
                'How do I structure my introduction?',
                'Help me improve my abstract',
                'What citation style should I use?'
            ],
            'literature_review' => [
                'How do I synthesize these findings?',
                'What databases should I search?',
                'How many sources do I need?'
            ]
        ];

        return $suggestions[$mode] ?? $suggestions['general'];
    }

    /**
     * Format conversation history as readable text
     */
    private function formatConversationAsText($history)
    {
        $content = "Academic Research Assistant - Conversation Export\n";
        $content .= "Generated: " . now()->format('Y-m-d H:i:s') . "\n";
        $content .= str_repeat('=', 50) . "\n\n";

        foreach ($history as $message) {
            $role = $message['role'] === 'user' ? 'YOU' : 'ASSISTANT';
            $timestamp = isset($message['timestamp']) ? 
                \Carbon\Carbon::parse($message['timestamp'])->format('H:i:s') : 
                'N/A';
            
            $content .= "[{$timestamp}] {$role}:\n";
            $content .= $message['content'] . "\n\n";
        }

        return $content;
    }
}