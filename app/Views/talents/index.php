<?php
$activePage = $activePage ?? 'talents';
$pageTitle = $pageTitle ?? 'Talents';
require BASE_PATH . '/app/Views/layouts/header.php';
?>
<?php require BASE_PATH . '/app/Views/layouts/sidebar.php'; ?>

<div class="main-content flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-stars me-2"></i>Talents</h2>
        <a href="/talents/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Add Talent
        </a>
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

    <?php if (empty($talents)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-stars text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3 mb-3">No talents yet. Add your skills and talents to enhance your proposals.</p>
                <a href="/talents/create" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Add Talent
                </a>
            </div>
        </div>
    <?php else: ?>
        <?php
        // Group talents by category
        $grouped = [];
        foreach ($talents as $talent) {
            $cat = $talent['category'] ?: 'Uncategorized';
            $grouped[$cat][] = $talent;
        }
        ksort($grouped);
        ?>

        <?php foreach ($grouped as $category => $categoryTalents): ?>
            <h5 class="text-muted mb-3 mt-4"><?= htmlspecialchars($category) ?></h5>
            <div class="row g-3 mb-3">
                <?php foreach ($categoryTalents as $talent): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 <?= $talent['is_active'] ? '' : 'border-secondary opacity-50' ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0"><?= htmlspecialchars($talent['name']) ?></h5>
                                    <?php
                                    $badgeClass = match($talent['proficiency']) {
                                        'beginner' => 'bg-info',
                                        'intermediate' => 'bg-primary',
                                        'advanced' => 'bg-success',
                                        'expert' => 'bg-warning text-dark',
                                        default => 'bg-secondary',
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= ucfirst(htmlspecialchars($talent['proficiency'])) ?></span>
                                </div>
                                <?php if ($talent['years_experience']): ?>
                                    <p class="card-text text-muted small mb-1">
                                        <i class="bi bi-clock-history me-1"></i><?= (int) $talent['years_experience'] ?> year<?= $talent['years_experience'] != 1 ? 's' : '' ?> experience
                                    </p>
                                <?php endif; ?>
                                <?php if ($talent['description']): ?>
                                    <p class="card-text small mt-2"><?= htmlspecialchars($talent['description']) ?></p>
                                <?php endif; ?>
                                <?php if (!$talent['is_active']): ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-transparent border-top-0">
                                <div class="btn-group btn-group-sm w-100">
                                    <a href="/talents/edit/<?= $talent['id'] ?>" class="btn btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil me-1"></i>Edit
                                    </a>
                                    <form method="POST" action="/talents/toggle/<?= $talent['id'] ?>" class="d-inline">
                                        <?= \Core\Csrf::field() ?>
                                        <button type="submit" class="btn btn-outline-<?= $talent['is_active'] ? 'warning' : 'success' ?>" title="<?= $talent['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                            <i class="bi bi-<?= $talent['is_active'] ? 'pause-circle' : 'play-circle' ?> me-1"></i><?= $talent['is_active'] ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                    </form>
                                    <form method="POST" action="/talents/delete/<?= $talent['id'] ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this talent?');">
                                        <?= \Core\Csrf::field() ?>
                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
