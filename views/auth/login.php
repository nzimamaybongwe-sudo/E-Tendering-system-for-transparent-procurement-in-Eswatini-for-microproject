<div class="page-bg" style="background-image: url('assets/003.png');"></div>
 <div class="row justify-content-center auth-page">
    <div class="col-lg-6">
        <div class="auth-hero">
            <div class="text-center mb-4">
                <img src="/E-Tendering system project/public/assets/logo.jpg" alt="MicroProjects Eswatini Logo" class="img-fluid mb-3" style="max-height: 100px; width: auto; border-radius: 50%; object-fit: cover;">
                <div style="padding: 8px 20px;">
                    <span style="color: #000000; font-size: 1.1rem; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">MicroProjects Eswatini</span>
                </div>
            </div>
            <h1 class="display-5 fw-bold mb-3">Hi, Welcome Back</h1>
            <p class="lead text-muted">Sign in to manage bids, review tenders, or evaluate procurement with full transparency.</p>
        </div>
        <div class="card auth-card">
            <div class="card-header">Login</div>
            <div class="card-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form id="loginForm" method="post" action="index.php?route=login" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <div class="js-form-feedback mb-3"></div>
                    <div class="mb-3">
                        <label class="form-label">Email address or Username</label>
                        <input type="text" name="email" class="form-control" placeholder="Email or username" autocomplete="off" autocapitalize="none" spellcheck="false" data-lpignore="true" data-1p-ignore required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" autocomplete="new-password" data-lpignore="true" data-1p-ignore required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mb-2">Login</button>
                    <div class="text-center mb-3">
                        <a href="index.php?route=forgot-password" class="text-muted small">Forgot your password?</a>
                    </div>
                    <div class="text-center mt-3">
                        <p class="text-muted mb-2">Don't have an account? Register as:</p>
                        <div>
                            <a href="index.php?route=register" class="text-decoration-none me-3"><strong>Contractor</strong></a>
                            <a href="index.php?route=register-evaluator" class="text-decoration-none me-3"><strong>Evaluator</strong></a>
                            <a href="index.php?route=register-officer" class="text-decoration-none me-3"><strong>Officer</strong></a>
                            <a href="index.php?route=register-admin" class="text-decoration-none"><strong>Administrator</strong></a>
                        </div>
                    </div>
                    <div class="text-center text-muted small mt-2">
                        Evaluator, Procurement Officer, and administrator registration each have their own form with the required professional documents and verification codes.
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
