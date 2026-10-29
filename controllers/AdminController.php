<?php
class AdminController extends BaseController
{
    public function dashboard()
    {
        $this->requireRole(['admin']);

        $contractorModel = new ContractorModel($this->pdo);
        $tenderModel = new TenderModel($this->pdo);
        $adminProfileModel = new AdminProfileModel($this->pdo);
        $evaluatorProfileModel = new EvaluatorProfileModel($this->pdo);
        $officerProfileModel = new OfficerProfileModel($this->pdo);

        $tenderModel->cleanupTenderLifecycle();

        $pending = $contractorModel->getPending();
        $approvedContractorCount = count($contractorModel->getApproved());
        $rejectedContractorCount = count($contractorModel->getRejected());
        $published = $tenderModel->getPublished();
        $totalTenderCount = $tenderModel->countAll();
        $closed = $tenderModel->getByStatus('closed');
        foreach ($closed as &$t) {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM bids WHERE tender_id = :id');
            $stmt->execute(['id' => $t['tender_id']]);
            $t['bid_count'] = (int)$stmt->fetchColumn();
        }
        unset($t);
        $evaluatedTenders = $tenderModel->getByStatus('evaluated');
        $awardedCount = $tenderModel->countAwarded();
        $pendingAdmins = $adminProfileModel->getPending();
        $pendingAdminCount = count($pendingAdmins);
        $pendingEvaluatorCount = count($evaluatorProfileModel->getPending());
        $pendingOfficers = $officerProfileModel->getPendingProfiles();
        $pendingOfficerCount = count($pendingOfficers);
        
        $pendingEvaluators = $evaluatorProfileModel->getPending();
        $approvedEvaluatorCount = $evaluatorProfileModel->getApprovedCount();
        $rejectedEvaluatorCount = count($evaluatorProfileModel->getRejected());
        $recentReviewedContractors = $contractorModel->getRecentReviewed(5);
        $recentReviewedEvaluators = $evaluatorProfileModel->getRecentReviewed(5);

        // Get audit count and recent activity
        $auditModel = new AuditModel($this->pdo);
        $totalActions = count($auditModel->getAll() ?? []);
        $recentActions = $auditModel->getRecent(3);

        // Recent bid submissions requiring admin attention
        $bidModel = new BidModel($this->pdo, $this->config['bid_encryption_key']);
        $recentBids = $bidModel->getRecentWithTender(5);

        // Appeals
        $appealModel = new AppealModel($this->pdo);
        $pendingAppeals = $appealModel->getPending();

        // Conflict Alerts
        $evaluationModel = new EvaluationModel($this->pdo);
        $conflictAlerts = $evaluationModel->getConflictAlerts();

        // Blacklist Count
        $blacklistedCount = $contractorModel->getBlacklistedCount();

        // All Approved Participants
        $allContractors = $contractorModel->getApproved();
        $allEvaluators = $evaluatorProfileModel->getApproved();
        $allAdmins = $adminProfileModel->getApproved();
        $allOfficers = $officerProfileModel->getApproved();

        $this->view('dashboard_admin', [
            'pending' => $pending,
            'pendingEvaluators' => $pendingEvaluators,
            'pendingAdmins' => $pendingAdmins,
            'pendingOfficers' => $pendingOfficers,
            'pendingAppeals' => $pendingAppeals,
            'conflictAlerts' => $conflictAlerts,
            'published' => $published,
            'totalTenderCount' => $totalTenderCount,
            'closed' => $closed,
            'evaluatedTenders' => $evaluatedTenders,
            'pendingAdminCount' => $pendingAdminCount,
            'pendingEvaluatorCount' => $pendingEvaluatorCount,
            'pendingOfficerCount' => $pendingOfficerCount,
            'approvedEvaluatorCount' => $approvedEvaluatorCount,
            'rejectedEvaluatorCount' => $rejectedEvaluatorCount,
            'approvedContractorCount' => $approvedContractorCount,
            'rejectedContractorCount' => $rejectedContractorCount,
            'recentReviewedContractors' => $recentReviewedContractors,
            'recentReviewedEvaluators' => $recentReviewedEvaluators,
            'blacklistedCount' => $blacklistedCount,
            'totalActions' => $totalActions,
            'recentActions' => $recentActions,
            'recentBids' => $recentBids,
            'awardedCount' => $awardedCount,
            'allContractors' => $allContractors,
            'allEvaluators' => $allEvaluators,
            'allAdmins' => $allAdmins,
            'allOfficers' => $allOfficers
        ]);
    }

