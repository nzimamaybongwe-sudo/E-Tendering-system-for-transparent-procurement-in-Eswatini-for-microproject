<div class="page-bg" style="background-image: url('assets/003.jpg');"></div>
<div class="row mb-4">
    <div class="col-12">
        <h2>Pending Evaluator & Administrator Applications</h2>
        <p>Review and approve or reject new evaluator and administrator signups before they can access the system.</p>
        <a href="index.php?route=admin-dashboard" class="btn btn-secondary">Back to Admin Dashboard</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header">Evaluator Applications</div>
            <div class="card-body">
                <?php if (empty($evaluators)): ?>
                    <div class="alert alert-secondary">No pending evaluator applications.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Accreditation</th>
                                    <th>Institution</th>
                                    <th>Submitted</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($evaluators as $app): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($app['full_name'] ?? trim(($app['first_name'] ?? '') . ' ' . ($app['last_name'] ?? '')) ?: ($app['username'] ?? 'N/A')) ?></td>
                                        <td><?= htmlspecialchars($app['email']) ?></td>
                                        <td>
                                            <?php if (!empty($app['accreditation_document'])): ?>
                                                <a href="index.php?route=download-evaluator-doc&id=<?= intval($app['evaluator_profile_id']) ?>" target="_blank" title="View Accreditation Document">
                                                    <?= htmlspecialchars($app['accreditation_number']) ?> <i class="bi bi-file-earmark-pdf text-danger"></i>
                                                </a>
                                            <?php else: ?>
                                                <?= htmlspecialchars($app['accreditation_number']) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($app['institution']) ?></td>
                                        <td><?= date('d M Y', strtotime($app['applied_at'])) ?></td>
                                        <td>
                                            <form method="post" action="index.php?route=process-applicant" class="d-flex gap-2" onsubmit="return confirm('Are you sure you want to approve this evaluator application?');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                                <input type="hidden" name="return_route" value="review-applicants">
                                                <input type="hidden" name="profile_type" value="evaluator">
                                                <input type="hidden" name="profile_id" value="<?= intval($app['evaluator_profile_id']) ?>">
                                                <a href="index.php?route=view-applicant&type=evaluator&id=<?= intval($app['evaluator_profile_id']) ?>" class="btn btn-sm btn-secondary" title="View Details"><i class="bi bi-eye"></i></a>
                                                <input type="text" name="rejection_reason" class="form-control form-control-sm me-2" placeholder="Rejection reason (optional)">
                                                <button type="submit" name="approval_status" value="approved" class="btn btn-sm btn-success">Approve</button>
                                                <button type="submit" name="approval_status" value="rejected" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to reject this evaluator application? Please provide a reason.');">Reject</button>
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

    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header">Administrator Applications</div>
            <div class="card-body">
                <?php if (empty($admins)): ?>
                    <div class="alert alert-secondary">No pending administrator applications.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Government ID</th>
                                    <th>Reference</th>
                                    <th>Submitted</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($admins as $app): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($app['full_name'] ?? trim(($app['first_name'] ?? '') . ' ' . ($app['last_name'] ?? '')) ?: ($app['username'] ?? 'N/A')) ?></td>
                                        <td><?= htmlspecialchars($app['email']) ?></td>
                                        <td><?= htmlspecialchars($app['government_id']) ?></td>
                                        <td><?= htmlspecialchars($app['authorization_reference']) ?></td>
                                        <td><?= date('d M Y', strtotime($app['applied_at'])) ?></td>
                                        <td>
                                            <form method="post" action="index.php?route=process-applicant" class="d-flex gap-2" onsubmit="return confirm('Are you sure you want to approve this administrator application?');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                                <input type="hidden" name="return_route" value="review-applicants">
                                                <input type="hidden" name="profile_type" value="admin">
                                                <input type="hidden" name="profile_id" value="<?= intval($app['admin_profile_id']) ?>">
                                                <a href="index.php?route=view-applicant&type=admin&id=<?= intval($app['admin_profile_id']) ?>" class="btn btn-sm btn-secondary" title="View Details"><i class="bi bi-eye"></i></a>
                                                <input type="text" name="rejection_reason" class="form-control form-control-sm me-2" placeholder="Rejection reason (optional)">
                                                <button type="submit" name="approval_status" value="approved" class="btn btn-sm btn-success">Approve</button>
                                                <button type="submit" name="approval_status" value="rejected" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to reject this administrator application? Please provide a reason.');">Reject</button>
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

    <!-- Procurement Officer Applications -->
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white fw-bold">Procurement Officer Applications</div>
            <div class="card-body">
                <?php if (empty($officers)): ?>
                    <div class="alert alert-secondary">No pending procurement officer applications.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Employee ID</th>
                                    <th>Department</th>
                                    <th>Title</th>
                                    <th>Submitted</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($officers as $app): ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($app['full_name'] ?? $app['username'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($app['email']) ?></td>
                                        <td><?= htmlspecialchars($app['employee_id']) ?></td>
                                        <td><?= htmlspecialchars($app['department']) ?></td>
                                        <td><?= htmlspecialchars($app['title']) ?></td>
                                        <td><?= date('d M Y', strtotime($app['registered_at'])) ?></td>
                                        <td>
                                            <form method="post" action="index.php?route=process-applicant" class="d-flex gap-2" onsubmit="return confirm('Are you sure you want to approve this officer application?');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                                <input type="hidden" name="return_route" value="review-applicants">
                                                <input type="hidden" name="profile_type" value="officer">
                                                <input type="hidden" name="profile_id" value="<?= intval($app['officer_profile_id']) ?>">
                                                <input type="text" name="rejection_reason" class="form-control form-control-sm me-2" placeholder="Rejection reason (optional)">
                                                <button type="submit" name="approval_status" value="approved" class="btn btn-sm btn-success">Approve</button>
                                                <button type="submit" name="approval_status" value="rejected" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to reject this officer application? Please provide a reason.');">Reject</button>
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
