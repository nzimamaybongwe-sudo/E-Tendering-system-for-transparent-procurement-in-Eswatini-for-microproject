<?php
/**
 * Digital Envelope Opening Board
 * Accessible to: admin, evaluator (actions), contractor (view-only)
 */
$isActionRole = in_array($role, ['admin', 'evaluator']);
$isAdmin      = $role === 'admin';

// Build anonymous code helper
$anonCode = function(array $bid): string {
    $seed = ($bid['tender_id'] ?? '') . '|' . ($bid['bid_id'] ?? '') . '|' . ($bid['digital_signature'] ?? '');
    return 'BID-' . strtoupper(substr(hash('sha256', $seed), 0, 10));
};

// Pre-index evaluations by bid_id for fast lookup
$evalByBid = [];
foreach ($evaluations as $ev) {
    $evalByBid[$ev['bid_id']] = $ev;
}
?>

<?php if (isset($_GET['awarded'])): ?>
<div class="alert alert-success border-0 shadow-sm mb-3 d-flex align-items-center">
    <i class="bi bi-trophy-fill text-warning me-3 h4 mb-0"></i>
    <div>
        <strong>Tender Awarded Successfully.</strong><br>
        <small>The winning contractor has been notified. They can view project specifications on their dashboard.</small>
    </div>
</div>
<?php endif; ?>

<!-- ── Header ──────────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
    <div class="card-header bg-dark text-white p-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-1">
                    <i class="bi bi-envelope-open me-2"></i>Bid Opening Board
                </h5>
                <small class="opacity-75">
                    <?= htmlspecialchars($tender['title']) ?> &mdash; Ref #MP-<?= str_pad($tender['tender_id'], 5, '0', STR_PAD_LEFT) ?>
                    &nbsp;|&nbsp; Deadline: <?= date('d M Y, H:i', strtotime($tender['deadline'])) ?>
                </small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <?php if (!empty($tender['consensus_meeting_link'])): ?>
                    <a href="<?= htmlspecialchars($tender['consensus_meeting_link']) ?>" target="_blank" class="btn btn-warning btn-sm fw-bold">
                        <i class="bi bi-camera-video me-1"></i> Join Opening Meeting
                    </a>
                <?php endif; ?>
                <span class="badge bg-<?= $tender['status'] === 'awarded' ? 'success' : 'primary' ?> text-uppercase">
                    <?= htmlspecialchars($tender['status']) ?>
                </span>
            </div>
        </div>
    </div>
    <div class="card-body bg-light py-2 px-4">
        <small class="text-muted">
            <i class="bi bi-info-circle me-1"></i>
            <?php if ($isActionRole): ?>
                Open each bid envelope in sequence, verify all documents against the checklist, then submit evaluation scores. All actions are permanently recorded in the audit log.
            <?php else: ?>
                You are viewing this opening session as a bidder. Envelopes are opened one by one. All actions are transparent and recorded.
            <?php endif; ?>
        </small>
    </div>
</div>

