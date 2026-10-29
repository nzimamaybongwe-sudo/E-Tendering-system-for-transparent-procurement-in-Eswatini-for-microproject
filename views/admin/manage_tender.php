<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold">Manage Tender: <?= htmlspecialchars($tender['title']) ?></h2>
            <span class="badge bg-<?= $tender['status'] === 'published' ? 'success' : ($tender['status'] === 'closed' ? 'warning' : 'info') ?> px-3 py-2 rounded-pill">
                Status: <?= htmlspecialchars(ucfirst($tender['status'])) ?>
            </span>
        </div>
        <div>
            <?php if ($tender['status'] === 'closed'): ?>
                <?php if (empty($tender['evaluation_release_at'])): ?>
                    <form method="post" action="index.php?route=release-tender-evaluation" onsubmit="return confirm('Forward all submitted bids to approved evaluators for evaluation?');" class="d-inline-block">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <input type="hidden" name="tender_id" value="<?= $tender['tender_id'] ?>">
                        <button type="submit" class="btn btn-warning btn-lg shadow-sm fw-bold text-dark">
                            <i class="bi bi-send-check me-2"></i> Forward to Evaluators
                        </button>
                    </form>
                <?php else: ?>
                    <span class="badge bg-success text-white px-4 py-3 rounded-pill">Released for Evaluation</span>
                <?php endif; ?>
            <?php elseif ($tender['status'] === 'published' && !empty($bids)): ?>
                <form method="post" action="index.php?route=release-tender-evaluation" onsubmit="return confirm('Release this tender to approved evaluators? Bidding will close and bids will be anonymized for evaluation.');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="tender_id" value="<?= $tender['tender_id'] ?>">
                    <button type="submit" class="btn btn-primary btn-lg shadow-sm fw-bold">
                        <i class="bi bi-send-check me-2"></i> Release to Evaluators
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Consensus Summary (If exists) -->
        <?php if ($consensus): ?>
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-success bg-opacity-10 border-success border">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-check-circle-fill text-success h3 mb-0 me-3"></i>
                        <h5 class="fw-bold mb-0">Evaluator Consensus Reached</h5>
                    </div>
                    <p class="mb-2"><strong>Recommended Winner:</strong> Bid #<?= $consensus['winner_bid_id'] ?></p>
                    <p class="small text-muted mb-0"><strong>Justification:</strong> <?= htmlspecialchars($consensus['justification']) ?></p>
                    <hr>
                    <p class="small text-muted mb-0"><strong>Participating Evaluators:</strong> <?= htmlspecialchars($consensus['evaluator_ids']) ?></p>
                    <?php if ($tender['status'] !== 'awarded'): ?>
                        <form method="post" action="index.php?route=award-tender" class="mt-3" onsubmit="return confirm('Publish the consensus winner now? This will mark the tender as awarded and notify the winning contractor.');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                            <input type="hidden" name="tender_id" value="<?= $tender['tender_id'] ?>">
                            <input type="hidden" name="bid_id" value="<?= $consensus['winner_bid_id'] ?>">
                            <button type="submit" class="btn btn-success fw-bold">
                                <i class="bi bi-megaphone me-2"></i> Publish Winner
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="mt-3">
                            <span class="badge bg-success rounded-pill px-3 py-2">Winner Published</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-dark text-white fw-bold">Submitted Bids</div>
            <div class="card-body p-0">
                <?php if (empty($bids)): ?>
                    <div class="p-5 text-center text-muted">No bids submitted yet.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Contractor</th>
                                    <th>Amount</th>
                                    <th>Signature</th>
                                    <th>Submitted</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bids as $bid): ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($bid['company_name']) ?></td>
                                        <td class="text-primary fw-bold">E<?= number_format((float)$bid['decrypted_amount'], 2) ?></td>
                                        <td><code class="small text-muted" title="<?= $bid['digital_signature'] ?>"><?= substr($bid['digital_signature'] ?? 'N/A', 0, 8) ?>...</code></td>
                                        <td class="small"><?= date('d M Y H:i', strtotime($bid['submitted_at'])) ?></td>
                                        <td>
                                            <?php if ($tender['status'] === 'closed'): ?>
                                                <span class="badge bg-info rounded-pill">With Evaluators</span>
                                            <?php elseif ($tender['status'] === 'awarded'): ?>
                                                <span class="badge bg-success rounded-pill">Awarded</span>
                                            <?php elseif ($tender['status'] === 'published'): ?>
                                                <span class="badge bg-secondary rounded-pill">Bid Received</span>
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

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white border-bottom fw-bold">Evaluations (Individual)</div>
            <div class="card-body">
                 <?php if (empty($evaluations)): ?>
                    <p class="text-muted small">Waiting for evaluators to grade bids...</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($evaluations as $eval): ?>
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold"><?= htmlspecialchars($eval['company_name']) ?></span>
                                    <span class="text-primary fw-bold"><?= number_format($eval['total_score'], 2) ?>%</span>
                                </div>
                                <div class="small text-muted">By Evaluator #<?= $eval['evaluator_id'] ?> on <?= date('d M Y', strtotime($eval['evaluated_at'])) ?></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-primary text-white fw-bold">Tender Details</div>
            <div class="card-body">
                <p class="mb-2"><strong>Budget:</strong> E<?= number_format($tender['budget'], 2) ?></p>
                <p class="mb-2"><strong>Tender Fee:</strong> E<?= number_format($tender['tender_fee'] ?? 0, 2) ?></p>
                <p class="mb-2"><strong>Deadline:</strong> <?= date('d M Y', strtotime($tender['deadline'])) ?></p>
                <p class="mb-2"><strong>Bids Received:</strong> <?= count($bids) ?></p>
                <?php if (!empty($tender['consensus_meeting_link'])): ?>
                    <p class="mb-2"><strong>Meeting Link:</strong> <a href="<?= htmlspecialchars($tender['consensus_meeting_link']) ?>" target="_blank">Join evaluation meeting</a></p>
                <?php endif; ?>
                <div class="alert alert-secondary small mt-3 mb-0">
                    Published tenders are immutable. Details are kept read-only to protect the tender record after publication and bidding.
                </div>
                <div class="mt-3">
                    <span class="badge bg-light text-dark border me-1"><?= htmlspecialchars($tender['category']) ?></span>
                    <span class="badge bg-secondary text-white"><?= htmlspecialchars($tender['region']) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
