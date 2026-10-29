<div class="row mb-4">
    <div class="col-12">
        <h2>Evaluator Directory</h2>
        <p>Approved evaluators are identified by their system code. Use these codes for secure evaluator assignment instead of email addresses.</p>
        <a href="index.php?route=admin-dashboard" class="btn btn-secondary">Back to Admin Dashboard</a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header">Approved Evaluators</div>
            <div class="card-body">
                <?php if (empty($evaluators)): ?>
                    <div class="alert alert-secondary">No approved evaluators are available yet.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Evaluator Code</th>
                                    <th>Accreditation</th>
                                    <th>Specialization</th>
                                    <th>Institution</th>
                                    <th>Approved On</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($evaluators as $evaluator): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($evaluator['evaluator_code']) ?></strong></td>
                                        <td><?= htmlspecialchars($evaluator['accreditation_number']) ?></td>
                                        <td><?= htmlspecialchars($evaluator['specialization']) ?></td>
                                        <td><?= htmlspecialchars($evaluator['institution']) ?></td>
                                        <td><?= date('d M Y', strtotime($evaluator['created_at'])) ?></td>
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
