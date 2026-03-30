<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>
<?php require BASE_PATH . '/app/Views/layouts/sidebar.php'; ?>

<div class="main-content flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Proposal for: <?= htmlspecialchars($proposal['job_title']) ?></h2>
            <span class="text-muted"><?= htmlspecialchars($proposal['platform_name']) ?> &bull; Version <?= $proposal['version'] ?> &bull; <?= date('M j, Y g:ia', strtotime($proposal['created_at'])) ?></span>
        </div>
        <div>
            <?php if (empty($proposal['is_submitted'])): ?>
                <a href="/proposals/edit/<?= $proposal['id'] ?>" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i> Edit</a>
            <?php endif; ?>
            <a href="/proposals/pdf/<?= $proposal['id'] ?>" class="btn btn-outline-primary" target="_blank"><i class="bi bi-file-pdf"></i> PDF</a>
            <a href="/proposals" class="btn btn-outline-dark"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
    </div>

    <?php if ($flash = $this->getFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($flash) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <!-- Proposal Content -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Proposal Text</strong>
                    <button class="btn btn-sm btn-outline-primary" onclick="copyProposal()" id="copyBtn"><i class="bi bi-clipboard"></i> Copy</button>
                </div>
                <div class="card-body">
                    <div id="proposalText" style="white-space:pre-wrap;line-height:1.7;"><?= htmlspecialchars($proposal['content']) ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Meta Info -->
            <div class="card mb-4">
                <div class="card-header"><strong>Details</strong></div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><td class="text-muted">Tone</td><td><span class="badge bg-info"><?= htmlspecialchars($proposal['tone'] ?? '—') ?></span></td></tr>
                        <tr>
                            <td class="text-muted">Suggested Rate</td>
                            <td>
                                <?php if ($proposal['suggested_rate']): ?>
                                    $<?= number_format($proposal['suggested_rate'], 2) ?>
                                    <?php if ($proposal['rate_type'] && $proposal['rate_type'] !== 'not_specified'): ?>
                                        <small class="text-muted">(<?= $proposal['rate_type'] ?>)</small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr><td class="text-muted">Model</td><td><small><?= htmlspecialchars($proposal['api_model'] ?? '—') ?></small></td></tr>
                        <tr><td class="text-muted">Tokens</td><td><?= number_format($proposal['api_tokens_used'] ?? 0) ?></td></tr>
                        <tr><td class="text-muted">Gen Time</td><td><?= number_format(($proposal['generation_time_ms'] ?? 0) / 1000, 1) ?>s</td></tr>
                    </table>
                </div>
            </div>

            <!-- Regenerate -->
            <div class="card mb-4">
                <div class="card-header"><strong>Regenerate</strong></div>
                <div class="card-body">
                    <form method="POST" action="/proposals/regenerate/<?= $proposal['id'] ?>" data-processing data-processing-title="Regenerating Proposal..." data-processing-message="Crafting a new version of your proposal. This may take 15-30 seconds.">
                        <?= \Core\Csrf::field() ?>
                        <div class="mb-3">
                            <label class="form-label">Tone</label>
                            <select name="tone" class="form-select">
                                <option value="auto">Auto-detect</option>
                                <option value="corporate">Corporate</option>
                                <option value="casual">Casual</option>
                                <option value="technical">Technical</option>
                                <option value="professional">Professional</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-arrow-repeat"></i> Regenerate</button>
                    </form>
                </div>
            </div>

            <!-- Job Link -->
            <div class="card">
                <div class="card-body">
                    <a href="/jobs/view/<?= $proposal['job_id'] ?>" class="btn btn-outline-secondary w-100"><i class="bi bi-briefcase"></i> View Job Posting</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyProposal() {
    const text = document.getElementById('proposalText').innerText;
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.getElementById('copyBtn');
        btn.innerHTML = '<i class="bi bi-check"></i> Copied!';
        setTimeout(() => { btn.innerHTML = '<i class="bi bi-clipboard"></i> Copy'; }, 2000);
    });
}
</script>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
