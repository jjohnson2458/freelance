<?php
$pageTitle = 'Add Rule';
require BASE_PATH . '/app/Views/layouts/header.php';
require BASE_PATH . '/app/Views/layouts/sidebar.php';

$categoryLabels = [
    'always'       => 'Always Include',
    'never'        => 'Never Include',
    'tone'         => 'Tone',
    'skills'       => 'Skills',
    'availability' => 'Availability',
    'rate'         => 'Rate',
    'custom'       => 'Custom',
];
?>

<main class="main-content flex-grow-1 p-4">
    <div class="d-flex align-items-center mb-4">
        <a href="/rules" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
        <h1 class="h3 mb-0">Add Proposal Rule</h1>
    </div>

    <div class="card border-0 shadow-sm" style="max-width:600px;">
        <div class="card-body">
            <form method="POST" action="/rules">
                <?= \Core\Csrf::field() ?>

                <div class="mb-3">
                    <label for="category" class="form-label fw-semibold">Category</label>
                    <select class="form-select" id="category" name="category" required>
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat ?>"><?= $categoryLabels[$cat] ?? ucfirst($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="rule_text" class="form-label fw-semibold">Rule Text</label>
                    <textarea class="form-control" id="rule_text" name="rule_text" rows="4" required
                              placeholder="e.g., Always mention my 5 years of React experience"></textarea>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary" style="background-color:#2c5f8a;border-color:#2c5f8a;">
                        <i class="bi bi-check-lg me-1"></i> Save Rule
                    </button>
                    <a href="/rules" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
