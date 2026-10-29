<div class="row mb-4">
    <div class="col-12">
        <h2>Contractor Blacklist Management</h2>
        <p>Use this page to blacklist high-risk contractors and review all active blacklist entries.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header">Blacklist a Contractor</div>
            <div class="card-body">
                <form method="post" action="index.php?route=blacklist-contractor" onsubmit="return confirm('Are you sure you want to blacklist this contractor? This action cannot be undone.');">
                    <div class="mb-3">
                        <label class="form-label">Contractor</label>
                        <select name="contractor_id" class="form-select" required>
                            <option value="">Select contractor</option>
                            <?php foreach ($approvedContractors as $contractor): ?>
                                <option value="<?= $contractor['contractor_id'] ?>"<?= $selectedContractorId === intval($contractor['contractor_id']) ? ' selected' : '' ?>>
                                    <?= htmlspecialchars($contractor['company_name']) ?> (ID <?= $contractor['contractor_id'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason for Blacklisting</label>
                        <textarea name="blacklist_reason" class="form-control" rows="4" required><?= htmlspecialchars($_POST['blacklist_reason'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger">Blacklist Contractor</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header">Blacklisted Contractors</div>
            <div class="card-body">
                <?php if (empty($blacklisted)): ?>
                    <div class="alert alert-secondary">No contractors are currently blacklisted.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Company</th>
                                    <th>Email</th>
                                    <th>Reason</th>
                                    <th>Blacklisted At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($blacklisted as $contractor): ?>
                                    <tr>
                                        <td><?= $contractor['contractor_id'] ?></td>
                                        <td><?= htmlspecialchars($contractor['company_name']) ?></td>
                                        <td><?= htmlspecialchars($contractor['email']) ?></td>
                                        <td><?= htmlspecialchars($contractor['blacklist_reason']) ?></td>
                                        <td><?= date('d M Y H:i', strtotime($contractor['blacklisted_at'])) ?></td>
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
