<div class="page-bg" style="background-image: url('assets/003.png');"></div>
<?php
$rubricWeights = [
    'technical' => isset($rubric['technical_weight']) ? (float)$rubric['technical_weight'] : 50.0,
    'financial' => isset($rubric['financial_weight']) ? (float)$rubric['financial_weight'] : 40.0,
    'local' => isset($rubric['local_weight']) ? (float)$rubric['local_weight'] : 10.0,
];
?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($_GET['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php
// Watch-Live banner — only for opening-stage tenders with a published meeting link
$_isOpeningStage = in_array($tender['status'], ['closed', 'evaluated', 'awarded']);
$_hasMeetingLink = !empty($tender['consensus_meeting_link']);
if ($_isOpeningStage && $_hasMeetingLink):
?>
<a href="index.php?route=watch-live&id=<?= $tender['tender_id'] ?>" class="wl-banner-strip text-decoration-none d-block mb-4">
    <span class="wl-banner-dot"></span>
    <span class="wl-banner-text">
        <i class="bi bi-broadcast me-2"></i>
        <strong>Opening session is live!</strong>
        Click here to watch as an observer →
    </span>
</a>
<style>
.wl-banner-strip {
    display: flex; align-items: center; gap: .75rem;
    background: linear-gradient(90deg,#1e1b4b,#312e81);
    border-radius: .75rem; padding: .9rem 1.5rem;
    box-shadow: 0 4px 20px rgba(99,102,241,.35);
    transition: transform .2s, box-shadow .2s;
}
.wl-banner-strip:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(99,102,241,.5); }
.wl-banner-dot {
    width: 10px; height: 10px; flex-shrink: 0;
    border-radius: 50%; background: #ef4444;
    box-shadow: 0 0 0 3px rgba(239,68,68,.3);
    animation: wlBannerPulse 1.2s ease-in-out infinite;
}
@keyframes wlBannerPulse {
    0%,100% { box-shadow: 0 0 0 3px rgba(239,68,68,.3); }
    50%      { box-shadow: 0 0 0 7px rgba(239,68,68,.1); }
}
.wl-banner-text { color: rgba(255,255,255,.9); font-size: .9rem; }
.wl-banner-text strong { color: #fff; }
</style>
<?php endif; ?>
<div class="row mb-5">
    <!-- Tender Details -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-primary text-white p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-white text-primary px-3"><?= htmlspecialchars($tender['category']) ?></span>
                    <span class="small opacity-75">Ref: #MP-<?= str_pad($tender['tender_id'], 5, '0', STR_PAD_LEFT) ?></span>
                </div>
                <h2 class="fw-bold mb-0"><?= htmlspecialchars($tender['title']) ?></h2>
            </div>
            <div class="card-body p-4">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="text-muted small">Budget</div>
                        <div class="h5 fw-bold">E<?= number_format($tender['budget'], 2) ?></div>
                    </div>
                    <div class="col-md-3 border-start">
                        <div class="text-muted small">Tender Fee</div>
                        <div class="h5 fw-bold">E<?= number_format($tender['tender_fee'] ?? 0, 2) ?></div>
                    </div>
                    <div class="col-md-3 border-start">
                        <div class="text-muted small">Required CIC Grade</div>
                        <div class="h5 fw-bold text-primary"><?= htmlspecialchars($tender['required_grade']) ?></div>
                    </div>
                    <div class="col-md-3 border-start">
                        <div class="text-muted small">Submission Deadline</div>
                        <div class="h5 fw-bold text-danger"><?= date('d M Y, H:i', strtotime($tender['deadline'])) ?></div>
                    </div>
                </div>

                <h5 class="fw-bold mb-3 border-bottom pb-2">Project Description</h5>
                <p class="text-muted lead-sm mb-4"><?= nl2br(htmlspecialchars($tender['description'])) ?></p>

                <h5 class="fw-bold mb-3 border-bottom pb-2">Project Location (<?= htmlspecialchars($tender['region']) ?>)</h5>
                <div class="ratio ratio-16x9 mb-3 rounded overflow-hidden shadow-sm">
                    <iframe 
                        width="100%" 
                        height="100%" 
                        frameborder="0" 
                        scrolling="no" 
                        marginheight="0" 
                        marginwidth="0" 
                        src="https://maps.google.com/maps?q=<?= urlencode($tender['location'] ?? $tender['region'] . ', Eswatini') ?>&t=&z=13&ie=UTF8&iwloc=&output=embed">
                    </iframe>
                </div>
                <p class="text-muted small mb-4">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                    <?= htmlspecialchars($tender['location'] ?? ($tender['region'] . ', Eswatini')) ?>
                </p>

                <!-- Tender Documents -->
                <?php if (!empty($tenderDocuments)): ?>
                <h5 class="fw-bold mb-3 border-bottom pb-2">Technical Specifications & BOQ</h5>
                <div class="list-group list-group-flush mb-4">
                    <?php foreach ($tenderDocuments as $doc): ?>
                        <a href="index.php?route=download-tender-doc&id=<?= $doc['document_id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0">
                            <span><i class="bi bi-file-earmark-pdf text-danger me-2"></i> <?= htmlspecialchars($doc['document_name']) ?></span>
                            <i class="bi bi-download text-muted"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($requiredDocs)): ?>
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-warning text-dark fw-bold py-3">Required Contractor Documents</div>
                        <div class="card-body p-4">
                            <p class="small text-muted mb-3">For published tenders, make sure the following documents are current before bidding.</p>
                            <div class="list-group list-group-flush">
                                <?php foreach ($requiredDocs as $req): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                                        <div>
                                            <strong><?= htmlspecialchars($req['name']) ?></strong>
                                            <?php if (!empty($req['uploaded_at'])): ?>
                                                <div class="small text-muted">Uploaded: <?= date('d M Y', strtotime($req['uploaded_at'])) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <span class="badge <?= $req['uploaded'] ? 'bg-success' : 'bg-danger' ?> rounded-pill">
                                            <?= $req['uploaded'] ? 'Complete' : 'Missing' ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($tender['status'] === 'evaluated' && !empty($tender['meeting_link'])): ?>
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-success text-white fw-bold py-3">Live Evaluation Meeting</div>
                        <div class="card-body p-4">
                            <p class="mb-3">This tender is currently in evaluation. Join the live meeting with the link below.</p>
                            <a href="<?= htmlspecialchars($tender['meeting_link']) ?>" target="_blank" class="btn btn-success btn-lg mb-3">
                                <i class="bi bi-camera-video me-1"></i> Join Meeting
                            </a>
                            <?php if (!empty($tender['meeting_password'])): ?>
                                <div class="small text-muted">Meeting password: <strong><?= htmlspecialchars($tender['meeting_password']) ?></strong></div>
                            <?php endif; ?>
                            <?php if (!empty($tender['meeting_start_time'])): ?>
                                <div class="small text-muted mt-2">Meeting started at: <?= date('d M Y, H:i', strtotime($tender['meeting_start_time'])) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Scoring Rubric & Bid Actions -->
    <div class="col-lg-8">
        <!-- Rubric Transparency Card -->
        <?php if (!empty($rubric)): ?>
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light border">
            <div class="card-body p-4 text-center">
                <h5 class="fw-bold mb-4"><i class="bi bi-info-circle-fill text-primary me-2"></i> Scoring Rubric</h5>
                <p class="small text-muted mb-4">This project will be evaluated based on the following transparent weights:</p>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between small fw-bold mb-1">
                        <span>Technical Capacity</span>
                        <span><?= $rubricWeights['technical'] ?>%</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-primary" style="width: <?= $rubricWeights['technical'] ?>%"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between small fw-bold mb-1">
                        <span>Financial Proposal</span>
                        <span><?= $rubricWeights['financial'] ?>%</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success" style="width: <?= $rubricWeights['financial'] ?>%"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between small fw-bold mb-1">
                        <span>Local Preference</span>
                        <span><?= $rubricWeights['local'] ?>%</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-warning" style="width: <?= $rubricWeights['local'] ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Digital templates removed to simplify bidding UI -->

        <!-- Action Card -->
        <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'contractor'): ?>
            <?php /* Removed verification_status block to allow bidding regardless of approval/documents
            if (!empty($contractor) && $contractor['verification_status'] !== 'approved'): ?>
                <div class="alert alert-warning shadow-sm border-0 rounded-4 p-4">
                    <h6 class="fw-bold"><i class="bi bi-lock-fill"></i> Account Pending</h6>
                    <p class="small mb-0">Your company is still waiting for Admin approval. You cannot submit bids yet.</p>
                </div>
            <?php else*/if (!empty($alreadyBid)): ?>
                <div class="alert alert-info shadow-sm border-0 rounded-4 p-4 text-center">
                    <i class="bi bi-check-circle-fill d-block mb-2 h3"></i>
                    <h6 class="fw-bold">Bid Submitted</h6>
                    <p class="small mb-0">You have already submitted a bid for this project.</p>
                </div>
            <?php elseif (!$canSubmitBid): ?>
                <div class="alert alert-secondary shadow-sm border-0 rounded-4 p-4 text-center">
                    <h6 class="fw-bold">Bid Submission Unavailable</h6>
                    <p class="small mb-0">The deadline for this tender has passed or you are not eligible to submit at this time.</p>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Submit Your Proposal</span>

                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($complianceIssues)): ?>
                            <div class="alert alert-info small mb-4 border-0 shadow-sm">
                                <strong><i class="bi bi-info-circle-fill"></i> Document Compliance:</strong><br>
                                Some required profile documents are missing or expired. Please upload them using the file inputs below to submit your bid.
                            </div>
                        <?php endif; ?>
                        <form id="bidForm" method="post" action="index.php?route=submit-bid" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                            <input type="hidden" name="tender_id" value="<?= $tender['tender_id'] ?>">
                            
                            <!-- 1. Financial Proposal -->
                            <div class="mb-4">
                                <label class="form-label fw-bold small">Your Proposed Amount (E)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">E</span>
                                    <input type="number" step="0.01" name="bid_amount" class="form-control" placeholder="0.00" required>
                                </div>
                                <div class="form-text text-muted small mt-2">Inclusive of all taxes and overheads.</div>
                            </div>

                            <!-- 2. Strategic Plan & Bidding Application -->
                            <div class="mb-4">
                                <label class="form-label fw-bold small">Strategic Plan &amp; Bidding Application</label>
                                <textarea name="method_statement" class="form-control" rows="6" placeholder="Write your bidding application and strategic plan here. Include:
