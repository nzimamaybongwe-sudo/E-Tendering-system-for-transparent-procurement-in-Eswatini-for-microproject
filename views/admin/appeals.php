<div class="page-bg" style="background-image: url('assets/003.jpg');"></div>
<div class="row mb-4">
    <div class="col-12">
        <h2>Appeals and Grievances</h2>
        <p>Review contractor appeals and respond where appropriate.</p>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <?php if (empty($appeals)): ?>
            <div class="alert alert-secondary">No appeals have been submitted.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Submitted</th>
                            <th>Contractor</th>
                            <th>Tender</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appeals as $appeal): ?>
                            <tr>
                                <td><?= date('d M Y H:i', strtotime($appeal['created_at'])) ?></td>
                                <td><?= htmlspecialchars($appeal['contractor_name']) ?> (<?= htmlspecialchars($appeal['contractor_email']) ?>)</td>
                                <td><?= htmlspecialchars($appeal['title']) ?></td>
                                <td><?= htmlspecialchars($appeal['subject']) ?></td>
                                <td><?= htmlspecialchars($appeal['status']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#appeal-<?= intval($appeal['appeal_id']) ?>">Respond</button>
                                </td>
                            </tr>
                            <tr class="collapse" id="appeal-<?= intval($appeal['appeal_id']) ?>">
                                <td colspan="6">
                                    <div class="card card-body">
                                        <p><strong>Appeal Reason:</strong> <?= nl2br(htmlspecialchars($appeal['appeal_reason'])) ?></p>
                                        <?php if (!empty($appeal['admin_response'])): ?>
                                            <p><strong>Admin Response:</strong> <?= nl2br(htmlspecialchars($appeal['admin_response'])) ?></p>
                                        <?php endif; ?>
                                        <form method="post" action="index.php?route=respond-appeal" onsubmit="return confirm('Are you sure you want to save this response to the appeal?');">
                                            <input type="hidden" name="appeal_id" value="<?= intval($appeal['appeal_id']) ?>">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="under_review"<?= $appeal['status'] === 'under_review' ? ' selected' : '' ?>>Under Review</option>
                                                        <option value="resolved"<?= $appeal['status'] === 'resolved' ? ' selected' : '' ?>>Resolved</option>
                                                        <option value="rejected"<?= $appeal['status'] === 'rejected' ? ' selected' : '' ?>>Rejected</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-8">
                                                    <label class="form-label">Response</label>
                                                    <textarea name="admin_response" class="form-control" rows="3"><?= htmlspecialchars($appeal['admin_response'] ?? '') ?></textarea>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-success mt-3">Save Response</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
