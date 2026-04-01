<?php require BASE_PATH . '/app/Views/layouts/header.php'; ?>
<?php require BASE_PATH . '/app/Views/layouts/sidebar.php'; ?>

<div class="main-content flex-grow-1 p-4">
    <h2 class="mb-4"><i class="bi bi-graph-up me-2"></i>API Token Usage</h2>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-2">All Time</h6>
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="fs-4 fw-bold"><?= number_format($allTime['calls']) ?></div>
                            <small class="text-muted">API Calls</small>
                        </div>
                        <div class="text-end">
                            <div class="fs-4 fw-bold"><?= number_format($allTime['tokens']) ?></div>
                            <small class="text-muted">Tokens</small>
                        </div>
                        <div class="text-end">
                            <div class="fs-4 fw-bold text-primary">$<?= number_format($allTime['cost'], 2) ?></div>
                            <small class="text-muted">Est. Cost</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-2">Today</h6>
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="fs-4 fw-bold"><?= number_format($today['calls']) ?></div>
                            <small class="text-muted">Calls</small>
                        </div>
                        <div class="text-end">
                            <div class="fs-4 fw-bold"><?= number_format($today['tokens']) ?></div>
                            <small class="text-muted">Tokens</small>
                        </div>
                        <div class="text-end">
                            <div class="fs-4 fw-bold text-success">$<?= number_format($today['cost'], 4) ?></div>
                            <small class="text-muted">Cost</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-info">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-2">This Month</h6>
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="fs-4 fw-bold"><?= number_format($month['calls']) ?></div>
                            <small class="text-muted">Calls</small>
                        </div>
                        <div class="text-end">
                            <div class="fs-4 fw-bold"><?= number_format($month['tokens']) ?></div>
                            <small class="text-muted">Tokens</small>
                        </div>
                        <div class="text-end">
                            <div class="fs-4 fw-bold text-info">$<?= number_format($month['cost'], 4) ?></div>
                            <small class="text-muted">Cost</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Daily Usage (30 days) -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><strong><i class="bi bi-calendar3 me-1"></i> Daily Usage (Last 30 Days)</strong></div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height:400px; overflow-y:auto;">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr><th>Date</th><th class="text-end">Calls</th><th class="text-end">Tokens</th><th class="text-end">Cost</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($daily as $d): ?>
                                    <tr>
                                        <td><?= date('M j', strtotime($d['date'])) ?></td>
                                        <td class="text-end"><?= number_format($d['calls']) ?></td>
                                        <td class="text-end"><?= number_format($d['tokens']) ?></td>
                                        <td class="text-end">$<?= number_format($d['cost'], 4) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($daily)): ?>
                                    <tr><td colspan="4" class="text-center text-muted py-3">No data yet</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- By Feature -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><strong><i class="bi bi-puzzle me-1"></i> Usage by Feature</strong></div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Feature</th><th class="text-end">Calls</th><th class="text-end">Tokens</th><th class="text-end">Cost</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($byFeature as $f): ?>
                                <tr>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($f['feature']) ?></span></td>
                                    <td class="text-end"><?= number_format($f['calls']) ?></td>
                                    <td class="text-end"><?= number_format($f['tokens']) ?></td>
                                    <td class="text-end">$<?= number_format($f['cost'], 4) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($byFeature)): ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">No data yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- By Model -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><strong><i class="bi bi-cpu me-1"></i> Usage by Model</strong></div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Model</th><th class="text-end">Calls</th><th class="text-end">Tokens</th><th class="text-end">Cost</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($byModel as $m): ?>
                                <tr>
                                    <td><code class="small"><?= htmlspecialchars($m['model']) ?></code></td>
                                    <td class="text-end"><?= number_format($m['calls']) ?></td>
                                    <td class="text-end"><?= number_format($m['tokens']) ?></td>
                                    <td class="text-end">$<?= number_format($m['cost'], 4) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($byModel)): ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">No data yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- By User -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><strong><i class="bi bi-people me-1"></i> Usage by User (Top 20)</strong></div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>User</th><th class="text-end">Calls</th><th class="text-end">Tokens</th><th class="text-end">Cost</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($byUser as $u): ?>
                                <tr>
                                    <td><?= htmlspecialchars($u['name'] ?? $u['email'] ?? 'User #' . $u['user_id']) ?></td>
                                    <td class="text-end"><?= number_format($u['calls']) ?></td>
                                    <td class="text-end"><?= number_format($u['tokens']) ?></td>
                                    <td class="text-end">$<?= number_format($u['cost'], 4) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($byUser)): ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">No data yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Calls -->
    <div class="card">
        <div class="card-header"><strong><i class="bi bi-clock-history me-1"></i> Recent API Calls (Last 100)</strong></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Feature</th>
                            <th>Model</th>
                            <th class="text-end">In</th>
                            <th class="text-end">Out</th>
                            <th class="text-end">Cost</th>
                            <th class="text-end">Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent as $r): ?>
                            <tr>
                                <td class="small"><?= date('M j g:ia', strtotime($r['created_at'])) ?></td>
                                <td class="small"><?= htmlspecialchars($r['user_name'] ?? '—') ?></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($r['feature']) ?></span></td>
                                <td class="small"><code><?= htmlspecialchars(str_replace('claude-', '', $r['model'])) ?></code></td>
                                <td class="text-end small"><?= number_format($r['input_tokens']) ?></td>
                                <td class="text-end small"><?= number_format($r['output_tokens']) ?></td>
                                <td class="text-end small">$<?= number_format($r['estimated_cost_usd'], 4) ?></td>
                                <td class="text-end small"><?= number_format(($r['response_time_ms'] ?? 0) / 1000, 1) ?>s</td>
                                <td>
                                    <?php if ($r['success']): ?>
                                        <span class="badge bg-success">OK</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger" title="<?= htmlspecialchars($r['error_message'] ?? '') ?>">Fail</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recent)): ?>
                            <tr><td colspan="9" class="text-center text-muted py-3">No API calls logged yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
