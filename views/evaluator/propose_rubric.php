<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php?route=evaluator-dashboard">Evaluator Dashboard</a></li>
                <li class="breadcrumb-item active">Propose Tender Rubric</li>
            </ol>
        </nav>
        <h2 class="fw-bold">Propose Tender Rubric</h2>
        <p class="text-muted">Draft rubric weights for a published tender and submit your proposal to the administrator.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-bottom fw-bold py-3">New Rubric Proposal</div>
            <div class="card-body p-4">
                <form method="post" action="index.php?route=submit-rubric-proposal">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Tender</label>
                        <select name="tender_id" class="form-select" required>
                            <option value="">Choose a tender</option>
                            <?php foreach ($tenders as $tender): ?>
                                <option value="<?= intval($tender['tender_id']) ?>"><?= htmlspecialchars($tender['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-4">
                            <label class="form-label fw-bold">Technical (%)</label>
                            <input type="number" name="technical_weight" class="form-control" value="50" min="0" max="100" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-bold">Financial (%)</label>
                            <input type="number" name="financial_weight" class="form-control" value="40" min="0" max="100" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-bold">Local (%)</label>
                            <input type="number" name="local_weight" class="form-control" value="10" min="0" max="100" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Comments for Admin</label>
                        <textarea name="notes" class="form-control" rows="4" placeholder="Explain why this rubric is appropriate for this tender..."></textarea>
                    </div>
                    <div class="mb-3 text-muted small">The total must add up to exactly 100% for the proposal to be accepted.</div>
                    <button type="submit" class="btn btn-primary">Submit Proposal</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-dark text-white fw-bold py-3">Your Proposals</div>
            <div class="card-body p-4">
                <?php if (empty($proposals)): ?>
                    <div class="text-center text-muted py-5">
                        <p class="mb-0">You have not submitted any rubric proposals yet.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($proposals as $proposal): ?>
                            <div class="list-group-item mb-3 rounded-4 border">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1 fw-bold"><?= htmlspecialchars($proposal['tender_title']) ?></h6>
                                        <span class="small text-muted">Submitted <?= date('d M Y', strtotime($proposal['created_at'])) ?></span>
                                    </div>
                                    <span class="badge bg-<?= $proposal['status'] === 'submitted' ? 'warning text-dark' : ($proposal['status'] === 'approved' ? 'success' : 'danger') ?> rounded-pill text-capitalize"><?= htmlspecialchars($proposal['status']) ?></span>
                                </div>
                                <div class="small mb-2">
                                    Technical <?= htmlspecialchars($proposal['technical_weight']) ?>%, Financial <?= htmlspecialchars($proposal['financial_weight']) ?>%, Local <?= htmlspecialchars($proposal['local_weight']) ?>%
                                </div>
                                <?php if (!empty($proposal['notes'])): ?>
                                    <div class="small text-muted">Notes: <?= htmlspecialchars($proposal['notes']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
