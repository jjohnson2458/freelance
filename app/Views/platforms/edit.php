<?php
$activePage = $activePage ?? 'platforms';
$pageTitle = $pageTitle ?? 'Edit Platform';
require BASE_PATH . '/app/Views/layouts/header.php';
?>
<?php require BASE_PATH . '/app/Views/layouts/sidebar.php'; ?>

<div class="main-content flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Platform</h2>
        <a href="/platforms" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Platforms
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="/platforms/<?= $platform['id'] ?>/update">
                <?= \Core\Csrf::field() ?>

                <div class="mb-3">
                    <label for="name" class="form-label fw-medium">Name</label>
                    <input type="text" class="form-control" id="name" name="name"
                           value="<?= htmlspecialchars($platform['name']) ?>" readonly>
                    <div class="form-text">Platform name cannot be changed.</div>
                </div>

                <div class="mb-3">
                    <label for="base_url" class="form-label fw-medium">Base URL</label>
                    <input type="url" class="form-control" id="base_url" name="base_url"
                           value="<?= htmlspecialchars($platform['base_url'] ?? '') ?>"
                           placeholder="https://www.example.com">
                </div>

                <div class="mb-3">
                    <label for="alert_email_from" class="form-label fw-medium">Alert Email From</label>
                    <input type="text" class="form-control" id="alert_email_from" name="alert_email_from"
                           value="<?= htmlspecialchars($platform['alert_email_from'] ?? '') ?>"
                           placeholder="alerts@platform.com">
                    <div class="form-text">Email address that job alert emails come from (used for parsing).</div>
                </div>

                <div class="mb-3">
                    <label for="parser_class" class="form-label fw-medium">Parser Class</label>
                    <input type="text" class="form-control" id="parser_class" name="parser_class"
                           value="<?= htmlspecialchars($platform['parser_class'] ?? '') ?>"
                           placeholder="App\Parsers\ExampleParser">
                    <div class="form-text">Fully qualified class name of the email parser for this platform.</div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label fw-medium">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="4"
                              placeholder="Any notes about this platform..."><?= htmlspecialchars($platform['notes'] ?? '') ?></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Update Platform
                    </button>
                    <a href="/platforms" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
