<?php

namespace App\Controllers;

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Csrf.php';
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/app/Models/Job.php';
require_once BASE_PATH . '/app/Models/Platform.php';
require_once BASE_PATH . '/app/Models/Proposal.php';
require_once BASE_PATH . '/app/Models/Resume.php';

use App\Models\Job;
use App\Models\Platform;
use App\Models\Proposal;
use App\Models\Resume;
use Core\Auth;
use Core\Csrf;

class JobController extends \Core\Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $userId = Auth::id();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;

        // Build WHERE clause from filters
        $conditions = ['j.user_id = ?'];
        $params = [$userId];

        $platformId = $_GET['platform_id'] ?? '';
        if ($platformId !== '' && $platformId !== 'all') {
            $conditions[] = 'j.platform_id = ?';
            $params[] = (int) $platformId;
        }

        $status = $_GET['status'] ?? '';
        if ($status !== '' && $status !== 'all') {
            $conditions[] = 'j.status = ?';
            $params[] = $status;
        }

        $source = $_GET['source'] ?? '';
        if ($source !== '' && $source !== 'all') {
            $conditions[] = 'j.source = ?';
            $params[] = $source;
        }

        $search = trim($_GET['search'] ?? '');
        if ($search !== '') {
            $conditions[] = 'j.title LIKE ?';
            $params[] = '%' . $search . '%';
        }

        $where = implode(' AND ', $conditions);
        $db = Job::db();

        // Count total
        $countStmt = $db->prepare("SELECT COUNT(*) as cnt FROM jobs j WHERE {$where}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetch()['cnt'];
        $totalPages = max(1, (int) ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        // Fetch jobs with platform name
        $stmt = $db->prepare(
            "SELECT j.*, p.name as platform_name
             FROM jobs j
             LEFT JOIN platforms p ON j.platform_id = p.id
             WHERE {$where}
             ORDER BY j.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
        $stmt->execute($params);
        $jobs = $stmt->fetchAll();

        // Load platforms for filter dropdown
        $platforms = Platform::where('is_active', 1);

        $success = $this->getFlash('success');
        $error = $this->getFlash('error');

        $this->view('jobs.index', [
            'pageTitle' => 'Jobs - Freelance Proposal Optimizer',
            'activePage' => 'jobs',
            'jobs' => $jobs,
            'platforms' => $platforms,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'filterPlatformId' => $platformId,
            'filterStatus' => $status,
            'filterSource' => $source,
            'filterSearch' => $search,
            'success' => $success,
            'error' => $error,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        $platforms = Platform::where('is_active', 1);

        $this->view('jobs.create', [
            'pageTitle' => 'Paste New Job - Freelance Proposal Optimizer',
            'activePage' => 'jobs',
            'platforms' => $platforms,
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        Csrf::verifyOrFail();

        $platformId = (int) ($_POST['platform_id'] ?? 0);
        $rawPosting = trim($_POST['raw_posting'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $jobUrl = trim($_POST['job_url'] ?? '');

        // Validation
        if ($platformId < 1 || empty($rawPosting)) {
            $this->flash('error', 'Platform and job posting text are required.');
            $this->redirect('/jobs/create');
        }

        // Auto-detect title from first line of posting if not provided
        if (empty($title)) {
            $firstLine = strtok($rawPosting, "\n");
            $title = mb_strimwidth(trim($firstLine), 0, 500);
        }

        // Store the raw posting as description for now
        $description = $rawPosting;

        $data = [
            'user_id' => Auth::id(),
            'platform_id' => $platformId,
            'title' => $title,
            'description' => $description,
            'job_url' => $jobUrl ?: null,
            'source' => 'manual',
            'status' => 'new',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Handle file upload
        if (!empty($_FILES['job_file']['name'])) {
            $file = $_FILES['job_file'];
            $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
            $allowedExts = ['pdf', 'doc', 'docx', 'txt'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($file['type'], $allowedTypes) || !in_array($ext, $allowedExts)) {
                $this->flash('error', 'Invalid file type. Allowed: PDF, DOC, DOCX, TXT.');
                $this->redirect('/jobs/create');
                return;
            }

            if ($file['size'] > 10 * 1024 * 1024) {
                $this->flash('error', 'File too large. Maximum size: 10MB.');
                $this->redirect('/jobs/create');
                return;
            }

            $uploadDir = BASE_PATH . '/public/uploads/jobs/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = uniqid('job_') . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                $data['file_path'] = '/uploads/jobs/' . $filename;
                $data['file_type'] = $ext;
            }
        }

        $jobId = Job::create($data);

        $this->flash('success', 'Job saved successfully.');
        $this->redirect('/jobs/view/' . $jobId);
    }

    public function show(string $id): void
    {
        $this->requireAuth();

        $job = Job::find((int) $id);
        if (!$job || $job['user_id'] !== Auth::id()) {
            $this->flash('error', 'Job not found.');
            $this->redirect('/jobs');
        }

        // Load platform name
        $platform = Platform::find((int) $job['platform_id']);
        $platformName = $platform['name'] ?? 'Unknown';

        // Load related proposals
        $db = Job::db();
        $stmt = $db->prepare(
            "SELECT * FROM proposals WHERE job_id = ? ORDER BY version DESC"
        );
        $stmt->execute([(int) $id]);
        $proposals = $stmt->fetchAll();

        // Check for active resume
        $activeResume = Resume::getActive(Auth::id());

        $success = $this->getFlash('success');
        $error = $this->getFlash('error');

        $this->view('jobs.view', [
            'pageTitle' => htmlspecialchars($job['title']) . ' - Freelance Proposal Optimizer',
            'activePage' => 'jobs',
            'job' => $job,
            'platformName' => $platformName,
            'proposals' => $proposals,
            'activeResume' => $activeResume,
            'success' => $success,
            'error' => $error,
        ]);
    }

    public function delete(string $id): void
    {
        $this->requireAuth();
        Csrf::verifyOrFail();

        $job = Job::find((int) $id);
        if (!$job || $job['user_id'] !== Auth::id()) {
            $this->flash('error', 'Job not found.');
            $this->redirect('/jobs');
        }

        Job::delete((int) $id);

        $this->flash('success', 'Job deleted.');
        $this->redirect('/jobs');
    }

    public function archive(string $id): void
    {
        $this->requireAuth();
        Csrf::verifyOrFail();

        $job = Job::find((int) $id);
        if (!$job || $job['user_id'] !== Auth::id()) {
            $this->flash('error', 'Job not found.');
            $this->redirect('/jobs');
        }

        Job::update((int) $id, [
            'status' => 'archived',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->flash('success', 'Job archived.');
        $this->redirect('/jobs');
    }
}
