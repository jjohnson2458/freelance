<?php

namespace App\Services\Parsers;

/**
 * Base email parser for job alert emails.
 * Platform-specific parsers extend this and implement parse().
 */
abstract class BaseParser
{
    protected string $rawEmail;
    protected string $subject;
    protected string $from;
    protected string $body;
    protected string $htmlBody;

    public function __construct(string $rawEmail)
    {
        $this->rawEmail = $rawEmail;
        $this->parseRawEmail();
    }

    /**
     * Parse the raw email into headers and body.
     */
    private function parseRawEmail(): void
    {
        // Split headers from body
        $parts = preg_split('/\r?\n\r?\n/', $this->rawEmail, 2);
        $headerBlock = $parts[0] ?? '';
        $rawBody = $parts[1] ?? '';

        // Extract key headers
        $this->subject = $this->extractHeader($headerBlock, 'Subject');
        $this->from = $this->extractHeader($headerBlock, 'From');

        // Check for multipart content
        $contentType = $this->extractHeader($headerBlock, 'Content-Type');
        if (str_contains($contentType, 'multipart')) {
            $boundary = $this->extractBoundary($contentType);
            if ($boundary) {
                $this->parseMultipart($rawBody, $boundary);
            } else {
                $this->body = $this->stripHtml($rawBody);
                $this->htmlBody = $rawBody;
            }
        } else {
            if (str_contains($contentType, 'text/html')) {
                $this->htmlBody = $rawBody;
                $this->body = $this->stripHtml($rawBody);
            } else {
                $this->body = $rawBody;
                $this->htmlBody = '';
            }
        }
    }

    private function extractHeader(string $headers, string $name): string
    {
        if (preg_match('/^' . preg_quote($name, '/') . ':\s*(.+?)$/mi', $headers, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    private function extractBoundary(string $contentType): string
    {
        if (preg_match('/boundary="?([^";\s]+)"?/i', $contentType, $m)) {
            return $m[1];
        }
        return '';
    }

    private function parseMultipart(string $body, string $boundary): void
    {
        $this->body = '';
        $this->htmlBody = '';
        $parts = explode('--' . $boundary, $body);

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '' || $part === '--') continue;

            $sections = preg_split('/\r?\n\r?\n/', $part, 2);
            $partHeaders = $sections[0] ?? '';
            $partBody = $sections[1] ?? '';

            if (str_contains($partHeaders, 'text/html')) {
                $this->htmlBody = $partBody;
                if (empty($this->body)) {
                    $this->body = $this->stripHtml($partBody);
                }
            } elseif (str_contains($partHeaders, 'text/plain')) {
                $this->body = $partBody;
            }
        }

        if (empty($this->body) && !empty($this->htmlBody)) {
            $this->body = $this->stripHtml($this->htmlBody);
        }
    }

    /**
     * Strip HTML tags and decode entities.
     */
    protected function stripHtml(string $html): string
    {
        // Remove style and script blocks
        $html = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $html);
        $html = preg_replace('/<script[^>]*>.*?<\/script>/si', '', $html);
        // Convert line breaks
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);
        $html = preg_replace('/<\/p>/i', "\n\n", $html);
        $html = preg_replace('/<\/div>/i', "\n", $html);
        $html = preg_replace('/<\/li>/i', "\n", $html);
        // Strip remaining tags
        $text = strip_tags($html);
        // Decode entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Normalize whitespace
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        return trim($text);
    }

    /**
     * Extract a dollar amount or range from text.
     */
    protected function extractBudget(string $text): array
    {
        $result = ['min' => null, 'max' => null, 'type' => 'not_specified'];

        // Range: $50 - $100 or $50-$100
        if (preg_match('/\$\s*([\d,]+(?:\.\d{2})?)\s*[-–]\s*\$?\s*([\d,]+(?:\.\d{2})?)/i', $text, $m)) {
            $result['min'] = (float) str_replace(',', '', $m[1]);
            $result['max'] = (float) str_replace(',', '', $m[2]);
        }
        // Single amount: $50
        elseif (preg_match('/\$\s*([\d,]+(?:\.\d{2})?)/', $text, $m)) {
            $result['min'] = (float) str_replace(',', '', $m[1]);
            $result['max'] = $result['min'];
        }

        // Detect hourly vs fixed
        if (preg_match('/\b(hourly|per\s*hour|\/hr|\/hour)\b/i', $text)) {
            $result['type'] = 'hourly';
        } elseif (preg_match('/\b(fixed[- ]?price|flat\s*rate|project[- ]?based|budget)\b/i', $text)) {
            $result['type'] = 'fixed';
        }

        return $result;
    }

    /**
     * Extract skills from a text block.
     */
    protected function extractSkills(string $text): string
    {
        // Look for explicit skills sections
        if (preg_match('/(?:skills|technologies|requirements|tech stack)[:\s]*(.+?)(?:\n\n|\z)/si', $text, $m)) {
            $skills = $m[1];
            // Clean up bullet points, commas, etc.
            $skills = preg_replace('/[•·●\-\*]\s*/', '', $skills);
            $skills = preg_replace('/\s+/', ' ', $skills);
            return trim($skills);
        }
        return '';
    }

    /**
     * Parse the email and return structured job data.
     *
     * @return array|null Parsed job data with keys:
     *   - title: string
     *   - description: string
     *   - skills_required: string
     *   - budget_min: float|null
     *   - budget_max: float|null
     *   - budget_type: string (fixed|hourly|not_specified)
     *   - client_info: string
     *   - job_url: string
     *   - external_id: string
     */
    abstract public function parse(): ?array;

    /**
     * Check if this parser can handle the given email.
     */
    abstract public static function canHandle(string $from, string $subject): bool;

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getHtmlBody(): string
    {
        return $this->htmlBody;
    }
}