    public function reviewApplicants()
    {
        $this->requireRole(['admin']);

        $adminProfileModel = new AdminProfileModel($this->pdo);
        $evaluatorProfileModel = new EvaluatorProfileModel($this->pdo);
        $officerProfileModel = new OfficerProfileModel($this->pdo);

        $admins = $adminProfileModel->getPending();
        $evaluators = $evaluatorProfileModel->getPending();
        $officers = $officerProfileModel->getPendingProfiles();

        $this->view('admin/review_applicants', [
            'admins' => $admins,
            'evaluators' => $evaluators,
            'officers' => $officers,
        ]);
    }

    public function viewApplicant()
    {
        $this->requireRole(['admin']);
        $profileType = trim($_GET['type'] ?? '');
        $profileId = intval($_GET['id'] ?? 0);
        
        $profile = null;
        $documents = [];
        
        if ($profileType === 'contractor') {
            $profileModel = new ContractorModel($this->pdo);
            $profile = $profileModel->getById($profileId);
            if ($profile) {
                $documentModel = new ContractorDocumentModel($this->pdo);
                $documents = $documentModel->getByContractor($profileId);
            }
        } elseif ($profileType === 'evaluator') {
            $profileModel = new EvaluatorProfileModel($this->pdo);
            $profile = $profileModel->getById($profileId);
        } elseif ($profileType === 'officer') {
            $profileModel = new OfficerProfileModel($this->pdo);
            $profile = $profileModel->getById($profileId);
        } elseif ($profileType === 'admin') {
            $profileModel = new AdminProfileModel($this->pdo);
            $profile = $profileModel->getById($profileId);
        } else {
            $this->redirect('admin-dashboard');
            return;
        }
        
        if (!$profile) {
            $this->redirect('admin-dashboard');
            return;
        }
        
        // Fetch user email to fix the missing email issue
        $userModel = new UserModel($this->pdo);
        $user = $userModel->getById($profile['user_id']);
        if ($user) {
            $profile['email'] = $user['email'];
        } else {
            $profile['email'] = 'Unknown';
        }

        $this->view('admin/view_applicant', [
            'profileType' => $profileType,
            'profile' => $profile,
            'documents' => $documents
        ]);
    }

    public function listEvaluators()
    {
        $this->requireRole(['admin']);

        $evaluatorProfileModel = new EvaluatorProfileModel($this->pdo);
        $evaluators = $evaluatorProfileModel->getApproved();

        $this->view('admin/evaluators', [
            'evaluators' => $evaluators,
        ]);
    }

