<?php

namespace Core;

class ErrorHandler
{
    public static function init(): void
    {
        $debug = Env::get('APP_DEBUG', 'false') === 'true';
        if (!$debug) {
            ini_set('display_errors', '0');
        }
        error_reporting(E_ALL);
        set_exception_handler([self::class, 'handleException']);
    }

    public static function handleException(\Throwable $e): void
    {
        self::log($e->getMessage(), $e->getTraceAsString());

        $debug = Env::get('APP_DEBUG', 'false') === 'true';
        if ($debug) {
            http_response_code(500);
            echo '<h1>Error</h1>';
            echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        } else {
            http_response_code(500);
            if (file_exists(BASE_PATH . '/app/Views/errors/500.php')) {
                require BASE_PATH . '/app/Views/errors/500.php';
            } else {
                echo 'An error occurred. Please try again later.';
            }
        }
    }

    public static function log(string $message, string $context = ''): void
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("INSERT INTO error_log (message, context) VALUES (?, ?)");
            $stmt->execute([$message, $context]);

            // Email on error if configured
            $errorEmail = Env::get('ERROR_EMAIL');
            if ($errorEmail) {
                self::sendErrorEmail($message, $context);
            }
        } catch (\Throwable $e) {
            // Fallback to file logging if DB is unavailable
            $logFile = BASE_PATH . '/storage/logs/error.log';
            $dir = dirname($logFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $entry = date('Y-m-d H:i:s') . " | {$message} | {$context}\n";
            file_put_contents($logFile, $entry, FILE_APPEND);
        }
    }

    private static function sendErrorEmail(string $message, string $context): void
    {
        $messengerPath = 'C:/xampp/htdocs/claude_messenger/notify.php';
        if (!file_exists($messengerPath)) {
            return;
        }
        $subject = escapeshellarg('claude_freelance Error: ' . substr($message, 0, 80));
        $body = escapeshellarg('<p><strong>Error:</strong> ' . htmlspecialchars($message) . '</p><pre>' . htmlspecialchars(substr($context, 0, 2000)) . '</pre>');
        exec("php {$messengerPath} -s {$subject} -b {$body} -p claude_freelance > /dev/null 2>&1 &");
    }
}
