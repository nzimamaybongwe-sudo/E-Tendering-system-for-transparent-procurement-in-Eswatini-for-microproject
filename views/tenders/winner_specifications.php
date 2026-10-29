<?php
/**
 * Winner Specifications & Kickoff View
 * Shown to the awarded contractor and admin after a tender is publicly awarded.
 */
$isContractor = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'contractor';
?>

<?php if ($submitted): ?>
<div class="alert alert-success border-0 shadow-sm mb-4 d-flex align-items-center rounded-4">
    <i class="bi bi-check-circle-fill text-success me-3 h4 mb-0"></i>
    <div>
        <strong>Acknowledgement submitted.</strong><br>
        <small class="text-muted">Your response has been recorded. The procurement office will be in contact with you shortly.</small>
    </div>
</div>
<?php endif; ?>

<!-- Header -->
<div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
    <div class="card-header bg-success text-white p-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-1">
                    <i class="bi bi-trophy-fill me-2"></i>
                    Award Notification &amp; Project Kickoff
                </h5>
                <small class="opacity-75">
                    Tender: <?= htmlspecialchars($specs['tender_title']) ?>
                </small>
            </div>
            <span class="badge bg-white text-success fw-bold">
                <?= strtoupper($specs['status']) ?>
            </span>
        </div>
    </div>
    <div class="card-body bg-light py-2 px-4">
        <small class="text-muted">
            <i class="bi bi-info-circle me-1"></i>
            <?php if ($isContractor): ?>
                Your bid has been selected as the winning bid for this tender. Please review the project specifications below carefully before acknowledging.
            <?php else: ?>
                Viewing the award specifications for this tender. The winning contractor has been notified.
            <?php endif; ?>
        </small>
    </div>
</div>

<div class="row g-4">

    <!-- Left: Specifications -->
    <div class="col-lg-8">

        <!-- Project Specifications -->
        <div class="card border-0 shadow-sm mb-4 rounded-4">
            <div class="card-header bg-dark text-white py-2 px-4 fw-bold small">
                <i class="bi bi-file-earmark-text me-1"></i> Project Specifications &amp; Expectations
            </div>
            <div class="card-body p-4">
                <?php if (!empty($specs['winner_notes'])): ?>
                    <div class="border-start border-4 border-success ps-3" style="white-space: pre-wrap; font-size: 0.95rem;">
                        <?= nl2br(htmlspecialchars($specs['winner_notes'])) ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center py-3">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        No specifications have been entered yet. The procurement office will update this shortly.
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tender Description -->
        <?php if (!empty($specs['tender_description'])): ?>
        <div class="card border-0 shadow-sm mb-4 rounded-4">
            <div class="card-header bg-light py-2 px-4 fw-bold small">
                <i class="bi bi-info-circle me-1"></i> Tender Background
            </div>
            <div class="card-body p-4 small text-muted">
                <?= nl2br(htmlspecialchars($specs['tender_description'])) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Contractor Acknowledgement Form -->
        <?php if ($isContractor && $specs['status'] === 'pending'): ?>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-primary text-white py-2 px-4 fw-bold small">
                <i class="bi bi-pencil-square me-1"></i> Contractor Acknowledgement
            </div>
            <div class="card-body p-4">
                <p class="small text-muted mb-3">
                    Please confirm that you have read and understood the project specifications above.
                    You may also add any questions or clarifications for the procurement office.
                </p>
                <form method="POST" action="index.php?route=api-submit-winner-ack">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="tender_id" value="<?= (int)$tenderId ?>">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Your Questions or Comments (optional)</label>
                        <textarea name="contractor_notes" class="form-control" rows="4"
                            placeholder="Write any questions, clarifications, or confirmations regarding the project specifications..."></textarea>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" name="acknowledged" value="1" id="ackCheck" class="form-check-input" required>
                        <label class="form-check-label small" for="ackCheck">
                            I confirm that I have read and understood the project specifications and expectations outlined above.
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        <i class="bi bi-check-circle me-1"></i> Submit Acknowledgement
                    </button>
                </form>
            </div>
        </div>

        <?php elseif ($isContractor && in_array($specs['status'], ['submitted', 'acknowledged'])): ?>
        <div class="alert alert-success border-0 rounded-4">
            <i class="bi bi-check-circle-fill me-2"></i>
            <strong>Acknowledgement Already Submitted.</strong>
            <div class="small text-muted mt-1">
                You have already acknowledged the project specifications. The procurement office will be in touch with next steps.
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Right: Summary Panel -->
    <div class="col-lg-4">

        <!-- Award Summary Card -->
        <div class="card border-0 shadow-sm mb-3 rounded-4 overflow-hidden">
            <div class="card-header bg-warning text-dark fw-bold small py-2">
                <i class="bi bi-trophy me-1"></i> Award Summary
            </div>
            <div class="card-body p-3 small">
                <table class="table table-sm small mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted">Tender</td>
                            <td class="fw-bold"><?= htmlspecialchars($specs['tender_title']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Bid Submitted</td>
                            <td><?= date('d M Y', strtotime($specs['bid_submitted_at'])) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Award Status</td>
                            <td>
                                <span class="badge bg-<?= $specs['status'] === 'pending' ? 'warning text-dark' : 'success' ?>">
                                    <?= ucfirst($specs['status']) ?>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Next Steps -->
        <div class="card border-0 shadow-sm mb-3 rounded-4">
            <div class="card-header bg-light fw-bold small py-2">
                <i class="bi bi-arrow-right-circle me-1"></i> What Happens Next
            </div>
            <div class="card-body p-3">
                <ol class="small text-muted ps-3 mb-0">
                    <li class="mb-2">Review the project specifications carefully.</li>
                    <li class="mb-2">Submit your acknowledgement using the form.</li>
                    <li class="mb-2">A contract will be drafted by the procurement office.</li>
                    <li class="mb-2">A contract signing meeting will be scheduled.</li>
                    <li>Project mobilisation and kickoff will begin as per the agreed timeline.</li>
                </ol>
            </div>
        </div>

        <!-- Navigation -->
        <div class="d-grid gap-2">
            <a href="index.php?route=tender-opening-room&id=<?= (int)$tenderId ?>"
               class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Opening Room
            </a>
            <?php if (!$isContractor): ?>
            <a href="index.php?route=admin-dashboard" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-house me-1"></i> Admin Dashboard
            </a>
            <?php else: ?>
            <a href="index.php?route=contractor-dashboard" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-house me-1"></i> My Dashboard
            </a>
            <?php endif; ?>
        </div>

    </div>
</div>
