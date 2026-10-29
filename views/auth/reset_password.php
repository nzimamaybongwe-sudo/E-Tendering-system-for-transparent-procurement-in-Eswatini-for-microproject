<div class="row justify-content-center auth-page">
    <div class="col-lg-5">
        <div class="auth-hero text-center mb-4">
            <img src="assets/logo.jpg" alt="Logo" class="img-fluid mb-3" style="max-height:80px;border-radius:50%;object-fit:cover;">
            <h1 class="h3 fw-bold">Reset Your Password</h1>
        </div>
        <div class="card auth-card">
            <div class="card-body p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?>
                        <div class="mt-2"><a href="index.php?route=login" class="btn btn-sm btn-success">Go to Login</a></div>
                    </div>
                <?php else: ?>
                    <?php if (empty($error) || strpos($error, 'invalid') === false): ?>
                    <form method="post" action="index.php?route=reset-password&token=<?= htmlspecialchars($token) ?>" autocomplete="off">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" autocomplete="new-password" minlength="8" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" autocomplete="new-password" minlength="8" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Set New Password</button>
                    </form>
                    <?php endif; ?>
                <?php endif; ?>
                <div class="text-center mt-3">
                    <a href="index.php?route=login" class="text-muted small">Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
