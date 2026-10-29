<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold">Evaluation Module: <?= htmlspecialchars($tender['title']) ?></h2>
        <p class="text-muted">Budget: E<?= number_format($tender['budget'], 2) ?> | Deadline: <?= date('d M Y', strtotime($tender['deadline'])) ?></p>
        
        <!-- Evaluation Session Timer & Status -->
        <?php if ($session && $isSessionActive): ?>
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center">
                <i class="bi bi-hourglass-split me-3 h4 mb-0 text-danger" id="timerIcon"></i>
                <div class="flex-grow-1">
                    <span class="fw-bold">⏱️ EVALUATION SESSION ACTIVE</span><br>
                    <span class="small">Remaining Time: <strong id="countdown"><?= $remainingMinutes ?></strong> minutes</span>
                </div>
                <div>
                    <?php if (!$evaluatorReady): ?>
                        <button type="button" class="btn btn-success btn-sm" onclick="markEvaluatorReady(<?= $tender['tender_id'] ?>, true)">
                            <i class="bi bi-check-circle me-1"></i> Mark Evaluation Complete
                        </button>
                    <?php else: ?>
                        <span class="badge bg-success">✓ Ready to Close</span>
                    <?php endif; ?>
                </div>
            </div>
            <script>
                // Countdown timer
                let remainingMinutes = <?= $remainingMinutes ?>;
                function updateCountdown() {
                    if (remainingMinutes > 0) {
                        remainingMinutes--;
                        document.getElementById('countdown').textContent = remainingMinutes;
                        
                        // Change color when less than 15 minutes remain
                        if (remainingMinutes < 15) {
                            document.getElementById('timerIcon').classList.add('text-danger');
                        }
                    } else {
                        document.getElementById('countdown').textContent = '0';
                        document.getElementById('timerIcon').classList.add('text-danger');
                    }
                }
                setInterval(updateCountdown, 60000); // Update every minute
                
                function markEvaluatorReady(tenderId, isReady) {
                    fetch('index.php?route=mark-evaluator-ready', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'tender_id=' + tenderId + '&is_ready=' + (isReady ? '1' : '0')
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
                }
                
                function requestZoomMeeting() {
                    const reason = prompt('Please provide the reason for requesting a zoom meeting:');
                    if (reason) {
                        fetch('index.php?route=request-zoom-meeting', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'tender_id=' + <?= $tender['tender_id'] ?> + '&reason=' + encodeURIComponent(reason)
                        })
                        .then(response => response.json())
                        .then(data => {
                            alert(data.message);
                        });
                    }
                }
            </script>
        <?php endif; ?>
        
        <?php if (!empty($rubric)): ?>
            <div class="alert alert-info border-0 shadow-sm d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-3 h4 mb-0"></i>
                <div>
                    <span class="fw-bold">Tender Scoring Rubric:</span> 
                    Technical (<?= (float)$rubric['technical_weight'] ?>%) | 
                    Financial (<?= (float)$rubric['financial_weight'] ?>%) | 
                    Local (<?= (float)$rubric['local_weight'] ?>%)
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-dark text-white fw-bold">Anonymized Bids</div>
            <div class="card-body p-4">
                <?php if (empty($bids)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-folder-x text-muted opacity-25 h1"></i>
                        <p class="text-muted mt-2">No bids submitted for evaluation.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($bids as $bid): ?>
                        <div class="card border mb-4 rounded-3 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="fw-bold mb-0 text-primary"><?= htmlspecialchars($bid['anonymous_code']) ?></h5>
                                        <span class="small text-muted">Submitted: <?= date('d M Y, H:i', strtotime($bid['submitted_at'])) ?></span>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#portfolioModal<?= $bid['bid_id'] ?>">
                                        <i class="bi bi-briefcase me-1"></i> View Work History
                                    </button>
                                </div>
                                
                                <div class="p-3 bg-light rounded-3 mb-3 border shadow-sm">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="small text-muted">Bid Amount</div>
                                            <div class="h5 fw-bold mb-0">E<?= number_format((float)$bid['decrypted_amount'], 2) ?></div>
                                        </div>
                                        <div class="col-6 border-start">
                                            <div class="small text-muted">Price vs Budget</div>
                                            <?php 
                                                $diff = (($bid['decrypted_amount'] - $tender['budget']) / $tender['budget']) * 100;
                                                $color = abs($diff) > 20 ? 'danger' : 'success';
                                            ?>
                                            <div class="h5 fw-bold mb-0 text-<?= $color ?>"><?= ($diff > 0 ? '+' : '') . number_format($diff, 1) ?>%</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Technical Methodology & Declarations -->
                                <div class="card bg-white border rounded-3 mb-4">
                                    <div class="card-header bg-light py-2 small fw-bold">TECHNICAL PROPOSAL & DECLARATIONS</div>
                                    <div class="card-body p-3 small">
                                        <div class="mb-3">
                                            <h6 class="fw-bold small mb-1"><i class="bi bi-gear-wide-connected me-1"></i> Methodology / Work Plan:</h6>
                                            <div class="text-muted border-start border-3 ps-2 italic" style="font-style: italic;">
                                                <?= nl2br(htmlspecialchars($bid['method_statement'] ?? 'No methodology provided.')) ?>
                                            </div>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-md-6 border-end">
                                                <h6 class="fw-bold small mb-1"><i class="bi bi-geo-alt me-1"></i> Site Visit Verification:</h6>
                                                <div class="text-muted">
                                                    Date: <?= $bid['site_visit_date'] ? date('d M Y', strtotime($bid['site_visit_date'])) : 'N/A' ?><br>
                                                    Officer: <?= htmlspecialchars($bid['site_visit_officer'] ?? 'N/A') ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6 ps-2">
                                                <h6 class="fw-bold small mb-1"><i class="bi bi-shield-check me-1"></i> Digital Declarations:</h6>
                                                <div class="<?= $bid['eligibility_confirmed'] ? 'text-success' : 'text-danger' ?>">
                                                    <i class="bi bi-<?= $bid['eligibility_confirmed'] ? 'check' : 'x' ?>-circle me-1"></i> Eligibility: <?= $bid['eligibility_confirmed'] ? 'Confirmed' : 'Pending' ?>
                                                </div>
                                                <div class="<?= $bid['form_of_tender_accepted'] ? 'text-success' : 'text-danger' ?>">
                                                    <i class="bi bi-<?= $bid['form_of_tender_accepted'] ? 'check' : 'x' ?>-circle me-1"></i> Form of Tender: <?= $bid['form_of_tender_accepted'] ? 'Signed' : 'Not Signed' ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <form method="post" action="index.php?route=submit-evaluation" class="row g-3">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                    <input type="hidden" name="bid_id" value="<?= $bid['bid_id'] ?>">
                                    <div class="col-12">
                                        <div class="alert alert-secondary small mb-0">
                                            Score this anonymized bid using the approved rubric above. Use the methodology and portfolio history as evidence for the technical score.
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Technical Grade (%)</label>
                                        <input type="number" name="technical_score" class="form-control" min="0" max="100" required>
                                        <div class="form-text">Methodology and portfolio history.</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Financial Grade (%)</label>
                                        <input type="number" name="financial_score" class="form-control" min="0" max="100" required>
                                        <div class="form-text">Price reasonableness.</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Local Preference (%)</label>
                                        <input type="number" name="local_bonus" class="form-control" min="0" max="100" value="0" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Compliance</label>
                                        <select name="responsiveness_pass" class="form-select">
                                            <option value="1">Responsive (Pass)</option>
                                            <option value="0">Non-Responsive (Fail)</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 mt-3">
                                        <label class="form-label small fw-bold text-danger">⚠️ Conflict of Interest?</label>
                                        <select name="conflict_of_interest_declared" class="form-select border-danger coi-select" data-bid="<?= $bid['bid_id'] ?>" onchange="handleCOI(this)">
                                            <option value="0">None Declared</option>
                                            <option value="1">I Have a Conflict of Interest</option>
                                        </select>
                                        <div class="form-text text-danger">If you declare a COI, your scores will NOT be counted for this bid.</div>
                                    </div>
                                    <div class="col-md-6 mt-3">
                                        <label class="form-label small fw-bold" id="coiReasonLabel<?= $bid['bid_id'] ?>">COI Reason / Remarks</label>
                                        <textarea name="coi_reason" class="form-control" rows="2" id="coiReason<?= $bid['bid_id'] ?>" placeholder="If conflict declared: state your relationship. Otherwise: leave blank."></textarea>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <label class="form-label small fw-bold">📝 Evaluator Remarks <span class="text-muted fw-normal">(required — justify your scoring)</span></label>
                                        <textarea name="remarks" class="form-control score-input-<?= $bid['bid_id'] ?>" rows="3" required
                                                  placeholder="Briefly explain your scoring rationale. E.g: 'Strong methodology but overpriced by 18% vs budget. Local presence verified.'"></textarea>
                                    </div>
                                    <!-- COI Recusal Alert (hidden by default) -->
                                    <div class="col-12 mt-2 d-none" id="coiAlert<?= $bid['bid_id'] ?>">
                                        <div class="alert alert-danger d-flex align-items-center gap-3 mb-0">
                                            <i class="bi bi-shield-exclamation fs-4"></i>
                                            <div>
                                                <strong>You are recused from scoring this bid.</strong><br>
                                                <span class="small">Your declaration of a Conflict of Interest means your scores will not be counted. Please state your reason above and submit.</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Public Comments Section -->
                                    <!-- Documents Section -->
                                    <div class="col-12 mt-4 border-top pt-3">
                                        <h6 class="fw-bold mb-3"><i class="bi bi-file-earmark-text me-2"></i>Submitted Documents</h6>

                                        <?php if (!empty($bid['documents'])): ?>
                                            <div class="mb-3">
                                                <strong>Bid Documents</strong>
                                                <div class="list-group mt-2">
                                                    <?php foreach ($bid['documents'] as $doc): ?>
                                                        <a href="<?= htmlspecialchars($doc['download_url']) ?>" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <div class="fw-bold">
                                                                    <?= htmlspecialchars($doc['document_name']) ?>
                                                                    <?php if (!empty($doc['is_fraudulent'])): ?>
                                                                        <span class="badge bg-danger ms-1"><i class="bi bi-shield-exclamation"></i> Fraudulent</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($doc['is_incorrect'])): ?>
                                                                        <span class="badge bg-warning text-dark ms-1"><i class="bi bi-exclamation-triangle"></i> Incorrect</span>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <small class="text-muted">Uploaded: <?= date('d M Y, H:i', strtotime($doc['uploaded_at'])) ?></small>
                                                                <?php if (!empty($doc['flag_reason'])): ?>
                                                                    <div class="text-danger small mt-1">Warning: <?= htmlspecialchars($doc['flag_reason']) ?></div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="text-end small text-muted">
                                                                <?= !empty($doc['sha256']) ? 'SHA256: ' . substr($doc['sha256'], 0, 12) . '...' : '<span class="text-success"><i class="bi bi-check-circle"></i> Available</span>' ?>
                                                            </div>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($bid['contractor_documents'])): ?>
                                            <div class="mb-3">
                                                <strong>Contractor Documents</strong>
                                                <div class="list-group mt-2">
                                                    <?php foreach ($bid['contractor_documents'] as $doc): ?>
                                                        <a href="<?= htmlspecialchars($doc['download_url']) ?>" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <div class="fw-bold">
                                                                    <?= htmlspecialchars($doc['document_name']) ?>
                                                                    <?php if (!empty($doc['is_fraudulent'])): ?>
                                                                        <span class="badge bg-danger ms-1"><i class="bi bi-shield-exclamation"></i> Fraudulent</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($doc['is_incorrect'])): ?>
                                                                        <span class="badge bg-warning text-dark ms-1"><i class="bi bi-exclamation-triangle"></i> Incorrect</span>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <small class="text-muted">Uploaded: <?= date('d M Y, H:i', strtotime($doc['uploaded_at'])) ?></small>
                                                                <?php if (!empty($doc['expiry_date'])): ?>
                                                                    <br><small class="<?= strtotime($doc['expiry_date']) < time() ? 'text-danger fw-bold' : 'text-success' ?>">Expires: <?= date('d M Y', strtotime($doc['expiry_date'])) ?></small>
                                                                <?php endif; ?>
                                                                <?php if (!empty($doc['flag_reason'])): ?>
                                                                    <div class="text-danger small mt-1">Warning: <?= htmlspecialchars($doc['flag_reason']) ?></div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="text-end small text-muted">
                                                                <?= !empty($doc['sha256']) ? 'SHA256: ' . substr($doc['sha256'], 0, 12) . '...' : '<span class="text-success"><i class="bi bi-check-circle"></i> Available</span>' ?>
                                                            </div>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="col-12 mt-4 border-top pt-3">
                                        <h6 class="fw-bold mb-3"><i class="bi bi-chat-left-text me-2"></i>Public Evaluation Comments</h6>
                                        
                                        <!-- Existing Comments -->
                                        <?php if (!empty($bid['comments'])): ?>
                                            <div class="mb-3">
                                                <?php foreach ($bid['comments'] as $comment): ?>
                                                    <div class="card border-left-primary mb-2 small">
                                                        <div class="card-body p-2">
                                                            <strong><?= htmlspecialchars($comment['evaluator_name']) ?></strong>
                                                            <span class="badge bg-secondary ms-1"><?= ucfirst(str_replace('_', ' ', $comment['comment_type'])) ?></span>
                                                            <div class="text-muted small"><?= date('d M Y, H:i', strtotime($comment['created_at'])) ?></div>
                                                            <div class="mt-1 ps-2 border-start"><?= nl2br(htmlspecialchars($comment['comment_text'])) ?></div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-muted small italic">No comments yet</p>
                                        <?php endif; ?>
                                        
                                        <!-- Add New Comment -->
                                        <?php if ($isSessionActive): ?>
                                            <div class="input-group input-group-sm mt-2">
                                                <input type="text" class="form-control" id="comment_<?= $bid['bid_id'] ?>" placeholder="Add a public comment...">
                                                <select class="form-select form-select-sm" id="commentType_<?= $bid['bid_id'] ?>" style="max-width: 150px;">
                                                    <option value="general_comment">General</option>
                                                    <option value="technical_observation">Technical</option>
                                                    <option value="financial_observation">Financial</option>
                                                    <option value="flag">Flag Issue</option>
                                                </select>
                                                <button class="btn btn-outline-primary btn-sm" type="button" onclick="submitComment(<?= $tender['tender_id'] ?>, <?= $bid['bid_id'] ?>, 'comment_<?= $bid['bid_id'] ?>', 'commentType_<?= $bid['bid_id'] ?>')">
                                                    <i class="bi bi-send"></i>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="col-12 mt-3">
                                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">Submit Final Grade</button>
                                    </div>
                                </form>
                                
                                <!-- Zoom Meeting Request Button -->
                                <?php if ($isSessionActive): ?>
                                    <div class="mt-3 pt-3 border-top">
                                        <button type="button" class="btn btn-warning btn-sm w-100" onclick="requestZoomMeeting()">
                                            <i class="bi bi-camera-video me-1"></i> Request Zoom Meeting (for clarifications)
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Add submitComment function to scripts -->
                        <script>
                            function submitComment(tenderId, bidId, inputId, typeSelectId) {
                                const comment = document.getElementById(inputId).value;
                                const type = document.getElementById(typeSelectId).value;
                                
                                if (!comment.trim()) {
                                    alert('Please enter a comment');
                                    return;
                                }
                                
                                fetch('index.php?route=submit-evaluation-comment', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded',
                                    },
                                    body: 'tender_id=' + tenderId + '&bid_id=' + bidId + 
                                          '&comment_text=' + encodeURIComponent(comment) +
                                          '&comment_type=' + encodeURIComponent(type)
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        document.getElementById(inputId).value = '';
                                        location.reload();
                                    } else {
                                        alert('Error: ' + data.message);
                                    }
                                });
                            }
                        </script>
                            </div>
                        </div>

                        <!-- Modal: Bidder Portfolio -->
                        <div class="modal fade" id="portfolioModal<?= $bid['bid_id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content rounded-4 border-0 shadow-lg">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title fw-bold">Work History: <?= htmlspecialchars($bid['anonymous_code']) ?></h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <h6 class="fw-bold mb-3 border-bottom pb-2">Past Projects & Capacity</h6>
                                        <?php if (empty($bid['portfolio'])): ?>
                                            <p class="text-muted text-center py-4 italic">No portfolio data provided by this contractor.</p>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm small table-striped align-middle">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>Project Name</th>
                                                            <th>Client / Sector</th>
                                                            <th>Value (E)</th>
                                                            <th>Year</th>
                                                            <th>Proof</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($bid['portfolio'] as $p_item): ?>
                                                            <tr>
                                                                <td class="fw-bold"><?= htmlspecialchars($p_item['project_name']) ?></td>
                                                                <td><?= htmlspecialchars($p_item['client_name']) ?></td>
                                                                <td><?= number_format($p_item['project_value'], 2) ?></td>
                                                                <td><?= $p_item['completion_year'] ?></td>
                                                                <td>
                                                                    <?php if (!empty($p_item['media'])): ?>
                                                                        <div class="d-flex flex-wrap gap-1">
                                                                            <?php foreach ($p_item['media'] as $media): ?>
                                                                                <?php if (strpos($media['file_type'], 'image/') === 0): ?>
                                                                                    <a href="<?= htmlspecialchars($media['file_path']) ?>" target="_blank" class="d-inline-block border rounded-3 overflow-hidden" style="width: 40px; height: 40px;">
                                                                                        <img src="<?= htmlspecialchars($media['file_path']) ?>" alt="Proof image" class="img-fluid" style="width:100%; height:100%; object-fit:cover;">
                                                                                    </a>
                                                                                <?php else: ?>
                                                                                    <a href="<?= htmlspecialchars($media['file_path']) ?>" target="_blank" class="badge bg-secondary text-white">Video</a>
                                                                                <?php endif; ?>
                                                                            <?php endforeach; ?>
                                                                        </div>
                                                                    <?php else: ?>
                                                                        <span class="text-muted small">None</span>
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
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($isSessionActive && !empty($bids)): ?>
        <!-- ===== FORMAL RECOMMENDATION FORM ===== -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mt-4" id="recommendationSection">
            <div class="card-header text-white fw-bold d-flex align-items-center gap-2"
                 style="background:linear-gradient(135deg,#1e3a5f,#2563eb);">
                <i class="bi bi-award-fill fs-5"></i>
                <span>Step 2 &mdash; Submit Your Winner Recommendation</span>
                <span class="badge bg-warning text-dark ms-auto">Required after scoring all bids</span>
            </div>
            <div class="card-body p-4">
                <p class="text-muted small mb-4">
                    After scoring <strong>all</strong> bids above, formally recommend the bid you believe should win this tender.
                    Your recommendation is confidential and will be tallied alongside other evaluators' choices.
                    The Procurement Officer makes the final decision.
                </p>
                <form method="post" action="index.php?route=submit-evaluator-recommendation">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="tender_id" value="<?= $tender['tender_id'] ?>">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-bold">🏆 Recommended Winning Bid</label>
                            <select name="recommended_bid_id" class="form-select form-select-lg" required>
                                <option value="">— Select a bid code —</option>
                                <?php foreach ($bids as $b): ?>
                                    <option value="<?= $b['bid_id'] ?>">
                                        <?= htmlspecialchars($b['anonymous_code']) ?>
                                        (E<?= number_format((float)$b['decrypted_amount'], 2) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label fw-bold">📋 Justification <span class="text-danger">*</span></label>
                            <textarea name="justification" class="form-control" rows="3" required
                                      placeholder="Explain why this bid should win. Reference their technical score, methodology, price competitiveness, and compliance."></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-lg fw-bold px-4"
                                    style="background:linear-gradient(135deg,#1e3a5f,#2563eb);color:#fff;border:none;border-radius:12px;">
                                <i class="bi bi-send-check me-2"></i>Submit My Recommendation
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Live Ranking -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden sticky-top" style="top: 2rem;">
            <div class="card-header bg-success text-white fw-bold d-flex align-items-center gap-2">
                <i class="bi bi-bar-chart-fill"></i> Live Evaluation Ranking
                <span class="badge bg-light text-success ms-auto">Avg across all evaluators</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($evaluations)): ?>
                    <div class="p-4 text-center text-muted">Waiting for first evaluation...</div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($evaluations as $rank => $eval): ?>
                            <div class="list-group-item p-3 border-start border-<?= $rank === 0 ? 'success' : 'secondary' ?> border-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold"><?= htmlspecialchars($eval['anonymous_code']) ?></span>
                                        <?php if ($rank === 0): ?>
                                            <span class="badge bg-success ms-1">🏆 Leading</span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="badge bg-<?= $rank === 0 ? 'success' : 'dark' ?>">
                                        <?= number_format($eval['total_score'], 2) ?> pts
                                    </span>
                                </div>
                                <div class="row g-0 mt-2 small text-muted">
                                    <div class="col-4">Tech: <?= number_format($eval['technical_score'], 1) ?></div>
                                    <div class="col-4">Fin: <?= number_format($eval['financial_score'], 1) ?></div>
                                    <div class="col-4">Loc: <?= number_format($eval['local_bonus'], 1) ?></div>
                                </div>
                                <?php if (!empty($eval['remarks'])): ?>
                                    <div class="mt-2 text-muted small fst-italic border-start border-2 ps-2">
                                        "<?= htmlspecialchars(substr($eval['remarks'], 0, 80)) ?><?= strlen($eval['remarks']) > 80 ? '…' : '' ?>"
                                    </div>
                                <?php endif; ?>
                                <div class="mt-2 progress" style="height: 5px;">
                                    <div class="progress-bar bg-success" style="width: <?= $eval['total_score'] ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * COI Enforcement: When an evaluator declares a Conflict of Interest,
 * disable all score inputs for that bid and show a recusal warning.
 * The form can still be submitted — but with coi_recused=1 flag.
 */
