<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <?php $dashRoute = $_SESSION['user']['role'] === 'officer' ? 'officer-dashboard' : 'admin-dashboard'; ?>
                <li class="breadcrumb-item"><a href="index.php?route=<?= $dashRoute ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php?route=manage-tender&id=<?= $tender['tender_id'] ?>">Manage Tender</a></li>
                <li class="breadcrumb-item active">Evaluator Consensus</li>
            </ol>
        </nav>
        <h2 class="fw-bold">Evaluator Consensus Meeting</h2>
        <p class="text-muted">Review all evaluator scores side-by-side and record the final team decision for <strong><?= htmlspecialchars($tender['title']) ?></strong>.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body">
                <?php if (!empty($tender['consensus_meeting_link'])): ?>
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="fw-bold mb-1">Consensus Meeting Scheduled</h5>
                            <p class="small text-muted mb-0">A live evaluation meeting has been scheduled for this tender.</p>
                        </div>
                        <a href="<?= htmlspecialchars($tender['consensus_meeting_link']) ?>" target="_blank" class="btn btn-success">Join Meeting</a>
                    </div>
                    <p class="small text-muted mb-0">Meeting link: <a href="<?= htmlspecialchars($tender['consensus_meeting_link']) ?>" target="_blank"><?= htmlspecialchars($tender['consensus_meeting_link']) ?></a></p>
                <?php elseif (in_array($_SESSION['user']['role'], ['officer', 'admin'])): ?>
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="fw-bold mb-1">Schedule Consensus Meeting</h5>
                            <p class="small text-muted mb-0">Enter a Zoom, Google Meet, or Teams link for this evaluator meeting.</p>
                        </div>
                    </div>
                    <form method="post" action="index.php?route=schedule-consensus-meeting">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <input type="hidden" name="tender_id" value="<?= $tender['tender_id'] ?>">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Meeting Link</label>
                            <div class="mb-2">
                                <a href="https://meet.google.com/new" target="_blank" class="btn btn-outline-primary btn-sm fw-bold">
                                    <i class="bi bi-camera-video me-1"></i> Generate Google Meet Link
                                </a>
                                <span class="small text-muted ms-2">(Click to generate a link, then copy and paste it below)</span>
                            </div>
                            <input type="url" name="meeting_link" class="form-control form-control-lg border-primary border-2" placeholder="https://meet.google.com/abc-defg-hij" required>
                            <div class="form-text">Use a secure Google Meet/Zoom/Teams link so evaluators can join the live meeting.</div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold shadow">Schedule Meeting & Notify Users</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p class="text-muted small mb-0">The meeting link will appear here once the Procurement Officer schedules the evaluation call.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Comparison Table -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-dark text-white fw-bold py-3">Comparative Score Matrix</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Bidder / Company</th>
                                <th>Bid Amount</th>
                                <th>Avg. Technical</th>
                                <th>Avg. Financial</th>
                                <th>Final Rank</th>
                                <th>Evaluator Participation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Index aggregated evaluations by bid_id (one row per bid from getAggregateByTender)
                            $evalByBid = [];
                            foreach ($evaluations as $eval) {
                                $evalByBid[(int)$eval['bid_id']] = $eval;
                            }

                            foreach ($bids as $bid):
                                $agg = $evalByBid[$bid['bid_id']] ?? null;
                                $avgTech  = $agg ? floatval($agg['avg_technical']) : 0;
                                $avgFin   = $agg ? floatval($agg['avg_financial']) : 0;
                                $avgTotal = $agg ? floatval($agg['avg_total'])     : 0;
                                $evalCount = $agg ? intval($agg['evaluator_count']) : 0;
                            ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($bid['company_name']) ?></div>
                                        <span class="small text-muted">ID: #<?= $bid['bid_id'] ?></span>
                                    </td>
                                    <td>E<?= number_format($bid['decrypted_amount'], 2) ?></td>
                                    <td>
                                        <div class="progress" style="height: 6px; width: 100px;">
                                            <div class="progress-bar" style="width: <?= min($avgTech, 100) ?>%"></div>
                                        </div>
                                        <span class="small"><?= number_format($avgTech, 1) ?></span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 6px; width: 100px;">
                                            <div class="progress-bar bg-success" style="width: <?= min($avgFin, 100) ?>%"></div>
                                        </div>
                                        <span class="small text-success"><?= number_format($avgFin, 1) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary rounded-pill px-3"><?= number_format($avgTotal, 2) ?> pts</span>
                                    </td>
                                    <td>
                                        <?php if ($evalCount > 0): ?>
                                            <div class="avatar-group d-flex align-items-center gap-1">
                                                <?php for ($i = 0; $i < $evalCount; $i++): ?>
                                                    <div class="bg-light border rounded-circle text-center small p-1" style="width:28px;height:28px;" title="Evaluator <?= $i+1 ?>">
                                                        <i class="bi bi-person"></i>
                                                    </div>
                                                <?php endfor; ?>
                                                <span class="small text-muted ms-1"><?= $evalCount ?> evaluator<?= $evalCount > 1 ? 's' : '' ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small fst-italic">No grades yet</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Final Decision Form -->
    <div class="col-lg-8 mx-auto">
        <div class="card border-0 shadow-lg rounded-4">
            <div class="card-header bg-primary text-white fw-bold py-3">Record Final Consensus</div>
            <div class="card-body p-4">
                <?php if ($existing): ?>
                    <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-4">
                        <i class="bi bi-check-circle-fill me-3 h4 mb-0"></i>
                        <div>
                            <strong>Consensus Already Recorded:</strong> The team has selected Bid #<?= $existing['winner_bid_id'] ?> as the winner.
                        </div>
                    </div>
                <?php endif; ?>

                <form method="post" action="index.php?route=submit-consensus">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="tender_id" value="<?= $tender['tender_id'] ?>">

                    <div class="mb-4">
                        <label class="form-label fw-bold">Select Consensus Winner</label>
                        <select name="winner_bid_id" class="form-select form-select-lg border-primary border-2" required <?= $existing ? 'disabled' : '' ?>>
                            <option value="">-- Choose winning bid --</option>
                            <?php foreach ($bids as $bid): ?>
                                <option value="<?= $bid['bid_id'] ?>" <?= ($existing && $existing['winner_bid_id'] == $bid['bid_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($bid['company_name']) ?> (Score: E<?= number_format($bid['decrypted_amount'], 2) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">The highest scoring company is usually selected, unless there is a strong technical justification otherwise.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Meeting Justification & Minutes</label>
                        <textarea name="justification" class="form-control" rows="5" placeholder="Describe why this company was chosen. Reference the technical scores, financial bid, and the rubric." required <?= $existing ? 'readonly' : '' ?>><?= $existing ? htmlspecialchars($existing['justification']) : '' ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Participating Evaluators (Full Names)</label>
                        <input type="text" name="evaluator_names" class="form-control" placeholder="e.g. John Doe, Sarah Smith, Michael Zwane" required <?= $existing ? 'readonly' : '' ?> value="<?= $existing ? htmlspecialchars($existing['evaluator_ids']) : '' ?>">
                    </div>

                    <?php if (!$existing): ?>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold shadow">Save Consensus & Finalize Result</button>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Award Tender Section -->
        <?php if ($existing): ?>
        <div class="card border-0 shadow-lg rounded-4 mt-4">
            <div class="card-header bg-success text-white fw-bold py-3">Publish & Award Winner</div>
            <div class="card-body p-4">
                <p class="text-muted mb-4">The consensus has been recorded. You can now officially award and publish the tender result to all stakeholders.</p>
                
                <div class="alert alert-info border-0 mb-4">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>Winner:</strong> Bid #<?= $existing['winner_bid_id'] ?> 
                    <?php foreach ($bids as $bid) {
                        if ($bid['bid_id'] == $existing['winner_bid_id']) {
                            echo ' — ' . htmlspecialchars($bid['company_name']);
                            break;
                        }
                    } ?>
                </div>

                <form method="post" action="index.php?route=award-tender" class="row g-3">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="tender_id" value="<?= $tender['tender_id'] ?>">
                    <input type="hidden" name="bid_id" value="<?= $existing['winner_bid_id'] ?>">
                    
                    <div class="col-12">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg fw-bold shadow">
                                <i class="bi bi-award me-2"></i> Publish & Award Winner
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
