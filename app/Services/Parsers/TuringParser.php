<?php

namespace App\Services\Parsers;

require_once __DIR__ . '/BaseParser.php';

/**
 * Parser for Turing.com job alert emails.
 *
 * Turing sends from notifications@turing.com with AI-matched job alerts.
 * Focused on long-term remote contracts.
 */
class TuringParser extends BaseParser
{
    public static function canHandle(string $from, string $subject): bool
    {
        return str_contains(strtolower($from), 'turing.com');
    }

    public function parse(): ?array
    {
        $body = $this->body;
        $html = $this->htmlBody;

        $title = $this->extractTitle();
        if (!$title) {
            return null;
        }

        // Extract job URL
        $jobUrl = '';
        if (preg_match('/https?:\/\/(?:www\.)?turing\.com\/(?:jobs|remote-developer-jobs)\/[^\s"<>]+/i', $html ?: $body, $m)) {
            $jobUrl = $m[0];
        }

        $description = $this->extractDescription();
        $budget = $this->extractBudget($body);
        $skills = $this->extractSkills($body);
        $clientInfo = $this->extractClientInfo();

        $externalId = '';
        if ($jobUrl && preg_match('/\/(\d+)/', $jobUrl, $m)) {
            $externalId = $m[1];
        }

        return [
            'title' => $title,
            'description' => $description,
            'skills_required' => $skills,
            'budget_min' => $budget['min'],
            'budget_max' => $budget['max'],
            'budget_type' => $budget['type'],
            'client_info' => $clientInfo,
            'job_url' => $jobUrl,
            'external_id' => $externalId,
        ];
    }

    private function extractTitle(): string
    {
        // "You matched with: Senior PHP Developer"
        if (preg_match('/(?:matched with|new match|job alert):\s*(.+)/i', $this->subject, $m)) {
            return trim($m[1]);
        }

        if (preg_match('/(?:role|position|job title):\s*(.+)/i', $this->body, $m)) {
            return trim($m[1]);
        }

        // Look for developer/engineer title patterns in body
        if (preg_match('/((?:senior|junior|lead|staff|mid|sr\.?|jr\.?)?\s*(?:\w+\s+)?(?:developer|engineer|architect|designer|analyst))/i', $this->body, $m)) {
            return trim($m[1]);
        }

        return $this->subject;
    }

    private function extractDescription(): string
    {
        $body = $this->body;

        if (preg_match('/(?:job description|about the role|responsibilities)[:\s]*\n(.+?)(?:\n(?:requirements|skills|qualifications|compensation|apply)|\z)/si', $body, $m)) {
            return trim($m[1]);
        }

        $lines = explode("\n", $body);
        $content = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^(unsubscribe|©|turing\.com|manage preferences)/i', $line)) break;
            if (strlen($line) > 5) {
                $content[] = $line;
            }
        }
        return trim(implode("\n", $content)) ?: $body;
    }

    private function extractClientInfo(): string
    {
        $info = [];
        $body = $this->body;

        if (preg_match('/(?:company|employer):\s*(.+)/i', $body, $m)) {
            $info[] = 'Company: ' . trim($m[1]);
        }
        if (preg_match('/(?:duration|contract length):\s*(.+)/i', $body, $m)) {
            $info[] = 'Duration: ' . trim($m[1]);
        }
        if (preg_match('/(?:timezone|time zone):\s*(.+)/i', $body, $m)) {
            $info[] = 'Timezone: ' . trim($m[1]);
        }

        return implode(', ', $info);
    }
}