function handleCOI(selectEl) {
    const bidId   = selectEl.dataset.bid;
    const hasCOI  = selectEl.value === '1';

    // Score input fields for this specific bid
    const scoreInputs = document.querySelectorAll(
        `form:has([data-bid="${bidId}"]) input[type="number"], form:has([data-bid="${bidId}"]) select:not(.coi-select)`
    );
    // Fallback for older browsers — target by class
    const remarksEl = document.querySelector(`.score-input-${bidId}`);
    const coiAlert  = document.getElementById('coiAlert' + bidId);
    const submitBtn = selectEl.closest('form')?.querySelector('button[type="submit"]');

    if (hasCOI) {
        // Disable scoring inputs
        scoreInputs.forEach(inp => {
            inp.disabled = true;
            inp.style.opacity = '0.4';
        });
        if (remarksEl) { remarksEl.required = false; remarksEl.disabled = true; remarksEl.style.opacity = '0.4'; }
        if (coiAlert) coiAlert.classList.remove('d-none');
        if (submitBtn) {
            submitBtn.textContent = '⚠️ Submit COI Declaration (No Score)';
            submitBtn.classList.replace('btn-primary', 'btn-danger');
        }
        // Add hidden recused flag
        let recusedInput = selectEl.closest('form').querySelector('input[name="coi_recused"]');
        if (!recusedInput) {
            recusedInput = document.createElement('input');
            recusedInput.type = 'hidden';
            recusedInput.name = 'coi_recused';
            selectEl.closest('form').appendChild(recusedInput);
        }
        recusedInput.value = '1';
    } else {
        scoreInputs.forEach(inp => {
            inp.disabled = false;
            inp.style.opacity = '1';
        });
        if (remarksEl) { remarksEl.required = true; remarksEl.disabled = false; remarksEl.style.opacity = '1'; }
        if (coiAlert) coiAlert.classList.add('d-none');
        if (submitBtn) {
            submitBtn.textContent = 'Submit Evaluation';
            submitBtn.classList.replace('btn-danger', 'btn-primary');
        }
        const recusedInput = selectEl.closest('form')?.querySelector('input[name="coi_recused"]');
        if (recusedInput) recusedInput.value = '0';
    }
}
</script>
