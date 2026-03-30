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
            <?php if (empty($proposal['is_submitted'])): ?>
                <form method="POST" action="/proposals/submit/<?= $proposal['id'] ?>" class="d-inline" onsubmit="return confirm('Mark this proposal as sent?')">
                    <?= \Core\Csrf::field() ?>
                    <button type="submit" class="btn btn-success"><i class="bi bi-send"></i> Mark as Sent</button>
                </form>
            <?php else: ?>
                <span class="badge bg-success fs-6 align-middle"><i class="bi bi-check-circle me-1"></i> Sent <?= date('M j, Y g:i A', strtotime($proposal['submitted_at'])) ?></span>
            <?php endif; ?>
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
                    <button class="btn btn-sm btn-outline-light" onclick="copyProposal()" id="copyBtn"><i class="bi bi-clipboard"></i> Copy</button>
                </div>
                <div class="card-body">
                    <div id="proposalText" style="white-space:pre-wrap;line-height:1.7;"><?= htmlspecialchars($proposal['content']) ?></div>
                </div>
            </div>

            <!-- Fit Analysis -->
            <?php
                $fitScore = (int) ($proposal['fit_score'] ?? 0);
                $hasAnalysis = $fitScore > 0 || !empty($proposal['fit_notes']) || !empty($proposal['recommendation']);
            ?>
            <?php if ($hasAnalysis): ?>
                <?php
                    if ($fitScore >= 7) { $fitColor = 'success'; $fitIcon = 'check-circle-fill'; }
                    elseif ($fitScore >= 5) { $fitColor = 'warning'; $fitIcon = 'exclamation-triangle-fill'; }
                    else { $fitColor = 'danger'; $fitIcon = 'x-circle-fill'; }

                    $shouldPropose = isset($proposal['should_propose']) ? (bool) $proposal['should_propose'] : ($fitScore >= 5);
                ?>
                <div class="card mb-4 border-<?= $fitColor ?>">
                    <div class="card-header bg-<?= $fitColor ?> bg-opacity-10 d-flex justify-content-between align-items-center">
                        <strong><i class="bi bi-<?= $fitIcon ?> me-1 text-<?= $fitColor ?>"></i> Fit Analysis</strong>
                        <span class="badge bg-<?= $fitColor ?> fs-6"><?= $fitScore ?>/10</span>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($proposal['recommendation'])): ?>
                            <div class="alert alert-<?= $shouldPropose ? 'success' : 'warning' ?> mb-3">
                                <i class="bi bi-<?= $shouldPropose ? 'hand-thumbs-up-fill' : 'hand-thumbs-down-fill' ?> me-1"></i>
                                <strong><?= $shouldPropose ? 'Recommended to propose' : 'Consider skipping' ?>:</strong>
                                <?= htmlspecialchars($proposal['recommendation']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($proposal['fit_notes'])): ?>
                            <div class="mb-3">
                                <h6 class="text-muted mb-2">Why this score?</h6>
                                <p class="mb-0"><?= nl2br(htmlspecialchars($proposal['fit_notes'])) ?></p>
                            </div>
                        <?php endif; ?>

                        <?php
                            $skillGaps = [];
                            if (!empty($proposal['skill_gaps'])) {
                                $skillGaps = is_string($proposal['skill_gaps']) ? json_decode($proposal['skill_gaps'], true) : $proposal['skill_gaps'];
                            }
                        ?>
                        <?php if (!empty($skillGaps)): ?>
                            <div>
                                <h6 class="text-muted mb-2"><i class="bi bi-book me-1"></i>Skills to Learn</h6>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($skillGaps as $gap): ?>
                                        <div class="list-group-item px-0">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong><?= htmlspecialchars($gap['skill'] ?? '') ?></strong>
                                                    <?php if (!empty($gap['why'])): ?>
                                                        <p class="text-muted small mb-1"><?= htmlspecialchars($gap['why']) ?></p>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (!empty($gap['learn_url'])): ?>
                                                    <a href="<?= htmlspecialchars($gap['learn_url']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary flex-shrink-0 ms-2">
                                                        <i class="bi bi-box-arrow-up-right me-1"></i>Learn
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php
                $milestones = [];
                if (!empty($proposal['milestones'])) {
                    $milestones = is_string($proposal['milestones']) ? json_decode($proposal['milestones'], true) : $proposal['milestones'];
                }
            ?>
            <?php if (!empty($milestones)): ?>
                <div class="card mb-4 border-primary">
                    <div class="card-header bg-primary bg-opacity-10">
                        <strong><i class="bi bi-flag me-1 text-primary"></i> Suggested Milestones</strong>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:5%">#</th>
                                    <th style="width:25%">Milestone</th>
                                    <th>Deliverables</th>
                                    <th style="width:12%" class="text-end">Budget %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($milestones as $i => $ms): ?>
                                    <tr>
                                        <td class="text-muted"><?= $i + 1 ?></td>
                                        <td class="fw-medium"><?= htmlspecialchars($ms['name'] ?? '') ?></td>
                                        <td class="small"><?= htmlspecialchars($ms['description'] ?? '') ?></td>
                                        <td class="text-end">
                                            <span class="badge bg-primary"><?= (int) ($ms['percentage'] ?? 0) ?>%</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($proposal['is_submitted'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <strong><i class="bi bi-chat-dots me-1"></i> Proposal Feedback</strong>
                </div>
                <div class="card-body">
                    <?php if (!empty($proposal['feedback'])): ?>
                        <div class="mb-3">
                            <?php if (!empty($proposal['client_response'])): ?>
                                <?php
                                    $responseColors = ['won' => 'success', 'rejected' => 'danger', 'no_response' => 'secondary', 'interview' => 'info'];
                                    $responseLabels = ['won' => 'Won', 'rejected' => 'Rejected', 'no_response' => 'No Response', 'interview' => 'Interview'];
                                    $rColor = $responseColors[$proposal['client_response']] ?? 'secondary';
                                    $rLabel = $responseLabels[$proposal['client_response']] ?? $proposal['client_response'];
                                ?>
                                <span class="badge bg-<?= $rColor ?> mb-2"><?= $rLabel ?></span>
                            <?php endif; ?>
                            <p class="mb-1"><?= nl2br(htmlspecialchars($proposal['feedback'])) ?></p>
                            <?php if (!empty($proposal['feedback_at'])): ?>
                                <small class="text-muted"><?= date('M j, Y g:i A', strtotime($proposal['feedback_at'])) ?></small>
                            <?php endif; ?>
                        </div>
                        <hr>
                    <?php endif; ?>
                    <form method="POST" action="/proposals/feedback/<?= $proposal['id'] ?>">
                        <?= \Core\Csrf::field() ?>
                        <div class="mb-3">
                            <label for="client_response" class="form-label">Client Response</label>
                            <select name="client_response" id="client_response" class="form-select">
                                <option value="">Select...</option>
                                <option value="interview" <?= ($proposal['client_response'] ?? '') === 'interview' ? 'selected' : '' ?>>Interview / Follow-up</option>
                                <option value="won" <?= ($proposal['client_response'] ?? '') === 'won' ? 'selected' : '' ?>>Won the Job</option>
                                <option value="rejected" <?= ($proposal['client_response'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                <option value="no_response" <?= ($proposal['client_response'] ?? '') === 'no_response' ? 'selected' : '' ?>>No Response</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="feedback" class="form-label">Notes / Feedback</label>
                            <textarea name="feedback" id="feedback" class="form-control" rows="3" placeholder="What was the client's response? Any lessons learned?"><?= htmlspecialchars($proposal['feedback'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i> Save Feedback</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
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
