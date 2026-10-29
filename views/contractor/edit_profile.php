<div class="row mb-4">
    <div class="col-12">
        <h1 class="fw-bold">Edit Company Profile</h1>
        <p class="text-muted">Update your company details. Changes will be notified to administrators for review.</p>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-primary text-white fw-bold py-3">Company Information</div>
            <div class="card-body p-4">
                <form method="post" action="index.php?route=contractor-edit-profile">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

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

                    <div class="mb-4">
                        <label class="form-label fw-bold small">Profile Summary (Optional)</label>
                        <textarea name="profile_summary" class="form-control" rows="4"
                                  placeholder="Brief description of your company's experience and capabilities..."><?= htmlspecialchars($contractor['profile_summary'] ?? '') ?></textarea>
                        <div class="form-text small text-muted">This summary will be visible to evaluators during tender assessment.</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                        <a href="index.php?route=contractor-profile" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-dark text-white fw-bold py-3">Current Details</div>
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
                    <label class="small text-muted d-block">Local Ownership</label>
                    <span class="badge <?= $contractor['local_ownership'] ? 'bg-success' : 'bg-secondary' ?>">
                        <?= $contractor['local_ownership'] ? '100% Eswatini-owned' : 'Not locally owned' ?>
                    </span>
                </div>
                <?php if (!empty($contractor['profile_summary'])): ?>
                    <div>
                        <label class="small text-muted d-block">Current Profile Summary</label>
                        <p class="small"><?= nl2br(htmlspecialchars($contractor['profile_summary'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="alert alert-info small">
            <i class="bi bi-info-circle me-1"></i>
            <strong>Note:</strong> After submitting changes, administrators will be notified and may review your updated profile. Certain fields like Tax PIN and Form J require admin assistance to modify — please contact support for those changes.
        </div>
    </div>
</div>
