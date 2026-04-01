<?php

namespace App\Services;

use Core\Database;

class ApiUsageLogger
{
    private static array $modelPricing = [
        // Per 1M tokens: [input, output]
        'claude-sonnet-4' => [3.00, 15.00],
        'claude-sonnet-4-20250514' => [3.00, 15.00],
        'claude-sonnet-4-6' => [3.00, 15.00],
        'claude-haiku-4-5' => [0.80, 4.00],
        'claude-haiku-4-5-20251001' => [0.80, 4.00],
        'claude-opus-4' => [15.00, 75.00],
        'claude-opus-4-20250514' => [15.00, 75.00],
    ];

    private static array $defaultPricing = [3.00, 15.00];

    public static function log(
        ?int $userId,
        string $feature,
        string $model,
        int $inputTokens,
        int $outputTokens,
        ?int $responseTimeMs = null,
        bool $success = true,
        ?string $errorMessage = null,
        ?array $metadata = null
    ): void {
        try {
            $totalTokens = $inputTokens + $outputTokens;
            $cost = self::calculateCost($model, $inputTokens, $outputTokens);

            $db = Database::getInstance();
            $stmt = $db->prepare("
                INSERT INTO api_usage_log
                (user_id, feature, model, input_tokens, output_tokens, total_tokens, estimated_cost_usd, response_time_ms, success, error_message, metadata, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $userId,
                $feature,
                $model,
                $inputTokens,
                $outputTokens,
                $totalTokens,
                $cost,
                $responseTimeMs,
                $success ? 1 : 0,
                $errorMessage,
                $metadata ? json_encode($metadata) : null,
            ]);
        } catch (\Throwable $e) {
            // Don't let logging failures break the app
            error_log('ApiUsageLogger error: ' . $e->getMessage());
        }
    }

    public static function calculateCost(string $model, int $inputTokens, int $outputTokens): float
    {
        $pricing = self::$modelPricing[$model] ?? self::$defaultPricing;
        $inputCost = ($inputTokens / 1_000_000) * $pricing[0];
        $outputCost = ($outputTokens / 1_000_000) * $pricing[1];
        return round($inputCost + $outputCost, 6);
    }
}
