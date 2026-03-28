<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>
<?php require BASE_PATH . '/app/Views/layouts/sidebar.php'; ?>

<div class="main-content flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Proposal</h2>
        <a href="/proposals/view/<?= $proposal['id'] ?>" class="btn btn-outline-dark"><i class="bi bi-arrow-left"></i> Back</a>
    </div>

    <div class="card">
        <div class="card-header"><strong><?= htmlspecialchars($proposal['job_title']) ?></strong></div>
        <div class="card-body">
            <form method="POST" action="/proposals/update/<?= $proposal['id'] ?>">
                <?= \Core\Csrf::field() ?>
                <div class="mb-3">
                    <label for="content" class="form-label">Proposal Text</label>
                    <textarea name="content" id="content" class="form-control" rows="20"><?= htmlspecialchars($proposal['content']) ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Changes</button>
                <a href="/proposals/view/<?= $proposal['id'] ?>" class="btn btn-outline-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
