<div class="page-bg" style="background-image: url('assets/003.jpg');"></div>
<div class="row mb-4">
    <div class="col-12">
        <h2>My Appeals</h2>
        <p>Track your submitted grievances and review decisions.</p>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <?php if (empty($appeals)): ?>
            <div class="alert alert-secondary">You have not submitted any appeals.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Tender</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Response</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appeals as $appeal): ?>
                            <tr>
                                <td><?= date('d M Y H:i', strtotime($appeal['created_at'])) ?></td>
                                <td><?= htmlspecialchars($appeal['title']) ?></td>
                                <td><?= htmlspecialchars($appeal['subject']) ?></td>
                                <td><?= htmlspecialchars($appeal['status']) ?></td>
                                <td><?= nl2br(htmlspecialchars($appeal['admin_response'] ?? 'Awaiting response')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
