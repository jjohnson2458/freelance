<?php

namespace App\Controllers;

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Csrf.php';
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/app/Models/Resume.php';

use App\Models\Resume;
use Core\Auth;
use Core\Csrf;

class ResumeController extends \Core\Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $resumes = Resume::where('user_id', Auth::id());
        $success = $this->getFlash('success');
        $error = $this->getFlash('error');
        $activePage = 'resumes';
        $pageTitle = 'Resumes';

        $this->view('resumes.index', compact('resumes', 'success', 'error', 'activePage', 'pageTitle'));
    }

    public function create(): void
    {
        $this->requireAuth();

        $activePage = 'resumes';
        $pageTitle = 'Add Resume';
        $error = $this->getFlash('error');

        $this->view('resumes.create', compact('activePage', 'pageTitle', 'error'));
    }

    public function store(): void
    {
        $this->requireAuth();
        Csrf::verifyOrFail();

        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if (empty($title)) {
            $this->flash('error', 'Title is required.');
            $this->redirect('/resumes/create');
        }

        $data = [
            'user_id' => Auth::id(),
            'title' => $title,
            'content' => $content,
            'is_active' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Handle file upload
        if (!empty($_FILES['resume_file']['name'])) {
            $file = $_FILES['resume_file'];

            // Validate size (10MB)
            if ($file['size'] > 10 * 1024 * 1024) {
                $this->flash('error', 'File must be under 10MB.');
                $this->redirect('/resumes/create');
            }

            // Validate file type
            $allowedExtensions = ['pdf', 'doc', 'docx', 'txt'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExtensions)) {
                $this->flash('error', 'File must be PDF, DOC, DOCX, or TXT.');
                $this->redirect('/resumes/create');
            }

            // Validate MIME type
            $allowedMimes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'text/plain',
            ];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedMimes)) {
                $this->flash('error', 'Invalid file type.');
                $this->redirect('/resumes/create');
            }

            $uploadDir = BASE_PATH . '/public/uploads/resumes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = uniqid('resume_') . '.' . $ext;
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $data['file_path'] = '/uploads/resumes/' . $filename;
                $data['file_type'] = $ext;
            } else {
                $this->flash('error', 'Failed to upload file.');
                $this->redirect('/resumes/create');
            }
        }

        Resume::create($data);

        $this->flash('success', 'Resume created successfully.');
        $this->redirect('/resumes');
    }

    public function edit(string $id): void
    {
        $this->requireAuth();

        $resume = Resume::find((int) $id);

        if (!$resume || (int) $resume['user_id'] !== Auth::id()) {
            $this->flash('error', 'Resume not found.');
            $this->redirect('/resumes');
        }

        $activePage = 'resumes';
        $pageTitle = 'Edit Resume';
        $error = $this->getFlash('error');

        $this->view('resumes.edit', compact('resume', 'activePage', 'pageTitle', 'error'));
    }

    public function update(string $id): void
    {
        $this->requireAuth();
        Csrf::verifyOrFail();

        $resume = Resume::find((int) $id);

        if (!$resume || (int) $resume['user_id'] !== Auth::id()) {
            $this->flash('error', 'Resume not found.');
            $this->redirect('/resumes');
        }

        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if (empty($title)) {
            $this->flash('error', 'Title is required.');
            $this->redirect('/resumes/' . $id . '/edit');
        }

        $data = [
            'title' => $title,
            'content' => $content,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Handle new file upload
        if (!empty($_FILES['resume_file']['name'])) {
            $file = $_FILES['resume_file'];

            if ($file['size'] > 10 * 1024 * 1024) {
                $this->flash('error', 'File must be under 10MB.');
                $this->redirect('/resumes/' . $id . '/edit');
            }

            $allowedExtensions = ['pdf', 'doc', 'docx', 'txt'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExtensions)) {
                $this->flash('error', 'File must be PDF, DOC, DOCX, or TXT.');
                $this->redirect('/resumes/' . $id . '/edit');
            }

            $allowedMimes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'text/plain',
            ];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedMimes)) {
                $this->flash('error', 'Invalid file type.');
                $this->redirect('/resumes/' . $id . '/edit');
            }

            // Delete old file if exists
            if (!empty($resume['file_path'])) {
                $oldFile = BASE_PATH . '/public' . $resume['file_path'];
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }

            $uploadDir = BASE_PATH . '/public/uploads/resumes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = uniqid('resume_') . '.' . $ext;
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $data['file_path'] = '/uploads/resumes/' . $filename;
                $data['file_type'] = $ext;
            }
        }

        Resume::update((int) $id, $data);

        $this->flash('success', 'Resume updated successfully.');
        $this->redirect('/resumes');
    }

    public function delete(string $id): void
    {
        $this->requireAuth();
        Csrf::verifyOrFail();

        $resume = Resume::find((int) $id);

        if (!$resume || (int) $resume['user_id'] !== Auth::id()) {
            $this->flash('error', 'Resume not found.');
            $this->redirect('/resumes');
        }

        // Delete file if exists
        if (!empty($resume['file_path'])) {
            $filePath = BASE_PATH . '/public' . $resume['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        Resume::delete((int) $id);

        $this->flash('success', 'Resume deleted successfully.');
        $this->redirect('/resumes');
    }

    public function activate(string $id): void
    {
        $this->requireAuth();
        Csrf::verifyOrFail();

        $resume = Resume::find((int) $id);

        if (!$resume || (int) $resume['user_id'] !== Auth::id()) {
            $this->flash('error', 'Resume not found.');
            $this->redirect('/resumes');
        }

        // Deactivate all user's resumes
        $db = \Core\Database::getInstance();
        $stmt = $db->prepare("UPDATE resumes SET is_active = 0 WHERE user_id = ?");
        $stmt->execute([Auth::id()]);

        // Activate this one
        Resume::update((int) $id, ['is_active' => 1]);

        $this->flash('success', 'Resume set as active.');
        $this->redirect('/resumes');
    }
}
