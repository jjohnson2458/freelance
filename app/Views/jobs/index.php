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
        <h1 class="h3 mb-0"><i class="bi bi-briefcase me-2"></i>Jobs</h1>
        <a href="/jobs/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Paste New Job
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="GET" action="/jobs" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Platform</label>
                    <select name="platform_id" class="form-select form-select-sm">
                        <option value="all">All Platforms</option>
                        <?php foreach ($platforms as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $filterPlatformId == $p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="all">All Statuses</option>
                        <?php
                        $statuses = ['new', 'proposal_drafted', 'submitted', 'won', 'rejected', 'archived'];
                        foreach ($statuses as $s):
                        ?>
                            <option value="<?= $s ?>" <?= $filterStatus === $s ? 'selected' : '' ?>>
                                <?= ucfirst(str_replace('_', ' ', $s)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by title..." value="<?= htmlspecialchars($filterSearch) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-funnel me-1"></i> Apply
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Jobs Table -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($jobs)): ?>
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                    <h5>No jobs found</h5>
                    <p class="mb-3">Paste a job posting to get started.</p>
                    <a href="/jobs/create" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Paste New Job
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Platform</th>
                                <th>Fit Score</th>
                                <th>Budget</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jobs as $job): ?>
                                <tr>
                                    <td>
                                        <a href="/jobs/view/<?= $job['id'] ?>" class="text-decoration-none fw-semibold">
                                            <?= htmlspecialchars(mb_strimwidth($job['title'], 0, 50, '...')) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($job['platform_name'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php if ($job['fit_score'] !== null): ?>
                                            <?php
                                            $score = (int) $job['fit_score'];
                                            if ($score >= 7) $scoreClass = 'success';
                                            elseif ($score >= 4) $scoreClass = 'warning';
                                            else $scoreClass = 'danger';
                                            ?>
                                            <span class="badge bg-<?= $scoreClass ?>"><?= $score ?>/10</span>
                                        <?php else: ?>
                                            <span class="text-muted">--</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($job['budget_min'] || $job['budget_max']): ?>
                                            <?php
                                            $budgetStr = '';
                                            if ($job['budget_min'] && $job['budget_max']) {
                                                $budgetStr = '$' . number_format($job['budget_min']) . ' - $' . number_format($job['budget_max']);
                                            } elseif ($job['budget_min']) {
                                                $budgetStr = 'From $' . number_format($job['budget_min']);
                                            } else {
                                                $budgetStr = 'Up to $' . number_format($job['budget_max']);
                                            }
                                            if ($job['budget_type'] === 'hourly') $budgetStr .= '/hr';
                                            ?>
                                            <span class="text-nowrap"><?= $budgetStr ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">--</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
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
                                        <span class="badge bg-<?= $badgeColor ?>">
                                            <?= ucfirst(str_replace('_', ' ', $job['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small text-nowrap"><?= date('M j, Y', strtotime($job['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="/jobs/view/<?= $job['id'] ?>" class="btn btn-outline-primary" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if ($job['status'] !== 'archived'): ?>
                                                <form method="POST" action="/jobs/archive/<?= $job['id'] ?>" class="d-inline">
                                                    <?= \Core\Csrf::field() ?>
                                                    <button type="submit" class="btn btn-outline-secondary" title="Archive">
                                                        <i class="bi bi-archive"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" action="/jobs/delete/<?= $job['id'] ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this job?')">
                                                <?= \Core\Csrf::field() ?>
                                                <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
                        <small class="text-muted">
                            Showing <?= (($page - 1) * 20) + 1 ?>-<?= min($page * 20, $total) ?> of <?= $total ?> jobs
                        </small>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page - 1 ?>&platform_id=<?= htmlspecialchars($filterPlatformId) ?>&status=<?= htmlspecialchars($filterStatus) ?>&search=<?= urlencode($filterSearch) ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&platform_id=<?= htmlspecialchars($filterPlatformId) ?>&status=<?= htmlspecialchars($filterStatus) ?>&search=<?= urlencode($filterSearch) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>&platform_id=<?= htmlspecialchars($filterPlatformId) ?>&status=<?= htmlspecialchars($filterStatus) ?>&search=<?= urlencode($filterSearch) ?>">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
