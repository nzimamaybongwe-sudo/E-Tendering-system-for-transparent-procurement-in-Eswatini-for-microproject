<div class="page-bg" style="background-image: url('assets/003.jpg');"></div>
<?php
// Professional Admin Dashboard
// Clean, modern design with proper information hierarchy
?>
<style>
    :root {
        --primary: #0b5ed7; /* Microproject blue */
        --primary-dark: #094a9b;
        --accent-yellow: #fbbf24; /* Microproject yellow */
        --success: #059669;
        --warning: var(--accent-yellow);
        --danger: #dc2626;
        --info: #0891b2;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --gray-900: #111827;
    }

    .dashboard-wrapper {
        background: rgba(249, 250, 251, 0.8) !important;
        backdrop-filter: blur(8px);
        border-radius: 15px;
        padding-top: 10px;
        padding-bottom: 20px;
        min-height: 100vh;
    }

    /* Header Section */
    .dashboard-header {
        background: white;
        border-bottom: 1px solid var(--gray-200);
        padding: 2rem 0;
        margin-bottom: 2rem;
    }

    .dashboard-title {
        font-size: 2rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 0.5rem;
    }

    .dashboard-subtitle {
        font-size: 0.95rem;
        color: var(--gray-600);
    }

    .header-actions {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    /* KPI Cards */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .kpi-card {
        background: white;
        border-radius: 8px;
        padding: 1rem 1.25rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        border-left: 4px solid var(--gray-200);
    }

    .kpi-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .kpi-card.primary { border-left-color: var(--primary); }
    .kpi-card.success { border-left-color: var(--success); }
    .kpi-card.warning { border-left-color: var(--warning); }
    .kpi-card.danger { border-left-color: var(--danger); }

    .kpi-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--gray-700);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .kpi-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--gray-900);
        line-height: 1.2;
    }

    .kpi-meta {
        font-size: 0.72rem;
        color: var(--gray-600);
        margin-top: 0.35rem;
    }

    /* Main Content Grid */
    .dashboard-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    @media (max-width: 1200px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Card Component */
    .card-component {
        background: white;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .card-component:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .card-header {
        background: white;
        border-bottom: 1px solid var(--gray-200);
        padding: 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--gray-900);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .card-header-action {
        font-size: 0.85rem;
    }

    .card-body {
        padding: 1.25rem;
    }

    /* List Items */
    .list-container {
        max-height: 400px;
        overflow-y: auto;
    }

    .list-item {
        padding: 1rem;
        border-bottom: 1px solid var(--gray-100);
        transition: background 0.2s;
    }

    .list-item:last-child {
        border-bottom: none;
    }

    .list-item:hover {
        background: var(--gray-50);
    }

    .item-primary {
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 0.25rem;
        font-size: 0.95rem;
    }

    .item-secondary {
        font-size: 0.8rem;
        color: var(--gray-600);
    }

    .item-badge {
        display: inline-block;
        font-size: 0.75rem;
        padding: 0.35rem 0.75rem;
        border-radius: 4px;
        font-weight: 600;
        margin-top: 0.5rem;
    }

    .badge-primary { background: #dbeafe; color: var(--primary); }
    .badge-success { background: #dcfce7; color: var(--success); }
    .badge-warning { background: #fef3c7; color: var(--warning); }
    .badge-danger { background: #fee2e2; color: var(--danger); }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        margin-top: 0.75rem;
    }

    .btn-action {
        padding: 0.35rem 0.75rem;
        font-size: 0.8rem;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-approve {
        background: #dcfce7;
        color: var(--success);
    }

    .btn-approve:hover {
        background: #bbf7d0;
    }

    .btn-reject {
        background: #fee2e2;
        color: var(--danger);
    }

    .btn-reject:hover {
        background: #fecaca;
    }

    /* Tab Buttons */
    .tab-buttons {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }

    .tab-btn {
        padding: 0.6rem 1rem;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s;
        color: white;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        text-rendering: optimizeLegibility;
        backface-visibility: hidden;
        transform: translateZ(0);
    }

    .tab-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .tab-primary { background: var(--navy-mid); }
    .tab-info { background: var(--navy); }
    .tab-warning { background: #f59e0b; }
    .tab-danger { background: #dc2626; }

    /* Activity Section */
    .activity-item {
        padding: 1rem;
        border-left: 3px solid var(--gray-300);
        margin-bottom: 1rem;
        background: var(--gray-50);
        border-radius: 0 6px 6px 0;
        transition: all 0.2s;
    }

    .activity-item:hover {
        border-left-color: var(--primary);
        background: white;
    }

    .activity-title {
        font-weight: 600;
        color: var(--gray-900);
        font-size: 0.95rem;
        margin-bottom: 0.25rem;
    }

    .activity-meta {
        font-size: 0.8rem;
        color: var(--gray-600);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: var(--gray-600);
    }

    .empty-state-icon {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        opacity: 0.5;
    }

    /* Table */
    .table-responsive-custom {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background: var(--gray-100);
    }

    th {
        padding: 1rem;
        text-align: left;
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--gray-700);
        border-bottom: 1px solid var(--gray-200);
    }

    td {
        padding: 1rem;
        border-bottom: 1px solid var(--gray-100);
    }

    tbody tr:hover {
        background: var(--gray-50);
    }

    /* Buttons */
    .btn {
        border: none;
        border-radius: 6px;
        padding: 0.6rem 1.25rem;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-dark);
    }

    .btn-secondary {
        background: var(--gray-200);
        color: var(--gray-900);
    }

    .btn-secondary:hover {
        background: var(--gray-300);
    }

    .btn-sm {
        padding: 0.35rem 0.75rem;
        font-size: 0.8rem;
    }

    /* Scrollbar */
    .list-container::-webkit-scrollbar {
        width: 6px;
    }

    .list-container::-webkit-scrollbar-track {
        background: var(--gray-100);
    }

    .list-container::-webkit-scrollbar-thumb {
        background: var(--gray-300);
        border-radius: 3px;
    }

    .list-container::-webkit-scrollbar-thumb:hover {
        background: var(--gray-400);
    }
    
    @keyframes pulse-border {
        0% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(220, 38, 38, 0); }
        100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
    }
    .kpi-card.ready-evaluation-active {
        border-left-color: var(--danger) !important;
        animation: pulse-border 2s infinite;
        background: #fff8f8;
    }
</style>

<div class="dashboard-wrapper">
    <div class="container-fluid px-4">
        <!-- Header -->
        <div class="dashboard-header">
            <div class="row align-items-center justify-content-between">
                <div class="col">
                    <h1 class="dashboard-title">Admin Dashboard</h1>
                    <p class="dashboard-subtitle">Manage tenders, users, and monitor system activities</p>
                </div>
                <div class="col-auto">
                    <div class="header-actions">
                        <a href="index.php?route=audit-log" class="btn btn-secondary btn-sm">
                            <i class="bi bi-shield-check me-2"></i>View Logs
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Row -->
        <?php
            $pendingApplicationCount = count($pending) + count($pendingEvaluators) + intval($pendingAdminCount);
            $openTenderCount = count($published);
            $pendingAppealsCount = count($pendingAppeals);
            $blacklistedCountValue = intval($blacklistedCount);

            // Separate closed tenders into ready for evaluation vs under evaluation
            $readyTenders = [];
            $underEvaluationTenders = [];
            if (isset($closed) && is_array($closed)) {
                foreach ($closed as $t) {
                    if (empty($t['evaluation_release_at'])) {
                        $readyTenders[] = $t;
                    } else {
                        $underEvaluationTenders[] = $t;
                    }
                }
            }
        ?>
        <div class="kpi-grid">
            <div class="kpi-card primary">
                <div class="kpi-label"><i class="bi bi-people"></i>Pending Applications</div>
                <div class="kpi-value"><?= $pendingApplicationCount ?: '—' ?></div>
                <div class="kpi-meta">Contractors, Evaluators, Admins</div>
            </div>

            <div class="kpi-card success">
                <div class="kpi-label"><i class="bi bi-briefcase"></i>Open Tenders</div>
                <div class="kpi-value"><?= $openTenderCount ?: '—' ?></div>
                <div class="kpi-meta">Active procurement projects</div>
            </div>

            <div class="kpi-card warning" title="Counts appeals with status='submitted' requiring admin response">
                <div class="kpi-label"><i class="bi bi-exclamation-circle"></i>Pending Appeals</div>
                <div class="kpi-value"><?= $pendingAppealsCount ?: '—' ?></div>
                <div class="kpi-meta">Requiring review</div>
            </div>

            <div class="kpi-card <?= !empty($readyTenders) ? 'ready-evaluation-active' : '' ?>">
                <div class="kpi-label <?= !empty($readyTenders) ? 'text-danger fw-bold' : '' ?>">
                    <i class="bi bi-exclamation-triangle-fill <?= !empty($readyTenders) ? 'text-danger' : '' ?>"></i>
                    Ready for Evaluation
                </div>
                <div class="kpi-value <?= !empty($readyTenders) ? 'text-danger' : '' ?>"><?= count($readyTenders) ?: '—' ?></div>
                <div class="kpi-meta">Closed tenders awaiting release</div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="dashboard-grid">
            <!-- Left Column: Reviews & Approvals -->
            <div>
                <div class="card-component mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="bi bi-clipboard-check"></i>Pending Reviews
                        </div>
                        <a href="index.php?route=review-applicants" class="btn btn-sm btn-secondary">View All</a>
                    </div>
                    <div class="card-body">
                        <!-- Tab Buttons -->
                        <div class="tab-buttons">
                            <?php if (count($pending) > 0): ?>
                                <button type="button" class="tab-btn tab-warning" onclick="showTab('contractors')">
                                    <i class="bi bi-person-check me-1"></i>Contractors (<?= count($pending) ?>)
                                </button>
                            <?php endif; ?>
                            <?php if (count($pendingEvaluators) > 0): ?>
                                <button type="button" class="tab-btn tab-warning" onclick="showTab('evaluators')">
                                    <i class="bi bi-award me-1"></i>Evaluators (<?= count($pendingEvaluators) ?>)
                                </button>
                            <?php endif; ?>
                            <?php if (count($pendingAdmins) > 0): ?>
                                <button type="button" class="tab-btn tab-warning" onclick="showTab('admins')">
                                    <i class="bi bi-shield-lock me-1"></i>Admins (<?= count($pendingAdmins) ?>)
                                </button>
                            <?php endif; ?>
                            <?php if (count($pendingOfficers ?? []) > 0): ?>
                                <button type="button" class="tab-btn tab-warning" onclick="showTab('officers')">
                                    <i class="bi bi-briefcase me-1"></i>Procurement Officers (<?= count($pendingOfficers) ?>)
                                </button>
                            <?php endif; ?>
                        </div>

                        <div class="list-container">
                            <!-- Contractors -->
                            <div id="tab-contractors" style="<?= count($pending) > 0 ? 'display:block;' : 'display:none;' ?>">
                                <?php if (empty($pending)): ?>
                                    <div class="empty-state"><p>No pending contractors</p></div>
                                <?php else: ?>
                                    <?php foreach (array_slice($pending, 0, 8) as $c): ?>
                                        <div class="list-item">
                                            <div class="item-primary"><?= htmlspecialchars(substr($c['company_name'], 0, 40)) ?></div>
                                            <div class="item-secondary">Grade: <?= htmlspecialchars($c['cic_grade']) ?> • <?= date('d M Y', strtotime($c['created_at'])) ?></div>
                                            <div class="action-buttons">
                                                <form method="post" action="index.php?route=process-applicant" style="display:inline;">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                                    <input type="hidden" name="return_route" value="admin-dashboard">
                                                    <input type="hidden" name="profile_id" value="<?= $c['contractor_id'] ?>">
                                                    <input type="hidden" name="profile_type" value="contractor">
                                                    <a href="index.php?route=view-applicant&type=contractor&id=<?= $c['contractor_id'] ?>" class="btn-action btn-secondary" style="text-decoration:none; color:var(--gray-700); background:var(--gray-200);">👁 View Docs</a>
                                                    <button type="submit" name="approval_status" value="approved" class="btn-action btn-approve">✓ Approve</button>
                                                    <button type="submit" name="approval_status" value="rejected" class="btn-action btn-reject">✕ Reject</button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Evaluators -->
                            <div id="tab-evaluators" style="display:none;">
                                <?php if (empty($pendingEvaluators)): ?>
                                    <div class="empty-state"><p>No pending evaluators</p></div>
                                <?php else: ?>
                                    <?php foreach (array_slice($pendingEvaluators, 0, 8) as $e): ?>
                                        <div class="list-item">
                                            <div class="item-primary"><?= htmlspecialchars(substr($e['full_name'] ?? trim(($e['first_name'] ?? '') . ' ' . ($e['last_name'] ?? '')) ?: ($e['username'] ?? 'N/A'), 0, 40)) ?></div>
                                            <div class="item-secondary"><?= htmlspecialchars(substr($e['specialization'] ?? '', 0, 40)) ?></div>
                                            <div class="action-buttons">
                                                <form method="post" action="index.php?route=process-applicant" style="display:inline;">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                                    <input type="hidden" name="profile_id" value="<?= $e['evaluator_profile_id'] ?>">
                                                    <input type="hidden" name="return_route" value="admin-dashboard">
                                                    <input type="hidden" name="profile_type" value="evaluator">
                                                    <a href="index.php?route=view-applicant&type=evaluator&id=<?= $e['evaluator_profile_id'] ?>" class="btn-action btn-secondary" style="text-decoration:none; color:var(--gray-700); background:var(--gray-200);">👁 View Details</a>
                                                    <button type="submit" name="approval_status" value="approved" class="btn-action btn-approve">✓ Approve</button>
                                                    <button type="submit" name="approval_status" value="rejected" class="btn-action btn-reject">✕ Reject</button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Admins -->
                            <div id="tab-admins" style="display:none;">
                                <?php if (empty($pendingAdmins)): ?>
                                    <div class="empty-state"><p>No pending admin applications</p></div>
                                <?php else: ?>
                                    <?php foreach (array_slice($pendingAdmins, 0, 8) as $adm): ?>
                                        <div class="list-item">
                                            <div class="item-primary"><?= htmlspecialchars(substr($adm['full_name'] ?? trim(($adm['first_name'] ?? '') . ' ' . ($adm['last_name'] ?? '')) ?: ($adm['username'] ?? 'N/A'), 0, 40)) ?></div>
                                            <div class="item-secondary">ID: <?= htmlspecialchars(substr($adm['government_id'] ?? '', 0, 25)) ?></div>
                                            <div class="action-buttons">
                                                <form method="post" action="index.php?route=process-applicant" style="display:inline;">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                                    <input type="hidden" name="profile_id" value="<?= $adm['admin_profile_id'] ?>">
                                                    <input type="hidden" name="return_route" value="admin-dashboard">
                                                    <input type="hidden" name="profile_type" value="admin">
                                                    <a href="index.php?route=view-applicant&type=admin&id=<?= $adm['admin_profile_id'] ?>" class="btn-action btn-secondary" style="text-decoration:none; color:var(--gray-700); background:var(--gray-200);">👁 View Details</a>
                                                    <button type="submit" name="approval_status" value="approved" class="btn-action btn-approve">✓ Approve</button>
                                                    <button type="submit" name="approval_status" value="rejected" class="btn-action btn-reject">✕ Reject</button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Officers -->
                            <div id="tab-officers" style="display:none;">
                                <?php if (empty($pendingOfficers)): ?>
                                    <div class="empty-state"><p>No pending procurement officers</p></div>
                                <?php else: ?>
                                    <?php foreach (array_slice($pendingOfficers, 0, 8) as $off): ?>
                                        <div class="list-item">
                                            <div class="item-primary"><?= htmlspecialchars(substr($off['full_name'] ?? trim(($off['first_name'] ?? '') . ' ' . ($off['last_name'] ?? '')) ?: ($off['username'] ?? 'N/A'), 0, 40)) ?></div>
                                            <div class="item-secondary">Emp ID: <?= htmlspecialchars(substr($off['employee_id'] ?? '', 0, 25)) ?> • <?= htmlspecialchars(substr($off['department'] ?? '', 0, 40)) ?></div>
                                            <div class="action-buttons">
                                                <form method="post" action="index.php?route=process-applicant" style="display:inline;">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                                    <input type="hidden" name="profile_id" value="<?= $off['officer_profile_id'] ?>">
                                                    <input type="hidden" name="return_route" value="admin-dashboard">
                                                    <input type="hidden" name="profile_type" value="officer">
                                                    <a href="index.php?route=view-applicant&type=officer&id=<?= $off['officer_profile_id'] ?>" class="btn-action btn-secondary" style="text-decoration:none; color:var(--gray-700); background:var(--gray-200);">👁 View Details</a>
                                                    <button type="submit" name="approval_status" value="approved" class="btn-action btn-approve">✓ Approve</button>
                                                    <button type="submit" name="approval_status" value="rejected" class="btn-action btn-reject">✕ Reject</button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Participants -->
                <div class="card-component mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="bi bi-people-fill"></i>Active System Participants
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="tabs-container mb-3" style="border-bottom: 1px solid var(--gray-200); padding-bottom: 10px;">
                            <button class="tab-btn tab-warning active" onclick="switchActiveTab('active-contractors', this)">Contractors (<?= count($allContractors ?? []) ?>)</button>
                            <button class="tab-btn tab-warning" onclick="switchActiveTab('active-evaluators', this)">Evaluators (<?= count($allEvaluators ?? []) ?>)</button>
                            <button class="tab-btn tab-warning" onclick="switchActiveTab('active-admins', this)">Admins (<?= count($allAdmins ?? []) ?>)</button>
                            <button class="tab-btn tab-warning" onclick="switchActiveTab('active-officers', this)">Procurement Officers (<?= count($allOfficers ?? []) ?>)</button>
                        </div>
                        <div class="tab-contents">
                            <!-- Active Contractors -->
                            <div id="tab-active-contractors">
                                <?php if (empty($allContractors)): ?>
                                    <div class="empty-state"><p>No active contractors</p></div>
                                <?php else: ?>
                                    <div class="table-responsive-custom" style="max-height: 300px; overflow-y: auto;">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>Company Name</th>
                                                    <th>Contact</th>
                                                    <th>Joined</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($allContractors as $c): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($c['company_name']) ?></td>
                                                        <td><?= htmlspecialchars($c['email']) ?></td>
                                                        <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                                                        <td>
                                                            <a href="index.php?route=view-applicant&type=contractor&id=<?= $c['contractor_id'] ?>" class="btn btn-sm btn-secondary">View</a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Active Evaluators -->
                            <div id="tab-active-evaluators" style="display:none;">
                                <?php if (empty($allEvaluators)): ?>
                                    <div class="empty-state"><p>No active evaluators</p></div>
                                <?php else: ?>
                                    <div class="table-responsive-custom" style="max-height: 300px; overflow-y: auto;">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>Full Name</th>
                                                    <th>Specialization</th>
                                                    <th>Joined</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($allEvaluators as $e): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($e['full_name'] ?? trim(($e['first_name'] ?? '') . ' ' . ($e['last_name'] ?? '')) ?: ($e['username'] ?? 'N/A')) ?></td>
                                                        <td><?= htmlspecialchars($e['specialization'] ?? '') ?></td>
                                                        <td><?= date('M d, Y', strtotime($e['applied_at'] ?? $e['created_at'] ?? 'now')) ?></td>
                                                        <td>
                                                            <a href="index.php?route=view-applicant&type=evaluator&id=<?= $e['evaluator_profile_id'] ?>" class="btn btn-sm btn-secondary">View</a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Active Admins -->
                            <div id="tab-active-admins" style="display:none;">
                                <?php if (empty($allAdmins)): ?>
                                    <div class="empty-state"><p>No active admins</p></div>
                                <?php else: ?>
                                    <div class="table-responsive-custom" style="max-height: 300px; overflow-y: auto;">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>Full Name</th>
                                                    <th>Gov ID</th>
                                                    <th>Joined</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($allAdmins as $a): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($a['full_name'] ?? trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? '')) ?: ($a['username'] ?? 'N/A')) ?></td>
                                                        <td><?= htmlspecialchars($a['government_id'] ?? '') ?></td>
                                                        <td><?= date('M d, Y', strtotime($a['applied_at'] ?? $a['created_at'] ?? 'now')) ?></td>
                                                        <td>
                                                            <a href="index.php?route=view-applicant&type=admin&id=<?= $a['admin_profile_id'] ?>" class="btn btn-sm btn-secondary">View</a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Active Officers -->
                            <div id="tab-active-officers" style="display:none;">
                                <?php if (empty($allOfficers)): ?>
                                    <div class="empty-state"><p>No active procurement officers</p></div>
                                <?php else: ?>
                                    <div class="table-responsive-custom" style="max-height: 300px; overflow-y: auto;">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>Full Name</th>
                                                    <th>Department</th>
                                                    <th>Title</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($allOfficers as $off): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($off['full_name'] ?? trim(($off['first_name'] ?? '') . ' ' . ($off['last_name'] ?? '')) ?: ($off['username'] ?? 'N/A')) ?></td>
                                                        <td><?= htmlspecialchars($off['department'] ?? '') ?></td>
                                                        <td><?= htmlspecialchars($off['title'] ?? '') ?></td>
                                                        <td>
                                                            <a href="index.php?route=view-applicant&type=officer&id=<?= $off['officer_profile_id'] ?>" class="btn btn-sm btn-secondary">View</a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Bids -->
                <?php if (!empty($recentBids)): ?>
                    <div class="card-component">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="bi bi-receipt"></i>Recent Bid Submissions
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive-custom">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Contractor</th>
                                            <th>Tender</th>
                                            <th>Amount</th>
                                            <th>Submitted</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($recentBids, 0, 5) as $bid): ?>
                                            <tr>
                                                <td class="item-primary"><?= htmlspecialchars(substr($bid['company_name'] ?? 'N/A', 0, 20)) ?></td>
                                                <td><small><?= htmlspecialchars(substr($bid['title'] ?? 'N/A', 0, 25)) ?></small></td>
                                                <td><strong>E<?= number_format((float)($bid['decrypted_amount'] ?? 0), 2) ?></strong></td>
                                                <td><small><?= date('d M Y H:i', strtotime($bid['submitted_at'] ?? now())) ?></small></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tenders Ready for Evaluation -->
                <?php if (!empty($readyTenders)): ?>
                    <div class="card-component mt-4" style="border: 1px solid var(--danger);">
                        <div class="card-header" style="background: #fff8f8;">
                            <div class="card-title text-danger">
                                <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i>Tenders Ready for Evaluation
                                <span class="badge bg-danger ms-2 animate-pulse">Push Required</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive-custom">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Tender Title</th>
                                            <th>Submission Deadline</th>
                                            <th class="text-center">Bidders</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($readyTenders as $rt): ?>
                                            <?php $bidCount = intval($rt['bid_count'] ?? 0); ?>
                                            <tr>
                                                <td class="item-primary"><?= htmlspecialchars(substr($rt['title'] ?? 'N/A', 0, 40)) ?></td>
                                                <td><small><?= date('d M Y H:i', strtotime($rt['deadline'])) ?></small></td>
                                                <td class="text-center">
                                                    <span class="badge bg-<?= $bidCount > 0 ? 'success' : 'secondary' ?>">
                                                        <?= $bidCount ?> <?= $bidCount === 1 ? 'bidder' : 'bidders' ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-1 align-items-center">
                                                        <a href="index.php?route=tender-opening-room&id=<?= $rt['tender_id'] ?>" class="btn btn-primary btn-sm"><i class="bi bi-envelope-open me-1"></i>Opening Room</a>
                                                        <a href="index.php?route=watch-live&id=<?= $rt['tender_id'] ?>" target="_blank" class="btn btn-outline-danger btn-sm" title="Public watch live link"><i class="bi bi-broadcast me-1"></i>Watch Live</a>
                                                        <?php if ($bidCount > 0): ?>
                                                            <form method="post" action="index.php?route=release-tender-evaluation" onsubmit="return confirm('Release this tender to approved evaluators? Bidding will close and bids will be anonymized for evaluation.');" style="margin:0;">
                                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                                                <input type="hidden" name="tender_id" value="<?= $rt['tender_id'] ?>">
                                                                <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-send me-1"></i>Push to Evaluators</button>
                                                            </form>
                                                        <?php else: ?>
                                                            <button class="btn btn-secondary btn-sm" disabled title="Cannot push tender without bidders"><i class="bi bi-send me-1"></i>Push to Evaluators</button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tenders Under Evaluation -->
                <?php if (!empty($underEvaluationTenders)): ?>
                    <div class="card-component mt-4">
                        <div class="card-header">
                            <div class="card-title" style="color: var(--warning);">
                                <i class="bi bi-hourglass-split"></i>Tenders Under Evaluation
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive-custom">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Tender Title</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($underEvaluationTenders as $ct): ?>
                                            <tr>
                                                <td class="item-primary"><?= htmlspecialchars(substr($ct['title'] ?? 'N/A', 0, 50)) ?></td>
                                                <td>
                                                    <a href="index.php?route=tender-opening-room&id=<?= $ct['tender_id'] ?>" class="btn btn-outline-primary btn-sm me-1"><i class="bi bi-envelope-open me-1"></i>Opening Room</a>

                                                    <a href="index.php?route=manage-tender&id=<?= $ct['tender_id'] ?>" class="btn btn-secondary btn-sm"><i class="bi bi-eye me-1"></i>Monitor</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tenders Ready for Award -->
                <?php if (!empty($evaluatedTenders)): ?>
                    <div class="card-component mt-4">
                        <div class="card-header">
                            <div class="card-title" style="color: var(--success);">
                                <i class="bi bi-trophy"></i>Tenders Ready for Award
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive-custom">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Tender Title</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($evaluatedTenders as $et): ?>
                                            <tr>
                                                <td class="item-primary"><?= htmlspecialchars(substr($et['title'] ?? 'N/A', 0, 50)) ?></td>
                                                <td>

                                                    <a href="index.php?route=manage-tender&id=<?= $et['tender_id'] ?>" class="btn btn-success btn-sm"><i class="bi bi-award me-1"></i>Publish Winner</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Column: Stats & Activity -->
            <div>
                <!-- Quick Stats -->
                <div class="card-component mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="bi bi-graph-up"></i>System Statistics
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; gap: 1rem;">
                            <div style="padding-bottom: 1rem; border-bottom: 1px solid var(--gray-200);">
                                <div style="font-size: 0.9rem; color: var(--gray-600); margin-bottom: 0.5rem;">Total Tenders</div>
                                <div style="font-size: 1.75rem; font-weight: 700; color: var(--gray-900);"><?= $totalTenderCount ?></div>
                            </div>
                            <div style="padding-bottom: 1rem; border-bottom: 1px solid var(--gray-200);">
                                <div style="font-size: 0.9rem; color: var(--gray-600); margin-bottom: 0.5rem;">Awarded Projects</div>
                                <div style="font-size: 1.75rem; font-weight: 700; color: var(--success);"><?= intval($awardedCount ?? 0) ?></div>
                            </div>
                            <div>
                                <div style="font-size: 0.9rem; color: var(--gray-600); margin-bottom: 0.5rem;">Closed Tenders</div>
                                <div style="font-size: 1.75rem; font-weight: 700; color: var(--gray-900);"><?= count($closed) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appeals -->
                <?php if (!empty($pendingAppeals)): ?>
                    <div class="card-component mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="bi bi-exclamation-circle"></i>Active Appeals
                            </div>
                            <a href="index.php?route=manage-appeals" class="btn btn-sm btn-secondary">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="list-container" style="max-height: 250px;">
                                <?php foreach (array_slice($pendingAppeals, 0, 4) as $appeal): ?>
                                    <div class="list-item">
                                        <div class="item-primary" style="color: var(--danger);"><?= htmlspecialchars(substr($appeal['subject'], 0, 30)) ?></div>
                                        <div class="item-secondary">Tender: <?= htmlspecialchars(substr($appeal['title'] ?? 'N/A', 0, 30)) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Recent Activity -->
                <div class="card-component">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="bi bi-clock-history"></i>Recent Activity
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="list-container" style="max-height: 300px;">
                            <?php if (!empty($recentActions)): ?>
                                <?php foreach (array_slice($recentActions, 0, 5) as $action): ?>
                                    <div class="activity-item">
                                        <div class="activity-title"><?= htmlspecialchars($action['action_type'] ?? 'System Action') ?></div>
                                        <div class="activity-meta"><?= date('d M Y H:i', strtotime($action['created_at'] ?? now())) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state"><p>No recent activity</p></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conflict Alerts Section (if any) -->
        <?php if (!empty($conflictAlerts)): ?>
            <div class="card-component">
                <div class="card-header">
                    <div class="card-title" style="color: var(--danger);">
                        <i class="bi bi-exclamation-triangle"></i>Conflict of Interest Alerts
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive-custom">
                        <table>
                            <thead>
                                <tr>
                                    <th>Evaluator</th>
                                    <th>Reason</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($conflictAlerts, 0, 5) as $alert): ?>
                                    <tr>
                                        <td><?= htmlspecialchars(substr($alert['evaluator_name'] ?? 'N/A', 0, 25)) ?></td>
                                        <td><small><?= htmlspecialchars(substr($alert['coi_reason'] ?? 'No reason provided', 0, 30)) ?></small></td>
                                        <td><small><?= date('d M Y', strtotime($alert['evaluated_at'] ?? now())) ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function showTab(tabName) {
        // Hide all tabs
        document.getElementById('tab-contractors').style.display = 'none';
        document.getElementById('tab-evaluators').style.display = 'none';
        document.getElementById('tab-admins').style.display = 'none';
        document.getElementById('tab-officers').style.display = 'none';
        
        // Show selected tab
        const tabId = 'tab-' + tabName;
        const tabElement = document.getElementById(tabId);
        if (tabElement) {
            tabElement.style.display = 'block';
        }
    }

    function switchActiveTab(tabName, btn) {
        // Hide all active tabs
        document.getElementById('tab-active-contractors').style.display = 'none';
        document.getElementById('tab-active-evaluators').style.display = 'none';
        document.getElementById('tab-active-admins').style.display = 'none';
        document.getElementById('tab-active-officers').style.display = 'none';
        
        // Remove active class from all buttons in this container
        const btns = btn.parentElement.querySelectorAll('.tab-btn');
        btns.forEach(b => b.classList.remove('active'));
        
        // Show selected tab
        const tabId = 'tab-' + tabName;
        const tabElement = document.getElementById(tabId);
        if (tabElement) {
            tabElement.style.display = 'block';
        }
        
        // Add active class to clicked button
        btn.classList.add('active');
    }
</script>
