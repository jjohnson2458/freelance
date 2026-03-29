<?php

/**
 * Email pipeline entry point.
 *
 * Receives raw email via stdin (pipe) or file argument and processes it
 * through the platform parsers → Claude API → email notification pipeline.
 *
 * Usage:
 *   Pipe mode (from mail server):
 *     echo "$raw_email" | php process_email.php
 *     # Or in /etc/aliases:
 *     jobs: "|/usr/bin/php /var/www/html/freelance/process_email.php"
 *
 *   File mode (testing/manual):
 *     php process_email.php --file /path/to/email.eml
 *
 *   IMAP poll mode (scheduled via cron):
 *     php process_email.php --poll
 *
 *   Dry run (parse only, no API call or DB write):
 *     php process_email.php --file /path/to/email.eml --dry-run
 */

define('BASE_PATH', __DIR__);

require_once BASE_PATH . '/core/Env.php';
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/core/ErrorHandler.php';

Core\Env::load(BASE_PATH . '/.env');

require_once BASE_PATH . '/app/Services/EmailPipeline.php';

use App\Services\EmailPipeline;

// Parse CLI arguments
$options = getopt('', ['file:', 'poll', 'dry-run', 'user:', 'help']);

if (isset($options['help'])) {
    echo "Usage: php process_email.php [options]\n";
    echo "  --file <path>   Process a single email file\n";
    echo "  --poll           Poll IMAP inbox for unread emails\n";
    echo "  --dry-run        Parse only, don't generate proposals or save to DB\n";
    echo "  --user <id>      User ID (default: 1)\n";
    echo "  --help           Show this help\n";
    exit(0);
}

$userId = (int) ($options['user'] ?? 1);
$dryRun = isset($options['dry-run']);

// Mode 1: Poll IMAP inbox
if (isset($options['poll'])) {
    pollImap($userId, $dryRun);
    exit(0);
}

// Mode 2: Read from file
if (isset($options['file'])) {
    $filePath = $options['file'];
    if (!file_exists($filePath)) {
        fwrite(STDERR, "File not found: {$filePath}\n");
        exit(1);
    }
    $rawEmail = file_get_contents($filePath);
} else {
    // Mode 3: Read from stdin (pipe mode)
    $rawEmail = file_get_contents('php://stdin');
}

if (empty(trim($rawEmail))) {
    fwrite(STDERR, "No email content received.\n");
    exit(1);
}

// Process the email
if ($dryRun) {
    dryRun($rawEmail);
} else {
    $pipeline = new EmailPipeline($userId);
    $result = $pipeline->process($rawEmail);

    echo $result['message'] . "\n";
    exit($result['success'] ? 0 : 1);
}

/**
 * Dry run: parse only, show what would happen.
 */
function dryRun(string $rawEmail): void
{
    require_once BASE_PATH . '/app/Services/Parsers/UpworkParser.php';
    require_once BASE_PATH . '/app/Services/Parsers/WellfoundParser.php';
    require_once BASE_PATH . '/app/Services/Parsers/ContraParser.php';
    require_once BASE_PATH . '/app/Services/Parsers/TuringParser.php';
    require_once BASE_PATH . '/app/Services/Parsers/FreelancerParser.php';

    $parsers = [
        \App\Services\Parsers\UpworkParser::class,
        \App\Services\Parsers\WellfoundParser::class,
        \App\Services\Parsers\ContraParser::class,
        \App\Services\Parsers\TuringParser::class,
        \App\Services\Parsers\FreelancerParser::class,
    ];

    // Extract headers for detection
    $from = '';
    $subject = '';
    if (preg_match('/^From:\s*(.+)$/mi', $rawEmail, $m)) $from = trim($m[1]);
    if (preg_match('/^Subject:\s*(.+)$/mi', $rawEmail, $m)) $subject = trim($m[1]);

    echo "=== DRY RUN ===\n";
    echo "From: {$from}\n";
    echo "Subject: {$subject}\n\n";

    $matched = false;
    foreach ($parsers as $class) {
        if ($class::canHandle($from, $subject)) {
            $shortName = (new \ReflectionClass($class))->getShortName();
            echo "Parser: {$shortName}\n\n";

            $parser = new $class($rawEmail);
            $data = $parser->parse();

            if ($data) {
                echo "Parsed Job Data:\n";
                echo "  Title: {$data['title']}\n";
                echo "  Description: " . substr($data['description'], 0, 200) . "...\n";
                echo "  Skills: {$data['skills_required']}\n";
                echo "  Budget: " . ($data['budget_min'] ? '$' . $data['budget_min'] : 'N/A');
                if ($data['budget_max'] && $data['budget_max'] != $data['budget_min']) {
                    echo " - \${$data['budget_max']}";
                }
                echo " ({$data['budget_type']})\n";
                echo "  Client: {$data['client_info']}\n";
                echo "  URL: {$data['job_url']}\n";
                echo "  External ID: {$data['external_id']}\n";
            } else {
                echo "  Parser returned null — could not extract job data.\n";
            }
            $matched = true;
            break;
        }
    }

    if (!$matched) {
        echo "No parser matched this email.\n";
        echo "Known senders: upwork.com, wellfound.com, contra.com, turing.com, freelancer.com\n";
    }
}

/**
 * Poll IMAP inbox for unread job alert emails.
 */
function pollImap(int $userId, bool $dryRun): void
{
    $host = Core\Env::get('IMAP_HOST');
    $port = Core\Env::get('IMAP_PORT', '993');
    $user = Core\Env::get('IMAP_USER');
    $pass = Core\Env::get('IMAP_PASSWORD');
    $folder = Core\Env::get('IMAP_FOLDER', 'INBOX');

    if (empty($host) || empty($user) || empty($pass)) {
        fwrite(STDERR, "IMAP not configured. Set IMAP_HOST, IMAP_USER, IMAP_PASSWORD in .env\n");
        exit(1);
    }

    $mailbox = "{{$host}:{$port}/imap/ssl}{$folder}";
    $inbox = @imap_open($mailbox, $user, $pass);

    if (!$inbox) {
        fwrite(STDERR, "IMAP connection failed: " . imap_last_error() . "\n");
        exit(1);
    }

    $emails = imap_search($inbox, 'UNSEEN');

    if (!$emails) {
        echo "No unread emails.\n";
        imap_close($inbox);
        return;
    }

    echo "Found " . count($emails) . " unread email(s).\n\n";
    $pipeline = new EmailPipeline($userId);

    foreach ($emails as $msgNum) {
        $header = imap_headerinfo($inbox, $msgNum);
        $from = $header->fromaddress ?? '';
        $subject = $header->subject ?? '';

        echo "Processing: {$subject} (from: {$from})\n";

        // Get raw email
        $rawHeader = imap_fetchheader($inbox, $msgNum);
        $rawBody = imap_body($inbox, $msgNum);
        $rawEmail = $rawHeader . "\r\n" . $rawBody;

        if ($dryRun) {
            dryRun($rawEmail);
            echo "\n---\n\n";
        } else {
            $result = $pipeline->process($rawEmail);
            echo "  → {$result['message']}\n";

            if ($result['success']) {
                // Mark as read
                imap_setflag_full($inbox, (string) $msgNum, '\\Seen');
            }
        }
    }

    imap_close($inbox);
    echo "\nPoll complete.\n";
}
