<div class="row justify-content-center auth-page">
    <div class="col-lg-5">
        <div class="auth-hero text-center mb-4">
            <img src="/E-Tendering system project/public/assets/logo.jpg" alt="Logo" class="img-fluid mb-3" style="max-height:80px;border-radius:50%;object-fit:cover;">
            <h1 class="h3 fw-bold">Forgot Password</h1>
            <p class="text-muted">Enter your account email and we'll send you a reset link.</p>
        </div>
        <div class="card auth-card">
            <div class="card-body p-4">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="post" action="index.php?route=forgot-password">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="your@email.com" required autofocus>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                </form>
                <div class="text-muted small mt-3">
                    Note: On local development, password reset delivery depends on PHP mail configuration. If you do not have SMTP/sendmail configured, the message may not actually be delivered.
                </div>
                <div class="text-center mt-3">
                    <a href="index.php?route=login" class="text-muted small">Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
