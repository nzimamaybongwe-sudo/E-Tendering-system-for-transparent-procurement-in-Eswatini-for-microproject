<style>
    .eval-card {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        transition: transform 0.3s;
    }
    .eval-card:hover { transform: translateY(-3px); }
</style>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold text-dark">Evaluator Dashboard</h2>
        <p class="text-muted">Analyze submitted bids and collaborate with other evaluators to finalize decisions.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-primary text-white fw-bold py-3">Tenders Pending Evaluation</div>
            <div class="card-body">
                <?php if (empty($pendingEvaluationTenders)): ?>
                    <div class="text-center py-5 opacity-50">
                        <i class="bi bi-folder2-open h1"></i>
                        <p>No tenders are currently awaiting evaluation.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($pendingEvaluationTenders as $tender): ?>
                        <div class="card eval-card border mb-3 p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="fw-bold mb-1 text-primary"><?= htmlspecialchars($tender['title']) ?></h5>
                                    <div class="small text-muted">
                                        Budget: E<?= number_format($tender['budget'], 2) ?> | 
                                        Required Grade: <?= htmlspecialchars($tender['required_grade']) ?>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="index.php?route=evaluate-tender&id=<?= $tender['tender_id'] ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">Grade Bids</a>
                                    
                                    <form method="post" action="index.php?route=finish-tender-evaluation" style="margin:0;" onsubmit="return confirm('Are you sure you have completed all grading for this tender? Admin will be notified.');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <input type="hidden" name="tender_id" value="<?= $tender['tender_id'] ?>">
                                        <button type="submit" class="btn btn-outline-success btn-sm rounded-pill px-3"><i class="bi bi-check2-circle me-1"></i>Finish Grading</button>
                                    </form>

                                    <?php $meetingLink = $tender['meeting_link'] ?? ($tender['consensus_meeting_link'] ?? ''); ?>
                                    <?php if (!empty($meetingLink)): ?>
                                        <a href="<?= htmlspecialchars($meetingLink) ?>" target="_blank" class="btn btn-success btn-sm rounded-pill px-3">Join Live Meeting</a>
                                    <?php else: ?>
                                        <a href="index.php?route=consensus-meeting&id=<?= $tender['tender_id'] ?>" class="btn btn-primary btn-sm rounded-pill px-3">Join Meeting</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-success text-white fw-bold py-3">Propose Rubric</div>
            <div class="card-body small">
                <p class="mb-3">Create a scoring rubric proposal for a published tender and submit it to the administrator for final approval.</p>
                <div class="d-grid">
                    <a href="index.php?route=propose-rubric" class="btn btn-outline-success">New Rubric Proposal</a>
                </div>
                <?php if (!empty($proposalCount)): ?>
                    <div class="mt-3 small text-muted">You have <?= intval($proposalCount) ?> proposal<?= intval($proposalCount) === 1 ? '' : 's' ?> in your history.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-dark text-white fw-bold py-3">Evaluation Guidelines</div>
            <div class="card-body small">
                <div class="d-flex mb-3">
                    <i class="bi bi-1-circle-fill text-primary me-2"></i>
                    <span>Review individual bid documents carefully.</span>
                </div>
                <div class="d-flex mb-3">
                    <i class="bi bi-2-circle-fill text-primary me-2"></i>
                    <span>Submit your grades based on the specific tender rubric.</span>
                </div>
                <div class="d-flex mb-3">
                    <i class="bi bi-3-circle-fill text-primary me-2"></i>
                    <span>Join the <strong>Consensus Meeting</strong> to discuss results and provide a team justification.</span>
                </div>
                <div class="alert alert-warning py-2 mb-0">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Remember to declare any conflicts of interest.
                </div>
            </div>
        </div>
    </div>
</div>
