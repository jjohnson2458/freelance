<?php

namespace App\Services;

class FileTextExtractor
{
    /**
     * Extract text content from a file based on its type.
     *
     * @param string $filePath Absolute path to the file
     * @param string $fileType File extension (pdf, doc, docx, txt)
     * @return string|null Extracted text or null on failure
     */
    public static function extract(string $filePath, string $fileType): ?string
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $text = match (strtolower($fileType)) {
            'txt' => self::extractTxt($filePath),
            'pdf' => self::extractPdf($filePath),
            'docx' => self::extractDocx($filePath),
            'doc' => self::extractDoc($filePath),
            default => null,
        };

        if ($text === null) {
            return null;
        }

        // Clean up whitespace
        $text = preg_replace('/\r\n?/', "\n", $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        return trim($text);
    }

    private static function extractTxt(string $filePath): ?string
    {
        $content = file_get_contents($filePath);
        return $content !== false ? $content : null;
    }

    private static function extractPdf(string $filePath): ?string
    {
        $pdftotext = self::findBinary('pdftotext');
        if (!$pdftotext) {
            return null;
        }

        $escaped = escapeshellarg($filePath);
        $output = shell_exec("{$pdftotext} {$escaped} - 2>/dev/null");
        return $output ?: null;
    }

    private static function extractDocx(string $filePath): ?string
    {
        if (!class_exists('ZipArchive')) {
            return null;
        }

        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            return null;
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!$xml) {
            return null;
        }

        // Strip XML tags, keeping paragraph breaks
        $xml = str_replace('</w:p>', "\n", $xml);
        $text = strip_tags($xml);
        return $text ?: null;
    }

    private static function extractDoc(string $filePath): ?string
    {
        $antiword = self::findBinary('antiword');
        if (!$antiword) {
            return null;
        }

        $escaped = escapeshellarg($filePath);
        $output = shell_exec("{$antiword} {$escaped} 2>/dev/null");
        return $output ?: null;
    }

    private static function findBinary(string $name): ?string
    {
        $output = shell_exec("which {$name} 2>/dev/null");
        return $output ? trim($output) : null;
    }
}
