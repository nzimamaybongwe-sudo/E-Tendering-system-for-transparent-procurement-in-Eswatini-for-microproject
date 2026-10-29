<div class="row mb-4">
    <div class="col-12">
        <h2>Notifications</h2>
        <p>Here are your latest system notifications. Notifications help you track bid submissions, evaluations, and award decisions.</p>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <?php if (empty($notifications)): ?>
            <div class="alert alert-secondary">No notifications available.</div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($notifications as $note): ?>
                    <div class="list-group-item<?= $note['read_at'] === null ? ' list-group-item-warning' : '' ?>">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="mb-1"><?= htmlspecialchars($note['message']) ?></p>
                                <small class="text-muted">Sent: <?= date('d M Y H:i', strtotime($note['created_at'])) ?></small>
                            </div>
                            <?php if ($note['read_at'] === null): ?>
                                <span class="badge bg-primary">Unread</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Read</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
