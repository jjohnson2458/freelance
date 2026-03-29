<?php

namespace App\Services;

use Core\Database;
use Core\Env;
use Core\ErrorHandler;
use App\Models\Job;
use App\Models\Platform;
use App\Models\Resume;
use App\Models\ProposalRule;
use App\Models\Proposal;
use App\Models\Availability;

require_once BASE_PATH . '/app/Services/Parsers/BaseParser.php';
require_once BASE_PATH . '/app/Services/Parsers/UpworkParser.php';
require_once BASE_PATH . '/app/Services/Parsers/WellfoundParser.php';
require_once BASE_PATH . '/app/Services/Parsers/ContraParser.php';
require_once BASE_PATH . '/app/Services/Parsers/TuringParser.php';
require_once BASE_PATH . '/app/Services/Parsers/FreelancerParser.php';
require_once BASE_PATH . '/app/Services/ProposalGenerator.php';
require_once BASE_PATH . '/app/Models/Job.php';
require_once BASE_PATH . '/app/Models/Platform.php';
require_once BASE_PATH . '/app/Models/Resume.php';
require_once BASE_PATH . '/app/Models/ProposalRule.php';
require_once BASE_PATH . '/app/Models/Proposal.php';
require_once BASE_PATH . '/app/Models/Availability.php';

use App\Services\Parsers\UpworkParser;
use App\Services\Parsers\WellfoundParser;
use App\Services\Parsers\ContraParser;
use App\Services\Parsers\TuringParser;
use App\Services\Parsers\FreelancerParser;

/**
 * Email pipeline: receives raw email, detects platform, parses job,
 * generates proposal via Claude API, and emails the draft back.
 */
class EmailPipeline
{
    /** Parser classes in detection order */
    private array $parserClasses = [
        UpworkParser::class,
        WellfoundParser::class,
        ContraParser::class,
        TuringParser::class,
        FreelancerParser::class,
    ];

    /** Map parser class short name to platform slug */
    private array $platformMap = [
        'UpworkParser' => 'upwork',
        'WellfoundParser' => 'wellfound',
        'ContraParser' => 'contra',
        'TuringParser' => 'turing',
        'FreelancerParser' => 'freelancer',
    ];

    private int $userId;
    private int $fitScoreThreshold;

    public function __construct(int $userId = 1)
    {
        $this->userId = $userId;
        $this->fitScoreThreshold = (int) Env::get('FIT_SCORE_THRESHOLD', '5');
    }

