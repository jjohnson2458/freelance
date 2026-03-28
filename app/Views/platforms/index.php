<?php
$activePage = $activePage ?? 'platforms';
$pageTitle = $pageTitle ?? 'Platforms';
require BASE_PATH . '/app/Views/layouts/header.php';
?>
<?php require BASE_PATH . '/app/Views/layouts/sidebar.php'; ?>

<div class="main-content flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-globe me-2"></i>Platforms</h2>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($platforms)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-globe text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3">No platforms configured yet.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($platforms as $platform): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0"><?= htmlspecialchars($platform['name']) ?></h5>
                                <?php if (!empty($platform['is_active'])): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($platform['base_url'])): ?>
                                <p class="card-text small mb-2">
                                    <i class="bi bi-link-45deg me-1 text-muted"></i>
                                    <a href="<?= htmlspecialchars($platform['base_url']) ?>" target="_blank" class="text-decoration-none">
                                        <?= htmlspecialchars($platform['base_url']) ?>
                                    </a>
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($platform['parser_class'])): ?>
                                <p class="card-text small mb-2">
                                    <i class="bi bi-code-slash me-1 text-muted"></i>
                                    <code><?= htmlspecialchars($platform['parser_class']) ?></code>
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($platform['alert_email_from'])): ?>
                                <p class="card-text small mb-2">
                                    <i class="bi bi-envelope me-1 text-muted"></i>
                                    <?= htmlspecialchars($platform['alert_email_from']) ?>
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($platform['notes'])): ?>
                                <p class="card-text small text-muted mt-2"><?= nl2br(htmlspecialchars($platform['notes'])) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-transparent d-flex justify-content-between">
                            <a href="/platforms/<?= $platform['id'] ?>/edit" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </a>
                            <form method="POST" action="/platforms/<?= $platform['id'] ?>/toggle" class="d-inline">
                                <?= \Core\Csrf::field() ?>
                                <?php if (!empty($platform['is_active'])): ?>
                                    <button type="submit" class="btn btn-sm btn-outline-warning">
                                        <i class="bi bi-pause-circle me-1"></i>Disable
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-play-circle me-1"></i>Enable
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
