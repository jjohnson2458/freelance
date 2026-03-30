<?php

namespace App\Services;

use Core\Database;
use Core\ErrorHandler;

require_once BASE_PATH . '/app/Services/ClaudeApiService.php';
require_once BASE_PATH . '/app/Services/FileTextExtractor.php';

class ProposalGenerator
{
    private ClaudeApiService $api;

    public function __construct()
    {
        $this->api = new ClaudeApiService();
    }

    /**
     * Generate a proposal for a job posting.
     *
     * @param array $job The job record
     * @param array $resume The active resume record
     * @param array $rules Active proposal rules
     * @param array $availability Current availability windows
     * @param array $platform The platform record
     * @param string $tone Requested tone (auto, corporate, casual, technical)
     * @return array|null Generated proposal data or null on failure
     */
    public function generate(
        array $job,
        array $resume,
        array $rules,
        array $availability,
        array $platform,
        string $tone = 'auto'
    ): ?array {
        $systemPrompt = $this->buildSystemPrompt($rules, $platform, $tone);
        $userMessage = $this->buildUserMessage($job, $resume, $availability);

        $response = $this->api->sendMessage($systemPrompt, $userMessage, 4096);

        if (!$response) {
            return null;
        }

        // Parse the JSON response from Claude
        $parsed = $this->parseResponse($response['text']);

        if (!$parsed) {
            // Fallback: treat the whole response as proposal text
            $parsed = [
                'fit_score' => 5,
                'fit_notes' => '',
                'proposal_text' => $response['text'],
                'suggested_rate' => null,
                'tone' => $tone === 'auto' ? 'professional' : $tone,
                'skip_reason' => null,
            ];
        }

        return [
            'content' => $parsed['proposal_text'],
            'fit_score' => $parsed['fit_score'],
            'fit_notes' => $parsed['fit_notes'] ?? '',
            'suggested_rate' => $parsed['suggested_rate'],
            'rate_type' => $job['budget_type'] ?? 'not_specified',
            'tone' => $parsed['tone'] ?? $tone,
            'skip_reason' => $parsed['skip_reason'] ?? null,
            'api_model' => $response['model'],
            'api_tokens_used' => $response['tokens_used'],
            'generation_time_ms' => $response['generation_time_ms'],
        ];
    }

    private function buildSystemPrompt(array $rules, array $platform, string $tone): string
    {
        $prompt = <<<PROMPT
You are an expert freelance proposal writer. Your job is to craft compelling, honest, and tailored proposals for freelance job postings.

IMPORTANT RULES:
- Write in first person as the freelancer
- Be honest about skills and experience — never exaggerate
- Match the proposal tone and length to the platform and client style
- If the job is a poor fit, say so honestly in the fit_notes

PLATFORM: {$platform['name']}
Platform notes: {$platform['notes']}

PROMPT;

        if ($tone !== 'auto') {
            $prompt .= "\nREQUESTED TONE: {$tone}\n";
        }

        // Add active rules
        if (!empty($rules)) {
            $prompt .= "\nPROPOSAL RULES (follow these strictly):\n";
            foreach ($rules as $rule) {
                $category = strtoupper($rule['category']);
                $prompt .= "- [{$category}] {$rule['rule_text']}\n";
            }
        }

        $prompt .= <<<PROMPT

RESPOND WITH VALID JSON ONLY. No markdown, no code fences. The JSON must have these fields:
{
    "fit_score": <integer 1-10, how well the job matches the freelancer's skills>,
    "fit_notes": "<string explaining why this is/isn't a good fit>",
    "proposal_text": "<the full proposal text, ready to submit>",
    "suggested_rate": <number or null if not applicable>,
    "tone": "<detected/applied tone: corporate, casual, technical, professional>",
    "skip_reason": "<string or null — if fit_score < 5, explain why this job should be skipped>"
}
PROMPT;

        return $prompt;
    }

    private function buildUserMessage(array $job, array $resume, array $availability): string
    {
        $resumeContent = $resume['content'] ?? '';

        // If resume has an attached file, extract and append its text
        if (!empty($resume['file_path']) && !empty($resume['file_type'])) {
            $absPath = BASE_PATH . '/public' . $resume['file_path'];
            $fileText = FileTextExtractor::extract($absPath, $resume['file_type']);
            if ($fileText) {
                $resumeContent = $fileText . ($resumeContent ? "\n\n" . $resumeContent : '');
            }
        }

        $message = "MY RESUME:\n" . ($resumeContent ?: 'No resume content available.') . "\n\n";

        $message .= "JOB POSTING:\n";
        $message .= "Title: " . ($job['title'] ?? 'Untitled') . "\n";
        $message .= "Description:\n" . ($job['description'] ?? '') . "\n";

        if (!empty($job['skills_required'])) {
            $message .= "Required Skills: {$job['skills_required']}\n";
        }
        if (!empty($job['budget_min']) || !empty($job['budget_max'])) {
            $budgetType = $job['budget_type'] ?? 'not_specified';
            $message .= "Budget: ";
            if (!empty($job['budget_min'])) $message .= "$" . number_format($job['budget_min'], 2);
            if (!empty($job['budget_min']) && !empty($job['budget_max'])) $message .= " - ";
            if (!empty($job['budget_max'])) $message .= "$" . number_format($job['budget_max'], 2);
            $message .= " ({$budgetType})\n";
        }
        if (!empty($job['client_info'])) {
            $message .= "Client Info: {$job['client_info']}\n";
        }

        // Include text extracted from attached file
        if (!empty($job['file_path']) && !empty($job['file_type'])) {
            $absPath = BASE_PATH . '/public' . $job['file_path'];
            $fileText = FileTextExtractor::extract($absPath, $job['file_type']);
            if ($fileText) {
                $message .= "\nATTACHED DOCUMENT CONTENT:\n" . $fileText . "\n";
            }
        }

        // Add availability
        if (!empty($availability)) {
            $message .= "\nMY AVAILABILITY:\n";
            foreach ($availability as $slot) {
                $from = $slot['available_from'];
                $to = $slot['available_to'] ?? 'indefinitely';
                $hours = $slot['hours_per_week'];
                $message .= "- Available from {$from} to {$to}, {$hours} hours/week";
                if (!empty($slot['notes'])) {
                    $message .= " ({$slot['notes']})";
                }
                $message .= "\n";
            }
        }

        return $message;
    }

    private function parseResponse(string $text): ?array
    {
        // Try to extract JSON from the response
        $text = trim($text);

        // Remove markdown code fences if present
        if (str_starts_with($text, '```')) {
            $text = preg_replace('/^```(?:json)?\s*/', '', $text);
            $text = preg_replace('/\s*```$/', '', $text);
        }

        $data = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Try to find JSON within the text
            if (preg_match('/\{[^{}]*"fit_score"[^{}]*\}/s', $text, $matches)) {
                $data = json_decode($matches[0], true);
            }
        }

        if (!$data || !isset($data['proposal_text'])) {
            return null;
        }

        return [
            'fit_score' => (int) ($data['fit_score'] ?? 5),
            'fit_notes' => $data['fit_notes'] ?? '',
            'proposal_text' => $data['proposal_text'],
            'suggested_rate' => isset($data['suggested_rate']) ? (float) $data['suggested_rate'] : null,
            'tone' => $data['tone'] ?? 'professional',
            'skip_reason' => $data['skip_reason'] ?? null,
        ];
    }

    public function isConfigured(): bool
    {
        return $this->api->isConfigured();
    }
}
