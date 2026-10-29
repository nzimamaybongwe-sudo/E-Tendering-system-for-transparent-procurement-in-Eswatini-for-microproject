<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php?route=admin-dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Review Rubric Proposals</li>
            </ol>
        </nav>
        <h2 class="fw-bold">Review Scoring Rubric Proposals</h2>
        <p class="text-muted">Approve or reject the rubric proposals submitted by evaluators before they are published to contractors.</p>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php if (empty($proposals)): ?>
            <div class="alert alert-secondary">There are no submitted rubric proposals awaiting review.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Tender</th>
                            <th>Evaluator</th>
                            <th>Weights</th>
                            <th>Comments</th>
                            <th>Submitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($proposals as $proposal): ?>
                            <tr>
                                <td><?= htmlspecialchars($proposal['tender_title']) ?></td>
                                <td><?= htmlspecialchars($proposal['evaluator_username']) ?></td>
                                <td>
                                    <strong>Technical:</strong> <?= htmlspecialchars($proposal['technical_weight']) ?>%<br>
                                    <strong>Financial:</strong> <?= htmlspecialchars($proposal['financial_weight']) ?>%<br>
                                    <strong>Local:</strong> <?= htmlspecialchars($proposal['local_weight']) ?>%
                                </td>
                                <td><?= nl2br(htmlspecialchars($proposal['notes'])) ?></td>
                                <td><?= date('d M Y', strtotime($proposal['created_at'])) ?></td>
                                <td>
                                    <form method="post" action="index.php?route=approve-rubric-proposal" class="mb-2" onsubmit="return confirm('Are you sure you want to approve this rubric proposal?');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <input type="hidden" name="proposal_id" value="<?= intval($proposal['proposal_id']) ?>">
                                        <button type="submit" class="btn btn-sm btn-success w-100">Approve</button>
                                    </form>
                                    <form method="post" action="index.php?route=reject-rubric-proposal" onsubmit="return confirm('Are you sure you want to reject this rubric proposal?');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <input type="hidden" name="proposal_id" value="<?= intval($proposal['proposal_id']) ?>">
                                        <input type="text" name="rejection_notes" class="form-control form-control-sm mb-2" placeholder="Rejection reason (optional)">
                                        <button type="submit" class="btn btn-sm btn-danger w-100">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
