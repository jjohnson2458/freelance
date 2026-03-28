<?php

namespace App\Controllers;

require_once BASE_PATH . '/app/Models/Job.php';
require_once BASE_PATH . '/app/Models/Proposal.php';

use App\Models\Job;
use App\Models\Proposal;
use Core\Auth;

class DashboardController extends \Core\Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $userId = Auth::id();
        $db = Job::db();

        // Total counts
        $totalJobs = Job::count('user_id = ?', [$userId]);
        $totalProposals = (int) $db->prepare(
            "SELECT COUNT(*) as cnt FROM proposals p
             JOIN jobs j ON p.job_id = j.id
             WHERE j.user_id = ?"
        )->execute([$userId]) ? 0 : 0;

        $stmtTotalProposals = $db->prepare(
            "SELECT COUNT(*) as cnt FROM proposals p
             JOIN jobs j ON p.job_id = j.id
             WHERE j.user_id = ?"
        );
        $stmtTotalProposals->execute([$userId]);
        $totalProposals = (int) $stmtTotalProposals->fetch()['cnt'];

        // This month counts
        $monthStart = date('Y-m-01 00:00:00');
        $jobsThisMonth = Job::count('user_id = ? AND created_at >= ?', [$userId, $monthStart]);

        $stmtProposalsMonth = $db->prepare(
            "SELECT COUNT(*) as cnt FROM proposals p
             JOIN jobs j ON p.job_id = j.id
             WHERE j.user_id = ? AND p.created_at >= ?"
        );
        $stmtProposalsMonth->execute([$userId, $monthStart]);
        $proposalsThisMonth = (int) $stmtProposalsMonth->fetch()['cnt'];

        // Win rate: jobs with status='won' / total submitted proposals
        $wonJobs = Job::count("user_id = ? AND status = 'won'", [$userId]);
        $stmtSubmitted = $db->prepare(
            "SELECT COUNT(*) as cnt FROM proposals p
             JOIN jobs j ON p.job_id = j.id
             WHERE j.user_id = ? AND p.is_submitted = 1"
        );
        $stmtSubmitted->execute([$userId]);
        $submittedProposals = (int) $stmtSubmitted->fetch()['cnt'];
        $winRate = $submittedProposals > 0 ? round(($wonJobs / $submittedProposals) * 100) : 0;

        // Recent 5 jobs
        $stmtRecentJobs = $db->prepare(
            "SELECT j.*, p.name as platform_name
             FROM jobs j
             LEFT JOIN platforms p ON j.platform_id = p.id
             WHERE j.user_id = ?
             ORDER BY j.created_at DESC
             LIMIT 5"
        );
        $stmtRecentJobs->execute([$userId]);
        $recentJobs = $stmtRecentJobs->fetchAll();

        // Recent 5 proposals with job titles
        $stmtRecentProposals = $db->prepare(
            "SELECT pr.*, j.title as job_title, j.fit_score
             FROM proposals pr
             JOIN jobs j ON pr.job_id = j.id
             WHERE j.user_id = ?
             ORDER BY pr.created_at DESC
             LIMIT 5"
        );
        $stmtRecentProposals->execute([$userId]);
        $recentProposals = $stmtRecentProposals->fetchAll();

        $this->view('dashboard.index', [
            'pageTitle' => 'Dashboard - Freelance Proposal Optimizer',
            'activePage' => 'dashboard',
            'totalJobs' => $totalJobs,
            'totalProposals' => $totalProposals,
            'jobsThisMonth' => $jobsThisMonth,
            'proposalsThisMonth' => $proposalsThisMonth,
            'winRate' => $winRate,
            'recentJobs' => $recentJobs,
            'recentProposals' => $recentProposals,
            'userName' => Auth::user()['name'] ?? 'User',
        ]);
    }
}
