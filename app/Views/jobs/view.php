<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>
<?php require BASE_PATH . '/app/Views/layouts/sidebar.php'; ?>

<div class="main-content flex-grow-1 p-4">
    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show flash-message" role="alert">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show flash-message" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-briefcase me-2"></i><?= htmlspecialchars($job['title']) ?>
        </h1>
        <div class="d-flex gap-2">
            <a href="/jobs/edit/<?= $job['id'] ?>" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i>Edit</a>
            <a href="/jobs" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Jobs</a>
        </div>
    </div>

    <!-- Job Details Card -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-info-circle me-1"></i> Job Details
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Platform</label>
                        <div class="fw-semibold"><?= htmlspecialchars($platformName) ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Status</label>
                        <div>
                            <?php
                            $statusBadges = [
                                'new' => 'primary',
                                'proposal_drafted' => 'info',
                                'submitted' => 'warning',
                                'won' => 'success',
                                'rejected' => 'danger',
                                'archived' => 'secondary',
                            ];
                            $badgeColor = $statusBadges[$job['status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $badgeColor ?> fs-6">
                                <?= ucfirst(str_replace('_', ' ', $job['status'])) ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Created</label>
                        <div class="fw-semibold"><?= date('M j, Y g:i A', strtotime($job['created_at'])) ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Budget</label>
                        <div class="fw-semibold">
                            <?php if ($job['budget_min'] || $job['budget_max']): ?>
                                <?php
                                if ($job['budget_min'] && $job['budget_max']) {
                                    echo '$' . number_format($job['budget_min']) . ' - $' . number_format($job['budget_max']);
                                } elseif ($job['budget_min']) {
                                    echo 'From $' . number_format($job['budget_min']);
                                } else {
                                    echo 'Up to $' . number_format($job['budget_max']);
                                }
                                if ($job['budget_type'] === 'hourly') echo '/hr';
                                elseif ($job['budget_type'] === 'fixed') echo ' (fixed)';
                                ?>
                            <?php else: ?>
                                <span class="text-muted">Not specified</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Fit Score</label>
                        <div>
                            <?php if ($job['fit_score'] !== null): ?>
                                <?php
                                $score = (int) $job['fit_score'];
                                if ($score >= 7) $scoreClass = 'success';
                                elseif ($score >= 4) $scoreClass = 'warning';
                                else $scoreClass = 'danger';
                                ?>
                                <span class="badge bg-<?= $scoreClass ?> fs-6"><?= $score ?>/10</span>
                            <?php else: ?>
                                <span class="text-muted">Not scored yet</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php if (!empty($job['job_url'])): ?>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Job URL</label>
                            <div>
                                <a href="<?= htmlspecialchars($job['job_url']) ?>" target="_blank" rel="noopener" class="text-decoration-none">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>View Original
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (!empty($job['file_path'])): ?>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Attached File</label>
                            <div>
                                <a href="<?= htmlspecialchars($job['file_path']) ?>" target="_blank" class="text-decoration-none">
                                    <i class="bi bi-file-earmark me-1"></i><?= strtoupper(htmlspecialchars($job['file_type'] ?? 'FILE')) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($job['skills_required'])): ?>
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Skills Required</label>
                    <div>
                        <?php
                        $skills = array_map('trim', explode(',', $job['skills_required']));
                        foreach ($skills as $skill):
                            if (empty($skill)) continue;
                        ?>
                            <span class="badge bg-light text-dark border me-1 mb-1"><?= htmlspecialchars($skill) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($job['fit_notes'])): ?>
                <div class="mb-0">
                    <label class="form-label text-muted small mb-1">Fit Notes</label>
                    <div class="bg-light rounded p-3 small"><?= nl2br(htmlspecialchars($job['fit_notes'])) ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Job Description Card -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-file-text me-1"></i> Full Job Description
        </div>
        <div class="card-body">
            <div class="job-description" style="white-space: pre-wrap; font-size: 0.925rem; line-height: 1.7;">
<?= htmlspecialchars($job['description']) ?>
            </div>
        </div>
    </div>

    <!-- Proposals Section -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-file-earmark-text me-1"></i> Proposals</span>
            <span class="badge bg-light text-dark"><?= count($proposals) ?></span>
        </div>
        <div class="card-body">
            <?php if (!$activeResume): ?>
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>No active resume.</strong> Please <a href="/resumes" class="alert-link">upload and activate a resume</a> first before generating proposals.
                </div>
            <?php endif; ?>

            <?php if (!empty($proposals)): ?>
                <div class="table-responsive mb-4">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Version</th>
                                <th>Tone</th>
                                <th>Suggested Rate</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($proposals as $proposal): ?>
                                <tr>
                                    <td><span class="fw-semibold">v<?= $proposal['version'] ?></span></td>
                                    <td><?= htmlspecialchars(ucfirst($proposal['tone'] ?? 'Auto')) ?></td>
                                    <td>
                                        <?php if ($proposal['suggested_rate']): ?>
                                            $<?= number_format($proposal['suggested_rate'], 2) ?>
                                            <?php if ($proposal['rate_type'] === 'hourly'): ?>/hr<?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">--</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($proposal['is_submitted']): ?>
                                            <span class="badge bg-success">Submitted</span>
                                        <?php else: ?>
                                            <span class="badge bg-info">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small"><?= date('M j, Y', strtotime($proposal['created_at'])) ?></td>
                                    <td>
                                        <a href="/proposals/view/<?= $proposal['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i>View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted mb-4">No proposals generated yet for this job.</p>
            <?php endif; ?>

            <!-- Generate Proposal Form -->
            <?php if ($activeResume): ?>
                <div class="bg-light rounded p-3">
                    <h6 class="mb-3"><i class="bi bi-magic me-1"></i> Generate New Proposal</h6>
                    <form method="POST" action="/proposals/generate/<?= $job['id'] ?>" class="row g-2 align-items-end">
                        <?= \Core\Csrf::field() ?>
                        <div class="col-md-4">
                            <label for="tone" class="form-label">Tone</label>
                            <select name="tone" id="tone" class="form-select">
                                <option value="auto">Auto (best fit)</option>
                                <option value="corporate">Corporate</option>
                                <option value="casual">Casual</option>
                                <option value="technical">Technical</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-lightning me-1"></i> Generate Proposal
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex gap-2">
        <?php if ($job['status'] !== 'archived'): ?>
            <form method="POST" action="/jobs/archive/<?= $job['id'] ?>">
                <?= \Core\Csrf::field() ?>
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="bi bi-archive me-1"></i> Archive Job
                </button>
            </form>
        <?php endif; ?>
        <form method="POST" action="/jobs/delete/<?= $job['id'] ?>" onsubmit="return confirm('Are you sure you want to delete this job? This will also delete all associated proposals.')">
            <?= \Core\Csrf::field() ?>
            <button type="submit" class="btn btn-outline-danger">
                <i class="bi bi-trash me-1"></i> Delete Job
            </button>
        </form>
    </div>
</div>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