    /**
     * Process a raw email through the full pipeline.
     *
     * @param string $rawEmail The complete raw email (headers + body)
     * @return array Result with keys: success, message, job_id, proposal_id, skipped
     */
    public function process(string $rawEmail): array
    {
        // Step 1: Detect platform and get parser
        $parser = $this->detectParser($rawEmail);
        if (!$parser) {
            return $this->result(false, 'Could not identify platform from email sender');
        }

        $parserClass = (new \ReflectionClass($parser))->getShortName();
        $platformSlug = $this->platformMap[$parserClass] ?? null;

        // Step 2: Parse the job posting from email
        $jobData = $parser->parse();
        if (!$jobData || empty($jobData['title'])) {
            return $this->result(false, 'Failed to parse job data from email');
        }

        // Step 3: Look up platform
        $platform = $platformSlug ? Platform::findBySlug($platformSlug) : null;
        if (!$platform) {
            return $this->result(false, "Platform not found: {$platformSlug}");
        }

        if (!$platform['is_active']) {
            return $this->result(false, "Platform '{$platform['name']}' is disabled");
        }

        // Step 4: Check for duplicate job (by external_id or title+platform)
        if (!empty($jobData['external_id'])) {
            $existing = $this->findExistingJob($jobData['external_id'], $platform['id']);
            if ($existing) {
                return $this->result(false, "Job already exists (ID: {$existing['id']}): {$jobData['title']}");
            }
        }

        // Step 5: Create job record
        $jobId = Job::create([
            'user_id' => $this->userId,
            'platform_id' => $platform['id'],
            'external_id' => $jobData['external_id'] ?? null,
            'title' => $jobData['title'],
            'description' => $jobData['description'],
            'skills_required' => $jobData['skills_required'],
            'budget_min' => $jobData['budget_min'],
            'budget_max' => $jobData['budget_max'],
            'budget_type' => $jobData['budget_type'],
            'client_info' => $jobData['client_info'],
            'job_url' => $jobData['job_url'],
            'source' => 'email',
            'raw_email' => $rawEmail,
            'status' => 'new',
        ]);

        // Step 6: Get active resume
        $resume = Resume::getActive($this->userId);
        if (!$resume) {
            return $this->result(true, "Job saved (ID: {$jobId}) but no active resume — skipping proposal generation", $jobId);
        }

        // Step 7: Generate proposal
        $generator = new ProposalGenerator();
        if (!$generator->isConfigured()) {
            return $this->result(true, "Job saved (ID: {$jobId}) but API not configured — skipping proposal generation", $jobId);
        }

        $rules = ProposalRule::getActiveRules($this->userId);
        $availability = Availability::where('user_id', $this->userId);

        $proposalResult = $generator->generate(
            Job::find($jobId),
            $resume,
            $rules,
            $availability,
            $platform
        );

        if (!$proposalResult) {
            return $this->result(true, "Job saved (ID: {$jobId}) but proposal generation failed", $jobId);
        }

        // Update job with fit score
        Job::update($jobId, [
            'fit_score' => $proposalResult['fit_score'],
            'fit_notes' => $proposalResult['fit_notes'],
            'status' => 'proposal_drafted',
        ]);

        // Step 8: Check fit score threshold
        if ($proposalResult['fit_score'] < $this->fitScoreThreshold) {
            Job::update($jobId, ['status' => 'archived']);
            $this->sendNotification(
                "Low Fit: {$jobData['title']} ({$proposalResult['fit_score']}/10)",
                $this->buildSkipEmail($jobData, $proposalResult, $platform)
            );
            return $this->result(true, "Job archived — fit score {$proposalResult['fit_score']}/10 below threshold ({$this->fitScoreThreshold})", $jobId, null, true);
        }

        // Step 9: Save proposal
        $proposalId = Proposal::create([
            'job_id' => $jobId,
            'resume_id' => $resume['id'],
            'content' => $proposalResult['content'],
            'tone' => $proposalResult['tone'],
            'suggested_rate' => $proposalResult['suggested_rate'],
            'rate_type' => $proposalResult['rate_type'],
            'version' => 1,
            'api_model' => $proposalResult['api_model'],
            'api_tokens_used' => $proposalResult['api_tokens_used'],
            'generation_time_ms' => $proposalResult['generation_time_ms'],
        ]);

        // Step 10: Email the draft proposal
        $this->sendNotification(
            "Proposal Draft: {$jobData['title']} (Fit: {$proposalResult['fit_score']}/10)",
            $this->buildProposalEmail($jobData, $proposalResult, $platform)
        );

        return $this->result(true, "Proposal generated and emailed (Job: {$jobId}, Proposal: {$proposalId})", $jobId, $proposalId);
    }

    /**
     * Detect which parser handles this email.
     */
    private function detectParser(string $rawEmail): ?Parsers\BaseParser
    {
        // Extract From header quickly
        $from = '';
        $subject = '';
        if (preg_match('/^From:\s*(.+)$/mi', $rawEmail, $m)) {
            $from = trim($m[1]);
        }
        if (preg_match('/^Subject:\s*(.+)$/mi', $rawEmail, $m)) {
            $subject = trim($m[1]);
        }

        foreach ($this->parserClasses as $class) {
            if ($class::canHandle($from, $subject)) {
                return new $class($rawEmail);
            }
        }

        return null;
    }

