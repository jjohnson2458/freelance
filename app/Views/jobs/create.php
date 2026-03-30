<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>
<?php require BASE_PATH . '/app/Views/layouts/sidebar.php'; ?>

<div class="main-content flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="bi bi-clipboard-plus me-2"></i>Paste New Job</h1>
        <a href="/jobs" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Jobs
        </a>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <form method="POST" action="/jobs/store" enctype="multipart/form-data">
                <?= \Core\Csrf::field() ?>

                <div class="mb-3">
                    <label for="platform_id" class="form-label">Platform <span class="text-danger">*</span></label>
                    <select name="platform_id" id="platform_id" class="form-select" required>
                        <option value="">Select a platform...</option>
                        <?php foreach ($platforms as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" name="title" id="title" class="form-control" placeholder="Leave blank to auto-detect from posting" maxlength="500">
                    <div class="form-text">If left blank, the first line of the posting will be used as the title.</div>
                </div>

                <div class="mb-3">
                    <label for="job_url" class="form-label">Job URL</label>
                    <input type="url" name="job_url" id="job_url" class="form-control" placeholder="https://..." maxlength="500">
                </div>

                <div class="mb-3">
                    <label for="job_file" class="form-label">Upload Job Posting (optional)</label>
                    <input type="file" class="form-control" id="job_file" name="job_file" accept=".pdf,.doc,.docx,.txt">
                    <div class="form-text">Accepted formats: PDF, DOC, DOCX, TXT. Max size: 10MB.</div>
                </div>

                <div class="mb-4">
                    <label for="raw_posting" class="form-label">Job Posting <span class="text-danger">*</span></label>
                    <textarea name="raw_posting" id="raw_posting" class="form-control" rows="15" required placeholder="Paste the full job posting here..."></textarea>
                    <div class="form-text">Copy and paste the entire job listing. The system will analyze it to extract skills, budget, and other details.</div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Save &amp; Analyze
                    </button>
                    <a href="/jobs" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
