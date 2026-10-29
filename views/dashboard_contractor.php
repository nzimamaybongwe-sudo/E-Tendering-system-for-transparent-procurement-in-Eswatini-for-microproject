<div class="page-bg" style="background-image: url('assets/003.jpg');"></div>
<style>
    .contractor-dashboard {
        background: rgba(249, 250, 251, 0.8) !important;
        backdrop-filter: blur(8px);
        border-radius: 15px;
        padding-top: 10px;
        padding-bottom: 20px;
        min-height: 100vh;
    }

    .dashboard-header-contractor {
        background: white;
        border-bottom: 1px solid #e5e7eb;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .contractor-title {
        font-size: 2rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 0.5rem;
    }

    .contractor-subtitle {
        color: #6b7280;
        font-size: 0.95rem;
    }

    .status-badge-section {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        border-radius: 10px;
        padding: 1.25rem;
        margin-bottom: 2rem;
        border-left: 4px solid #2563eb;
    }

    .status-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        background: white;
        border-radius: 6px;
        font-weight: 600;
        margin-right: 0.75rem;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .status-badge.approved {
        background: #dcfce7;
        color: #059669;
    }

    .status-badge.rejected {
        background: #fee2e2;
        color: #dc2626;
    }

    .status-badge.pending {
        background: #fef3c7;
        color: #92400e;
    }

    .dashboard-card-contractor {
        background: white;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
    }

    .dashboard-card-contractor:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .card-header-contractor {
        background: white;
        border-bottom: 1px solid #f3f4f6;
        padding: 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-title-contractor {
        font-size: 1.1rem;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }

    .card-body-contractor {
        padding: 1.5rem;
    }

    .tender-item {
        padding: 1rem;
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.2s;
    }

    .tender-item:hover {
        background: #f9fafb;
    }

    .tender-item:last-child {
        border-bottom: none;
    }

    .tender-title-item {
        font-weight: 600;
        font-size: 1rem;
        color: #111827;
        margin-bottom: 0.5rem;
    }

    .tender-meta {
        font-size: 0.85rem;
        color: #6b7280;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .tender-meta-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.75rem;
        background: #f3f4f6;
        border-radius: 4px;
        font-size: 0.8rem;
    }

    .tender-actions {
        margin-top: 1rem;
        display: flex;
        gap: 0.75rem;
    }

    .bid-status-badge {
        display: inline-block;
        padding: 0.4rem 0.8rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .bid-status-locked {
        background: #f3e8ff;
        color: #7c3aed;
    }

    .bid-status-submitted {
        background: #dcfce7;
        color: #059669;
    }

    .compliance-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        background: #f9fafb;
        border-radius: 6px;
        margin-bottom: 0.75rem;
    }

    .compliance-check {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .compliance-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
    }

    .compliance-icon.valid {
        background: #dcfce7;
        color: #059669;
    }

    .compliance-icon.invalid {
        background: #fee2e2;
        color: #dc2626;
    }

    .compliance-text {
        flex: 1;
    }

    .compliance-label {
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.25rem;
    }

    .compliance-detail {
        font-size: 0.85rem;
        color: #6b7280;
    }

    .btn-contractor {
        border: none;
        border-radius: 6px;
        padding: 0.6rem 1.25rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary-contractor {
        background: #2563eb;
        color: white;
    }

    .btn-primary-contractor:hover {
        background: #1d4ed8;
    }

    .empty-state-contractor {
        text-align: center;
        padding: 3rem 1.5rem;
        color: #6b7280;
    }

    .empty-state-icon {
        font-size: 3rem;
        opacity: 0.3;
        margin-bottom: 1rem;
    }
</style>

<div class="contractor-dashboard">
    <!-- Alert Messages -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" style="margin: 1rem;">
            <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($_GET['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="margin: 1rem;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Dashboard Header -->
    <div class="dashboard-header-contractor">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h1 class="contractor-title">Contractor Dashboard</h1>
                <p class="contractor-subtitle">Welcome, <?= htmlspecialchars($_SESSION['user']['username']) ?></p>
            </div>
            <a href="index.php?route=contractor-profile&edit=1" class="btn btn-contractor btn-primary-contractor btn-sm">
                <i class="bi bi-pencil me-1"></i> Edit Profile
            </a>
        </div>
    </div>

    <!-- Status Banner -->
    <div class="container-fluid px-4">
        <div class="status-badge-section">
            <div style="margin-bottom: 0.75rem;">
                <small style="color: #1e40af; font-weight: 600;">YOUR VERIFICATION STATUS</small>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="status-badge <?= $contractor['verification_status'] === 'approved' ? 'approved' : ($contractor['verification_status'] === 'rejected' ? 'rejected' : 'pending') ?>">
                    <?= ucfirst(htmlspecialchars($contractor['verification_status'])) ?>
                </span>
                <?php if ($contractor['local_ownership']): ?>
                    <span class="status-badge" style="background: #dcfce7; color: #059669;">
                        <i class="bi bi-star-fill me-1"></i> Local SME
                    </span>
                <?php endif; ?>
                <?php if ($contractor['blacklisted']): ?>
                    <span class="status-badge" style="background: #fee2e2; color: #dc2626;">
                        <i class="bi bi-exclamation-circle me-1"></i> Blacklisted
                    </span>
                <?php endif; ?>
            </div>
            <?php if (!empty($contractor['rejection_reason']) && $contractor['verification_status'] === 'rejected'): ?>
                <div class="mt-3" style="color: #dc2626; font-size: 0.95rem;">
                    <strong>Rejection Reason:</strong> <?= htmlspecialchars($contractor['rejection_reason']) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Main Content Grid -->
        <div class="container-fluid px-4">
            <div class="row g-4">
                <!-- Available Tenders -->
                <div class="col-lg-8">
                    <div class="dashboard-card-contractor">
                        <div class="card-header-contractor">
                            <h5 class="card-title-contractor">
                                <i class="bi bi-document-earmark me-2"></i>Available Tenders
                            </h5>
                            <a href="index.php?route=tenders" class="btn btn-contractor btn-primary-contractor btn-sm">
                                View All
                            </a>
                        </div>
                        <div class="card-body-contractor">
                            <?php
                            // Only show tenders that are currently open for bidding (published) on this dashboard.
                            // Closed/evaluated tenders already submitted to will appear in My Bids below.
                            $openTenders = array_filter($tenders, fn($t) => $t['status'] === 'published');
                            $openingTenders = array_filter($tenders, fn($t) => in_array($t['status'], ['closed','evaluated','awarded']) && !empty($t['consensus_meeting_link']));
                            ?>

                            <?php if (!empty($openingTenders)): ?>
                                <?php foreach ($openingTenders as $ot): ?>
                                <div class="d-flex align-items-center gap-3 px-3 py-3 mb-2 rounded-3"
                                     style="background:linear-gradient(135deg,#0f172a,#1e3a5f);border:1px solid rgba(239,68,68,.4);">
                                    <span style="width:10px;height:10px;border-radius:50%;background:#ef4444;box-shadow:0 0 0 4px rgba(239,68,68,.3);flex-shrink:0;animation:livePulse 1.2s ease-in-out infinite;"></span>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-white" style="font-size:.9rem;"><?= htmlspecialchars($ot['title']) ?></div>
                                        <div style="color:rgba(255,255,255,.6);font-size:.75rem;">🔴 Opening session is live — envelopes are being unsealed now</div>
                                    </div>
                                    <a href="index.php?route=watch-live&id=<?= $ot['tender_id'] ?>" target="_blank"
                                       class="btn btn-sm fw-bold text-nowrap"
                                       style="background:linear-gradient(135deg,#ef4444,#f59e0b);color:#fff;border:none;border-radius:999px;padding:.4rem 1.2rem;">
                                        <i class="bi bi-broadcast me-1"></i> Watch Live
                                    </a>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if (empty($openTenders)): ?>
                                <div class="empty-state-contractor">
                                    <div class="empty-state-icon">📋</div>
                                    <p><strong>No open tenders at the moment</strong></p>
                                    <p style="font-size: 0.9rem;">New tenders will appear here when published. Check back soon!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($openTenders as $tender): ?>
                                    <div class="tender-item">
                                        <div class="tender-title-item">
                                            <?= htmlspecialchars($tender['title']) ?>
                                        </div>
                                        <div class="tender-meta">
                                            <div class="tender-meta-badge">
                                                <i class="bi bi-briefcase"></i> <?= htmlspecialchars($tender['required_grade']) ?>
                                            </div>
                                            <div class="tender-meta-badge">
                                                <i class="bi bi-calendar-event"></i> <?= date('d M Y', strtotime($tender['deadline'])) ?>
                                            </div>
                                        </div>
                                        <div class="tender-actions">
                                            <a href="index.php?route=tender-view&id=<?= $tender['tender_id'] ?>#bidForm" class="btn btn-contractor btn-primary-contractor btn-sm">
                                                <i class="bi bi-send-check me-1"></i> Bid Now
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div class="col-lg-4">
                    <!-- Compliance Status Card -->
                    <div class="dashboard-card-contractor">
                        <div class="card-header-contractor">
                            <h5 class="card-title-contractor">
                                <i class="bi bi-shield-check me-2"></i>Compliance Status
                            </h5>
                        </div>
                        <div class="card-body-contractor">
                            <?php
                                $taxCompliance = $compliance['phase2']['tax_clearance'] ?? ['valid' => false, 'uploaded_at' => null];
                                $enpfCompliance = $compliance['phase2']['snpf_enpf'] ?? ['valid' => false, 'uploaded_at' => null];
                            ?>
                            <div class="compliance-item">
                                <div class="compliance-check">
                                    <div class="compliance-icon <?= !empty($taxCompliance['uploaded_at']) ? 'valid' : 'invalid' ?>">
                                        <?= !empty($taxCompliance['uploaded_at']) ? '✓' : '!' ?>
                                    </div>
                                    <div class="compliance-text">
                                        <div class="compliance-label">Tax Certificate</div>
                                        <div class="compliance-detail">
                                            <?= !empty($taxCompliance['uploaded_at']) ? 'Uploaded' : 'Missing' ?>
                                        </div>
                                        <?php if (!empty($taxCompliance['uploaded_at'])): ?>
                                            <div class="compliance-detail" style="font-size: 0.75rem; margin-top: 0.25rem;">
                                                Last: <?= date('d M Y', strtotime($taxCompliance['uploaded_at'])) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="compliance-item">
                                <div class="compliance-check">
                                    <div class="compliance-icon <?= !empty($enpfCompliance['uploaded_at']) ? 'valid' : 'invalid' ?>">
                                        <?= !empty($enpfCompliance['uploaded_at']) ? '✓' : '!' ?>
                                    </div>
                                    <div class="compliance-text">
                                        <div class="compliance-label">SNPF/ENPF Certificate</div>
                                        <div class="compliance-detail">
                                            <?= !empty($enpfCompliance['uploaded_at']) ? 'Uploaded' : 'Missing' ?>
                                        </div>
                                        <?php if (!empty($enpfCompliance['uploaded_at'])): ?>
                                            <div class="compliance-detail" style="font-size: 0.75rem; margin-top: 0.25rem;">
                                                Last: <?= date('d M Y', strtotime($enpfCompliance['uploaded_at'])) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #f3f4f6;">
                                <button type="button" class="btn btn-contractor btn-primary-contractor btn-sm w-100" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                                    <i class="bi bi-cloud-upload me-1"></i> Upload Document
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Required Documents Card -->
                    <div class="dashboard-card-contractor" style="margin-top: 1.5rem;">
                        <div class="card-header-contractor">
                            <h5 class="card-title-contractor">
                                <i class="bi bi-file-earmark me-2"></i>Required Forms
                            </h5>
                        </div>
                        <div class="card-body-contractor">
                            <div style="padding: 0.75rem; background: #f9fafb; border-radius: 6px; margin-bottom: 0.75rem; cursor: pointer; transition: all 0.2s;" 
                                 data-bs-toggle="modal" data-bs-target="#companyFormsModal" 
                                 onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#f9fafb'">
                                <div style="font-weight: 600; color: #111827; font-size: 0.95rem;">
                                    Company Directors Profile
                                </div>
                                <div style="font-size: 0.85rem; color: #6b7280; margin-top: 0.25rem;">
                                    Form J & Form C
                                </div>
                                <div style="margin-top: 0.5rem;">
                                    <span class="bid-status-badge <?= ($compliance['phase1_valid'] ?? false) ? 'bid-status-submitted' : 'bid-status-locked' ?>">
                                        <?= ($compliance['phase1_valid'] ?? false) ? 'Ready' : 'Needs docs' ?>
                                    </span>
                                </div>
                            </div>

                            <div style="padding: 0.75rem; background: #f9fafb; border-radius: 6px; cursor: pointer; transition: all 0.2s;"
                                 data-bs-toggle="modal" data-bs-target="#declarationsModal"
                                 onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#f9fafb'">
                                <div style="font-weight: 600; color: #111827; font-size: 0.95rem;">
                                    General Declarations
                                </div>
                                <div style="font-size: 0.85rem; color: #6b7280; margin-top: 0.25rem;">
                                    Completed per bid submission
                                </div>
                                <div style="margin-top: 0.5rem;">
                                    <span class="bid-status-badge bid-status-submitted">Ongoing</span>
                                </div>
                            </div>

                            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #f3f4f6;">
                                <a href="index.php?route=tenders" class="btn btn-contractor btn-primary-contractor btn-sm w-100">
                                    <i class="bi bi-send-check me-1"></i> Submit Bids
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Bids Section -->
            <div class="dashboard-card-contractor" style="margin-top: 2rem;">
                <div class="card-header-contractor">
                    <h5 class="card-title-contractor">
                        <i class="bi bi-check2-circle me-2"></i>My Bids
                    </h5>
                    <a href="index.php?route=bids" class="btn btn-contractor btn-primary-contractor btn-sm">
                        View All
                    </a>
                </div>
                <div class="card-body-contractor">
                    <?php if (empty($myBids)): ?>
                        <div class="empty-state-contractor">
                            <div class="empty-state-icon">✓</div>
                            <p><strong>No bids submitted yet</strong></p>
                            <p style="font-size: 0.9rem;">Browse available tenders and submit your bids</p>
                            <a href="index.php?route=tenders" class="btn btn-contractor btn-primary-contractor btn-sm" style="margin-top: 1rem;">
                                <i class="bi bi-send-check me-1"></i> View Tenders
                            </a>
                        </div>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                                        <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #6b7280; font-size: 0.85rem;">Tender Title</th>
                                        <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #6b7280; font-size: 0.85rem;">Submitted</th>
                                        <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #6b7280; font-size: 0.85rem;">Status</th>
                                        <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #6b7280; font-size: 0.85rem;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($myBids as $bid): ?>
                                        <tr style="border-bottom: 1px solid #f3f4f6; transition: background 0.2s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">
                                            <td style="padding: 0.75rem;">
                                                <div style="font-weight: 500; color: #111827;">
                                                    <?= htmlspecialchars($bid['title']) ?>
                                                </div>
                                            </td>
                                            <td style="padding: 0.75rem; color: #6b7280; font-size: 0.9rem;">
                                                <?= date('d M Y', strtotime($bid['submitted_at'])) ?>
                                            </td>
                                            <td style="padding: 0.75rem;">
                                                <span class="bid-status-badge <?= $bid['locked'] ? 'bid-status-locked' : 'bid-status-submitted' ?>">
                                                    <?= $bid['locked'] ? 'Locked' : 'Submitted' ?>
                                                </span>
                                            </td>
                                            <td style="padding: 0.75rem;">
                                                <?php if (!empty($bid['consensus_meeting_link'])): ?>
                                                    <a href="index.php?route=watch-live&id=<?= $bid['tender_id'] ?>" target="_blank"
                                                       class="btn btn-sm fw-bold rounded-pill px-3"
                                                       style="background:linear-gradient(135deg,#ef4444,#f59e0b);color:#fff;border:none;">
                                                        <i class="bi bi-broadcast me-1"></i> Watch Live
                                                    </a>
                                                <?php elseif ($bid['status'] === 'evaluated' && !empty($bid['meeting_link'])): ?>
                                                    <a href="<?= htmlspecialchars($bid['meeting_link']) ?>" target="_blank" class="btn btn-sm btn-success rounded-pill px-3">
                                                        <i class="bi bi-camera-video me-1"></i> Join Meeting
                                                    </a>
                                                <?php else: ?>
                                                    <a href="index.php?route=bid-receipt&id=<?= $bid['bid_id'] ?>" style="color: #2563eb; text-decoration: none; font-weight: 500; font-size: 0.9rem;">
                                                        <i class="bi bi-file-earmark-text me-1"></i> View Receipt
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Company Standard Forms Modal -->
<div class="modal fade" id="companyFormsModal" tabindex="-1" aria-labelledby="companyFormsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="companyFormsModalLabel">Company Directors Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="small text-muted">Company Name</div>
                        <div class="fw-semibold"><?= htmlspecialchars($contractor['company_name']) ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">CIC Grade</div>
                        <div class="fw-semibold"><?= htmlspecialchars($contractor['cic_grade']) ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Tax PIN</div>
                        <div class="fw-semibold"><?= htmlspecialchars($contractor['tax_pin']) ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Form J Number</div>
                        <div class="fw-semibold"><?= htmlspecialchars($contractor['form_j_number']) ?></div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Required company form</th>
                                <th>Status</th>
                                <th>Last upload</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (($compliance['phase1_docs'] ?? []) as $docName => $docStatus): ?>
                                <tr>
                                    <td><?= htmlspecialchars($docName) ?></td>
                                    <td>
                                        <span class="badge <?= !empty($docStatus['uploaded_at']) ? 'bg-success' : 'bg-warning text-dark' ?>">
                                            <?= !empty($docStatus['uploaded_at']) ? 'Ready' : 'Missing' ?>
                                        </span>
                                    </td>
                                    <td><?= !empty($docStatus['uploaded_at']) ? date('d M Y', strtotime($docStatus['uploaded_at'])) : 'Not uploaded' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info small mb-0">
                    These company forms are reusable across tenders. Upload missing documents once, then they can support future bid submissions.
                </div>
            </div>
            <div class="modal-footer">
                <a href="index.php?route=contractor-profile&edit=1" class="btn btn-outline-primary">Edit Profile</a>
                <button type="button" class="btn btn-primary" data-bs-target="#uploadDocumentModal" data-bs-toggle="modal">
                    Upload Company Form
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Digital Declarations Modal -->
<div class="modal fade" id="declarationsModal" tabindex="-1" aria-labelledby="declarationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="declarationsModalLabel">General Declarations</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item px-0">
                        <h6 class="mb-1">Declaration of Eligibility</h6>
                        <p class="small text-muted mb-0">Confirmed inside each bid to state that the company is eligible and not debarred.</p>
                    </div>
                    <div class="list-group-item px-0">
                        <h6 class="mb-1">Conflict of Interest Declaration</h6>
                        <p class="small text-muted mb-0">Captured per tender because conflicts can change depending on the project and officials involved.</p>
                    </div>
                    <div class="list-group-item px-0">
                        <h6 class="mb-1">Site Visit Declaration</h6>
                        <p class="small text-muted mb-0">Captured per tender with visit date and MicroProjects officer met.</p>
                    </div>
                    <div class="list-group-item px-0">
                        <h6 class="mb-1">Form of Tender Acceptance</h6>
                        <p class="small text-muted mb-0">Digitally accepted at submission and sealed into the bid receipt with an integrity hash.</p>
                    </div>
                </div>
                <div class="alert alert-secondary small mt-3 mb-0">
                    These declarations are intentionally completed during bid submission so each tender has its own signed record.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">Print Summary</button>
                <a href="index.php?route=tenders" class="btn btn-primary">Open Tenders</a>
            </div>
        </div>
    </div>
</div>

<!-- Document Upload Modal -->
<div class="modal fade" id="uploadDocumentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Compliance Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="index.php?route=upload-document" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Document Type</label>
                        <select name="document_type" class="form-select" required>
                            <option value="">-- Select Document Type --</option>
                            <optgroup label="Phase 1 Documents">
                                <option value="Certificate of Incorporation">Certificate of Incorporation</option>
                                <option value="Form J & Form C">Form J & Form C</option>
                                <option value="CIC Registration Certificate">CIC Registration Certificate</option>
                                <option value="VAT Registration Certificate">VAT Registration Certificate</option>
                                <option value="Trading Licence">Trading Licence</option>
                            </optgroup>
                            <optgroup label="Phase 2 Documents">
                                <option value="Tax Clearance Certificate">Tax Clearance Certificate (Valid 30 days)</option>
                                <option value="SNPF/ENPF Compliance">SNPF/ENPF Compliance (Valid 90 days)</option>
                                <option value="Police Clearance">Police Clearance (Valid 90 days)</option>
                                <option value="Labour Compliance Certificate">Labour Compliance Certificate</option>
                            </optgroup>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select File(s)</label>
                        <input type="file" name="document[]" class="form-control" accept=".pdf,.jpg,.jpeg,.png" multiple required>
                        <div class="form-text">Accepted formats: PDF, JPG, PNG (Max 5MB). You can select multiple files if necessary (e.g. for Form J & Form C).</div>
                    </div>

                    <div class="alert alert-info small mb-0">
                        <strong>Note:</strong> After uploading, your document will be available for bidding. Some documents have expiry dates, so make sure they are current.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-cloud-upload me-2"></i>Upload Document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
