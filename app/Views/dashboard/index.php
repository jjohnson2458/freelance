<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>
<?php require BASE_PATH . '/app/Views/layouts/sidebar.php'; ?>

<div class="main-content flex-grow-1 p-4">
    <?php if (!empty($_SESSION['flash'])): ?>
        <?php foreach ($_SESSION['flash'] as $type => $msg): ?>
            <div class="alert alert-<?= $type === 'error' ? 'danger' : htmlspecialchars($type) ?> alert-dismissible fade show flash-message" role="alert">
                <?= htmlspecialchars($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Welcome back, <?= htmlspecialchars($userName) ?></h1>
        <span class="text-muted"><?= date('l, F j, Y') ?></span>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="card-header">
                    <i class="bi bi-briefcase me-1"></i> Total Jobs
                </div>
                <div class="card-body text-center">
                    <div class="display-5 fw-bold"><?= $totalJobs ?></div>
                    <small class="text-muted"><?= $jobsThisMonth ?> this month</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="card-header">
                    <i class="bi bi-file-text me-1"></i> Total Proposals
                </div>
                <div class="card-body text-center">
                    <div class="display-5 fw-bold"><?= $totalProposals ?></div>
                    <small class="text-muted"><?= $proposalsThisMonth ?> this month</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="card-header">
                    <i class="bi bi-calendar3 me-1"></i> This Month
                </div>
                <div class="card-body text-center">
                    <div class="display-5 fw-bold"><?= $jobsThisMonth + $proposalsThisMonth ?></div>
                    <small class="text-muted">jobs + proposals</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="card-header">
                    <i class="bi bi-trophy me-1"></i> Win Rate
                </div>
                <div class="card-body text-center">
                    <div class="display-5 fw-bold"><?= $winRate ?>%</div>
                    <small class="text-muted">of submitted proposals</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Tables -->
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-briefcase me-1"></i> Recent Jobs</span>
                    <a href="/jobs" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentJobs)): ?>
                        <div class="p-4 text-center text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No jobs yet. <a href="/jobs/create">Add your first job</a>.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Platform</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentJobs as $job): ?>
                                        <tr>
                                            <td>
                                                <a href="/jobs/view/<?= $job['id'] ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars(mb_strimwidth($job['title'], 0, 40, '...')) ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($job['platform_name'] ?? 'N/A') ?></td>
                                            <td><span class="badge status-<?= $job['status'] ?>"><?= ucfirst($job['status']) ?></span></td>
                                            <td class="text-muted small"><?= date('M j', strtotime($job['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-file-text me-1"></i> Recent Proposals</span>
                    <a href="/proposals" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentProposals)): ?>
                        <div class="p-4 text-center text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No proposals yet. Generate one from a job listing.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Job Title</th>
                                        <th>Fit Score</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentProposals as $proposal): ?>
                                        <tr>
                                            <td>
                                                <a href="/proposals/view/<?= $proposal['id'] ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars(mb_strimwidth($proposal['job_title'], 0, 40, '...')) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if ($proposal['fit_score'] !== null): ?>
                                                    <span class="badge bg-<?= $proposal['fit_score'] >= 70 ? 'success' : ($proposal['fit_score'] >= 40 ? 'warning' : 'danger') ?>">
                                                        <?= $proposal['fit_score'] ?>%
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">--</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($proposal['is_submitted']): ?>
                                                    <span class="badge status-submitted">Submitted</span>
                                                <?php else: ?>
                                                    <span class="badge status-new">Draft</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-muted small"><?= date('M j', strtotime($proposal['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
