<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MicroProjects Eswatini — Transparent, digital e-tendering for community construction and infrastructure projects in Eswatini.">
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token()) ?>">
    <title><?= htmlspecialchars($config['site_name'] ?? 'E-Tendering') ?> — MicroProjects Eswatini</title>
    <?php $assetVersion = time(); ?>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- App Styles -->
    <link rel="stylesheet" href="assets/styles.css?v=<?= $assetVersion ?>">
</head>
<body>

<nav class="navbar navbar-expand-lg site-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="/E-Tendering system project/public/assets/logo.jpg" alt="MicroProjects Eswatini Logo" class="navbar-logo">
            <span class="navbar-brand-text">MicroProjects Eswatini</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu"
                aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item">
                    <a class="nav-link" href="index.php"><?= t('Home') ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?route=tenders"><?= t('Available Tenders') ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?route=public-stats"><?= t('Public Statistics') ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?route=help">Help</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?route=guidance">Guidance</a>
                </li>



                <?php if (empty($_SESSION['user'])): ?>
                    <li class="nav-item ms-lg-2">
                        <a class="nav-link" href="index.php?route=login">
                            <i class="bi bi-box-arrow-in-right me-1"></i><?= t('Login') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-secondary btn-sm px-3" href="index.php?route=register">
                            <i class="bi bi-person-plus me-1"></i><?= t('Register') ?>
                        </a>
                    </li>
                <?php else: ?>
                    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                Admin
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="index.php?route=admin-dashboard">Dashboard</a></li>
                                <li><a class="dropdown-item" href="index.php?route=review-applicants">Review Applicants</a></li>
                                <li><a class="dropdown-item" href="index.php?route=manage-appeals">Appeals</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="index.php?route=audit-log">Audit Log</a></li>
                                <li><a class="dropdown-item" href="index.php?route=contractor-blacklist">Blacklist</a></li>
                            </ul>
                        </li>
                    <?php elseif ($_SESSION['user']['role'] === 'contractor'): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                My Account
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="index.php?route=contractor-dashboard">Dashboard</a></li>
                                <li><a class="dropdown-item" href="index.php?route=contractor-profile">My Profile</a></li>
                                <li><a class="dropdown-item" href="index.php?route=contractor-profile&edit=1">Edit Profile</a></li>
                                <li><a class="dropdown-item" href="index.php?route=my-appeals">My Appeals</a></li>
                            </ul>
                        </li>
                    <?php elseif ($_SESSION['user']['role'] === 'evaluator'): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                Evaluator
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="index.php?route=evaluator-dashboard">Dashboard</a></li>
                                <li><a class="dropdown-item" href="index.php?route=propose-rubric">Propose Rubric</a></li>
                            </ul>
                        </li>
                    <?php elseif ($_SESSION['user']['role'] === 'officer'): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                Procurement Officer
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="index.php?route=officer-dashboard">Dashboard</a></li>
                                <li><a class="dropdown-item" href="index.php?route=publish-tender">Create Tender</a></li>
                                <li><a class="dropdown-item" href="index.php?route=manage-site-visits">Manage Site Visits</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <!-- Notifications -->
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?route=notifications">
                            <i class="bi bi-bell me-1"></i>
                            <?php if (!empty($unreadNotificationsCount)): ?>
                                <span class="notif-badge"><?= intval($unreadNotificationsCount) ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <!-- User / Logout -->
                    <li class="nav-item ms-lg-1">
                        <a class="nav-link" href="index.php?route=logout">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-4 mb-2">
