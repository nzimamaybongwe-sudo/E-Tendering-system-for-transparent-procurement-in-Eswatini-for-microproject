<div class="page-bg" style="background-image: url('assets/003.jpg');"></div>
<style>
    :root {
        --primary: #0b5ed7; /* Microproject blue */
        --primary-dark: #094a9b;
        --accent-yellow: #fbbf24; /* Microproject yellow */
        --success: #059669;
        --warning: var(--accent-yellow);
        --danger: #dc2626;
        --info: #0891b2;
        --gray-200: #e5e7eb;
        --gray-700: #374151;
        --gray-900: #111827;
    }

    .dashboard-wrapper {
        background: rgba(249, 250, 251, 0.82) !important;
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 30px;
        min-height: 100vh;
        border: 1px solid rgba(255,255,255,0.4);
    }

    .kpi-card {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
        border-left: 5px solid var(--gray-200);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .kpi-card:hover {
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .kpi-card.total { border-left-color: var(--primary); }
    .kpi-card.published { border-left-color: var(--info); }
    .kpi-card.closed { border-left-color: var(--warning); }
    .kpi-card.evaluated { border-left-color: var(--success); }
    .kpi-card.awarded { border-left-color: #9333ea; } /* Purple */

    .kpi-num {
        font-size: 2.2rem;
        font-weight: 800;
        color: var(--gray-900);
        line-height: 1.1;
    }
</style>

<div class="dashboard-wrapper shadow-sm mt-2">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h2 class="fw-bold mb-1 text-dark"><i class="bi bi-briefcase-fill text-primary me-2"></i>Procurement Officer Dashboard</h2>
            <p class="text-muted mb-0 small">Welcome back, <strong><?= htmlspecialchars($_SESSION['user']['username'] ?? 'Officer') ?></strong> | MicroProjects Eswatini</p>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php?route=publish-tender" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold"><i class="bi bi-plus-circle me-1"></i>Create Tender</a>
            <a href="index.php?route=manage-site-visits" class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-semibold"><i class="bi bi-geo-alt me-1"></i>Manage Site Visits</a>
        </div>
    </div>

    <!-- KPIs Grid -->
    <div class="row g-3 mb-4">
        <div class="col-md-2.4 col-sm-6">
            <div class="kpi-card total">
                <div>
                    <div class="small fw-bold text-muted text-uppercase">Total Tenders</div>
                    <div class="kpi-num mt-1"><?= intval($totalCount) ?></div>
                </div>
                <i class="bi bi-journal-text text-primary h1 opacity-50 mb-0"></i>
            </div>
        </div>
        <div class="col-md-2.4 col-sm-6">
            <div class="kpi-card published">
                <div>
                    <div class="small fw-bold text-muted text-uppercase">Published</div>
                    <div class="kpi-num mt-1"><?= intval($publishedCount) ?></div>
                </div>
                <i class="bi bi-cloud-arrow-up text-info h1 opacity-50 mb-0"></i>
            </div>
        </div>
        <div class="col-md-2.4 col-sm-6">
            <div class="kpi-card closed">
                <div>
                    <div class="small fw-bold text-muted text-uppercase">Closed / In Opening</div>
                    <div class="kpi-num mt-1"><?= intval($closedCount) ?></div>
                </div>
                <i class="bi bi-clock-history text-warning h1 opacity-50 mb-0"></i>
            </div>
        </div>
        <div class="col-md-2.4 col-sm-6">
            <div class="kpi-card evaluated">
                <div>
                    <div class="small fw-bold text-muted text-uppercase">Evaluated</div>
                    <div class="kpi-num mt-1"><?= intval($evaluatedCount) ?></div>
                </div>
                <i class="bi bi-check2-square text-success h1 opacity-50 mb-0"></i>
            </div>
        </div>
        <div class="col-md-2.4 col-sm-6">
            <div class="kpi-card awarded">
                <div>
                    <div class="small fw-bold text-muted text-uppercase">Awarded</div>
                    <div class="kpi-num mt-1"><?= intval($awardedCount) ?></div>
                </div>
                <i class="bi bi-trophy text-purple h1 opacity-50 mb-0" style="color:#9333ea;"></i>
            </div>
        </div>
    </div>

    <?php $actionTenders = array_merge($closedTenders ?? [], $evaluatedTenders ?? []); ?>
    <?php if (!empty($actionTenders)): ?>
    <!-- Tenders Needing Officer Action -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-dark text-white fw-bold py-3 d-flex align-items-center justify-content-between">
            <span><i class="bi bi-exclamation-circle-fill text-warning me-2"></i>Tenders Needing Your Action</span>
            <span class="badge bg-warning text-dark"><?= count($actionTenders) ?></span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Tender</th>
                            <th>Status</th>
                            <th>Deadline</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($actionTenders as $at):
                            $isClosed    = $at['status'] === 'closed';
                            $isEvaluated = $at['status'] === 'evaluated';
                            $statusColor = $isClosed ? 'warning' : 'success';
                            $statusLabel = $isClosed ? 'Closed – Opening' : 'Evaluated';
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($at['title']) ?></div>
                                <div class="text-muted" style="font-size:.75rem;">Ref #MP-<?= str_pad($at['tender_id'], 5, '0', STR_PAD_LEFT) ?></div>
                            </td>
                            <td>
                                <span class="badge bg-<?= $statusColor ?> text-dark"><?= $statusLabel ?></span>
                            </td>
                            <td class="text-muted"><?= date('d M Y', strtotime($at['deadline'])) ?></td>
                            <td class="text-end pe-4">
                                <div class="d-flex gap-2 justify-content-end">
                                    <?php if ($isClosed): ?>
                                        <?php if (!empty($at['consensus_meeting_link'])): ?>
                                            <a href="index.php?route=watch-live&id=<?= $at['tender_id'] ?>"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-danger fw-semibold">
                                                <i class="bi bi-broadcast me-1"></i>Watch Live
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark px-3 py-2">
                                                <i class="bi bi-clock me-1"></i>Awaiting Admin to Open
                                            </span>
                                        <?php endif; ?>
                                    <?php elseif ($isEvaluated): ?>
                                        <a href="index.php?route=consensus-meeting&id=<?= $at['tender_id'] ?>"
                                           class="btn btn-sm btn-primary fw-semibold">
                                            <i class="bi bi-people me-1"></i>Consensus Meeting
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content Row -->
    <div class="row g-4">
        <!-- Left Column: Site Visits & Submissions -->

        <div class="col-lg-8">
            <!-- Tenders site visits needing verification -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-dark text-white fw-bold py-3"><i class="bi bi-geo-alt-fill me-2 text-warning"></i>Recent Bidders Site Visits</div>
                <div class="card-body p-0">
                    <?php if (empty($siteVisits)): ?>
                        <div class="text-center py-5 text-muted small"><i class="bi bi-geo h1 opacity-25"></i><br>No site visits declared by contractors yet.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0 align-middle">
                                <thead class="table-light small">
                                    <tr>
                                        <th class="ps-3">Company Name</th>
                                        <th>Tender Title</th>
                                        <th>Site Visit Date</th>
                                        <th>Officer Met</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="small">
                                    <?php foreach ($siteVisits as $sv): ?>
                                        <tr>
                                            <td class="ps-3 fw-bold"><?= htmlspecialchars($sv['company_name']) ?></td>
                                            <td><?= htmlspecialchars(strlen($sv['tender_title']) > 35 ? substr($sv['tender_title'],0,35).'...' : $sv['tender_title']) ?></td>
                                            <td><?= date('d M Y', strtotime($sv['site_visit_date'])) ?></td>
                                            <td><?= htmlspecialchars($sv['site_visit_officer']) ?></td>
                                            <td class="text-center">
                                                <a href="index.php?route=manage-site-visits" class="btn btn-outline-secondary btn-xs py-0 px-2 rounded-pill"><i class="bi bi-eye"></i> View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Bid Submissions -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-primary text-white fw-bold py-3"><i class="bi bi-file-earmark-check-fill me-2"></i>Recent Bid Submissions</div>
                <div class="card-body p-0">
                    <?php if (empty($recentBids)): ?>
                        <div class="text-center py-5 text-muted small"><i class="bi bi-mailbox h1 opacity-25"></i><br>No bids submitted recently.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0 align-middle">
                                <thead class="table-light small">
                                    <tr>
                                        <th class="ps-3">Company Name</th>
                                        <th>Tender</th>
                                        <th>Submitted At</th>
                                        <th class="text-end pe-3">Bid Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="small">
                                    <?php foreach ($recentBids as $bid): ?>
                                        <tr>
                                            <td class="ps-3 fw-bold"><?= htmlspecialchars($bid['company_name']) ?></td>
                                            <td><?= htmlspecialchars($bid['title']) ?></td>
                                            <td><?= date('d M Y, H:i', strtotime($bid['submitted_at'])) ?></td>
                                            <td class="text-end pe-3 fw-bold text-success">E<?= number_format((float)$bid['decrypted_amount'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Shortcuts & Quick stats -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-dark text-white fw-bold py-3"><i class="bi bi-gear-fill me-2 text-primary"></i>Procurement Actions</div>
                <div class="card-body p-3">
                    <div class="d-grid gap-2">
                        <a href="index.php?route=publish-tender" class="btn btn-outline-primary text-start px-3 py-2 small"><i class="bi bi-plus-lg me-2"></i>Create New Tender</a>
                        <a href="index.php?route=review-rubric-proposals" class="btn btn-outline-success text-start px-3 py-2 small"><i class="bi bi-file-earmark-ruled me-2"></i>Review Rubric Proposals</a>
                        <a href="index.php?route=tenders" class="btn btn-outline-dark text-start px-3 py-2 small"><i class="bi bi-view-list me-2"></i>List All Public Tenders</a>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-secondary text-white fw-bold py-3"><i class="bi bi-info-circle-fill me-2"></i>Procurement Guidelines</div>
                <div class="card-body p-3 small text-muted">
                    <p class="mb-2"><strong>Tender Lifecycle:</strong></p>
                    <ol class="ps-3 mb-3">
                        <li class="mb-1">Create and publish the tender detailing budget, categories, and required grades.</li>
                        <li class="mb-1">Once closed, open envelopes in the public opening room. Verify files for compliance.</li>
                        <li class="mb-1">Allow evaluators to score the bids and recommend a winner in a consensus meeting.</li>
                        <li class="mb-0">Publish the official award and notify stakeholders.</li>
                    </ol>
                    <div class="alert alert-info py-2 mb-0 small">
                        <i class="bi bi-shield-lock-fill me-1"></i> Bid values remain fully encrypted in the database until envelopes are opened on the board.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
