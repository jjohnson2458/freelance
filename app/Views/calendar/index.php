<?php
$pageTitle = 'Availability Calendar';
require BASE_PATH . '/app/Views/layouts/header.php';
require BASE_PATH . '/app/Views/layouts/sidebar.php';

// Build calendar data for current month
$year = (int) date('Y');
$month = (int) date('m');
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$firstDayOfWeek = (int) date('w', mktime(0, 0, 0, $month, 1, $year)); // 0=Sun
$monthName = date('F Y', mktime(0, 0, 0, $month, 1, $year));

// Index windows by date for quick lookup
$windowsByDate = [];
foreach ($windows as $w) {
    $from = $w['available_from'];
    $to = $w['available_to'] ?: $w['available_from'];
    $start = max(strtotime($from), mktime(0, 0, 0, $month, 1, $year));
    $end = min(strtotime($to), mktime(0, 0, 0, $month, $daysInMonth, $year));
    for ($d = $start; $d <= $end; $d += 86400) {
        $day = (int) date('j', $d);
        $windowsByDate[$day][] = $w;
    }
}
?>

<main class="main-content flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Availability Calendar</h1>
        <button class="btn btn-primary" style="background-color:#2c5f8a;border-color:#2c5f8a;" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-lg me-1"></i> Add Availability
        </button>
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

    <!-- Month Calendar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold text-center fs-5"><?= $monthName ?></div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0 text-center">
                <thead>
                    <tr class="table-light">
                        <th style="width:14.28%">Sun</th>
                        <th style="width:14.28%">Mon</th>
                        <th style="width:14.28%">Tue</th>
                        <th style="width:14.28%">Wed</th>
                        <th style="width:14.28%">Thu</th>
                        <th style="width:14.28%">Fri</th>
                        <th style="width:14.28%">Sat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $dayCounter = 1;
                    $today = (int) date('j');
                    $currentMonth = (int) date('m');
                    $currentYear = (int) date('Y');
                    for ($row = 0; $row < 6; $row++):
                        if ($dayCounter > $daysInMonth) break;
                    ?>
                    <tr>
                        <?php for ($col = 0; $col < 7; $col++): ?>
                            <?php if (($row === 0 && $col < $firstDayOfWeek) || $dayCounter > $daysInMonth): ?>
                                <td class="text-muted bg-light" style="height:70px;"></td>
                            <?php else: ?>
                                <?php
                                $isToday = ($dayCounter === $today && $month === $currentMonth && $year === $currentYear);
                                $hasWindow = !empty($windowsByDate[$dayCounter]);
                                ?>
                                <td style="height:70px;vertical-align:top;<?= $hasWindow ? 'background-color:#e8f4f8;' : '' ?>" class="<?= $isToday ? 'border-primary border-2' : '' ?>">
                                    <div class="fw-bold small <?= $isToday ? 'text-primary' : '' ?>"><?= $dayCounter ?></div>
                                    <?php if ($hasWindow): ?>
                                        <div class="mt-1">
                                            <span class="badge" style="background-color:#2c5f8a;font-size:0.65rem;">
                                                <?= $windowsByDate[$dayCounter][0]['hours_per_week'] ?>h/wk
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <?php $dayCounter++; ?>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Availability Windows List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-clock-history me-2"></i> Availability Windows
        </div>
        <div class="card-body p-0">
            <?php if (empty($windows)): ?>
                <div class="text-center py-4 text-muted">
                    <p class="mb-0">No availability windows set. Click "Add Availability" to get started.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>From</th>
                                <th>To</th>
                                <th>Hours/Week</th>
                                <th>Notes</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($windows as $w): ?>
                                <tr>
                                    <td><?= htmlspecialchars($w['available_from']) ?></td>
                                    <td><?= $w['available_to'] ? htmlspecialchars($w['available_to']) : '<span class="text-muted">Ongoing</span>' ?></td>
                                    <td><span class="badge" style="background-color:#2c5f8a;"><?= (int) $w['hours_per_week'] ?> hrs</span></td>
                                    <td><?= $w['notes'] ? htmlspecialchars($w['notes']) : '<span class="text-muted">-</span>' ?></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-secondary edit-window"
                                                data-bs-toggle="modal" data-bs-target="#editModal"
                                                data-id="<?= $w['id'] ?>"
                                                data-from="<?= htmlspecialchars($w['available_from']) ?>"
                                                data-to="<?= htmlspecialchars($w['available_to'] ?? '') ?>"
                                                data-hours="<?= (int) $w['hours_per_week'] ?>"
                                                data-notes="<?= htmlspecialchars($w['notes'] ?? '') ?>"
                                                title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="POST" action="/calendar/<?= $w['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this availability window?');">
                                            <?= \Core\Csrf::field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Add Availability Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/calendar">
                <?= \Core\Csrf::field() ?>
                <div class="modal-header" style="background-color:#2c5f8a;">
                    <h5 class="modal-title text-white" id="addModalLabel">Add Availability</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_from" class="form-label fw-semibold">From Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="add_from" name="available_from" required>
                    </div>
                    <div class="mb-3">
                        <label for="add_to" class="form-label fw-semibold">To Date</label>
                        <input type="date" class="form-control" id="add_to" name="available_to">
                        <div class="form-text">Leave blank for ongoing availability.</div>
                    </div>
                    <div class="mb-3">
                        <label for="add_hours" class="form-label fw-semibold">Hours Per Week <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="add_hours" name="hours_per_week" min="1" max="168" required>
                    </div>
                    <div class="mb-3">
                        <label for="add_notes" class="form-label fw-semibold">Notes</label>
                        <textarea class="form-control" id="add_notes" name="notes" rows="3" placeholder="e.g., Prefer mornings, no weekends"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background-color:#2c5f8a;border-color:#2c5f8a;">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Availability Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editForm" action="">
                <?= \Core\Csrf::field() ?>
                <div class="modal-header" style="background-color:#2c5f8a;">
                    <h5 class="modal-title text-white" id="editModalLabel">Edit Availability</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_from" class="form-label fw-semibold">From Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="edit_from" name="available_from" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_to" class="form-label fw-semibold">To Date</label>
                        <input type="date" class="form-control" id="edit_to" name="available_to">
                        <div class="form-text">Leave blank for ongoing availability.</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_hours" class="form-label fw-semibold">Hours Per Week <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="edit_hours" name="hours_per_week" min="1" max="168" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_notes" class="form-label fw-semibold">Notes</label>
                        <textarea class="form-control" id="edit_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background-color:#2c5f8a;border-color:#2c5f8a;">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Populate edit modal when clicking edit button
    document.querySelectorAll('.edit-window').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            document.getElementById('editForm').action = '/calendar/' + id + '/update';
            document.getElementById('edit_from').value = this.dataset.from;
            document.getElementById('edit_to').value = this.dataset.to;
            document.getElementById('edit_hours').value = this.dataset.hours;
            document.getElementById('edit_notes').value = this.dataset.notes;
        });
    });
});
</script>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
