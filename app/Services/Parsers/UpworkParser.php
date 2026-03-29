<?php

namespace App\Services\Parsers;

require_once __DIR__ . '/BaseParser.php';

/**
 * Parser for Upwork job alert emails.
 *
 * Upwork sends alerts from noreply@upwork.com with subjects like:
 * "New job: [Job Title]" or "[X] new jobs match your profile"
 */
class UpworkParser extends BaseParser
{
    public static function canHandle(string $from, string $subject): bool
    {
        return str_contains(strtolower($from), 'upwork.com');
    }

    public function parse(): ?array
    {
        $body = $this->body;
        $html = $this->htmlBody;

        // Extract job title from subject or body
        $title = $this->extractTitle();
        if (!$title) {
            return null;
        }

        // Extract job URL
        $jobUrl = '';
        if (preg_match('/https?:\/\/(?:www\.)?upwork\.com\/(?:jobs|ab\/proposals\/job)\/[^\s"<>]+/i', $html ?: $body, $m)) {
            $jobUrl = $m[0];
        }

        // Extract external ID from URL
        $externalId = '';
        if ($jobUrl && preg_match('/~([a-zA-Z0-9]+)/', $jobUrl, $m)) {
            $externalId = $m[1];
        }

        // Extract description
        $description = $this->extractDescription();

        // Extract budget
        $budget = $this->extractBudget($body);

        // Extract skills
        $skills = $this->extractSkills($body);

        // Extract client info
        $clientInfo = $this->extractClientInfo();

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
        // Subject: "New job: Building a React Dashboard"
        if (preg_match('/new job:\s*(.+)/i', $this->subject, $m)) {
            return trim($m[1]);
        }

        // Body: look for a prominent title line
        if (preg_match('/(?:job title|project):\s*(.+)/i', $this->body, $m)) {
            return trim($m[1]);
        }

        // First significant line after "new job" or similar
        $lines = array_filter(array_map('trim', explode("\n", $this->body)));
        foreach ($lines as $line) {
            if (strlen($line) > 10 && strlen($line) < 200 && !preg_match('/^(hi|hello|dear|you|we|click|view)/i', $line)) {
                return $line;
            }
        }

        return $this->subject;
    }

    private function extractDescription(): string
    {
        $body = $this->body;

        // Look for description block between common markers
        if (preg_match('/(?:description|about this job|project details)[:\s]*\n(.+?)(?:\n(?:skills|budget|experience|proposals|about the client)|\z)/si', $body, $m)) {
            return trim($m[1]);
        }

        // Fall back to the full body minus header/footer noise
        $lines = explode("\n", $body);
        $content = [];
        $started = false;
        foreach ($lines as $line) {
            $line = trim($line);
            if (!$started && strlen($line) > 20) {
                $started = true;
            }
            if ($started) {
                if (preg_match('/^(unsubscribe|manage alerts|view more|©|copyright)/i', $line)) {
                    break;
                }
                $content[] = $line;
            }
        }

        return trim(implode("\n", $content)) ?: $body;
    }

    private function extractClientInfo(): string
    {
        $info = [];
        $body = $this->body;

        if (preg_match('/(?:client|employer)\s*(?:location|country)[:\s]*(.+)/i', $body, $m)) {
            $info[] = 'Location: ' . trim($m[1]);
        }
        if (preg_match('/(?:client|employer)\s*(?:rating|score)[:\s]*([\d.]+)/i', $body, $m)) {
            $info[] = 'Rating: ' . trim($m[1]);
        }
        if (preg_match('/(?:spent|total spent)[:\s]*\$?([\d,]+)/i', $body, $m)) {
            $info[] = 'Total Spent: $' . trim($m[1]);
        }
        if (preg_match('/(?:hires|jobs posted)[:\s]*(\d+)/i', $body, $m)) {
            $info[] = 'Hires: ' . trim($m[1]);
        }

        return implode(', ', $info);
    }
}