<?php if (!empty($tender['consensus_meeting_link'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (!sessionStorage.getItem('meeting_popup_shown_<?= $tender['tender_id'] ?>')) {
        let conf = confirm("The video meeting for this tender opening has started. Join now?");
        if (conf) {
            window.open("<?= htmlspecialchars($tender['consensus_meeting_link']) ?>", "_blank");
        }
        sessionStorage.setItem('meeting_popup_shown_<?= $tender['tender_id'] ?>', '1');
    }
});
</script>
<?php endif; ?>

<?php if ($isAdmin): ?>
<div class="card border-0 shadow-sm mb-4 rounded-4 bg-white">
    <div class="card-body p-3">
        <form method="POST" action="index.php?route=api-set-meeting-link" class="d-flex align-items-center gap-2">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
            <input type="hidden" name="tender_id" value="<?= $tender['tender_id'] ?>">
            <i class="bi bi-camera-video text-muted h4 mb-0"></i>
            <a href="https://meet.google.com/new" target="_blank" class="btn btn-outline-secondary btn-sm text-nowrap" title="Generate Google Meet Link">
                <i class="bi bi-plus-circle me-1"></i>New Meet
            </a>
            <input type="url" name="consensus_meeting_link" class="form-control form-control-sm" placeholder="Paste video meeting link here to start the session..." value="<?= htmlspecialchars($tender['consensus_meeting_link'] ?? '') ?>" required>
            <button type="submit" class="btn btn-primary btn-sm text-nowrap">Broadcast Link</button>
        </form>
    </div>
</div>

<?php if (!in_array($tender['status'], ['awarded']) && empty($tender['evaluation_release_at']) && !empty($bids)): ?>
<div class="card border-0 shadow-sm mb-4 rounded-4 border-2" style="border-color: #198754 !important; background: #f0faf4;">
    <div class="card-body p-4 d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-send-check-fill text-success" style="font-size:2rem;"></i>
            <div>
                <div class="fw-bold text-success" style="font-size:1.05rem;">All documents reviewed? Release for Evaluation</div>
                <div class="text-muted small">Once satisfied with all bids, release them to the approved evaluators. This action cannot be undone.</div>
            </div>
        </div>
        <form method="POST" action="index.php?route=release-tender-evaluation" onsubmit="return confirm('Are you satisfied with all documents? This will release all bids to the approved evaluators for scoring.');" class="mb-0">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
            <input type="hidden" name="tender_id" value="<?= $tender['tender_id'] ?>">
            <button type="submit" class="btn btn-success btn-lg fw-bold shadow-sm">
                <i class="bi bi-send-check me-2"></i>Release for Evaluation
            </button>
        </form>
    </div>
</div>
<?php elseif (!empty($tender['evaluation_release_at'])): ?>
<div class="alert alert-success border-0 shadow-sm mb-4 rounded-4 d-flex align-items-center gap-3">
    <i class="bi bi-check-circle-fill text-success h4 mb-0"></i>
    <div>
        <strong>Released for Evaluation</strong><br>
        <small class="text-muted">Evaluators were notified on <?= date('d M Y H:i', strtotime($tender['evaluation_release_at'])) ?>.</small>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- ── Award notice (already awarded) ───────────────────────────── -->
<?php if ($tender['status'] === 'awarded' && !empty($awardSpecs)): ?>
<div class="alert alert-success border-0 shadow-sm mb-4 d-flex align-items-center rounded-4">
    <i class="bi bi-trophy-fill text-warning me-3 h4 mb-0"></i>
    <div>
        <strong>This tender has been officially awarded.</strong><br>
        <small class="text-muted">The winning contractor has been notified with project specifications.</small>
    </div>
</div>
<?php endif; ?>

<?php 
$firstSealedIdx = -1;
if (!empty($bids)) {
    foreach ($bids as $idx => $b) {
        if ($b['envelope_status'] === 'sealed') {
            $firstSealedIdx = $idx;
            break;
        }
    }
}
?>

<!-- ── Main layout ───────────────────────────────────────────────── -->
<div class="row g-4">

    <!-- Left: Bid Envelopes -->
    <div class="col-lg-8">

        <?php if (empty($bids)): ?>
            <div class="card border-0 shadow-sm text-center py-5 rounded-4">
                <i class="bi bi-folder-x text-muted" style="font-size:3rem;"></i>
                <p class="text-muted mt-3 mb-0">No bids have been submitted for this tender.</p>
            </div>
        <?php else: ?>
            <?php foreach ($bids as $bidIdx => $bid):
                $sealed   = $bid['envelope_status'] === 'sealed';
                $opened   = $bid['envelope_status'] === 'opened';
                $verified = $bid['envelope_status'] === 'verified';
                $rejected = $bid['envelope_status'] === 'rejected';
                $scored   = $bid['envelope_status'] === 'scored';
                $bidEval  = $evalByBid[$bid['bid_id']] ?? null;

                $headerBg = match($bid['envelope_status']) {
                    'sealed'   => 'bg-secondary',
                    'opened'   => 'bg-primary',
                    'verified' => 'bg-success',
                    'rejected' => 'bg-danger',
                    'scored'   => 'bg-info',
                    default    => 'bg-secondary',
                };
                $statusIcon = match($bid['envelope_status']) {
                    'sealed'   => 'bi-envelope-fill',
                    'opened'   => 'bi-envelope-open-fill',
                    'verified' => 'bi-check-circle-fill',
                    'rejected' => 'bi-x-circle-fill',
                    'scored'   => 'bi-star-fill',
                    default    => 'bi-envelope-fill',
                };

                $allDocsPass = true;
                foreach ($bid['doc_checklist'] as $dc) {
                    if (!$dc['valid']) { $allDocsPass = false; break; }
                }
                $hasTenderSecurity = !empty($bid['tender_security']);

                $code = $anonCode($bid);
            ?>
            <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">

                <!-- Envelope header -->
                <div class="card-header <?= $headerBg ?> text-white d-flex justify-content-between align-items-center py-3">
                    <div>
                        <i class="bi <?= $statusIcon ?> me-2"></i>
                        <strong>Envelope #<?= $bidIdx + 1 ?>:</strong> <?= htmlspecialchars($code) ?>
                        <?php if (!$sealed): ?>
                            <span class="ms-2 opacity-75 small">&mdash; <?= htmlspecialchars($bid['company_name']) ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="badge bg-white text-dark text-uppercase small">
                        <?= str_replace('_', ' ', $bid['envelope_status']) ?>
                    </span>
                </div>

                <div class="card-body p-4">

                    <!-- ===== SEALED STATE ===== -->
                    <?php if ($sealed): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-lock-fill text-secondary" style="font-size:2.5rem;"></i>
                            <p class="text-muted mt-2 mb-1">This envelope is sealed.</p>
                            <small class="text-muted">Submitted: <?= date('d M Y, H:i', strtotime($bid['submitted_at'])) ?></small>
                            <?php if ($isActionRole && $bidIdx === $firstSealedIdx): ?>
                                <?php if (empty($tender['consensus_meeting_link'])): ?>
                                    <div class="alert alert-warning mt-3 mb-0 small py-2">
                                        <i class="bi bi-exclamation-triangle me-1"></i> Start the video meeting first to unseal.
                                    </div>
                                <?php else: ?>
                                <div class="mt-3">
                                    <form method="POST" action="index.php?route=api-break-seal">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <input type="hidden" name="bid_id" value="<?= $bid['bid_id'] ?>">
                                        <input type="hidden" name="tender_id" value="<?= $tender['tender_id'] ?>">
                                        <button type="submit" class="btn btn-dark btn-sm"
                                            onclick="return confirm('Open this envelope? This action is permanent and recorded in the audit log.')">
                                            <i class="bi bi-envelope-open me-1"></i> Open Envelope
                                        </button>
                                    </form>
                                </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="small text-muted mt-2">Waiting for the admin to open this envelope.</p>
                            <?php endif; ?>
                        </div>

                    <!-- ===== OPENED / VERIFIED / REJECTED / SCORED ===== -->
                    <?php else: ?>

                        <!-- 1. Digital Signature Integrity Check -->
                        <div class="card mb-3 rounded-3 border-<?= $bid['signature_valid'] ? 'success' : 'danger' ?>">
                            <div class="card-body py-2 px-3 d-flex align-items-center">
                                <i class="bi bi-shield-<?= $bid['signature_valid'] ? 'check-fill text-success' : 'x-fill text-danger' ?> me-3 h5 mb-0"></i>
                                <div>
                                    <span class="fw-bold small">
                                        Digital Signature: <?= $bid['signature_valid'] ? 'VERIFIED' : 'INVALID' ?>
                                    </span>
                                    <div class="text-muted" style="font-size:0.76rem;">
                                        <?php if ($bid['signature_valid']): ?>
                                            SHA-256 HMAC matches. Bid is untampered since submission on <?= date('d M Y H:i:s', strtotime($bid['submitted_at'])) ?>.
                                        <?php else: ?>
                                            Warning: Signature mismatch detected. This bid may have been modified after submission.
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Bid Amount Row -->
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <div class="p-3 bg-light rounded-3 text-center">
                                    <div class="small text-muted">Bid Amount</div>
                                    <div class="fw-bold h6 mb-0">E<?= number_format((float)$bid['decrypted_amount'], 2) ?></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 bg-light rounded-3 text-center">
                                    <div class="small text-muted">Tender Budget</div>
                                    <div class="fw-bold h6 mb-0">E<?= number_format((float)$tender['budget'], 2) ?></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <?php
                                    $variance = $tender['budget'] > 0
                                        ? (($bid['decrypted_amount'] - $tender['budget']) / $tender['budget']) * 100
                                        : 0;
                                    $varColor = abs($variance) > 20 ? 'danger' : 'success';
                                ?>
                                <div class="p-3 bg-light rounded-3 text-center">
                                    <div class="small text-muted">Variance</div>
                                    <div class="fw-bold h6 mb-0 text-<?= $varColor ?>">
                                        <?= ($variance > 0 ? '+' : '') . number_format($variance, 1) ?>%
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Document Checklist -->
                        <div class="card border rounded-3 mb-3">
                            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center small fw-bold">
                                <span><i class="bi bi-list-check me-1"></i> Document Checklist</span>
                                <span class="badge bg-<?= ($allDocsPass && $hasTenderSecurity) ? 'success' : 'danger' ?>">
                                    <?= ($allDocsPass && $hasTenderSecurity) ? 'All Documents Valid' : 'Issues Found' ?>
                                </span>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm small mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Document</th>
                                            <th class="text-center" style="width:100px;">Status</th>
                                            <th class="text-center" style="width:120px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bid['doc_checklist'] as $dc): ?>
                                        <tr>
                                            <td class="ps-3">
                                                <?= htmlspecialchars($dc['name']) ?>
                                                <?php if (!empty($dc['is_fraudulent'])): ?>
                                                    <span class="badge bg-danger small ms-1" title="Flagged as Fraudulent"><i class="bi bi-shield-exclamation"></i> Fraudulent</span>
                                                <?php endif; ?>
                                                <?php if (!empty($dc['is_incorrect'])): ?>
                                                    <span class="badge bg-warning text-dark small ms-1" title="Flagged as Incorrect"><i class="bi bi-exclamation-triangle"></i> Incorrect</span>
                                                <?php endif; ?>
                                                <?php if (!empty($dc['flag_reason'])): ?>
                                                    <div class="text-danger small mt-1" style="font-size:0.75rem;">Reason: <?= htmlspecialchars($dc['flag_reason']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <i class="bi bi-<?= ($dc['valid'] && empty($dc['is_fraudulent']) && empty($dc['is_incorrect'])) ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' ?>"></i>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!empty($dc['document_id'])): ?>
                                                    <div class="d-flex flex-column gap-1 align-items-center">
                                                        <a href="index.php?route=download-contractor-doc&id=<?= $dc['document_id'] ?>" target="_blank" class="btn btn-outline-primary btn-xs py-0 px-2 fw-semibold mb-1" style="font-size:0.75rem;">
                                                            <i class="bi bi-eye me-1"></i>View
                                                        </a>
                                                        <?php if ($role === 'officer'): ?>
                                                            <button type="button" class="btn btn-xs btn-outline-danger py-0 px-1" onclick="openFlagModal(<?= intval($dc['document_id']) ?>, 'contractor', '<?= htmlspecialchars($dc['name'], ENT_QUOTES) ?>', <?= intval($dc['is_fraudulent']) ?>, <?= intval($dc['is_incorrect']) ?>, '<?= htmlspecialchars($dc['flag_reason'] ?? '', ENT_QUOTES) ?>')" style="font-size:0.7rem;">
                                                                <i class="bi bi-flag"></i> Flag
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted small">Not Uploaded</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <tr>
                                            <td class="ps-3">
                                                Tender Security (Bank/Insurance Bond)
                                                <?php if ($hasTenderSecurity && !empty($bid['tender_security']['is_fraudulent'])): ?>
                                                    <span class="badge bg-danger small ms-1"><i class="bi bi-shield-exclamation"></i> Fraudulent</span>
                                                <?php endif; ?>
                                                <?php if ($hasTenderSecurity && !empty($bid['tender_security']['is_incorrect'])): ?>
                                                    <span class="badge bg-warning text-dark small ms-1"><i class="bi bi-exclamation-triangle"></i> Incorrect</span>
                                                <?php endif; ?>
                                                <?php if ($hasTenderSecurity && !empty($bid['tender_security']['flag_reason'])): ?>
                                                    <div class="text-danger small mt-1" style="font-size:0.75rem;">Reason: <?= htmlspecialchars($bid['tender_security']['flag_reason']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <i class="bi bi-<?= ($hasTenderSecurity && empty($bid['tender_security']['is_fraudulent']) && empty($bid['tender_security']['is_incorrect'])) ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' ?>"></i>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($hasTenderSecurity && !empty($bid['tender_security']['document_id'])): ?>
                                                    <div class="d-flex flex-column gap-1 align-items-center">
                                                        <a href="index.php?route=download-bid-doc&id=<?= $bid['tender_security']['document_id'] ?>" target="_blank" class="btn btn-outline-primary btn-xs py-0 px-2 fw-semibold mb-1" style="font-size:0.75rem;">
                                                            <i class="bi bi-eye me-1"></i>View
                                                        </a>
                                                        <?php if ($role === 'officer'): ?>
                                                            <button type="button" class="btn btn-xs btn-outline-danger py-0 px-1" onclick="openFlagModal(<?= intval($bid['tender_security']['document_id']) ?>, 'bid', 'Tender Security', <?= intval($bid['tender_security']['is_fraudulent'] ?? 0) ?>, <?= intval($bid['tender_security']['is_incorrect'] ?? 0) ?>, '<?= htmlspecialchars($bid['tender_security']['flag_reason'] ?? '', ENT_QUOTES) ?>')" style="font-size:0.7rem;">
                                                                <i class="bi bi-flag"></i> Flag
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted small">Not Uploaded</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php $hasProofOfFunds = !empty($bid['proof_of_funds']); ?>
                                        <tr>
                                            <td class="ps-3">
                                                Proof of Funds / Bank Rating
                                                <?php if ($hasProofOfFunds && !empty($bid['proof_of_funds']['is_fraudulent'])): ?>
                                                    <span class="badge bg-danger small ms-1"><i class="bi bi-shield-exclamation"></i> Fraudulent</span>
                                                <?php endif; ?>
                                                <?php if ($hasProofOfFunds && !empty($bid['proof_of_funds']['is_incorrect'])): ?>
                                                    <span class="badge bg-warning text-dark small ms-1"><i class="bi bi-exclamation-triangle"></i> Incorrect</span>
                                                <?php endif; ?>
                                                <?php if ($hasProofOfFunds && !empty($bid['proof_of_funds']['flag_reason'])): ?>
                                                    <div class="text-danger small mt-1" style="font-size:0.75rem;">Reason: <?= htmlspecialchars($bid['proof_of_funds']['flag_reason']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <i class="bi bi-<?= ($hasProofOfFunds && empty($bid['proof_of_funds']['is_fraudulent']) && empty($bid['proof_of_funds']['is_incorrect'])) ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' ?>"></i>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($hasProofOfFunds && !empty($bid['proof_of_funds']['document_id'])): ?>
                                                    <div class="d-flex flex-column gap-1 align-items-center">
                                                        <a href="index.php?route=download-bid-doc&id=<?= $bid['proof_of_funds']['document_id'] ?>" target="_blank" class="btn btn-outline-primary btn-xs py-0 px-2 fw-semibold mb-1" style="font-size:0.75rem;">
                                                            <i class="bi bi-eye me-1"></i>View
                                                        </a>
                                                        <?php if ($role === 'officer'): ?>
                                                            <button type="button" class="btn btn-xs btn-outline-danger py-0 px-1" onclick="openFlagModal(<?= intval($bid['proof_of_funds']['document_id']) ?>, 'bid', 'Proof of Funds', <?= intval($bid['proof_of_funds']['is_fraudulent'] ?? 0) ?>, <?= intval($bid['proof_of_funds']['is_incorrect'] ?? 0) ?>, '<?= htmlspecialchars($bid['proof_of_funds']['flag_reason'] ?? '', ENT_QUOTES) ?>')" style="font-size:0.7rem;">
                                                                <i class="bi bi-flag"></i> Flag
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted small">Not Uploaded</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php $hasTenderFeeReceipt = !empty($bid['tender_fee_receipt']); ?>
                                        <tr>
                                            <td class="ps-3">
                                                Proof of Payment (Tender Fee)
                                                <?php if ($hasTenderFeeReceipt && !empty($bid['tender_fee_receipt']['is_fraudulent'])): ?>
                                                    <span class="badge bg-danger small ms-1"><i class="bi bi-shield-exclamation"></i> Fraudulent</span>
                                                <?php endif; ?>
                                                <?php if ($hasTenderFeeReceipt && !empty($bid['tender_fee_receipt']['is_incorrect'])): ?>
                                                    <span class="badge bg-warning text-dark small ms-1"><i class="bi bi-exclamation-triangle"></i> Incorrect</span>
                                                <?php endif; ?>
                                                <?php if ($hasTenderFeeReceipt && !empty($bid['tender_fee_receipt']['flag_reason'])): ?>
                                                    <div class="text-danger small mt-1" style="font-size:0.75rem;">Reason: <?= htmlspecialchars($bid['tender_fee_receipt']['flag_reason']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <i class="bi bi-<?= ($hasTenderFeeReceipt && empty($bid['tender_fee_receipt']['is_fraudulent']) && empty($bid['tender_fee_receipt']['is_incorrect'])) ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' ?>"></i>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($hasTenderFeeReceipt && !empty($bid['tender_fee_receipt']['document_id'])): ?>
                                                    <div class="d-flex flex-column gap-1 align-items-center">
                                                        <a href="index.php?route=download-bid-doc&id=<?= $bid['tender_fee_receipt']['document_id'] ?>" target="_blank" class="btn btn-outline-primary btn-xs py-0 px-2 fw-semibold mb-1" style="font-size:0.75rem;">
                                                            <i class="bi bi-eye me-1"></i>View
                                                        </a>
                                                        <?php if ($role === 'officer'): ?>
                                                            <button type="button" class="btn btn-xs btn-outline-danger py-0 px-1" onclick="openFlagModal(<?= intval($bid['tender_fee_receipt']['document_id']) ?>, 'bid', 'Proof of Payment (Tender Fee)', <?= intval($bid['tender_fee_receipt']['is_fraudulent'] ?? 0) ?>, <?= intval($bid['tender_fee_receipt']['is_incorrect'] ?? 0) ?>, '<?= htmlspecialchars($bid['tender_fee_receipt']['flag_reason'] ?? '', ENT_QUOTES) ?>')" style="font-size:0.7rem;">
                                                                <i class="bi bi-flag"></i> Flag
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted small">Not Uploaded</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- 4. Strategic Plan -->
                        <div class="card border rounded-3 mb-3">
                            <div class="card-header bg-light py-2 small fw-bold">
                                <i class="bi bi-file-text me-1"></i> Strategic Plan &amp; Bidding Application
                            </div>
                            <div class="card-body p-3 small">
                                <div class="border-start border-3 border-primary ps-3 text-muted" style="white-space:pre-wrap;">
                                    <?= nl2br(htmlspecialchars($bid['method_statement'] ?? 'No strategic plan submitted.')) ?>
                                </div>
                            </div>
                        </div>

                        <!-- 5. Declarations & Site Visit -->
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <div class="card border rounded-3 h-100">
                                    <div class="card-body p-3 small">
                                        <h6 class="fw-bold mb-2 small"><i class="bi bi-shield-check me-1"></i> Declarations</h6>
                                        <div class="<?= $bid['eligibility_confirmed'] ? 'text-success' : 'text-danger' ?>">
                                            <i class="bi bi-<?= $bid['eligibility_confirmed'] ? 'check' : 'x' ?>-circle me-1"></i>
                                            Eligibility: <?= $bid['eligibility_confirmed'] ? 'Confirmed' : 'Not Confirmed' ?>
                                        </div>
                                        <div class="<?= $bid['form_of_tender_accepted'] ? 'text-success' : 'text-danger' ?>">
                                            <i class="bi bi-<?= $bid['form_of_tender_accepted'] ? 'check' : 'x' ?>-circle me-1"></i>
                                            Form of Tender: <?= $bid['form_of_tender_accepted'] ? 'Accepted' : 'Not Accepted' ?>
                                        </div>
                                        <?php if ($bid['conflict_declared']): ?>
                                            <div class="text-warning mt-1">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                COI Declared: <?= htmlspecialchars($bid['conflict_details'] ?? '') ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border rounded-3 h-100">
                                    <div class="card-body p-3 small">
                                        <h6 class="fw-bold mb-2 small"><i class="bi bi-geo-alt me-1"></i> Site Visit</h6>
                                        <div class="text-muted">
                                            Date: <?= $bid['site_visit_date'] ? date('d M Y', strtotime($bid['site_visit_date'])) : 'N/A' ?><br>
                                            Officer: <?= htmlspecialchars($bid['site_visit_officer'] ?? 'N/A') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 6. Portfolio Media -->
                        <?php if (!empty($bid['bid_media']) || !empty($bid['portfolio'])): ?>
                        <div class="card border rounded-3 mb-3">
                            <div class="card-header bg-light py-2 small fw-bold">
                                <i class="bi bi-images me-1"></i> Portfolio Proof
                            </div>
                            <div class="card-body p-3">
                                <?php if (!empty($bid['bid_media'])): ?>
                                    <p class="small fw-bold text-muted mb-2">Attached to this Bid:</p>
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <?php foreach ($bid['bid_media'] as $bm): ?>
                                            <?php if (strpos($bm['file_type'], 'image/') === 0): ?>
                                                <a href="<?= htmlspecialchars($bm['file_path']) ?>" target="_blank"
                                                   class="border rounded-3 overflow-hidden d-inline-block" style="width:80px;height:80px;">
                                                    <img src="<?= htmlspecialchars($bm['file_path']) ?>" alt="Bid portfolio"
                                                         style="width:100%;height:100%;object-fit:cover;">
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= htmlspecialchars($bm['file_path']) ?>" target="_blank"
                                                   class="btn btn-outline-secondary btn-sm">
                                                    <i class="bi bi-camera-video me-1"></i> Video Proof
                                                </a>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($bid['portfolio'])): ?>
                                    <p class="small fw-bold text-muted mb-2">Company Past Projects:</p>
                                    <?php foreach ($bid['portfolio'] as $proj): ?>
                                        <div class="mb-2 p-2 bg-light rounded-3 small">
                                            <strong><?= htmlspecialchars($proj['project_name']) ?></strong>
                                            &mdash; <?= htmlspecialchars($proj['client_name']) ?>
                                            (E<?= number_format($proj['project_value'], 2) ?>, <?= $proj['completion_year'] ?>)
                                            <?php if (!empty($proj['description'])): ?>
                                                <div class="text-muted x-small mt-1"><?= htmlspecialchars($proj['description']) ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($proj['media'])): ?>
                                                <div class="d-flex flex-wrap gap-1 mt-1">
                                                    <?php foreach ($proj['media'] as $pm): ?>
                                                        <?php if (strpos($pm['file_type'], 'image/') === 0): ?>
                                                            <a href="<?= htmlspecialchars($pm['file_path']) ?>" target="_blank"
                                                               class="border rounded-2 overflow-hidden d-inline-block" style="width:40px;height:40px;">
                                                                <img src="<?= htmlspecialchars($pm['file_path']) ?>" alt=""
                                                                     style="width:100%;height:100%;object-fit:cover;">
                                                            </a>
                                                        <?php elseif (strpos($pm['file_type'], 'video/') === 0): ?>
                                                            <a href="<?= htmlspecialchars($pm['file_path']) ?>" target="_blank" class="badge bg-secondary">Video</a>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- 7. Legitimacy Review (evaluator/officer when envelope is just opened) -->
                        <?php if (in_array($role, ['evaluator', 'officer']) && $opened): ?>
                            <div class="card border-warning rounded-3 mb-3">
                                <div class="card-header bg-warning text-dark py-2 fw-bold small">
                                    <i class="bi bi-clipboard-check me-1"></i> Legitimacy Review
                                </div>
                                <div class="card-body p-3">
                                    <p class="small text-muted mb-3">
                                        Review the document checklist and declarations above. Confirm whether this bid qualifies to proceed to scoring.
                                    </p>
                                    <form method="POST" action="index.php?route=api-verify-legitimacy">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <input type="hidden" name="bid_id" value="<?= $bid['bid_id'] ?>">
                                        <input type="hidden" name="tender_id" value="<?= $tender['tender_id'] ?>">
                                        <div class="mb-2">
                                            <label class="form-label small fw-bold">Notes</label>
                                            <textarea name="legitimacy_notes" class="form-control form-control-sm" rows="2"
                                                placeholder="Record any observations, issues, or confirmation details..."></textarea>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="submit" name="legitimacy_status" value="legit"
                                                class="btn btn-success btn-sm flex-fill">
                                                <i class="bi bi-check-circle me-1"></i> Pass — Documents Verified
                                            </button>
                                            <button type="submit" name="legitimacy_status" value="invalid"
                                                class="btn btn-danger btn-sm flex-fill"
                                                onclick="return confirm('Reject this bid? It will be excluded from scoring.')">
                                                <i class="bi bi-x-circle me-1"></i> Fail — Reject Bid
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        <?php elseif ($verified): ?>
                            <div class="alert alert-success py-2 small mb-3 rounded-3">
                                <i class="bi bi-check-circle-fill me-1"></i>
                                <strong>Documents Verified &amp; Accepted.</strong>
                                <?php if (!empty($bid['legitimacy_notes'])): ?>
                                    — <?= htmlspecialchars($bid['legitimacy_notes']) ?>
                                <?php endif; ?>
                            </div>

                        <?php elseif ($rejected): ?>
                            <div class="alert alert-danger py-2 small mb-3 rounded-3">
                                <i class="bi bi-x-circle-fill me-1"></i>
                                <strong>Bid Rejected — Excluded from Scoring.</strong>
                                <?php if (!empty($bid['legitimacy_notes'])): ?>
                                    — <?= htmlspecialchars($bid['legitimacy_notes']) ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- 8. Scoring Form (when verified and not yet scored) -->
                        <?php if ($isActionRole && $verified && !$bidEval): ?>
                            <div class="card border-primary rounded-3">
                                <div class="card-header bg-primary text-white py-2 fw-bold small">
                                    <i class="bi bi-star me-1"></i> Evaluation Scoring
                                </div>
                                <div class="card-body p-3">
                                    <?php if (!empty($rubric)): ?>
                                        <p class="small text-muted mb-3">
                                            Rubric: Technical <?= (float)$rubric['technical_weight'] ?>%
                                            | Financial <?= (float)$rubric['financial_weight'] ?>%
                                            | Local Preference <?= (float)$rubric['local_weight'] ?>%
                                        </p>
                                    <?php endif; ?>
                                    <form method="POST" action="index.php?route=submit-evaluation">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <input type="hidden" name="bid_id" value="<?= $bid['bid_id'] ?>">
                                        <input type="hidden" name="return_to" value="opening-room">
                                        <input type="hidden" name="tender_id_return" value="<?= $tender['tender_id'] ?>">
                                        <div class="row g-2 mb-2">
                                            <div class="col-md-3">
                                                <label class="form-label small fw-bold">Technical (0–100)</label>
                                                <input type="number" name="technical_score" class="form-control form-control-sm" min="0" max="100" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-bold">Financial (0–100)</label>
                                                <input type="number" name="financial_score" class="form-control form-control-sm" min="0" max="100" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-bold">Local Pref (0–100)</label>
                                                <input type="number" name="local_bonus" class="form-control form-control-sm" min="0" max="100"
                                                    value="<?= !empty($bid['local_ownership']) ? 100 : 0 ?>">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-bold">Compliance</label>
                                                <select name="responsiveness_pass" class="form-select form-select-sm">
                                                    <option value="1">Responsive (Pass)</option>
                                                    <option value="0">Non-Responsive (Fail)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row g-2 mb-3">
                                            <div class="col-md-5">
                                                <label class="form-label small fw-bold text-danger">Conflict of Interest?</label>
                                                <select name="conflict_of_interest_declared" class="form-select form-select-sm">
                                                    <option value="0">None Declared</option>
                                                    <option value="1">I Have a Conflict</option>
                                                </select>
                                            </div>
                                            <div class="col-md-7">
                                                <label class="form-label small fw-bold">Remarks / Justification</label>
                                                <input type="text" name="coi_reason" class="form-control form-control-sm"
                                                    placeholder="Briefly justify your scoring...">
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">
                                            <i class="bi bi-star me-1"></i> Submit Score &amp; Proceed
                                        </button>
                                    </form>
                                </div>
                            </div>

                        <?php elseif ($bidEval): ?>
                            <!-- Score already submitted -->
                            <div class="card border-info rounded-3">
                                <div class="card-header bg-info text-white py-2 fw-bold small">
                                    <i class="bi bi-star-fill me-1"></i> Evaluation Score Recorded
                                </div>
                                <div class="card-body py-2 px-3">
                                    <div class="row text-center small g-2">
                                        <div class="col-3">
                                            <div class="text-muted x-small">Technical</div>
                                            <strong><?= number_format($bidEval['technical_score'], 1) ?></strong>
                                        </div>
                                        <div class="col-3">
                                            <div class="text-muted x-small">Financial</div>
                                            <strong><?= number_format($bidEval['financial_score'], 1) ?></strong>
                                        </div>
                                        <div class="col-3">
                                            <div class="text-muted x-small">Local</div>
                                            <strong><?= number_format($bidEval['local_bonus'], 1) ?></strong>
                                        </div>
                                        <div class="col-3">
                                            <div class="text-muted x-small">Total</div>
                                            <strong class="text-success"><?= number_format($bidEval['total_score'], 2) ?> pts</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                    <?php endif; // end !sealed ?>

                </div><!-- /card-body -->
            </div><!-- /card -->
            <?php endforeach; ?>
        <?php endif; ?>

    </div><!-- /col-lg-8 -->

    <!-- Right Sidebar -->
    <div class="col-lg-4">

        <!-- Scoring Rubric -->
        <?php if (!empty($rubric)): ?>
        <div class="card border-0 shadow-sm mb-3 rounded-4">
            <div class="card-header bg-light fw-bold small py-2">
                <i class="bi bi-info-circle me-1"></i> Scoring Rubric
            </div>
            <div class="card-body p-3 small">
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span>Technical Capacity</span>
                    <strong><?= (float)$rubric['technical_weight'] ?>%</strong>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span>Financial Proposal</span>
                    <strong><?= (float)$rubric['financial_weight'] ?>%</strong>
                </div>
                <div class="d-flex justify-content-between py-1">
                    <span>Local Preference</span>
                    <strong><?= (float)$rubric['local_weight'] ?>%</strong>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Live Rankings -->
        <div class="card border-0 shadow-sm mb-3 rounded-4 overflow-hidden">
            <div class="card-header bg-success text-white fw-bold small py-2">
                <i class="bi bi-bar-chart-line me-1"></i> Evaluation Rankings
            </div>
            <div class="card-body p-0">
                <?php if (empty($evaluations)): ?>
                    <div class="p-3 text-center text-muted small">No scores recorded yet.</div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($evaluations as $rk => $ev): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3
                                border-start border-4 border-<?= $rk === 0 ? 'warning' : 'secondary' ?>">
                                <div>
                                    <span class="badge bg-<?= $rk === 0 ? 'warning text-dark' : 'secondary' ?> me-1">#<?= $rk + 1 ?></span>
                                    <span class="small fw-bold"><?= htmlspecialchars($ev['anonymous_code']) ?></span>
                                </div>
                                <span class="badge bg-<?= $rk === 0 ? 'success' : 'dark' ?>">
                                    <?= number_format($ev['total_score'], 2) ?> pts
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Award Panel (admin only, after evaluations exist, before awarding) -->
        <?php if ($isAdmin && !empty($evaluations) && !in_array($tender['status'], ['awarded'])): ?>
            <?php $topEval = $evaluations[0] ?? null; if ($topEval): ?>
            <div class="card border-0 shadow-sm mb-3 rounded-4 overflow-hidden">
                <div class="card-header bg-warning text-dark fw-bold small py-2">
                    <i class="bi bi-trophy me-1"></i> Award Tender Publicly
                </div>
                <div class="card-body p-3">
                    <p class="small text-muted mb-2">
                        Based on scores, the recommended winner is the top-ranked bid.
                    </p>
                    <div class="alert alert-light border py-2 small mb-3">
                        <strong>Recommended:</strong> <?= htmlspecialchars($topEval['anonymous_code']) ?>
                        — <span class="text-success fw-bold"><?= number_format($topEval['total_score'], 2) ?> pts</span>
                    </div>
                    <form method="POST" action="index.php?route=api-award-publicly"
                        onsubmit="return confirm('Award this tender publicly? This is permanent. All bidders will be notified.')">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <input type="hidden" name="tender_id" value="<?= $tender['tender_id'] ?>">
                        <input type="hidden" name="bid_id" value="<?= $topEval['bid_id'] ?>">
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Project Specifications &amp; Expectations</label>
                            <textarea name="winner_notes" class="form-control form-control-sm" rows="4"
                                placeholder="Describe the project specifications and expectations for the winning contractor. This will appear on their dashboard."></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning btn-sm w-100 fw-bold text-dark">
                            <i class="bi bi-trophy-fill me-1"></i> Award Winner Publicly
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Award Result (after awarding) -->
        <?php if ($tender['status'] === 'awarded' && !empty($awardSpecs)): ?>
        <div class="card border-0 shadow-sm mb-3 rounded-4 overflow-hidden">
            <div class="card-header bg-success text-white fw-bold small py-2">
                <i class="bi bi-trophy-fill me-1"></i> Official Award — Public Record
            </div>
            <div class="card-body p-3 small">
                <p class="text-muted mb-2">This tender has been officially awarded. The winner has been notified.</p>
                <?php if (!empty($awardSpecs['winner_notes'])): ?>
                    <div class="bg-light rounded-3 p-2">
                        <strong class="small">Project Specifications:</strong><br>
                        <span class="text-muted"><?= nl2br(htmlspecialchars($awardSpecs['winner_notes'])) ?></span>
                    </div>
                <?php endif; ?>
                <div class="mt-2">
                    <a href="index.php?route=winner-specifications&id=<?= $tender['tender_id'] ?>"
                       class="btn btn-outline-success btn-sm w-100">
                        <i class="bi bi-box-arrow-up-right me-1"></i> View Kickoff Specifications
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- /col-lg-4 -->
</div><!-- /row -->

<!-- Document Flag Modal -->
<div class="modal fade" id="flagDocumentModal" tabindex="-1" aria-labelledby="flagDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white py-2 px-3">
                <h6 class="modal-title fw-bold" id="flagDocumentModalLabel">Flag Document</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <form id="flagDocumentForm" autocomplete="off">
                    <input type="hidden" id="flagDocId" name="document_id">
                    <input type="hidden" id="flagDocType" name="document_type">
                    
                    <div class="mb-2 text-muted small">
                        Document: <strong id="flagDocName" class="text-dark"></strong>
                    </div>
                    
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="flagIsFraudulent" name="is_fraudulent" value="1">
                        <label class="form-check-label small fw-bold text-danger" for="flagIsFraudulent">
                            <i class="bi bi-shield-exclamation me-1"></i> Fraudulent Document
                        </label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="flagIsIncorrect" name="is_incorrect" value="1">
                        <label class="form-check-label small fw-bold text-warning" for="flagIsIncorrect">
                            <i class="bi bi-exclamation-triangle me-1"></i> Incorrect / Invalid
                        </label>
                    </div>
                    
                    <div class="mb-3">
                        <label for="flagReason" class="form-label small fw-semibold">Reason for Flagging</label>
                        <textarea class="form-control form-control-sm" id="flagReason" name="flag_reason" rows="2" placeholder="e.g. Forged signatures or expired validation dates" required></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary btn-sm rounded-pill w-50" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill w-50">Save Flag</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let flagModal = null;
function openFlagModal(docId, type, docName, isFraud, isIncorrect, reason) {
    document.getElementById('flagDocId').value = docId;
    document.getElementById('flagDocType').value = type;
    document.getElementById('flagDocName').textContent = docName;
    document.getElementById('flagIsFraudulent').checked = isFraud === 1;
    document.getElementById('flagIsIncorrect').checked = isIncorrect === 1;
    document.getElementById('flagReason').value = reason || '';
    
    if (!flagModal) {
        flagModal = new bootstrap.Modal(document.getElementById('flagDocumentModal'));
    }
    flagModal.show();
}

document.getElementById('flagDocumentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    // Explicitly add checkbox values if unchecked (since browser omits unchecked checkboxes)
    if (!formData.has('is_fraudulent')) formData.append('is_fraudulent', '0');
    if (!formData.has('is_incorrect')) formData.append('is_incorrect', '0');
    
    fetch('index.php?route=api-flag-document', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (flagModal) flagModal.hide();
            window.location.reload();
        } else {
            alert(data.error || 'Failed to update document flag status.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('An error occurred. Please try again.');
    });
});
</script>
