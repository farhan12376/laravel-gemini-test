<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ContentGeneratorController extends Controller
{
    private $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function index(): View
    {
        try {
            $serviceRunning = $this->geminiService->isServiceRunning();
            $availableModels = $this->geminiService->getAvailableModels();
            
            $available_modes = [
                'general' => ['name' => 'General Assistant', 'description' => 'General academic help'],
                'brainstorm' => ['name' => 'Research Brainstorm', 'description' => 'Generate research ideas'],
                'analysis' => ['name' => 'Data Analysis', 'description' => 'Statistical analysis help'],
                'writing' => ['name' => 'Academic Writing', 'description' => 'Writing assistance'],
                'literature' => ['name' => 'Literature Review', 'description' => 'Research synthesis']
            ];
            
            return view('generator.index', compact('serviceRunning', 'availableModels', 'available_modes'));
        } catch (\Exception $e) {
            Log::error('Error loading generator index: ' . $e->getMessage());
            
            return view('generator.index', [
                'serviceRunning' => false,
                'availableModels' => [],
                'available_modes' => [],
                'error' => 'Failed to load service status: ' . $e->getMessage()
            ]);
        }
    }

    public function generate(Request $request): JsonResponse
    {
        set_time_limit(120);
        
        try {
            $validator = Validator::make($request->all(), [
                'content_type' => 'required|in:article,blog,social,email',
                'topic' => 'required|string|max:500',
                'keywords' => 'nullable|string|max:200',
                'tone' => 'required|in:professional,casual,friendly,formal',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak valid',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (!$this->geminiService->isServiceRunning()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Layanan Gemini tidak tersedia'
                ], 503);
            }

            $result = $this->geminiService->generateContent($request->all());

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result['content']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Gagal generate konten'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Generation error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sendMessage(Request $request): JsonResponse
    {
        set_time_limit(120);
        
        try {
            $request->validate([
                'message' => 'required|string|max:2000',
                'mode' => 'required|in:general,brainstorm,analysis,writing,literature'
            ]);

            $result = $this->geminiService->generateChatResponse(
                $request->input('message'),
                $request->input('mode')
            );

            if ($result['success']) {
                // Store in session for history
                $history = Session::get('chat_history', []);
                $history[] = [
                    'user_message' => $request->input('message'),
                    'response' => $result['content'],
                    'mode' => $result['mode'],
                    'timestamp' => $result['created_at']
                ];
                Session::put('chat_history', array_slice($history, -20)); // Keep last 20

                return response()->json([
                    'success' => true,
                    'response' => $result['content'],
                    'mode' => $result['mode'],
                    'model' => $result['model_used'],
                    'timestamp' => $result['created_at'],
                    'suggestions' => $this->getSuggestions($result['mode'])
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Failed to generate response'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Chat error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkService(): JsonResponse
    {
        try {
            $health = $this->geminiService->getServiceHealth();

            return response()->json([
                'success' => true,
                'service_running' => $health['status'] === 'running',
                'service_status' => $health['status'],
                'available_models' => $health['available_models'] ?? [],
                'config' => [
                    'base_url' => config('gemini.base_url'),
                    'model' => config('gemini.model'),
                    'api_configured' => $health['api_configured'] ?? false
                ],
                'last_checked' => $health['last_checked']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'service_running' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getTemplates(): JsonResponse
    {
        $templates = [
            'research_proposal' => [
                'title' => 'Research Proposal Help',
                'content' => 'Help me structure a research proposal with background, objectives, methodology, and expected outcomes.'
            ],
            'literature_review' => [
                'title' => 'Literature Review Guide',
                'content' => 'Guide me through conducting a comprehensive literature review for my research topic.'
            ],
            'methodology' => [
                'title' => 'Research Methodology',
                'content' => 'Help me choose and design appropriate research methodology for my study.'
            ],
            'data_analysis' => [
                'title' => 'Data Analysis Support',
                'content' => 'Assist me with statistical analysis and interpretation of my research data.'
            ]
        ];

        return response()->json([
            'success' => true,
            'templates' => $templates
        ]);
    }

    public function clearHistory(): JsonResponse
    {
        Session::forget('chat_history');
        
        return response()->json([
            'success' => true,
            'message' => 'Chat history cleared'
        ]);
    }

    public function exportConversation(Request $request)
    {
        try {
            $history = Session::get('chat_history', []);
            
            if (empty($history)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No conversation to export'
                ], 400);
            }

            $content = "Academic Research Assistant - Chat Export\n";
            $content .= "Exported: " . now()->format('Y-m-d H:i:s') . "\n";
            $content .= str_repeat('=', 50) . "\n\n";

            foreach ($history as $chat) {
                $content .= "USER: " . $chat['user_message'] . "\n\n";
                $content .= "ASSISTANT (" . strtoupper($chat['mode']) . "): " . $chat['response'] . "\n\n";
                $content .= str_repeat('-', 30) . "\n\n";
            }

            return response($content)
                ->header('Content-Type', 'text/plain')
                ->header('Content-Disposition', 'attachment; filename="academic_chat_' . date('Y-m-d_H-i-s') . '.txt"');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getSuggestions($mode)
    {
        $suggestions = [
            'general' => [
                'How do I start my research?',
                'What research methods should I use?',
                'Help me find relevant literature'
            ],
            'brainstorm' => [
                'What are current trends in this field?',
                'Help me narrow down my research focus',
                'Suggest related research questions'
            ],
            'analysis' => [
                'Which statistical test should I use?',
                'How do I interpret these results?',
                'Is my sample size adequate?'
            ],
            'writing' => [
                'How do I structure my introduction?',
                'Help me improve my abstract',
                'What citation style should I use?'
            ],
            'literature' => [
                'How do I synthesize findings?',
                'What databases should I search?',
                'Help identify research gaps'
            ]
        ];

        return $suggestions[$mode] ?? $suggestions['general'];
    }
}