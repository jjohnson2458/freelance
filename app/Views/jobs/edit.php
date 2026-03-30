<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>
<?php require BASE_PATH . '/app/Views/layouts/sidebar.php'; ?>

<div class="main-content flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Job</h1>
        <a href="/jobs/view/<?= $job['id'] ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Job
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body p-4">
            <form method="POST" action="/jobs/update/<?= $job['id'] ?>" enctype="multipart/form-data">
                <?= \Core\Csrf::field() ?>

                <div class="mb-3">
                    <label for="platform_id" class="form-label">Platform <span class="text-danger">*</span></label>
                    <select name="platform_id" id="platform_id" class="form-select" required>
                        <option value="">Select a platform...</option>
                        <?php foreach ($platforms as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $p['id'] == $job['platform_id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="title" class="form-control" value="<?= htmlspecialchars($job['title']) ?>" required maxlength="500">
                </div>

                <div class="mb-3">
                    <label for="job_url" class="form-label">Job URL</label>
                    <input type="url" name="job_url" id="job_url" class="form-control" value="<?= htmlspecialchars($job['job_url'] ?? '') ?>" placeholder="https://..." maxlength="500">
                </div>

                <div class="mb-3">
                    <label for="job_file" class="form-label">Upload File (optional)</label>
                    <?php if (!empty($job['file_path'])): ?>
                        <div class="mb-2">
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-file-earmark me-1"></i>
                                <a href="<?= htmlspecialchars($job['file_path']) ?>" target="_blank"><?= strtoupper(htmlspecialchars($job['file_type'] ?? 'FILE')) ?></a>
                            </span>
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="remove_file" id="remove_file" value="1">
                                <label class="form-check-label small text-muted" for="remove_file">Remove current file</label>
                            </div>
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="job_file" name="job_file" accept=".pdf,.doc,.docx,.txt">
                    <div class="form-text">Accepted formats: PDF, DOC, DOCX, TXT. Max size: 10MB. Uploading a new file replaces the existing one.</div>
                </div>

                <div class="mb-4">
                    <label for="raw_posting" class="form-label">Job Posting <span class="text-danger">*</span></label>
                    <textarea name="raw_posting" id="raw_posting" class="form-control" rows="15" required><?= htmlspecialchars($job['description']) ?></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Save Changes
                    </button>
                    <a href="/jobs/view/<?= $job['id'] ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
