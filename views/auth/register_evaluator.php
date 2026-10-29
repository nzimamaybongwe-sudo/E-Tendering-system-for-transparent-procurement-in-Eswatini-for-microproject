<div class="page-bg" style="background-image: url('assets/001.png');"></div>
 <div class="row justify-content-center auth-page">
    <div class="col-lg-7">
        <div class="auth-hero">
            <div class="text-center mb-4">
                <img src="assets/logo.jpg" alt="MicroProjects Eswatini Logo" class="img-fluid mb-3" style="max-height: 100px; width: auto; border-radius: 50%; object-fit: cover;">
                <div style="padding: 8px 20px;">
                    <span style="color: #000000; font-size: 1.1rem; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">MicroProjects Eswatini</span>
                </div>
            </div>
            <h1 class="display-5 fw-bold mb-3">Register as an Evaluator</h1>
            <p class="lead text-muted">Submit your accreditation and expertise to join the procurement evaluation team.</p>
        </div>
        <div class="card auth-card">
            <div class="card-header">Evaluator Registration</div>
            <div class="card-body">
                <p class="text-muted mb-3">Evaluator sign-up requires a valid evaluator registration code and professional accreditation details.</p>
                <div class="alert alert-info">Already a contractor? <a href="index.php?route=register">Use contractor registration</a>. For administrator registration, <a href="index.php?route=register-admin">use the admin page</a>.</div>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <form id="evaluatorRegisterForm" method="post" action="index.php?route=register-evaluator" enctype="multipart/form-data" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <div class="js-form-feedback mb-3"></div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" autocomplete="name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" autocomplete="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
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
                            <label class="form-label">Accreditation Number</label>
                            <input type="text" name="accreditation_number" class="form-control" autocomplete="off" value="<?= htmlspecialchars($_POST['accreditation_number'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Evaluator Registration Code</label>
                            <input type="password" name="role_passcode" class="form-control" autocomplete="new-password" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Institution / Agency</label>
                            <input type="text" name="institution" class="form-control" value="<?= htmlspecialchars($_POST['institution'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Specialization</label>
                            <input type="text" name="specialization" class="form-control" value="<?= htmlspecialchars($_POST['specialization'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Accreditation Document (PDF, JPEG, PNG)</label>
                        <input type="file" name="accreditation_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        <div class="form-text text-muted">Please upload your accreditation/certification document (Max size: 5MB).</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Evaluation Experience Summary</label>
                        <textarea name="experience_summary" class="form-control" rows="4"><?= htmlspecialchars($_POST['experience_summary'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Submit Evaluator Registration</button>
                </form>
            </div>
        </div>
    </div>
</div>
