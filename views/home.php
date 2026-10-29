<?php
// home.php - main landing page for E‑Tendering System (partial view – included by BaseController)
?>

<!-- ── Same blurred background as all other pages ─────────── -->
<div class="page-bg" style="background-image: url('assets/003.png');"></div>

<!-- ── Hero Banner ─────────────────────────────────────────── -->
<div class="home-hero-wrap fade-in-up">
    <div class="home-hero-logo-wrap">
        <img src="/E-Tendering system project/public/assets/logo.jpg"
             alt="MicroProjects Eswatini Logo"
             class="home-hero-logo">
    </div>

    <div class="home-hero-banner">
        <p class="home-hero-org">MICROPROJECTS ESWATINI</p>
        <h1 class="home-hero-title">Welcome to MicroProjects Eswatini</h1>
        <p class="home-hero-sub">Transparent procurement for Eswatini's community construction and infrastructure projects.</p>
    </div>
</div>

<!-- ── Hero Thumbnails Row ─────────────────────────────────── -->
<div class="row g-3 mb-4 fade-in-up" style="animation-delay:0.1s;">
    <div class="col-12 col-md-4">
        <img src="/E-Tendering system project/public/assets/002.png" alt="MicroProjects Image 002" class="home-pic w-100">
    </div>
    <div class="col-12 col-md-4">
        <img src="/E-Tendering system project/public/assets/02.jpg" alt="MicroProjects Image 02" class="home-pic w-100">
    </div>
    <div class="col-12 col-md-4">
        <img src="/E-Tendering system project/public/assets/04.jpg" alt="MicroProjects Image 04" class="home-pic w-100">
    </div>
</div>

<!-- ── Stats Info Strip ────────────────────────────────── -->
<div class="home-stats-strip fade-in-up" style="animation-delay:0.1s;">
    <i class="bi bi-info-circle me-2"></i>
    For full procurement metrics, including published tender totals, awarded value and category breakdown, visit the
    <a href="index.php?route=public-stats" class="home-stats-link">Public Tender Statistics</a> page.
</div>

<!-- ── Info Cards Row ──────────────────────────────────────── -->
<div class="row g-3 mb-5 fade-in-up" style="animation-delay:0.15s;">
    <!-- What is a MicroProject? -->
    <div class="col-md-6">
        <div class="home-info-card h-100">
            <div class="home-info-card-header">
                <i class="bi bi-building me-2"></i>What is a MicroProject?
            </div>
            <div class="home-info-card-body">
                <p>This platform supports small-scale construction projects in Eswatini such as schools, clinics, water systems and community facilities. It brings the paper-based procurement workflow into a transparent digital system.</p>
                <ul class="home-feature-list">
                    <li><i class="bi bi-dot fs-5 text-primary"></i>Online contractor registration</li>
                    <li><i class="bi bi-dot fs-5 text-primary"></i>Document verification</li>
                    <li><i class="bi bi-dot fs-5 text-primary"></i>Bid submission with deadline enforcement</li>
                    <li><i class="bi bi-dot fs-5 text-primary"></i>Evaluator scoring and audit logs</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- How the System Works -->
    <div class="col-md-6">
        <div class="home-info-card h-100">
            <div class="home-info-card-header">
                <i class="bi bi-diagram-3 me-2"></i>How the System Works
            </div>
            <div class="home-info-card-body">
                <ol class="home-steps-list">
                    <li>Guest views tenders and winners.</li>
                    <li>Contractors register and upload credentials.</li>
                    <li>Admin verifies companies and publishes tenders.</li>
                    <li>Contractors submit bids before closing.</li>
                    <li>Evaluators score and award the winning bid.</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- ── Recent Winners Section ────────────────────────────────── -->
<?php if (!empty($winners)): ?>
<div class="row fade-in-up" style="animation-delay:0.2s;">
    <div class="col-12">
        <div class="card shadow-sm border-0 bg-glass mb-5">
            <div class="card-header border-bottom border-secondary d-flex align-items-center bg-transparent py-3">
                <i class="bi bi-trophy text-warning fs-4 me-2"></i>
                <h4 class="mb-0 text-white">Recently Awarded Tenders</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-borderless table-glass mb-0 text-white">
                        <thead class="border-bottom border-secondary">
                            <tr>
                                <th class="px-4 py-3">Tender Title</th>
                                <th class="py-3">Winning Contractor</th>
                                <th class="py-3">Score</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($winners as $w): ?>
                            <tr>
                                <td class="px-4 py-3 fw-bold"><?= htmlspecialchars($w['title']) ?></td>
                                <td class="py-3">
                                    <span class="badge bg-success bg-opacity-75 fs-6 px-3 py-2 rounded-pill shadow-sm">
                                        <i class="bi bi-building me-1"></i> <?= htmlspecialchars($w['company_name']) ?>
                                    </span>
                                </td>
                                <td class="py-3 text-warning fw-bold"><?= number_format($w['total_score'], 2) ?>%</td>
                                <td class="px-4 py-3"><span class="badge bg-primary text-uppercase"><?= htmlspecialchars($w['status']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
