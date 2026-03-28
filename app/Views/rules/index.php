<?php
$pageTitle = 'Proposal Rules';
require BASE_PATH . '/app/Views/layouts/header.php';
require BASE_PATH . '/app/Views/layouts/sidebar.php';

$categoryColors = [
    'always'       => ['bg' => '#198754', 'label' => 'Always Include'],
    'never'        => ['bg' => '#dc3545', 'label' => 'Never Include'],
    'tone'         => ['bg' => '#0d6efd', 'label' => 'Tone'],
    'skills'       => ['bg' => '#6f42c1', 'label' => 'Skills'],
    'availability' => ['bg' => '#fd7e14', 'label' => 'Availability'],
    'rate'         => ['bg' => '#0dcaf0', 'label' => 'Rate'],
    'custom'       => ['bg' => '#6c757d', 'label' => 'Custom'],
];

// Group rules by category
$grouped = [];
foreach ($rules as $rule) {
    $grouped[$rule['category']][] = $rule;
}
?>

<main class="main-content flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Proposal Rules</h1>
        <a href="/rules/create" class="btn btn-primary" style="background-color:#2c5f8a;border-color:#2c5f8a;">
            <i class="bi bi-plus-lg me-1"></i> Add Rule
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

    <?php if (empty($rules)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-list-check fs-1 d-block mb-3"></i>
            <p>No proposal rules yet. Add rules to guide AI-generated proposals.</p>
        </div>
    <?php else: ?>
        <?php foreach ($categoryColors as $catKey => $catInfo): ?>
            <?php if (!empty($grouped[$catKey])): ?>
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header text-white fw-semibold" style="background-color:<?= $catInfo['bg'] ?>;">
                        <?= $catInfo['label'] ?>
                        <span class="badge bg-white text-dark ms-2"><?= count($grouped[$catKey]) ?></span>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush sortable-list" data-category="<?= $catKey ?>">
                            <?php foreach ($grouped[$catKey] as $rule): ?>
                                <li class="list-group-item d-flex align-items-center justify-content-between py-3" data-id="<?= $rule['id'] ?>" draggable="true">
                                    <div class="d-flex align-items-center flex-grow-1">
                                        <i class="bi bi-grip-vertical text-muted me-3 drag-handle" style="cursor:grab;"></i>
                                        <span class="<?= $rule['is_active'] ? '' : 'text-muted text-decoration-line-through' ?>">
                                            <?= htmlspecialchars($rule['rule_text']) ?>
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 ms-3 flex-shrink-0">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input toggle-rule" type="checkbox"
                                                   data-id="<?= $rule['id'] ?>"
                                                   <?= $rule['is_active'] ? 'checked' : '' ?>
                                                   title="Toggle active">
                                        </div>
                                        <a href="/rules/<?= $rule['id'] ?>/edit" class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="/rules/<?= $rule['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this rule?');">
                                            <?= \Core\Csrf::field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = '<?= \Core\Csrf::token() ?>';

    // Toggle active/inactive
    document.querySelectorAll('.toggle-rule').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const ruleId = this.dataset.id;
            fetch('/rules/' + ruleId + '/toggle', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const textSpan = this.closest('li').querySelector('span');
                    if (data.is_active) {
                        textSpan.classList.remove('text-muted', 'text-decoration-line-through');
                    } else {
                        textSpan.classList.add('text-muted', 'text-decoration-line-through');
                    }
                }
            });
        });
    });

    // Drag-and-drop reorder via SortableJS
    document.querySelectorAll('.sortable-list').forEach(function(list) {
        new Sortable(list, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function() {
                const items = [];
                list.querySelectorAll('li').forEach(function(li, index) {
                    items.push({ id: parseInt(li.dataset.id), sort_order: index + 1 });
                });
                fetch('/rules/reorder', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ items: items })
                });
            }
        });
    });
});
</script>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
