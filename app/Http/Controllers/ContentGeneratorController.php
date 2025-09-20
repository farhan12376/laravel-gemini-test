<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Services\LlamaService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ContentGeneratorController extends Controller
{
    private $llamaService;

    public function __construct(LlamaService $llamaService)
    {
        $this->llamaService = $llamaService;
    }

    /**
     * Display the content generator page
     */
    public function index(): View
    {
        try {
            $serviceRunning = $this->llamaService->isServiceRunning();
            $availableModels = $this->llamaService->getAvailableModels();
            
            return view('generator.index', compact('serviceRunning', 'availableModels'));
        } catch (\Exception $e) {
            Log::error('Error loading generator index: ' . $e->getMessage());
            
            return view('generator.index', [
                'serviceRunning' => false,
                'availableModels' => [],
                'error' => 'Failed to load service status: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generate new content using Llama
     */
    public function generate(Request $request): JsonResponse
    {
        // CRITICAL: Set PHP execution timeout to prevent script timeout
        set_time_limit(300); // 5 minutes
        ini_set('max_execution_time', 300);
        
        try {
            Log::info('Content generation request received', $request->all());

            // Validate request
            $validator = Validator::make($request->all(), [
                'content_type' => 'required|in:article,blog,social,email',
                'topic' => 'required|string|max:500',
                'keywords' => 'nullable|string|max:200',
                'tone' => 'required|in:professional,casual,friendly,formal',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed', $validator->errors()->toArray());
                
                return response()->json([
                    'success' => false,
                    'message' => 'Data yang dimasukkan tidak valid',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if Llama service is running
            if (!$this->llamaService->isServiceRunning()) {
                Log::error('Ollama service not running');
                
                return response()->json([
                    'success' => false,
                    'message' => 'Layanan Ollama tidak berjalan. Pastikan Ollama sudah dijalankan dan dapat diakses di: ' . config('llama.base_url'),
                    'debug' => [
                        'url' => config('llama.base_url'),
                        'expected_endpoint' => config('llama.base_url') . '/api/tags'
                    ]
                ], 503);
            }

            Log::info('Starting content generation', [
                'content_type' => $request->input('content_type'),
                'topic' => $request->input('topic')
            ]);

            // Generate content using Llama
            $result = $this->llamaService->generateContent($request->all());

            if ($result['success']) {
                Log::info('Content generation successful');
                
                return response()->json([
                    'success' => true,
                    'data' => $result['content']
                ]);
            }

            Log::error('Content generation failed', ['error' => $result['error'] ?? 'Unknown error']);

            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Gagal membuat konten',
                'debug' => [
                    'service_url' => config('llama.base_url'),
                    'model' => config('llama.model')
                ]
            ], 500);

        } catch (\Exception $e) {
            Log::error('Exception in generate method', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    // Keep all your other methods as they were...
    public function checkService(): JsonResponse
    {
        try {
            $health = $this->llamaService->getServiceHealth();

            return response()->json([
                'service_running' => $health['status'] === 'running',
                'available_models' => $health['available_models'] ?? [],
                'config' => [
                    'base_url' => config('llama.base_url'),
                    'model' => config('llama.model'),
                    'timeout' => config('llama.timeout'),
                    'temperature' => config('llama.temperature')
                ],
                'health' => $health
            ]);
        } catch (\Exception $e) {
            Log::error('Service check failed', ['error' => $e->getMessage()]);
            
            return response()->json([
                'service_running' => false,
                'available_models' => [],
                'error' => $e->getMessage(),
                'config' => [
                    'base_url' => config('llama.base_url', 'not configured'),
                    'model' => config('llama.model', 'not configured')
                ]
            ], 500);
        }
    }

    public function test(): JsonResponse
    {
        try {
            $checks = [];

            $checks['controller'] = [
                'status' => 'OK',
                'message' => 'Controller berfungsi dengan baik',
                'timestamp' => date('Y-m-d H:i:s')
            ];

            $checks['config'] = [
                'base_url' => config('llama.base_url'),
                'model' => config('llama.model'),
                'timeout' => config('llama.timeout'),
                'config_loaded' => config('llama') ? true : false
            ];

            try {
                $checks['service'] = [
                    'running' => $this->llamaService->isServiceRunning(),
                    'models' => $this->llamaService->getAvailableModels()
                ];
            } catch (\Exception $e) {
                $checks['service'] = [
                    'running' => false,
                    'error' => $e->getMessage()
                ];
            }

            return response()->json([
                'success' => true,
                'checks' => $checks
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function history(): View
    {
        $contents = [];
        return view('generator.history', compact('contents'));
    }

    public function show($id): View
    {
        $content = null;
        return view('generator.show', compact('content'));
    }

    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'total_generated' => 0,
                'today_generated' => 0,
                'popular_types' => [],
                'service_status' => $this->llamaService->isServiceRunning()
            ];
            
            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function regenerate(Request $request): JsonResponse
    {
        return $this->generate($request);
    }

    public function download(Request $request)
    {
        try {
            $filename = 'content_' . date('Y-m-d_H-i-s') . '.txt';
            $content = $request->input('content', 'No content provided');
            
            return response($content)
                ->header('Content-Type', 'text/plain')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download: ' . $e->getMessage()
            ], 500);
        }
    }
}