    private function findExistingJob(string $externalId, int $platformId): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id FROM jobs WHERE external_id = ? AND platform_id = ? LIMIT 1");
        $stmt->execute([$externalId, $platformId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    private function buildProposalEmail(array $job, array $proposal, array $platform): string
    {
        $appUrl = Env::get('APP_URL', 'https://freelance.visionquest2020.net');
        $rate = $proposal['suggested_rate'] ? '$' . number_format($proposal['suggested_rate'], 2) : 'N/A';

        return <<<HTML
<h3 style="color:#2c5f8a;">Proposal Draft Ready</h3>
<table style="border-collapse:collapse;width:100%;margin-bottom:15px;">
    <tr><td style="padding:4px 8px;font-weight:bold;width:120px;">Platform</td><td style="padding:4px 8px;">{$platform['name']}</td></tr>
    <tr><td style="padding:4px 8px;font-weight:bold;">Job Title</td><td style="padding:4px 8px;">{$job['title']}</td></tr>
    <tr><td style="padding:4px 8px;font-weight:bold;">Fit Score</td><td style="padding:4px 8px;">{$proposal['fit_score']}/10</td></tr>
    <tr><td style="padding:4px 8px;font-weight:bold;">Suggested Rate</td><td style="padding:4px 8px;">{$rate}</td></tr>
    <tr><td style="padding:4px 8px;font-weight:bold;">Tone</td><td style="padding:4px 8px;">{$proposal['tone']}</td></tr>
</table>

<h4 style="color:#2c5f8a;">Fit Notes</h4>
<p>{$proposal['fit_notes']}</p>

<h4 style="color:#2c5f8a;">Proposal</h4>
<div style="background:#f8f9fa;padding:15px;border-left:4px solid #2c5f8a;margin:10px 0;">
{$proposal['content']}
</div>

<p><a href="{$job['job_url']}" style="color:#2c5f8a;">View on {$platform['name']}</a> | <a href="{$appUrl}/proposals" style="color:#2c5f8a;">View in Dashboard</a></p>
HTML;
    }

    private function buildSkipEmail(array $job, array $proposal, array $platform): string
    {
        return <<<HTML
<h3 style="color:#c0392b;">Job Below Fit Threshold — Skipped</h3>
<table style="border-collapse:collapse;width:100%;margin-bottom:15px;">
    <tr><td style="padding:4px 8px;font-weight:bold;width:120px;">Platform</td><td style="padding:4px 8px;">{$platform['name']}</td></tr>
    <tr><td style="padding:4px 8px;font-weight:bold;">Job Title</td><td style="padding:4px 8px;">{$job['title']}</td></tr>
    <tr><td style="padding:4px 8px;font-weight:bold;">Fit Score</td><td style="padding:4px 8px;">{$proposal['fit_score']}/10 (threshold: {$this->fitScoreThreshold})</td></tr>
</table>
<h4>Skip Reason</h4>
<p>{$proposal['skip_reason']}</p>
<h4>Fit Notes</h4>
<p>{$proposal['fit_notes']}</p>
HTML;
    }

    private function sendNotification(string $subject, string $body): void
    {
        $messengerPath = 'C:/xampp/htdocs/claude_messenger/notify.php';

        // On production, adjust path
        if (!file_exists($messengerPath)) {
            $messengerPath = '/var/www/html/claude_messenger/notify.php';
        }

        if (!file_exists($messengerPath)) {
            ErrorHandler::log("claude_messenger not found at expected paths");
            return;
        }

        $subject = escapeshellarg($subject);
        $body = escapeshellarg($body);
        exec("php {$messengerPath} -s {$subject} -b {$body} -p claude_freelance 2>&1", $output, $code);

        if ($code !== 0) {
            ErrorHandler::log("Email notification failed: " . implode("\n", $output));
        }
    }

    private function result(bool $success, string $message, ?int $jobId = null, ?int $proposalId = null, bool $skipped = false): array
    {
        $level = $success ? 'info' : 'error';
        if (!$success) {
            ErrorHandler::log("EmailPipeline: {$message}");
        }
        return [
            'success' => $success,
            'message' => $message,
            'job_id' => $jobId,
            'proposal_id' => $proposalId,
            'skipped' => $skipped,
        ];
    }
}