    public function processApplicant()
    {
        $this->requireRole(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('review-applicants');
        }

        $profileType = trim($_POST['profile_type'] ?? '');
        $profileId = intval($_POST['profile_id'] ?? 0);
        $status = ($_POST['approval_status'] ?? 'rejected') === 'approved' ? 'approved' : 'rejected';
        $userModel = new UserModel($this->pdo);

        if ($profileType === 'admin') {
            $profileModel = new AdminProfileModel($this->pdo);
            $profile = $profileModel->getById($profileId);
        } elseif ($profileType === 'evaluator') {
            $profileModel = new EvaluatorProfileModel($this->pdo);
            $profile = $profileModel->getById($profileId);
        } elseif ($profileType === 'officer') {
            $profileModel = new OfficerProfileModel($this->pdo);
            $profile = $profileModel->getById($profileId);
        } elseif ($profileType === 'contractor') {
            $profileModel = new ContractorModel($this->pdo);
            $profile = $profileModel->getById($profileId);
        } else {
            $this->redirect('review-applicants');
            return;
        }

        if ($profile) {
            $rejectionReason = trim($_POST['rejection_reason'] ?? '');
            $reason = $status === 'rejected' ? ($rejectionReason ?: 'Application rejected by administrator.') : null;

            if ($profileType === 'contractor') {
                $profileModel->updateVerificationStatus($profileId, $status, $reason);
            } elseif ($profileType === 'evaluator') {
                $profileModel->updateApprovalStatus($profileId, $status, $_SESSION['user']['user_id'], $reason);
            } elseif ($profileType === 'officer') {
                $profileModel->updateApprovalStatus($profileId, $status, $_SESSION['user']['user_id'], $reason);
            } else {
                $profileModel->updateApprovalStatus($profileId, $status, $_SESSION['user']['user_id']);
            }

            if ($status === 'rejected') {
                $userModel->updateStatus($profile['user_id'], 'suspended');
            } elseif ($status === 'approved') {
                $userModel->updateStatus($profile['user_id'], 'active');
            }

            $notificationModel = new NotificationModel($this->pdo);
            $notificationMessage = $status === 'approved'
                ? 'Your application has been approved. You may now access your account.'
                : 'Your application has been rejected. ' . $reason;
            $notificationModel->create($profile['user_id'], $notificationMessage);

            $audit = new AuditModel($this->pdo);
            $audit->log($_SESSION['user']['user_id'], 'review_applicant', $profileType . '_profiles', $profileId, ucfirst($profileType) . ' application ' . $status . ($reason ? ': ' . $reason : ''));
        }

        $returnRoute = trim($_POST['return_route'] ?? '');
        $allowedReturnRoutes = ['admin-dashboard', 'review-applicants'];
        if (!in_array($returnRoute, $allowedReturnRoutes, true)) {
            $returnRoute = $profileType === 'contractor' ? 'admin-dashboard' : 'review-applicants';
        }

        $this->redirect($returnRoute);
    }

    /**
     * Open a tender for meeting-based evaluation
     * Admin creates a meeting link (Zoom) and this notifies all evaluators and contractors
     * STATUS: closed → evaluated
     * WORKFLOW: Admin manually triggers this action, not automatic
     */

    public function notifications()
    {
        $this->requireRole(['admin', 'evaluator', 'contractor', 'officer']);
        $notificationModel = new NotificationModel($this->pdo);
        $notifications = $notificationModel->getAllForUser($_SESSION['user']['user_id']);
        foreach ($notifications as $notification) {
            if ($notification['read_at'] === null) {
                $notificationModel->markAsRead($notification['notification_id']);
            }
        }
        $notifications = $notificationModel->getAllForUser($_SESSION['user']['user_id']);
        $this->view('notifications', ['notifications' => $notifications]);
    }

