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
            <h1 class="display-5 fw-bold mb-3">Register as a Contractor</h1>
            <p class="lead text-muted">This page is only for contractor accounts. Evaluator and administrator signups have their own dedicated registration pages.</p>
        </div>
        <div class="card auth-card">
            <div class="card-header">Contractor Registration</div>
            <div class="card-body">
                <p class="text-muted mb-3">Register your company to bid for Eswatini microprojects.</p>
                <div class="alert alert-info small mb-3">Portfolio proof is optional. New contractors and startups can still register and bid even if they have limited prior experience.</div>
                <div class="alert alert-info">Need evaluator, administrator, or officer access? <a href="index.php?route=register-evaluator">Evaluator sign up</a>, <a href="index.php?route=register-admin">Admin sign up</a>, or <a href="index.php?route=register-officer">Officer sign up</a>.</div>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <form id="registerForm" method="post" action="index.php?route=register" enctype="multipart/form-data" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <div class="js-form-feedback mb-3"></div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control" autocomplete="organization" value="<?= htmlspecialchars($_POST['company_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Trading Email</label>
                            <input type="email" name="email" class="form-control" autocomplete="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control" value="<?= htmlspecialchars($_POST['contact_person'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" autocomplete="new-password" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" autocomplete="new-password" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">CIC Grade</label>
                            <input type="text" name="cic_grade" class="form-control" placeholder="e.g. C3" autocomplete="off" value="<?= htmlspecialchars($_POST['cic_grade'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <?php
                            $selectedCategories = $_POST['category'] ?? [];
                            if (!is_array($selectedCategories)) {
                                $selectedCategories = $selectedCategories === '' ? [] : [$selectedCategories];
                            }
                            $categories = [
                                'Building Construction',
                                'Civil & Infrastructure',
                                'Health Facilities',
                                'Roads & Transport',
                                'Schools & Education',
                                'Electrical Works',
                                'Other',
                            ];
                            ?>
                            <label class="form-label">Category</label>
                            <div class="border rounded p-3 bg-white">
                                <?php foreach ($categories as $categoryOption): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="category[]" value="<?= htmlspecialchars($categoryOption) ?>" id="category-<?= strtolower(str_replace([' ', '&'], ['-', ''], $categoryOption)) ?>" <?= in_array($categoryOption, $selectedCategories, true) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="category-<?= strtolower(str_replace([' ', '&'], ['-', ''], $categoryOption)) ?>">
                                            <?= htmlspecialchars($categoryOption) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="form-text">Choose one or more categories that describe your business.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tax PIN</label>
                            <input type="text" name="tax_pin" class="form-control" value="<?= htmlspecialchars($_POST['tax_pin'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input class="form-check-input" type="checkbox" name="local_ownership" id="localOwnership" <?= isset($_POST['local_ownership']) ? 'checked' : '' ?> >
                        <label class="form-check-label" for="localOwnership">100% Eswatini-owned company</label>
                    </div>

                    <hr class="my-4">
                    <h5 class="mb-3">Required Documents</h5>
                    <div class="alert alert-warning small">Please upload the following required documents in PDF, JPG, or PNG format.</div>

                    <div class="row g-4 mb-4">
                        <!-- Left Column -->
                        <div class="col-md-6 d-flex flex-column gap-3">
                            <div>
                                <label class="form-label">Certificate of Incorporation</label>
                                <input type="file" name="cert_incorporation" class="form-control" required>
                            </div>
                            <div>
                                <label class="form-label">CIC Registration Certificate</label>
                                <input type="file" name="cic_registration" class="form-control" required>
                            </div>
                            <div>
                                <label class="form-label">Trading Licence</label>
                                <input type="file" name="trading_licence" class="form-control" required>
                            </div>
                            <div>
                                <label class="form-label">SNPF/ENPF Compliance</label>
                                <input type="file" name="enpf_compliance" class="form-control" required>
                            </div>
                            <div>
                                <label class="form-label">Valid Labour Compliance Certificate</label>
                                <input type="file" name="labour_compliance" class="form-control" required>
                            </div>
                        </div>
                        <!-- Right Column -->
                        <div class="col-md-6 d-flex flex-column gap-3">
                            <div>
                                <label class="form-label">Form J & Form C (60% ownership verification)</label>
                                <input type="file" name="form_j_c[]" class="form-control" multiple required>
                                <div class="form-text x-small">Select multiple files if Form J and Form C are separate documents.</div>
                            </div>
                            <div>
                                <label class="form-label">VAT Registration Certificate</label>
                                <input type="file" name="vat_registration" class="form-control" required>
                            </div>
                            <div>
                                <label class="form-label">Tax Clearance Certificate</label>
                                <input type="file" name="tax_clearance" class="form-control" required>
                            </div>
                            <div>
                                <label class="form-label">Police Clearance</label>
                                <input type="file" name="police_clearance" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Submit Registration</button>
                </form>
            </div>
        </div>
    </div>
</div>
