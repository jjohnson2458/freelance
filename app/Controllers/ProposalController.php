<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Csrf;
use Core\Database;

require_once BASE_PATH . '/app/Models/Proposal.php';
require_once BASE_PATH . '/app/Models/Job.php';
require_once BASE_PATH . '/app/Models/Resume.php';
require_once BASE_PATH . '/app/Models/Platform.php';
require_once BASE_PATH . '/app/Models/ProposalRule.php';
require_once BASE_PATH . '/app/Models/Availability.php';
require_once BASE_PATH . '/app/Services/ProposalGenerator.php';

use App\Models\Proposal;
use App\Models\Job;
use App\Models\Resume;
use App\Models\Platform;
use App\Models\ProposalRule;
use App\Models\Availability;
use App\Services\ProposalGenerator;

class ProposalController extends Controller
{
    public function index()
    {
        $this->requireAuth();

        $page = (int) ($_GET['page'] ?? 1);
        $db = Database::getInstance();

        $stmt = $db->prepare("
            SELECT p.*, j.title as job_title, j.status as job_status, pl.name as platform_name
            FROM proposals p
            JOIN jobs j ON p.job_id = j.id
            JOIN platforms pl ON j.platform_id = pl.id
            WHERE j.user_id = ?
            ORDER BY p.created_at DESC
            LIMIT 20 OFFSET ?
        ");
        $offset = ($page - 1) * 20;
        $stmt->execute([Auth::id(), $offset]);
        $proposals = $stmt->fetchAll();

        $stmt = $db->prepare("
            SELECT COUNT(*) as cnt FROM proposals p
            JOIN jobs j ON p.job_id = j.id
            WHERE j.user_id = ?
        ");
        $stmt->execute([Auth::id()]);
        $total = (int) $stmt->fetch()['cnt'];

        $this->view('proposals.index', [
            'activePage' => 'proposals',
            'pageTitle' => 'Proposals',
            'proposals' => $proposals,
            'page' => $page,
            'totalPages' => (int) ceil($total / 20),
            'total' => $total,
        ]);
    }

    public function view($id)
    {
        $this->requireAuth();

        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT p.*, j.title as job_title, j.description as job_description,
                   j.skills_required, j.budget_min, j.budget_max, j.budget_type,
                   j.status as job_status, j.user_id as job_user_id,
                   pl.name as platform_name
            FROM proposals p
            JOIN jobs j ON p.job_id = j.id
            JOIN platforms pl ON j.platform_id = pl.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $proposal = $stmt->fetch();

        if (!$proposal || $proposal['job_user_id'] != Auth::id()) {
            $this->redirect('/proposals');
            return;
        }

        $this->view('proposals.view', [
            'activePage' => 'proposals',
            'pageTitle' => 'Proposal - ' . $proposal['job_title'],
            'proposal' => $proposal,
        ]);
    }

    public function generate($jobId)
    {
        $this->requireAuth();
        Csrf::verifyOrFail();

        $job = Job::find((int) $jobId);
        if (!$job || $job['user_id'] != Auth::id()) {
            $this->flash('error', 'Job not found.');
            $this->redirect('/jobs');
            return;
        }

        // Get active resume
        $resume = Resume::getActive(Auth::id());
        if (!$resume) {
            $this->flash('error', 'Please upload and activate a resume first.');
            $this->redirect('/jobs/view/' . $jobId);
            return;
        }

        // Get platform
        $platform = Platform::find($job['platform_id']);

        // Get active rules
        $rules = ProposalRule::getActiveRules(Auth::id());

        // Get availability
        $availability = Availability::where('user_id', Auth::id());

        // Requested tone
        $tone = $_POST['tone'] ?? 'auto';

        // Check if API is configured
        $generator = new ProposalGenerator();
        if (!$generator->isConfigured()) {
            $this->flash('error', 'Anthropic API key not configured. Add ANTHROPIC_API_KEY to your .env file.');
            $this->redirect('/jobs/view/' . $jobId);
            return;
        }

        // Generate proposal
        $result = $generator->generate($job, $resume, $rules, $availability, $platform, $tone);

        if (!$result) {
            $this->flash('error', 'Failed to generate proposal. Check error log for details.');
            $this->redirect('/jobs/view/' . $jobId);
            return;
        }

        // Get next version number
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT MAX(version) as max_ver FROM proposals WHERE job_id = ?");
        $stmt->execute([$jobId]);
        $maxVer = (int) ($stmt->fetch()['max_ver'] ?? 0);

        // Save proposal
        $proposalId = Proposal::create([
            'job_id' => $jobId,
            'resume_id' => $resume['id'],
            'content' => $result['content'],
            'tone' => $result['tone'],
            'suggested_rate' => $result['suggested_rate'],
            'rate_type' => $result['rate_type'],
            'version' => $maxVer + 1,
            'api_model' => $result['api_model'],
            'api_tokens_used' => $result['api_tokens_used'],
            'generation_time_ms' => $result['generation_time_ms'],
        ]);

        // Update job with fit score and notes
        Job::update((int) $jobId, [
            'fit_score' => $result['fit_score'],
            'fit_notes' => $result['fit_notes'],
            'status' => 'proposal_drafted',
        ]);

        $this->flash('success', 'Proposal generated successfully! (Fit score: ' . $result['fit_score'] . '/10)');
        $this->redirect('/proposals/view/' . $proposalId);
    }

    public function regenerate($id)
    {
        $this->requireAuth();
        Csrf::verifyOrFail();

        $proposal = Proposal::find((int) $id);
        if (!$proposal) {
            $this->redirect('/proposals');
            return;
        }

        $job = Job::find($proposal['job_id']);
        if (!$job || $job['user_id'] != Auth::id()) {
            $this->redirect('/proposals');
            return;
        }

        $tone = $_POST['tone'] ?? 'auto';

        // Redirect to generate with the job ID
        $_POST['tone'] = $tone;
        $this->generate($job['id']);
    }

    public function edit($id)
    {
        $this->requireAuth();

        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT p.*, j.title as job_title, j.user_id as job_user_id
            FROM proposals p
            JOIN jobs j ON p.job_id = j.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $proposal = $stmt->fetch();

        if (!$proposal || $proposal['job_user_id'] != Auth::id()) {
            $this->redirect('/proposals');
            return;
        }

        $this->view('proposals.edit', [
            'activePage' => 'proposals',
            'pageTitle' => 'Edit Proposal',
            'proposal' => $proposal,
        ]);
    }

    public function update($id)
    {
        $this->requireAuth();
        Csrf::verifyOrFail();

        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT p.*, j.user_id as job_user_id FROM proposals p
            JOIN jobs j ON p.job_id = j.id WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $proposal = $stmt->fetch();

        if (!$proposal || $proposal['job_user_id'] != Auth::id()) {
            $this->redirect('/proposals');
            return;
        }

        Proposal::update((int) $id, [
            'content' => $_POST['content'] ?? $proposal['content'],
        ]);

        $this->flash('success', 'Proposal updated.');
        $this->redirect('/proposals/view/' . $id);
    }

    public function delete($id)
    {
        $this->requireAuth();
        Csrf::verifyOrFail();

        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT p.*, j.user_id as job_user_id FROM proposals p
            JOIN jobs j ON p.job_id = j.id WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $proposal = $stmt->fetch();

        if (!$proposal || $proposal['job_user_id'] != Auth::id()) {
            $this->redirect('/proposals');
            return;
        }

        Proposal::delete((int) $id);
        $this->flash('success', 'Proposal deleted.');
        $this->redirect('/proposals');
    }

    public function pdf($id)
    {
        $this->requireAuth();

        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT p.*, j.title as job_title, j.user_id as job_user_id, pl.name as platform_name
            FROM proposals p
            JOIN jobs j ON p.job_id = j.id
            JOIN platforms pl ON j.platform_id = pl.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $proposal = $stmt->fetch();

        if (!$proposal || $proposal['job_user_id'] != Auth::id()) {
            $this->redirect('/proposals');
            return;
        }

        // Simple HTML-to-PDF output (DOMPDF integration in Phase 4)
        // For now, render a print-friendly view
        $this->view('proposals.pdf', [
            'proposal' => $proposal,
        ]);
    }
}
