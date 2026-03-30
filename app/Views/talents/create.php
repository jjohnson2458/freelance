<?php
$activePage = $activePage ?? 'talents';
$pageTitle = $pageTitle ?? 'Add Talent';
require BASE_PATH . '/app/Views/layouts/header.php';
?>
<?php require BASE_PATH . '/app/Views/layouts/sidebar.php'; ?>

<div class="main-content flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add Talent</h2>
        <a href="/talents" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Talents
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
            <form method="POST" action="/talents/store">
                <?= \Core\Csrf::field() ?>

                <div class="mb-3">
                    <label for="name" class="form-label fw-medium">Talent / Skill Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" required
                           placeholder="e.g., PHP Development, UI/UX Design, Project Management" autofocus>
                </div>

                <div class="mb-3">
                    <label for="category" class="form-label fw-medium">Category</label>
                    <input type="text" class="form-control" id="category" name="category"
                           placeholder="e.g., Backend, Frontend, Design, DevOps, Soft Skills">
                    <div class="form-text">Group related talents together by category.</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="proficiency" class="form-label fw-medium">Proficiency Level</label>
                        <select class="form-select" id="proficiency" name="proficiency">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate" selected>Intermediate</option>
                            <option value="advanced">Advanced</option>
                            <option value="expert">Expert</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="years_experience" class="form-label fw-medium">Years of Experience</label>
                        <input type="number" class="form-control" id="years_experience" name="years_experience"
                               min="0" max="50" placeholder="e.g., 5">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label fw-medium">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4"
                              placeholder="Briefly describe your experience with this skill, notable projects, certifications, etc."></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Save Talent
                    </button>
                    <a href="/talents" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