- How you plan to execute the project (labor, timeline, materials)
- Your relevant experience and capacity
- Project management approach
- Quality assurance measures
- Why your company is best suited for this tender" required></textarea>
                                <div class="form-text small">This strategic plan is the core of your technical proposal and will be scored under Technical Capacity (<?= $rubricWeights['technical'] ?>%).</div>
                            </div>

                            <!-- 3. Bid Portfolio Media Upload -->
                            <div class="mb-4">
                                <label class="form-label fw-bold small"><i class="bi bi-images me-1"></i> Portfolio Proof (Optional)</label>
                                <p class="small text-muted mb-2">Upload photos or short video clips of relevant past projects that support this bid. This helps evaluators assess your technical capacity visually. Accepted: JPG, PNG, GIF, MP4, WEBM.</p>
                                <input type="file" name="bid_portfolio_files[]" id="bidPortfolioFiles" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.gif,.mp4,.webm" multiple>
                                <div class="form-text small text-muted">You may select multiple files. Maximum 10 MB per file.</div>
                                <!-- Preview area -->
                                <div id="portfolioPreviewArea" class="d-flex flex-wrap gap-2 mt-2"></div>
                            </div>
                            <script>
                            document.getElementById('bidPortfolioFiles')?.addEventListener('change', function() {
                                const area = document.getElementById('portfolioPreviewArea');
                                area.innerHTML = '';
                                Array.from(this.files).forEach(function(file) {
                                    const tag = document.createElement('span');
                                    tag.className = 'badge bg-secondary rounded-pill py-2 px-3';
                                    tag.innerHTML = '<i class="bi bi-file-earmark me-1"></i>' + file.name;
                                    area.appendChild(tag);
                                });
                            });
                            </script>

                            <hr class="my-4">
                            <h6 class="mb-3 fw-bold"><i class="bi bi-pencil-square me-2"></i>Digital Tender Form</h6>
                            <p class="small text-muted mb-4">Complete these declarations digitally to avoid printing and scanning costs.</p>

                            <!-- Eligibility Declaration -->
                            <div class="card bg-light border-0 rounded-3 mb-4">
                                <div class="card-body p-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="eligibility_confirmed" id="eligCheck" required>
                                        <label class="form-check-label small fw-bold" for="eligCheck">
                                            Declaration of Eligibility
                                        </label>
                                        <div class="form-text x-small">I/We declare that our company is not insolvent, in receivership, or bankrupt, and is not debarred from public procurement.</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Conflict of Interest -->
                            <div class="mb-4">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="conflict_declared" id="conflictCheck">
                                    <label class="form-check-label small fw-bold" for="conflictCheck">
                                        Declare Conflict of Interest
                                    </label>
                                </div>
                                <textarea name="conflict_details" class="form-control form-control-sm" rows="2" placeholder="If you have any relationship with MicroProjects staff, please detail it here..."></textarea>
                            </div>

                            <!-- Site Visit Details -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Site Visit Date</label>
                                    <input type="date" name="site_visit_date" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">MicroProjects Officer Met</label>
                                    <input type="text" name="site_visit_officer" class="form-control form-control-sm" placeholder="Officer Name" required>
                                </div>
                            </div>

                            <hr class="my-4">
                            <h6 class="mb-3 fw-bold"><i class="bi bi-file-earmark-arrow-up me-2"></i>Contractor Compliance Documents</h6>
                            <p class="small text-muted mb-3">Upload any missing or expired documents directly here to satisfy compliance checks.</p>
                            
                            <div class="row g-3 mb-4">
                                <?php foreach ($requiredDocs as $req): 
                                    $inputName = 'doc_' . preg_replace('/[^a-z0-9]/', '_', strtolower(str_replace(' ', '_', $req['name'])));
                                ?>
                                    <div class="col-md-4">
                                        <div class="p-3 rounded bg-light border border-light shadow-sm h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <span class="fw-bold small text-dark" style="font-size: 0.82rem; line-height: 1.2;"><?= htmlspecialchars($req['name']) ?></span>
                                                    <?php if ($req['uploaded']): ?>
                                                        <span class="badge bg-success ms-1"><i class="bi bi-check-circle-fill"></i></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark ms-1"><i class="bi bi-question-circle-fill"></i></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div>
                                                <input type="file" name="<?= $inputName ?>" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                                                <?php if ($req['uploaded']): ?>
                                                    <div class="form-text x-small text-muted mt-1" style="font-size: 0.7rem;">
                                                        Uploaded: <?= date('d M Y', strtotime($req['uploaded_at'])) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <hr class="my-4">
                            <h6 class="mb-3 fw-bold"><i class="bi bi-shield-lock me-2"></i>Third-Party Documents</h6>
                            <div class="alert alert-info py-2 small">Only upload documents that are issued by banks or insurance companies.</div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold">Tender Security (Bank/Insurance Bond)</label>
                                <input type="file" name="tender_security" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                                <div class="form-text x-small">Scanned copy of the original bank guarantee or bond.</div>
                            </div>
                            
                            <?php if (($tender['tender_fee'] ?? 0) > 0): ?>
                            <div class="mb-4">
                                <label class="form-label small fw-bold">Proof of Payment (Tender Fee)</label>
                                <input type="file" name="tender_fee_receipt" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                                <div class="form-text x-small">Scanned copy of the bank deposit slip or EFT receipt for E<?= number_format($tender['tender_fee'], 2) ?>.</div>
                            </div>
                            <?php endif; ?>

                            <div class="mb-4">
                                <label class="form-label small fw-bold">Proof of Funds / Bank Rating</label>
                                <input type="file" name="proof_of_funds" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                                <div class="form-text x-small">Recent bank statement or letter of credit from your financial institution.</div>
                            </div>

                            <!-- Final Commitment: Form of Tender -->
                            <div class="card border-primary border-1 bg-light rounded-3 mb-4">
                                <div class="card-body p-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="form_of_tender_accepted" id="fotCheck" required>
                                        <label class="form-check-label small fw-bold text-primary" for="fotCheck">
                                            Accept Form of Tender
                                        </label>
                                        <div class="form-text x-small mt-1">
                                            By checking this box, I/We agree to abide by this bid for the specified period and confirm that all information provided is true and correct. This serves as our official digital signature for the Form of Tender.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Submit Digital Bid</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4 text-center">
                    <h5 class="fw-bold mb-3">Ready to Bid?</h5>
                    <p class="text-muted small">Log in as a verified contractor to submit your proposal for this project.</p>
                    <a href="index.php?route=login" class="btn btn-outline-primary w-100">Login to Participate</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>



<style>
.step-number {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: bold;
}
</style>
