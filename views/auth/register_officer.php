<div class="page-bg" style="background-image: url('assets/003.jpg');"></div>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="mb-4 text-center">
                <img src="/E-Tendering system project/public/assets/logo.jpg" alt="Logo" style="height: 60px; width: auto; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                <h1 class="display-6 fw-bold mt-3 text-dark">Register as Procurement Officer</h1>
                <p class="lead text-muted small">Submit your employee credentials to join the MicroProjects procurement department.</p>
            </div>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-dark text-white text-center fw-bold py-3">Officer Registration Form</div>
                <div class="card-body p-4 bg-white">
                    <p class="text-muted small mb-4">
                        <i class="bi bi-info-circle me-1 text-primary"></i> Officer signup requires a valid department registration code and official employee details.
                    </p>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger border-0 rounded-3 py-2 small mb-3">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success border-0 rounded-3 py-2 small mb-3">
                            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <form id="officerRegisterForm" method="post" action="index.php?route=register-officer" autocomplete="new-password" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

                        <!-- Section: Credentials -->
                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Account Credentials</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Username</label>
                                <input type="text" name="username" class="form-control form-control-sm rounded-pill px-3" required placeholder="e.g. johnm" autocomplete="off" autocapitalize="none" spellcheck="false" data-lpignore="true" data-1p-ignore>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Email Address</label>
                                <input type="email" name="email" class="form-control form-control-sm rounded-pill px-3" required placeholder="john.mamba@microprojects.org.sz" autocomplete="off" autocapitalize="none" spellcheck="false" data-lpignore="true" data-1p-ignore>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Password</label>
                                <input type="password" name="password" class="form-control form-control-sm rounded-pill px-3" required placeholder="••••••••" autocomplete="new-password" data-lpignore="true" data-1p-ignore>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control form-control-sm rounded-pill px-3" required placeholder="••••••••" autocomplete="new-password" data-lpignore="true" data-1p-ignore>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-danger">Procurement Registration Passcode</label>
                                <input type="password" name="role_passcode" class="form-control form-control-sm rounded-pill px-3 border-danger" required placeholder="Enter the code provided by the administrator" autocomplete="new-password" data-lpignore="true" data-1p-ignore>
                            </div>
                        </div>

                        <!-- Section: Work Details -->
                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Employee Details</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Full Name</label>
                                <input type="text" name="full_name" class="form-control form-control-sm rounded-pill px-3" required placeholder="e.g. John Mamba" autocomplete="off" autocapitalize="none" spellcheck="false" data-lpignore="true" data-1p-ignore>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Employee ID / Code</label>
                                <input type="text" name="employee_id" class="form-control form-control-sm rounded-pill px-3" required placeholder="e.g. OFF-001" autocomplete="off" autocapitalize="none" spellcheck="false" data-lpignore="true" data-1p-ignore>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Department</label>
                                <input type="text" name="department" class="form-control form-control-sm rounded-pill px-3" required placeholder="e.g. Procurement" autocomplete="off" autocapitalize="none" spellcheck="false" data-lpignore="true" data-1p-ignore>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Job Title</label>
                                <input type="text" name="title" class="form-control form-control-sm rounded-pill px-3" required placeholder="e.g. Senior Procurement Officer" autocomplete="off" autocapitalize="none" spellcheck="false" data-lpignore="true" data-1p-ignore>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-success btn-sm rounded-pill py-2 fw-bold">Submit Officer Registration</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="text-center mt-3">
                <p class="small text-muted">Already have an account? <a href="index.php?route=login" class="text-decoration-none fw-bold text-primary">Login here</a></p>
            </div>
        </div>
    </div>
</div>
