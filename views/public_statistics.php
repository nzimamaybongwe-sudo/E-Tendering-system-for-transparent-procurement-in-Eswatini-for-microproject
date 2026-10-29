<div class="page-bg" style="background-image: url('assets/06.jpg');"></div>
<div class="row mb-4">
    <div class="col-12">
        <h2>Public Tender Statistics</h2>
        <p>Transparency metrics for MicroProjects procurement.</p>
    </div>
</div>
<style>
    .kpi-card { background: white; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); transition: 0.2s ease; }
    .kpi-icon { width: 48px; height: 48px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    .bg-soft-primary { background-color: #eff6ff; color: #2563eb; }
    .bg-soft-success { background-color: #f0fdf4; color: #16a34a; }
    .bg-soft-warning { background-color: #fefce8; color: #d97706; }
    .bg-soft-info { background-color: #f0f9ff; color: #0891b2; }
</style>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card kpi-card p-3">
            <div class="d-flex align-items-center">
                <div class="kpi-icon bg-soft-primary"><i class="bi bi-briefcase"></i></div>
                <div class="ms-3">
                    <div class="h4 fw-bold mb-0"><?= intval($stats['total_tenders']) ?></div>
                    <div class="small text-muted">Total Projects</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card kpi-card p-3">
            <div class="d-flex align-items-center">
                <div class="kpi-icon bg-soft-success"><i class="bi bi-check2-circle"></i></div>
                <div class="ms-3">
                    <div class="h4 fw-bold mb-0 text-success"><?= intval($stats['published_tenders']) ?></div>
                    <div class="small text-muted">Active Tenders</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card kpi-card p-3">
            <div class="d-flex align-items-center">
                <div class="kpi-icon bg-soft-info"><i class="bi bi-people"></i></div>
                <div class="ms-3">
                    <div class="h4 fw-bold mb-0"><?= intval($stats['contractor_count']) ?></div>
                    <div class="small text-muted">Contractors</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card kpi-card p-3">
            <div class="d-flex align-items-center">
                <div class="kpi-icon bg-soft-warning"><i class="bi bi-trophy"></i></div>
                <div class="ms-3">
                    <div class="h4 fw-bold mb-0"><?= intval($stats['awarded_projects']) ?></div>
                    <div class="small text-muted">Awarded Projects</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header">Category Breakdown</div>
            <div class="card-body">
                <?php if (empty($stats['category_breakdown'])): ?>
                    <p>No categories available yet.</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($stats['category_breakdown'] as $category): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($category['category']) ?>
                                <span class="badge bg-primary rounded-pill"><?= intval($category['tender_count']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
