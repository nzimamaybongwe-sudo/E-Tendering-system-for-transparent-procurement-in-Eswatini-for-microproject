<div class="container py-5 px-4 bg-white" id="receipt-content">
    <div class="text-center mb-5">
        <h2 class="fw-bold text-uppercase mb-0">MicroProjects Eswatini</h2>
        <p class="text-muted small mb-4">E-Tendering System — Official Bid Submission Receipt</p>
        <hr class="w-25 mx-auto">
    </div>

    <div class="row mb-5">
        <div class="col-6">
            <h6 class="text-muted small text-uppercase fw-bold">Submitted By</h6>
            <h5 class="fw-bold mb-1"><?= htmlspecialchars($_SESSION['user']['username']) ?></h5>
            <p class="text-muted small mb-0">Contractor ID: #<?= str_pad($bid['contractor_id'], 5, '0', STR_PAD_LEFT) ?></p>
        </div>
        <div class="col-6 text-end">
            <h6 class="text-muted small text-uppercase fw-bold">Submission Details</h6>
            <h5 class="fw-bold mb-1">Receipt #<?= str_pad($bid['bid_id'], 6, '0', STR_PAD_LEFT) ?></h5>
            <p class="text-muted small mb-0">Date: <?= date('d M Y, H:i:s', strtotime($bid['submitted_at'])) ?></p>
        </div>
    </div>

    <div class="card border-0 bg-light rounded-4 mb-5">
        <div class="card-body p-4">
            <h6 class="text-muted small text-uppercase fw-bold mb-3">Project Information</h6>
            <h4 class="fw-bold mb-2"><?= htmlspecialchars($tender['title']) ?></h4>
            <p class="text-muted mb-0">Tender ID: #<?= $tender['tender_id'] ?> | Category: <?= htmlspecialchars($tender['category']) ?></p>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-12">
            <h6 class="text-muted small text-uppercase fw-bold mb-3">Financial Proposal</h6>
            <div class="display-6 fw-bold text-primary">E <?= number_format($amount, 2) ?></div>
            <p class="text-muted small mt-2">Inclusive of all taxes and overheads as declared digitally.</p>
        </div>
    </div>

    <div class="mb-5">
        <h6 class="text-muted small text-uppercase fw-bold mb-3">Technical Methodology Summary</h6>
        <div class="p-3 bg-white border rounded-3 small">
            <?= nl2br(htmlspecialchars($bid['method_statement'])) ?>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-md-6">
            <h6 class="text-muted small text-uppercase fw-bold mb-3">Digital Declarations</h6>
            <ul class="list-unstyled small">
                <li class="mb-2">
                    <?php if ($bid['eligibility_confirmed']): ?>
                        <i class="bi bi-check-circle-fill text-success me-2"></i> Eligibility Confirmed
                    <?php else: ?>
                        <i class="bi bi-x-circle-fill text-danger me-2"></i> Eligibility Not Confirmed
                    <?php endif; ?>
                </li>
                <li class="mb-2">
                    <?php if ($bid['form_of_tender_accepted']): ?>
                        <i class="bi bi-check-circle-fill text-success me-2"></i> Form of Tender Accepted
                    <?php else: ?>
                        <i class="bi bi-x-circle-fill text-danger me-2"></i> Form of Tender Not Accepted
                    <?php endif; ?>
                </li>
                <li class="mb-2"><i class="bi <?= $bid['conflict_declared'] ? 'bi-exclamation-triangle-fill text-warning me-2' : 'bi-check-circle-fill text-success me-2' ?>"></i> Conflict of Interest: <?= $bid['conflict_declared'] ? 'Yes' : 'None Declared' ?></li>
            </ul>
        </div>
        <div class="col-md-6">
            <h6 class="text-muted small text-uppercase fw-bold mb-3">Site Visit Verification</h6>
            <p class="small mb-1"><strong>Date:</strong> <?= !empty($bid['site_visit_date']) ? date('d M Y', strtotime($bid['site_visit_date'])) : 'N/A' ?></p>
            <p class="small mb-0"><strong>Verified By:</strong> <?= !empty($bid['site_visit_officer']) ? htmlspecialchars($bid['site_visit_officer']) : 'N/A' ?></p>
        </div>
    </div>

    <div class="mt-5 p-4 border border-dashed rounded-4 text-center bg-light">
        <h6 class="text-muted small text-uppercase fw-bold mb-2">Digital Signature & Integrity Hash</h6>
        <code class="text-break small"><?= $bid['digital_signature'] ?></code>
        <div class="mt-3 small text-muted">
            <i class="bi bi-shield-check me-1"></i> This document is cryptographically sealed. Any alteration to the bid data will invalidate this signature.
        </div>
    </div>

    <div class="mt-5 text-center no-print">
        <button onclick="window.print()" class="btn btn-dark btn-lg px-5 rounded-pill shadow">
            <i class="bi bi-printer me-2"></i> Print or Save as PDF
        </button>
        <p class="mt-3 small"><a href="index.php?route=contractor-dashboard" class="text-decoration-none">← Return to Dashboard</a></p>
    </div>
</div>

<style>
@media print {
    .no-print { display: none !important; }
    body { background: white !important; }
    .container { width: 100% !important; max-width: 100% !important; }
}
#receipt-content {
    max-width: 800px;
    margin: 0 auto;
    border: 1px solid #eee;
}
</style>
