<?php
$activePage = $activePage ?? 'resumes';
$pageTitle = $pageTitle ?? 'Resumes';
require BASE_PATH . '/app/Views/layouts/header.php';
?>
<?php require BASE_PATH . '/app/Views/layouts/sidebar.php'; ?>

<div class="main-content flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-file-person me-2"></i>Resumes</h2>
        <a href="/resumes/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Add Resume
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

    <?php if (empty($resumes)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-file-person text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3 mb-3">No resumes yet. Add your first resume to get started.</p>
                <a href="/resumes/create" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Add Resume
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>File</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resumes as $resume): ?>
                            <tr>
                                <td class="fw-medium"><?= htmlspecialchars($resume['title']) ?></td>
                                <td>
                                    <?php if (!empty($resume['is_active'])): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($resume['file_path'])): ?>
                                        <a href="<?= htmlspecialchars($resume['file_path']) ?>" target="_blank" class="text-decoration-none">
                                            <i class="bi bi-file-earmark me-1"></i><?= strtoupper(htmlspecialchars($resume['file_type'] ?? 'FILE')) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Text only</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?= date('M j, Y', strtotime($resume['created_at'])) ?></td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/resumes/edit/<?= $resume['id'] ?>" class="btn btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if (empty($resume['is_active'])): ?>
                                            <form method="POST" action="/resumes/activate/<?= $resume['id'] ?>" class="d-inline">
                                                <?= \Core\Csrf::field() ?>
                                                <button type="submit" class="btn btn-outline-success" title="Set as Active">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" action="/resumes/delete/<?= $resume['id'] ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this resume?');">
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
        </div>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
