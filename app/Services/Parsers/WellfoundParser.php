<?php

namespace App\Services\Parsers;

require_once __DIR__ . '/BaseParser.php';

/**
 * Parser for Wellfound (formerly AngelList) job alert emails.
 *
 * Wellfound sends from notifications@wellfound.com with subjects like:
 * "X new jobs matching your profile" or "Company is interested in you"
 */
class WellfoundParser extends BaseParser
{
    public static function canHandle(string $from, string $subject): bool
    {
        return str_contains(strtolower($from), 'wellfound.com')
            || str_contains(strtolower($from), 'angel.co');
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
        if (preg_match('/https?:\/\/(?:www\.)?wellfound\.com\/(?:jobs|l\/[^\s"<>]+)/i', $html ?: $body, $m)) {
            $jobUrl = $m[0];
        }

        $description = $this->extractDescription();
        $budget = $this->extractBudget($body);
        $skills = $this->extractSkills($body);

        // Wellfound often includes company info
        $clientInfo = $this->extractCompanyInfo();

        // Extract external ID from URL
        $externalId = '';
        if ($jobUrl && preg_match('/\/(\d+)/', $jobUrl, $m)) {
            $externalId = $m[1];
        }

        // Wellfound typically shows salary ranges
        if (!$budget['min'] && preg_match('/\$([\d,]+)k?\s*[-–]\s*\$?([\d,]+)k?/i', $body, $m)) {
            $min = (float) str_replace(',', '', $m[1]);
            $max = (float) str_replace(',', '', $m[2]);
            // If values look like "80k-120k" shorthand
            if ($min < 1000) $min *= 1000;
            if ($max < 1000) $max *= 1000;
            $budget['min'] = $min;
            $budget['max'] = $max;
            $budget['type'] = 'fixed'; // Salary is essentially fixed/annual
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
        // Look for role title patterns
        if (preg_match('/(?:role|position|job):\s*(.+)/i', $this->body, $m)) {
            return trim($m[1]);
        }

        // Company - Role pattern
        if (preg_match('/^(.+?)\s+(?:is hiring|is looking for)\s+(?:a\s+)?(.+)/mi', $this->body, $m)) {
            return trim($m[2]) . ' at ' . trim($m[1]);
        }

        // Fall back to subject
        $subject = preg_replace('/^\d+\s+new\s+jobs?\s+/i', '', $this->subject);
        return trim($subject) ?: $this->subject;
    }

    private function extractDescription(): string
    {
        $body = $this->body;

        if (preg_match('/(?:about the role|job description|what you\'ll do)[:\s]*\n(.+?)(?:\n(?:requirements|qualifications|skills|salary|equity|about us)|\z)/si', $body, $m)) {
            return trim($m[1]);
        }

        // Strip email boilerplate
        $lines = explode("\n", $body);
        $content = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^(unsubscribe|view in browser|©|powered by)/i', $line)) break;
            if (strlen($line) > 5) {
                $content[] = $line;
            }
        }
        return trim(implode("\n", $content)) ?: $body;
    }

    private function extractCompanyInfo(): string
    {
        $info = [];
        $body = $this->body;

        if (preg_match('/(?:company|startup):\s*(.+)/i', $body, $m)) {
            $info[] = 'Company: ' . trim($m[1]);
        }
        if (preg_match('/(?:stage|funding):\s*(.+)/i', $body, $m)) {
            $info[] = 'Stage: ' . trim($m[1]);
        }
        if (preg_match('/(?:team size|employees|company size):\s*(.+)/i', $body, $m)) {
            $info[] = 'Size: ' . trim($m[1]);
        }
        if (preg_match('/(?:location|remote)[:\s]*(.+)/i', $body, $m)) {
            $info[] = 'Location: ' . trim($m[1]);
        }

        return implode(', ', $info);
    }
}
