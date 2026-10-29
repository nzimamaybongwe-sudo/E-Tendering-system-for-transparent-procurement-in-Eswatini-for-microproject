<div class="page-bg" style="background-image: url('assets/003.jpg');"></div>
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="fw-bold">Contractor Profile</h1>
            <p class="text-muted mb-0">Manage your business credentials and showcase your past project portfolio.</p>
        </div>
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
            <i class="bi bi-pencil"></i> Edit Profile
        </button>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($_GET['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($_GET['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Company Info -->
    <div class="col-lg-4">
         <div class="card border-0 shadow-sm rounded-4 mb-4">
             <div class="card-header bg-primary text-white fw-bold py-3">Company Details</div>
             <div class="card-body">
                 <div class="mb-3">
                     <label class="small text-muted d-block">Company Name</label>
                     <span class="fw-bold"><?= htmlspecialchars($contractor['company_name']) ?></span>
                 </div>
                 <div class="mb-3">
                     <label class="small text-muted d-block">CIC Grade</label>
                     <span class="badge bg-light text-primary border border-primary"><?= htmlspecialchars($contractor['cic_grade']) ?></span>
                 </div>
                 <div class="mb-3">
                     <label class="small text-muted d-block">Verification Status</label>
                     <span class="badge bg-<?= $contractor['verification_status'] === 'approved' ? 'success' : ($contractor['verification_status'] === 'rejected' ? 'danger' : 'warning') ?>">
                         <?= ucfirst(htmlspecialchars($contractor['verification_status'])) ?>
                     </span>
                 </div>
                 <div class="mb-3">
                     <label class="small text-muted d-block">Local SME</label>
                     <span class="badge <?= $contractor['local_ownership'] ? 'bg-success' : 'bg-secondary' ?>">
                         <?= $contractor['local_ownership'] ? '100% Eswatini-owned' : 'Not locally owned' ?>
                     </span>
                 </div>
                 <?php if ($contractor['blacklisted']): ?>
                     <div class="alert alert-danger py-2 small">
                         <i class="bi bi-exclamation-octagon-fill me-1"></i> **Blacklisted:** <?= htmlspecialchars($contractor['blacklist_reason']) ?>
                     </div>
                 <?php endif; ?>
                 <?php if (!empty($contractor['profile_summary'])): ?>
                     <div class="mt-3">
                         <label class="small text-muted d-block">Profile Summary</label>
                         <p class="small mb-0"><?= nl2br(htmlspecialchars($contractor['profile_summary'])) ?></p>
                     </div>
                 <?php endif; ?>
             </div>
         </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-dark text-white fw-bold py-3 d-flex justify-content-between align-items-center">
                <span>Legal Documents</span>
                <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                    <i class="bi bi-cloud-upload"></i> Upload
                </button>
            </div>
            <div class="card-body p-4">
                <p class="small text-muted mb-3">Keep these documents current before bidding on any tender.</p>
                <div class="list-group list-group-flush">
                    <?php foreach ($requiredDocs as $req): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-2">
                            <div>
                                <strong><?= htmlspecialchars($req['name']) ?></strong>
                                <?php if (!empty($req['uploaded_at'])): ?>
                                    <div class="small text-muted">Last uploaded: <?= date('d M Y', strtotime($req['uploaded_at'])) ?></div>
                                <?php endif; ?>
                            </div>
                            <span class="badge <?= $req['uploaded'] ? 'bg-success' : 'bg-warning text-dark' ?> rounded-pill">
                                <?= $req['uploaded'] ? 'Ready' : 'Missing' ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-3 small text-muted">If a document is missing, click Upload above or contact support before bidding.</div>
            </div>
        </div>
    </div>

    <!-- Portfolio Management -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div>
                <h5 class="fw-bold mb-0">Project Portfolio</h5>
                <small class="text-muted">Optional. Startups and new contractors can leave this empty.</small>
            </div>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPortfolioModal">
                    + Add Project
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($portfolio)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-briefcase text-muted opacity-25" style="font-size: 4rem;"></i>
                        <p class="text-muted mt-3">Your portfolio is empty. This is optional for new contractors; you can still bid and grow your experience over time.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Project Name</th>
                                    <th>Client</th>
                                    <th>Value (E)</th>
                                    <th>Year</th>
                                    <th>Proof</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($portfolio as $item): ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($item['project_name']) ?></td>
                                        <td><?= htmlspecialchars($item['client_name']) ?></td>
                                        <td><?= number_format($item['project_value'], 2) ?></td>
                                        <td><?= $item['completion_year'] ?></td>
                                        <td>
                                            <?php if (!empty($item['media'])): ?>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <?php foreach ($item['media'] as $media): ?>
                                                        <?php if (strpos($media['file_type'], 'image/') === 0): ?>
                                                            <a href="<?= htmlspecialchars($media['file_path']) ?>" target="_blank" class="d-inline-block border rounded-3 overflow-hidden" style="width: 60px; height: 60px;">
                                                                <img src="<?= htmlspecialchars($media['file_path']) ?>" alt="Proof image" class="img-fluid" style="width:100%; height:100%; object-fit:cover;">
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="<?= htmlspecialchars($media['file_path']) ?>" target="_blank" class="badge bg-secondary text-white">View Video</a>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted small">No proof</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="post" action="index.php?route=delete-portfolio-item" onsubmit="return confirm('Delete this project?')">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                                <input type="hidden" name="project_id" value="<?= $item['project_id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
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
    </div>
</div>

<!-- Modal: Add Portfolio -->
<div class="modal fade" id="addPortfolioModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="post" action="index.php?route=add-portfolio-item" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Past Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Project Title</label>
                        <input type="text" name="project_name" class="form-control" placeholder="e.g. Construction of Mbabane School Block" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Client Name</label>
                        <input type="text" name="client_name" class="form-control" placeholder="e.g. Ministry of Education" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label">Project Value (E)</label>
                            <input type="number" step="0.01" name="project_value" class="form-control" required>
                        </div>
                        <div class="col-5">
                            <label class="form-label">Year Finished</label>
                            <input type="number" name="completion_year" class="form-control" value="<?= date('Y') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Proof Images / Video</label>
                        <input type="file" name="media_files[]" class="form-control" accept="image/*,video/*" multiple>
                        <div class="form-text">Optional. Upload photos or video proof if you have completed a project; this is not required for new contractors.</div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Project</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Document Upload Modal -->
<div class="modal fade" id="uploadDocumentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Compliance Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="index.php?route=upload-document" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Document Type</label>
                        <select name="document_type" class="form-select" required>
                            <option value="">-- Select Document Type --</option>
                            <optgroup label="Phase 1 Documents">
                                <option value="Certificate of Incorporation">Certificate of Incorporation</option>
                                <option value="Form J & Form C">Form J & Form C</option>
                                <option value="CIC Registration Certificate">CIC Registration Certificate</option>
                                <option value="VAT Registration Certificate">VAT Registration Certificate</option>
                                <option value="Trading Licence">Trading Licence</option>
                            </optgroup>
                            <optgroup label="Phase 2 Documents">
                                <option value="Tax Clearance Certificate">Tax Clearance Certificate (Valid 30 days)</option>
                                <option value="SNPF/ENPF Compliance">SNPF/ENPF Compliance (Valid 90 days)</option>
                                <option value="Police Clearance">Police Clearance (Valid 90 days)</option>
                                <option value="Labour Compliance Certificate">Labour Compliance Certificate</option>
                            </optgroup>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select File(s)</label>
                        <input type="file" name="document[]" class="form-control" accept=".pdf,.jpg,.jpeg,.png" multiple required>
                        <div class="form-text">Accepted formats: PDF, JPG, PNG (Max 5MB). You can select multiple files if necessary (e.g. for Form J & Form C).</div>
                    </div>

                    <div class="alert alert-info small mb-0">
                        <strong>Note:</strong> After uploading, your document will be available for bidding. Some documents have expiry dates, so make sure they are current.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-cloud-upload me-2"></i>Upload Document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Profile -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="post" action="index.php?route=contractor-edit-profile">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editProfileModalLabel">Edit Company Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Company Name</label>
                        <input type="text" name="company_name" class="form-control"
                               value="<?= htmlspecialchars($contractor['company_name']) ?>" required>
                        <div class="form-text text-muted small">Legal registered company name as per registration documents.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">CIC Grade</label>
                        <input type="text" name="cic_grade" class="form-control"
                               value="<?= htmlspecialchars($contractor['cic_grade']) ?>" required>
                        <div class="form-text text-muted small">Your employer grade (e.g. C1, C2, C3, etc.).</div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="local_ownership"
                                   id="localOwnership" <?= $contractor['local_ownership'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="localOwnership">
                                100% Eswatini-owned company (Local SME)
                            </label>
                        </div>
                        <div class="form-text small text-muted">If checked, you may qualify for local preference bonus during evaluation.</div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small">Profile Summary (Optional)</label>
                        <textarea name="profile_summary" class="form-control" rows="4"
                                  placeholder="Brief description of your company's experience and capabilities..."><?= htmlspecialchars($contractor['profile_summary'] ?? '') ?></textarea>
                        <div class="form-text small text-muted">This summary will be visible to evaluators during tender assessment.</div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (isset($_GET['edit'])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var editModal = new bootstrap.Modal(document.getElementById('editProfileModal'));
        editModal.show();
    });
</script>
<?php endif; ?>

