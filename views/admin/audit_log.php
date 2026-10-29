<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="fw-bold mb-1">Audit Log</h1>
            <p class="text-muted mb-0">Immutable record of all system actions.</p>
        </div>
        <a href="index.php?route=admin-dashboard" class="btn btn-outline-secondary btn-sm">← Dashboard</a>
    </div>
</div>

<!-- Filters -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="index.php" class="row g-2 align-items-end">
            <input type="hidden" name="route" value="audit-log">
            <div class="col-md-5">
                <label class="form-label fw-semibold">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search username, table, or details…" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Action Type</label>
                <select name="action" class="form-select">
                    <option value="">All Actions</option>
                    <?php
                    $audit = new AuditModel($pdo);
                    foreach ($audit->getDistinctActions() as $act): ?>
                        <option value="<?= htmlspecialchars($act) ?>" <?= $filterAction === $act ? 'selected' : '' ?>><?= htmlspecialchars($act) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">Filter</button>
                <a href="index.php?route=audit-log" class="btn btn-outline-secondary flex-fill">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Logs Table -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($logs)): ?>
            <div class="p-4 text-muted">No audit log entries found.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Table</th>
                            <th>Record ID</th>
                            <th>Details</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="text-muted small"><?= $log['audit_id'] ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($log['username']) ?></span></td>
                                <td><code class="text-primary small"><?= htmlspecialchars($log['action_type']) ?></code></td>
                                <td class="small"><?= htmlspecialchars($log['target_table']) ?></td>
                                <td class="small"><?= intval($log['target_id']) ?></td>
                                <td class="small text-muted"><?= htmlspecialchars(mb_strimwidth($log['details'], 0, 80, '…')) ?></td>
                                <td class="small text-nowrap"><?= date('d M Y H:i', strtotime($log['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
    <nav class="mt-3">
        <ul class="pagination pagination-sm justify-content-center">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                    <a class="page-link" href="index.php?route=audit-log&page=<?= $p ?>&search=<?= urlencode($search) ?>&action=<?= urlencode($filterAction) ?>"><?= $p ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>
