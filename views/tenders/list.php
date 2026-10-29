<div class="page-bg" style="background-image: url('assets/003.png');"></div>
<div class="row mb-4">
    <div class="col-12">
        <h1 class="fw-bold">Available Tenders</h1>
        <p class="text-muted">Browse open tender opportunities for MicroProjects in Eswatini.</p>
    </div>
</div>

<!-- Filters -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="index.php" class="row g-2 align-items-end">
            <input type="hidden" name="route" value="tenders">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search by title or description..." value="<?= htmlspecialchars($filterSearch) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Category</label>
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= $filterCategory === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Region</label>
                <select name="region" class="form-select">
                    <option value="">All Regions</option>
                    <?php foreach ($regions as $reg): ?>
                        <option value="<?= htmlspecialchars($reg) ?>" <?= $filterRegion === $reg ? 'selected' : '' ?>><?= htmlspecialchars($reg) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">CIC Grade</label>
                <input type="text" name="grade" class="form-control" placeholder="e.g. C3" value="<?= htmlspecialchars($filterGrade) ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">Filter</button>
                <a href="index.php?route=tenders" class="btn btn-outline-secondary flex-fill">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Results -->
<?php if (empty($tenders)): ?>
    <div class="alert alert-info">No tenders match your search. <a href="index.php?route=tenders">View all tenders</a>.</div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
        <?php foreach ($tenders as $tender): ?>
            <?php
                $deadlineTs   = strtotime($tender['deadline']);
                $daysLeft     = ceil(($deadlineTs - time()) / 86400);
                $urgencyClass = $daysLeft <= 3 ? 'danger' : ($daysLeft <= 7 ? 'warning' : 'success');
                $isClosed = $tender['status'] === 'closed';
            ?>
            <div class="col">
                <div class="card h-100 shadow-sm border-0 tender-card <?= $isClosed ? 'opacity-50' : '' ?>" style="<?= $isClosed ? 'position: relative;' : '' ?>">
                    <?php if ($isClosed): ?>
                        <div style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; padding: 4px 12px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; z-index: 10;">
                            CLOSED
                        </div>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge bg-primary"><?= htmlspecialchars($tender['category']) ?></span>
                                <span class="badge bg-secondary"><?= htmlspecialchars($tender['region']) ?></span>
                            </div>
                            <span class="badge bg-<?= $urgencyClass ?>">
                                <?= $daysLeft > 0 ? $daysLeft . 'd left' : 'Closing today' ?>
                            </span>
                        </div>
                        <h5 class="card-title fw-bold"><?= htmlspecialchars($tender['title']) ?></h5>
                        <p class="card-text text-muted small flex-grow-1">
                            <?= htmlspecialchars(mb_strimwidth($tender['description'], 0, 120, '…')) ?>
                        </p>
                        <div class="row g-2 text-sm mt-2 mb-3">
                            <div class="col-6">
                                <div class="text-muted small">Budget</div>
                                <div class="fw-semibold">E <?= number_format($tender['budget'], 2) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">CIC Grade</div>
                                <div class="fw-semibold"><?= htmlspecialchars($tender['required_grade']) ?></div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small">Deadline</div>
                                <div class="fw-semibold"><?= date('d M Y, H:i', $deadlineTs) ?></div>
                            </div>
                        </div>
                        <?php
                            $isOpeningStage = in_array($tender['status'], ['closed','evaluated','awarded']);
                            $hasMeetingLink = !empty($tender['consensus_meeting_link']);
                        ?>
                        <a href="index.php?route=tender-view&id=<?= $tender['tender_id'] ?><?= !$isClosed ? '#bidForm' : '' ?>" class="btn <?= $isClosed ? 'btn-outline-primary' : 'btn-primary' ?> btn-sm mt-auto">
                            <?= $isClosed ? 'View Details' : 'Bid Now' ?>
                        </a>
                        <?php if ($isOpeningStage): ?>
                        <a href="index.php?route=watch-live&id=<?= $tender['tender_id'] ?>" class="btn btn-sm btn-outline-danger mt-2">
                            <i class="bi bi-broadcast me-1"></i>Watch Live Opening
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="text-muted mt-3 small"><?= count($tenders) ?> tender(s) found.</div>
<?php endif; ?>

<style>
.tender-card { transition: transform .15s, box-shadow .15s; }
.tender-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.12) !important; }
.x-small { font-size: 0.7rem; }
.dropdown-menu .active { background-color: var(--bs-primary); }
</style>
