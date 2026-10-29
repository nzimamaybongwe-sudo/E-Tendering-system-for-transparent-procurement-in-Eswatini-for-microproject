<div class="page-bg" style="background-image: url('assets/001.png');"></div>
 <div class="row justify-content-center auth-page">
    <div class="col-lg-7">
        <div class="auth-hero">
            <div class="text-center mb-4">
                <img src="/E-Tendering system project/public/assets/logo.jpg" alt="MicroProjects Eswatini Logo" class="img-fluid mb-3" style="max-height: 100px; width: auto; border-radius: 50%; object-fit: cover;">
                <div style="padding: 8px 20px;">
                    <span style="color: #000000; font-size: 1.1rem; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">MicroProjects Eswatini</span>
                </div>
            </div>
            <h1 class="display-5 fw-bold mb-3">Register as an Administrator</h1>
            <p class="lead text-muted">Provide your official identity and authorization reference for administrator access.</p>
        </div>
        <div class="card auth-card">
            <div class="card-header">Administrator Registration</div>
            <div class="card-body">
                <p class="text-muted mb-3">Administrator sign-up requires a valid admin registration code and formal authorization details.</p>
                <div class="alert alert-info">If you are a contractor, please <a href="index.php?route=register">use the contractor page</a>. Evaluators should <a href="index.php?route=register-evaluator">use the evaluator page</a>.</div>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <form id="adminRegisterForm" method="post" action="index.php?route=register-admin" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <div class="js-form-feedback mb-3"></div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" autocomplete="name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Government ID Number</label>
                            <input type="text" name="government_id" class="form-control" autocomplete="off" value="<?= htmlspecialchars($_POST['government_id'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" autocomplete="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Administrator Registration Code</label>
                            <input type="password" name="role_passcode" class="form-control" autocomplete="new-password" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" autocomplete="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" autocomplete="new-password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" autocomplete="new-password" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Authorization Reference</label>
                            <input type="text" name="authorization_reference" class="form-control" autocomplete="off" value="<?= htmlspecialchars($_POST['authorization_reference'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Office Phone</label>
                            <input type="tel" name="office_phone" class="form-control" autocomplete="tel" value="<?= htmlspecialchars($_POST['office_phone'] ?? '') ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Submit Administrator Registration</button>
                </form>
            </div>
        </div>
    </div>
</div>