    public function verifyContractor()
    {
        $this->requireRole(['admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $contractorId = intval($_POST['contractor_id'] ?? 0);
            $status       = ($_POST['verification_status'] ?? 'pending') === 'approved' ? 'approved' : 'rejected';
            $rejectionReason = trim($_POST['rejection_reason'] ?? '');
            $reason = $status === 'rejected' ? ($rejectionReason ?: 'Contractor verification rejected by administrator.') : null;

            $contractorModel = new ContractorModel($this->pdo);
            $contractor      = $contractorModel->getById($contractorId);
            $contractorModel->updateVerificationStatus($contractorId, $status, $reason);

            $audit = new AuditModel($this->pdo);
            $audit->log($_SESSION['user']['user_id'], 'verify_contractor', 'contractors', $contractorId, 'Contractor ' . $status . ($reason ? ': ' . $reason : ''));

            if ($contractor) {
                $userModel = new UserModel($this->pdo);
                $user = $userModel->findById($contractor['user_id']);
                if ($user) {
                    if ($status === 'approved') {
                        $userModel->updateStatus($user['user_id'], 'active');
                    } else {
                        $userModel->updateStatus($user['user_id'], 'suspended');
                    }

                    $notificationModel = new NotificationModel($this->pdo);
                    $notificationMessage = $status === 'approved'
                        ? 'Your company verification has been approved. You may now log in and submit bids.'
                        : 'Your company verification has been rejected. ' . $reason;
                    $notificationModel->create($user['user_id'], $notificationMessage);

                    $statusLabel = $status === 'approved' ? 'approved' : 'rejected';
                    $mailer = new MailerService($this->config);
                    $mailer->send(
                        $user['email'],
                        $user['username'],
                        'Company Verification Update — MicroProjects Eswatini',
                        "Dear {$user['username']},\n\nYour company \"{$contractor['company_name']}\" has been {$statusLabel} by the admin.\n"
                        . ($status === 'approved' ? "You may now log in and submit bids on open tenders." : "{$reason}\nPlease contact the administrator for further information.")
                        . "\n\nMicroProjects Eswatini E-Tendering System"
                    );
                }
            }
        }
        $this->redirect('admin-dashboard');
    }

    public function auditLog()
    {
        $this->requireRole(['admin']);

        $search    = trim($_GET['search'] ?? '');
        $filterAction = trim($_GET['action'] ?? '');
        $page      = max(1, intval($_GET['page'] ?? 1));
        $perPage   = 50;
        $offset    = ($page - 1) * $perPage;

        $auditModel = new AuditModel($this->pdo);
        ['logs' => $logs, 'total' => $total] = $auditModel->getFiltered($search, $filterAction, $perPage, $offset);
        $totalPages = max(1, (int)ceil($total / $perPage));

        $this->view('admin/audit_log', [
            'logs'         => $logs,
            'search'       => $search,
            'filterAction' => $filterAction,
            'page'         => $page,
            'totalPages'   => $totalPages,
            'pdo'          => $this->pdo,
        ]);
    }

    public function manageAppeals()
    {
        $this->requireRole(['admin']);
        $appealModel = new AppealModel($this->pdo);
        $appeals = $appealModel->getAll();
        $this->view('admin/appeals', ['appeals' => $appeals]);
    }

    public function respondAppeal()
    {
        $this->requireRole(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('manage-appeals');
        }
        $appealId    = intval($_POST['appeal_id'] ?? 0);
        $status      = trim($_POST['status'] ?? 'resolved');
        $response    = trim($_POST['admin_response'] ?? '');
        $appealModel = new AppealModel($this->pdo);
        $appeal      = $appealModel->getById($appealId);
        $appealModel->updateStatus($appealId, $status, $response, $_SESSION['user']['user_id']);
        $audit = new AuditModel($this->pdo);
        $audit->log($_SESSION['user']['user_id'], 'respond_appeal', 'appeals', $appealId, 'Appeal status updated to ' . $status);

        // Notify the appellant
        if ($appeal) {
            $notificationModel = new NotificationModel($this->pdo);
            $notificationModel->create($appeal['user_id'], "Your appeal (#{$appealId}) has been {$status}. Admin response: {$response}");
            $userModel   = new UserModel($this->pdo);
            $appellant   = $userModel->findById($appeal['user_id']);
            if ($appellant) {
                $mailer = new MailerService($this->config);
                $mailer->send(
                    $appellant['email'],
                    $appellant['username'],
                    'Appeal Update — MicroProjects E-Tendering',
                    "Dear {$appellant['username']},\n\nYour tender appeal (#{$appealId}) has been updated to status: {$status}.\n\nAdmin response: {$response}\n\nMicroProjects Eswatini E-Tendering System"
                );
            }
        }

        $this->redirect('manage-appeals');
    }

    public function blacklistContractor()
    {
        $this->requireRole(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin-dashboard');
        }
        $contractorId = intval($_POST['contractor_id'] ?? 0);
        $reason = trim($_POST['blacklist_reason'] ?? 'Violations found');
        $contractorModel = new ContractorModel($this->pdo);
        $contractorModel->blacklist($contractorId, $reason);

        $contractor = $contractorModel->getById($contractorId);
        if ($contractor) {
            $userModel = new UserModel($this->pdo);
            $userModel->updateStatus($contractor['user_id'], 'suspended');
        }

        $audit = new AuditModel($this->pdo);
        $audit->log($_SESSION['user']['user_id'], 'blacklist_contractor', 'contractors', $contractorId, 'Contractor blacklisted: ' . $reason);
        $this->redirect('contractor-blacklist');
    }

    public function contractorBlacklist()
    {
        $this->requireRole(['admin']);
        $contractorModel = new ContractorModel($this->pdo);
        $blacklisted = $contractorModel->getBlacklisted();
        $approved = $contractorModel->getApproved();
        $selectedContractorId = intval($_GET['contractor_id'] ?? 0);

        $this->view('admin/contractor_blacklist', [
            'blacklisted' => $blacklisted,
            'approvedContractors' => $approved,
            'selectedContractorId' => $selectedContractorId,
        ]);
    }

    /**
     * Open evaluation session for a tender
     * Called when the tender deadline has passed and evaluation should begin
     */

    /**
     * Submit an evaluation comment (public review)
     * Allows evaluators to post public comments about bids during evaluation
     */

    /**
     * Mark evaluator as ready to close evaluation
     */

    /**
     * Close evaluation session
     * Can only be done when all evaluators are ready or time expires
     */

    /**
     * Request a zoom meeting during evaluation
     */

    /**
     * Approve zoom meeting request
     */

    // ═══════════════════════════════════════════════════════════
    // DIGITAL ENVELOPE OPENING BOARD
    // ═══════════════════════════════════════════════════════════

    /**
     * Renders the public bid-opening board.
     * Accessible to: admin, evaluator (can take actions), contractor (view-only).
     */

    /**
     * POST: Break the seal on a single bid (admin / evaluator only).
     * Sets envelope_status = 'opened'.
     */

    /**
     * POST: Mark a bid as legit (→ verified) or invalid (→ rejected).
     */

    /**
     * POST: Publicly award the tender to the top-ranked bid.
     * Saves project specifications and notifies the winning contractor.
     */

    /**
     * GET: Display project specifications for the awarded tender.
     * Accessible to admin (any tender) or the winning contractor.
     */
    
    public function apiSetMeetingLink()
    {
        $this->requireRole(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin-dashboard');
            return;
        }
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $this->redirect('admin-dashboard');
            return;
        }

        $tenderId = intval($_POST['tender_id'] ?? 0);
        $link = trim($_POST['consensus_meeting_link'] ?? '');

        if (!$tenderId || !$link) {
            $_SESSION['error'] = 'Invalid tender ID or missing link.';
            $this->redirect('index.php?route=tender-opening-room&id=' . $tenderId);
        }

        $stmt = $this->pdo->prepare('UPDATE tenders SET consensus_meeting_link = :link WHERE tender_id = :tender_id');
        $stmt->execute(['link' => $link, 'tender_id' => $tenderId]);

        // Fetch tender title for the notification message
        $tenderStmt = $this->pdo->prepare('SELECT title FROM tenders WHERE tender_id = :id');
        $tenderStmt->execute(['id' => $tenderId]);
        $tenderTitle = $tenderStmt->fetchColumn() ?: 'Tender #' . $tenderId;

        // Notify all contractors who submitted bids for this tender
        require_once __DIR__ . '/../models/NotificationModel.php';
        $notificationModel = new NotificationModel($this->pdo);

        $bidStmt = $this->pdo->prepare(
            'SELECT DISTINCT u.user_id FROM bids b
             JOIN contractors c ON b.contractor_id = c.contractor_id
             JOIN users u ON c.user_id = u.user_id
             WHERE b.tender_id = :tender_id'
        );
        $bidStmt->execute(['tender_id' => $tenderId]);
        $bidderUsers = $bidStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($bidderUsers as $bidder) {
            $notificationModel->create(
                (int)$bidder['user_id'],
                '🔴 LIVE: The bid opening session for "' . $tenderTitle . '" has started. You can now watch the envelope unsealing ceremony live. Click Watch Live on your dashboard.'
            );
        }

        $auditModel = new AuditModel($this->pdo);
        $auditModel->log(
            $_SESSION['user']['user_id'],
            'UPDATE',
            'tenders',
            $tenderId,
            "Set opening meeting link for tender ID: $tenderId — notified " . count($bidderUsers) . " bidder(s)."
        );

        $_SESSION['success'] = 'Meeting link broadcasted. ' . count($bidderUsers) . ' bidder(s) have been notified.';
        $this->redirect('index.php?route=tender-opening-room&id=' . $tenderId);
    }
}

