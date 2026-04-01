<?php

namespace App\Services;

use Core\Env;
use Core\ErrorHandler;

require_once BASE_PATH . '/app/Services/ApiUsageLogger.php';

class ClaudeApiService
{
    private string $apiKey;
    private string $model;
    private string $apiUrl = 'https://api.anthropic.com/v1/messages';

    public function __construct()
    {
        $this->apiKey = Env::get('ANTHROPIC_API_KEY');
        $this->model = Env::get('ANTHROPIC_MODEL', 'claude-sonnet-4-6');
    }

    public function sendMessage(string $systemPrompt, string $userMessage, int $maxTokens = 4096, string $feature = 'unknown'): ?array
    {
        if (empty($this->apiKey)) {
            ErrorHandler::log('Anthropic API key not configured');
            return null;
        }

        $payload = [
            'model' => $this->model,
            'max_tokens' => $maxTokens,
            'system' => $systemPrompt,
            'messages' => [
                ['role' => 'user', 'content' => $userMessage],
            ],
        ];

        $startTime = microtime(true);

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: 2023-06-01',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 120,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $elapsedMs = (int) ((microtime(true) - $startTime) * 1000);

        $userId = $_SESSION['user_id'] ?? null;

        if ($error) {
            ErrorHandler::log('Claude API curl error: ' . $error);
            ApiUsageLogger::log($userId, $feature, $this->model, 0, 0, $elapsedMs, false, 'curl error: ' . $error);
            return null;
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMsg = $data['error']['message'] ?? 'Unknown API error';
            ErrorHandler::log("Claude API error ({$httpCode}): {$errorMsg}");
            ApiUsageLogger::log($userId, $feature, $this->model, 0, 0, $elapsedMs, false, "HTTP {$httpCode}: {$errorMsg}");
            return null;
        }

        $text = $data['content'][0]['text'] ?? '';
        $inputTokens = $data['usage']['input_tokens'] ?? 0;
        $outputTokens = $data['usage']['output_tokens'] ?? 0;

        ApiUsageLogger::log($userId, $feature, $data['model'] ?? $this->model, $inputTokens, $outputTokens, $elapsedMs);

        return [
            'text' => $text,
            'model' => $data['model'] ?? $this->model,
            'tokens_used' => $inputTokens + $outputTokens,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'generation_time_ms' => $elapsedMs,
        ];
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }
}
