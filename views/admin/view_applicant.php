<div class="page-bg" style="background-image: url('assets/003.jpg');"></div>
<div class="row mb-4">
    <div class="col-12">
        <a href="javascript:history.back()" class="btn btn-secondary mb-3"><i class="bi bi-arrow-left"></i> Back</a>
        <h2>Applicant Details</h2>
        <p>Review the application information and submitted documents. (Read-only view)</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white fw-bold">
                Profile Information: <?= ucfirst(htmlspecialchars($profileType)) ?>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tbody>
                        <?php if ($profileType === 'contractor'): ?>
                            <tr><th>Company Name</th><td><?= htmlspecialchars($profile['company_name']) ?></td></tr>
                            <tr><th>Email</th><td><?= htmlspecialchars($profile['email']) ?></td></tr>
                            <tr><th>CIC Grade</th><td><?= htmlspecialchars($profile['cic_grade']) ?></td></tr>
                            <tr><th>Contact Person</th><td><?= htmlspecialchars($profile['contact_person'] ?? 'N/A') ?></td></tr>
                            <tr><th>Phone</th><td><?= htmlspecialchars($profile['phone'] ?? 'N/A') ?></td></tr>
                            <tr><th>Address</th><td><?= htmlspecialchars($profile['address'] ?? 'N/A') ?></td></tr>
                            <tr><th>Status</th><td>
                                <span class="badge <?= $profile['verification_status'] === 'approved' ? 'bg-success' : ($profile['verification_status'] === 'rejected' ? 'bg-danger' : 'bg-warning') ?>">
                                    <?= htmlspecialchars(ucfirst($profile['verification_status'])) ?>
                                </span>
                            </td></tr>
                        <?php elseif ($profileType === 'evaluator'): ?>
                            <tr><th>Full Name</th><td><?= htmlspecialchars($profile['full_name'] ?? trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? '')) ?: ($profile['username'] ?? 'N/A')) ?></td></tr>
                            <tr><th>Email</th><td><?= htmlspecialchars($profile['email']) ?></td></tr>
                            <tr><th>Accreditation Number</th><td><?= htmlspecialchars($profile['accreditation_number']) ?></td></tr>
                            <tr><th>Institution</th><td><?= htmlspecialchars($profile['institution']) ?></td></tr>
                            <tr><th>Specialization</th><td><?= htmlspecialchars($profile['specialization']) ?></td></tr>
                            <tr><th>Status</th><td>
                                <span class="badge <?= $profile['approval_status'] === 'approved' ? 'bg-success' : ($profile['approval_status'] === 'rejected' ? 'bg-danger' : 'bg-warning') ?>">
                                    <?= htmlspecialchars(ucfirst($profile['approval_status'])) ?>
                                </span>
                            </td></tr>
                        <?php elseif ($profileType === 'admin'): ?>
                            <tr><th>Full Name</th><td><?= htmlspecialchars($profile['full_name'] ?? trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? '')) ?: ($profile['username'] ?? 'N/A')) ?></td></tr>
                            <tr><th>Email</th><td><?= htmlspecialchars($profile['email']) ?></td></tr>
                            <tr><th>Government ID</th><td><?= htmlspecialchars($profile['government_id']) ?></td></tr>
                            <tr><th>Department</th><td><?= htmlspecialchars($profile['department'] ?? 'N/A') ?></td></tr>
                            <tr><th>Authorization Reference</th><td><?= htmlspecialchars($profile['authorization_reference']) ?></td></tr>
                            <tr><th>Status</th><td>
                                <span class="badge <?= $profile['approval_status'] === 'approved' ? 'bg-success' : ($profile['approval_status'] === 'rejected' ? 'bg-danger' : 'bg-warning') ?>">
                                    <?= htmlspecialchars(ucfirst($profile['approval_status'])) ?>
                                </span>
                            </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <!-- If pending, show action buttons -->
                <?php 
                $status = $profileType === 'contractor' ? $profile['verification_status'] : $profile['approval_status'];
                $idField = $profileType === 'contractor' ? 'contractor_id' : ($profileType === 'evaluator' ? 'evaluator_profile_id' : 'admin_profile_id');
                if ($status === 'pending'): 
                ?>
                    <hr>
                    <form method="post" action="index.php?route=process-applicant" class="mt-3">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <input type="hidden" name="return_route" value="<?= $profileType === 'contractor' ? 'admin-dashboard' : 'review-applicants' ?>">
                        <input type="hidden" name="profile_type" value="<?= $profileType ?>">
                        <input type="hidden" name="profile_id" value="<?= $profile[$idField] ?>">
                        <div class="mb-3">
                            <label class="form-label">Rejection Reason (Optional)</label>
                            <input type="text" name="rejection_reason" class="form-control" placeholder="Only required if rejecting">
                        </div>
                        <button type="submit" name="approval_status" value="approved" class="btn btn-success me-2" onclick="return confirm('Approve this application?');"><i class="bi bi-check-circle"></i> Approve</button>
                        <button type="submit" name="approval_status" value="rejected" class="btn btn-danger" onclick="return confirm('Reject this application?');"><i class="bi bi-x-circle"></i> Reject</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <?php if ($profileType === 'contractor'): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white fw-bold">
                    Uploaded Documents
                </div>
                <div class="card-body">
                    <?php if (empty($documents)): ?>
                        <div class="alert alert-info">No documents have been uploaded by this contractor yet.</div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($documents as $doc): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($doc['document_name']) ?></strong><br>
                                        <small class="text-muted">Uploaded: <?= date('d M Y, H:i', strtotime($doc['uploaded_at'])) ?></small>
                                        <?php if (!empty($doc['expiry_date'])): ?>
                                            <br><small class="<?= strtotime($doc['expiry_date']) < time() ? 'text-danger fw-bold' : 'text-success' ?>">
                                                Expires: <?= date('d M Y', strtotime($doc['expiry_date'])) ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <a href="index.php?route=download-contractor-doc&id=<?= $doc['document_id'] ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($profileType === 'evaluator'): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white fw-bold">
                    Accreditation Document
                </div>
                <div class="card-body">
                    <?php if (empty($profile['accreditation_document'])): ?>
                        <div class="alert alert-warning">No accreditation document has been uploaded for this evaluator application. (Legacy record)</div>
                    <?php else: ?>
                        <div class="list-group">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Accreditation Document</strong><br>
                                    <small class="text-muted">Submitted as part of registration</small>
                                </div>
                                <a href="index.php?route=download-evaluator-doc&id=<?= $profile['evaluator_profile_id'] ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-secondary">
                No external documents are required for <?= htmlspecialchars($profileType) ?> profiles.
            </div>
        <?php endif; ?>
    </div>
</div>
