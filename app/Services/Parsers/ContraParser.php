<?php

namespace App\Services\Parsers;

require_once __DIR__ . '/BaseParser.php';

/**
 * Parser for Contra job alert emails.
 *
 * Contra sends from hello@contra.com with project opportunity alerts.
 * Contra is portfolio-driven with 0% commission.
 */
class ContraParser extends BaseParser
{
    public static function canHandle(string $from, string $subject): bool
    {
        return str_contains(strtolower($from), 'contra.com');
    }

    public function parse(): ?array
    {
        $body = $this->body;
        $html = $this->htmlBody;

        $title = $this->extractTitle();
        if (!$title) {
            return null;
        }

        // Extract project URL
        $jobUrl = '';
        if (preg_match('/https?:\/\/(?:www\.)?contra\.com\/(?:opportunity|p|project)\/[^\s"<>]+/i', $html ?: $body, $m)) {
            $jobUrl = $m[0];
        }

        $description = $this->extractDescription();
        $budget = $this->extractBudget($body);
        $skills = $this->extractSkills($body);

        $clientInfo = '';
        if (preg_match('/(?:client|posted by|from):\s*(.+)/i', $body, $m)) {
            $clientInfo = trim($m[1]);
        }

        $externalId = '';
        if ($jobUrl && preg_match('/\/([a-zA-Z0-9-]+)\/?$/', $jobUrl, $m)) {
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
        // Contra emails: "New opportunity: Project Title"
        if (preg_match('/(?:opportunity|project):\s*(.+)/i', $this->subject, $m)) {
            return trim($m[1]);
        }

        if (preg_match('/(?:project|opportunity|gig):\s*(.+)/i', $this->body, $m)) {
            return trim($m[1]);
        }

        return $this->subject;
    }

    private function extractDescription(): string
    {
        $body = $this->body;

        if (preg_match('/(?:project details|description|about this project)[:\s]*\n(.+?)(?:\n(?:skills|budget|timeline|apply)|\z)/si', $body, $m)) {
            return trim($m[1]);
        }

        $lines = explode("\n", $body);
        $content = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^(unsubscribe|©|contra\.com)/i', $line)) break;
            if (strlen($line) > 5) {
                $content[] = $line;
            }
        }
        return trim(implode("\n", $content)) ?: $body;
    }
}
