<?php

namespace App\Services\Parsers;

require_once __DIR__ . '/BaseParser.php';

/**
 * Parser for Freelancer.com job alert emails.
 *
 * Freelancer sends from noreply@freelancer.com with job recommendations.
 * Uses bidding + contest model.
 */
class FreelancerParser extends BaseParser
{
    public static function canHandle(string $from, string $subject): bool
    {
        return str_contains(strtolower($from), 'freelancer.com');
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
        if (preg_match('/https?:\/\/(?:www\.)?freelancer\.com\/projects\/[^\s"<>]+/i', $html ?: $body, $m)) {
            $jobUrl = $m[0];
        }

        $description = $this->extractDescription();
        $budget = $this->extractBudget($body);
        $skills = $this->extractSkills($body);
        $clientInfo = $this->extractClientInfo();

        // Extract project ID from URL
        $externalId = '';
        if ($jobUrl && preg_match('/\/projects\/[^\/]+\/([^\/\?\s]+)/', $jobUrl, $m)) {
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
        // "New project: Build a PHP Dashboard"
        if (preg_match('/(?:new project|project alert|recommended):\s*(.+)/i', $this->subject, $m)) {
            return trim($m[1]);
        }

        if (preg_match('/(?:project|job)(?:\s+title)?:\s*(.+)/i', $this->body, $m)) {
            return trim($m[1]);
        }

        return $this->subject;
    }

    private function extractDescription(): string
    {
        $body = $this->body;

        if (preg_match('/(?:project description|description|details)[:\s]*\n(.+?)(?:\n(?:skills|budget|bids|deadline|about the employer)|\z)/si', $body, $m)) {
            return trim($m[1]);
        }

        $lines = explode("\n", $body);
        $content = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^(unsubscribe|©|freelancer\.com|manage alerts)/i', $line)) break;
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

        if (preg_match('/(?:employer|client)\s*(?:location|country)[:\s]*(.+)/i', $body, $m)) {
            $info[] = 'Location: ' . trim($m[1]);
        }
        if (preg_match('/(?:employer|client)\s*rating[:\s]*([\d.]+)/i', $body, $m)) {
            $info[] = 'Rating: ' . trim($m[1]);
        }
        if (preg_match('/(?:bids|proposals)[:\s]*(\d+)/i', $body, $m)) {
            $info[] = 'Bids: ' . trim($m[1]);
        }
        if (preg_match('/(?:contest|type)[:\s]*(contest|project)/i', $body, $m)) {
            $info[] = 'Type: ' . trim($m[1]);
        }

        return implode(', ', $info);
    }
}
