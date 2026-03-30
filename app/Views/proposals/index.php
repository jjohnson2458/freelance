<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>
<?php require BASE_PATH . '/app/Views/layouts/sidebar.php'; ?>

<div class="main-content flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Proposals</h2>
        <span class="text-muted"><?= $total ?> total</span>
    </div>

    <?php if ($flash = $this->getFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($flash) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($flash = $this->getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($flash) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <?php if (empty($proposals)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-file-text" style="font-size:3rem;color:#ccc;"></i>
                <p class="mt-3 text-muted">No proposals yet. Paste a job and generate your first proposal!</p>
                <a href="/jobs/create" class="btn btn-primary">Paste a Job</a>
            </div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Job</th>
                        <th>Platform</th>
                        <th>Tone</th>
                        <th>Rate</th>
                        <th>Version</th>
                        <th>Status</th>
                        <th>Generated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proposals as $p): ?>
                    <tr>
                        <td><a href="/proposals/view/<?= $p['id'] ?>"><?= htmlspecialchars($p['job_title']) ?></a></td>
                        <td><?= htmlspecialchars($p['platform_name']) ?></td>
                        <td><span class="badge bg-info"><?= htmlspecialchars($p['tone'] ?? 'auto') ?></span></td>
                        <td>
                            <?php if ($p['suggested_rate']): ?>
                                $<?= number_format($p['suggested_rate'], 2) ?>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>v<?= $p['version'] ?></td>
                        <td>
                            <?php if (!empty($p['is_submitted'])): ?>
                                <span class="badge bg-success" title="Sent <?= date('M j, Y g:i A', strtotime($p['submitted_at'])) ?>"><i class="bi bi-check-circle me-1"></i>Sent</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><i class="bi bi-pencil me-1"></i>Draft</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('M j, Y', strtotime($p['created_at'])) ?></td>
                        <td>
                            <a href="/proposals/view/<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary" title="View"><i class="bi bi-eye"></i></a>
                            <a href="/proposals/edit/<?= $p['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="/proposals/delete/<?= $p['id'] ?>" class="d-inline" onsubmit="return confirm('Delete this proposal?')">
                                <?= \Core\Csrf::field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="/proposals?page=<?= $i ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
