<?php
$activePage = $activePage ?? 'resumes';
$pageTitle = $pageTitle ?? 'Add Resume';
require BASE_PATH . '/app/Views/layouts/header.php';
?>
<?php require BASE_PATH . '/app/Views/layouts/sidebar.php'; ?>

<div class="main-content flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add Resume</h2>
        <a href="/resumes" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Resumes
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="/resumes" enctype="multipart/form-data">
                <?= \Core\Csrf::field() ?>

                <div class="mb-3">
                    <label for="title" class="form-label fw-medium">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" required
                           placeholder="e.g., Full Stack Developer Resume" autofocus>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label fw-medium">Content</label>
                    <textarea class="form-control" id="content" name="content" rows="15"
                              placeholder="Paste your resume text here..."></textarea>
                    <div class="form-text">Plain text version of your resume. Used for proposal generation.</div>
                </div>

                <div class="mb-4">
                    <label for="resume_file" class="form-label fw-medium">Upload File (optional)</label>
                    <input type="file" class="form-control" id="resume_file" name="resume_file"
                           accept=".pdf,.doc,.docx,.txt">
                    <div class="form-text">Accepted formats: PDF, DOC, DOCX, TXT. Max size: 10MB.</div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Save Resume
                    </button>
                    <a href="/resumes" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
