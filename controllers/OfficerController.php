<?php
class OfficerController extends BaseController
{
    public function dashboard()
    {
        $this->requireRole(['officer']);

        $tenderModel = new TenderModel($this->pdo);
        $tenderModel->closeExpiredTenders();

        // Get tender counts by status
        $draftCount = count($tenderModel->getByStatus('draft') ?? []);
        $publishedCount = count($tenderModel->getByStatus('published') ?? []);
        $closedCount = count($tenderModel->getByStatus('closed') ?? []);
        $evaluatedCount = count($tenderModel->getByStatus('evaluated') ?? []);
        $awardedCount = count($tenderModel->getByStatus('awarded') ?? []);
        $totalCount = $tenderModel->countAll();

        // Get bids and site visits needing verification
        $bidModel = new BidModel($this->pdo, $this->config['bid_encryption_key']);
        $recentBids = $bidModel->getRecentWithTender(5);

        // Find bids with site visits
        $stmt = $this->pdo->query('
            SELECT b.*, t.title AS tender_title, c.company_name
            FROM bids b
            JOIN tenders t ON b.tender_id = t.tender_id
            JOIN contractors c ON b.contractor_id = c.contractor_id
            WHERE b.site_visit_date IS NOT NULL
            ORDER BY b.submitted_at DESC
            LIMIT 5
        ');
        $siteVisits = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Tenders needing officer action (opening / consensus meeting)
        $closedTenders    = $tenderModel->getByStatus('closed')    ?? [];
        $evaluatedTenders = $tenderModel->getByStatus('evaluated')  ?? [];

        $this->view('dashboard_officer', [
            'draftCount'       => $draftCount,
            'publishedCount'   => $publishedCount,
            'closedCount'      => $closedCount,
            'evaluatedCount'   => $evaluatedCount,
            'awardedCount'     => $awardedCount,
            'totalCount'       => $totalCount,
            'recentBids'       => $recentBids,
            'siteVisits'       => $siteVisits,
            'closedTenders'    => $closedTenders,
            'evaluatedTenders' => $evaluatedTenders,
        ]);
    }

    public function manageSiteVisits()
    {
        $this->requireRole(['officer']);

        $stmt = $this->pdo->query('
            SELECT b.*, t.title AS tender_title, c.company_name, c.phone, c.contact_person
            FROM bids b
            JOIN tenders t ON b.tender_id = t.tender_id
            JOIN contractors c ON b.contractor_id = c.contractor_id
            WHERE b.site_visit_date IS NOT NULL
            ORDER BY b.site_visit_date DESC
        ');
        $bids = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('officer/manage_site_visits', [
            'bids' => $bids,
        ]);
    }

    public function apiFlagDocument()
    {
        $this->requireRole(['officer']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request method.');
        }

        $docId       = intval($_POST['document_id'] ?? 0);
        $docType     = trim($_POST['document_type'] ?? ''); // \'contractor\' or \'bid\'
        $isFraud     = isset($_POST['is_fraudulent']) ? intval($_POST['is_fraudulent']) : 0;
        $isIncorrect = isset($_POST['is_incorrect']) ? intval($_POST['is_incorrect']) : 0;
        $reason      = trim($_POST['flag_reason'] ?? '');

        if (!$docId || !in_array($docType, ['contractor', 'bid'], true)) {
            jsonResponse(false, 'Invalid parameters.');
        }

        if ($docType === 'contractor') {
            require_once __DIR__ . '/../models/ContractorDocumentModel.php';
            $docModel = new ContractorDocumentModel($this->pdo);
            $success = $docModel->flagDocument($docId, $isFraud, $isIncorrect, $reason);
        } else {
            require_once __DIR__ . '/../models/BidDocumentModel.php';
            $docModel = new BidDocumentModel($this->pdo);
            $success = $docModel->flagDocument($docId, $isFraud, $isIncorrect, $reason);
        }

        if ($success) {
            $audit = new AuditModel($this->pdo);
            $audit->log(
                $_SESSION['user']['user_id'],
                'flag_document',
                $docType . '_documents',
                $docId,
                'Document flagged: Fraudulent=' . $isFraud . ', Incorrect=' . $isIncorrect . '. Reason: ' . $reason
            );
            jsonResponse(true, 'Document status updated successfully.');
        } else {
            jsonResponse(false, 'Failed to update document status.');
        }
    }

    public function publishTender()
    {
        $this->requireRole(['officer']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $tenderReference = trim($_POST['tender_reference'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $budget = floatval($_POST['budget'] ?? 0);
            $tenderFee = !empty($_POST['tender_fee']) ? floatval($_POST['tender_fee']) : 0.00;
            $tenderSecurity = !empty($_POST['tender_security']) ? floatval($_POST['tender_security']) : null;
            $requiredGrade = trim($_POST['required_grade'] ?? 'Grade 1');
            $category = trim($_POST['category'] ?? 'Construction');
            $region = trim($_POST['region'] ?? 'Hhohho');
            $deadline = trim($_POST['deadline'] ?? '');
            $clarificationDeadline = trim($_POST['clarification_deadline'] ?? '');
            $evaluationDeadline = trim($_POST['evaluation_deadline'] ?? '');
            $siteVisitDate = trim($_POST['site_visit_date'] ?? '');

            $tenderModel = new TenderModel($this->pdo);
            $tenderId = $tenderModel->create([
                'title'                  => $title,
                'tender_reference'       => $tenderReference ?: null,
                'description'            => $description,
                'budget'                 => $budget,
                'tender_fee'             => $tenderFee,
                'tender_security'        => $tenderSecurity,
                'required_grade'         => $requiredGrade,
                'category'               => $category,
                'region'                 => $region,
                'deadline'               => $deadline,
                'clarification_deadline' => $clarificationDeadline ?: null,
                'evaluation_deadline'    => $evaluationDeadline ?: null,
                'site_visit_date'        => $siteVisitDate ?: null,
                'published_at'           => date('Y-m-d H:i:s'),
                'status'                 => 'published',
                'created_by'             => $_SESSION['user']['user_id']
            ]);

            $rubricModel = new RubricModel($this->pdo);
            $rubricModel->upsert([
                'tender_id'        => $tenderId,
                'technical_weight' => floatval($_POST['weight_technical'] ?? 50),
                'financial_weight' => floatval($_POST['weight_financial'] ?? 40),
                'local_weight'     => floatval($_POST['weight_local'] ?? 10),
            ]);

            $audit = new AuditModel($this->pdo);
            $audit->log($_SESSION['user']['user_id'], 'publish_tender', 'tenders', $tenderId, 'Tender published with rubric weights');

            $contractorModel = new ContractorModel($this->pdo);
            $notificationModel = new NotificationModel($this->pdo);
            foreach ($contractorModel->getApproved() as $contractor) {
                $notificationModel->create($contractor['user_id'], 'New tender "' . $title . '" has been published. Check the tender details and upload required documents before bidding.');
            }

            $this->redirect('officer-dashboard');
            return;
        }

        $this->view('admin/create_tender');
    }

    public function apiPublishTender()
    {
        if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            jsonResponse(false, 'Only admins can publish tenders.');
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $budget = floatval($_POST['budget'] ?? 0);
        $requiredGrade = trim($_POST['required_grade'] ?? '');
        $deadline = trim($_POST['deadline'] ?? '');

        $tenderModel = new TenderModel($this->pdo);
        $tenderId = $tenderModel->create([
            'title'        => $title,
            'description'  => $description,
            'budget'       => $budget,
            'required_grade' => $requiredGrade,
            'deadline'     => $deadline,
            'status'       => 'published',
            'created_by'   => $_SESSION['user']['user_id'],
        ]);

        $audit = new AuditModel($this->pdo);
        $audit->log($_SESSION['user']['user_id'], 'publish_tender', 'tenders', $tenderId, 'Tender published via AJAX');

        jsonResponse(true, 'Tender published successfully.', ['redirect' => 'index.php?route=admin-dashboard']);
    }

    public function reviewRubricProposals()
    {
        $this->requireRole(['officer']);

        $proposalModel = new RubricProposalModel($this->pdo);
        $proposals = $proposalModel->getPending();

        $this->view('admin/review_rubric_proposals', ['proposals' => $proposals]);
    }

    public function approveRubricProposal()
    {
        $this->requireRole(['officer']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('review-rubric-proposals');
            return;
        }

        $proposalId = intval($_POST['proposal_id'] ?? 0);
        $proposalModel = new RubricProposalModel($this->pdo);
        $proposal = $proposalModel->getById($proposalId);
        if ($proposal) {
            $rubricModel = new RubricModel($this->pdo);
            $rubricModel->upsert([
                'tender_id'        => $proposal['tender_id'],
                'technical_weight' => $proposal['technical_weight'],
                'financial_weight' => $proposal['financial_weight'],
                'local_weight'     => $proposal['local_weight'],
            ]);

            $proposalModel->updateStatus($proposalId, 'approved', 'Approved by admin.');

            $contractorModel = new ContractorModel($this->pdo);
            $notificationModel = new NotificationModel($this->pdo);
            foreach ($contractorModel->getApproved() as $contractor) {
                $notificationModel->create($contractor['user_id'], 'The scoring rubric for tender "' . $proposal['tender_title'] . '" has been approved. Review the tender and prepare your bid.');
            }

            $audit = new AuditModel($this->pdo);
            $audit->log($_SESSION['user']['user_id'], 'approve_rubric_proposal', 'tender_rubric_proposals', $proposalId, 'Approved rubric proposal for tender ' . $proposal['tender_id']);
        }

        $this->redirect('review-rubric-proposals');
    }

    public function rejectRubricProposal()
    {
        $this->requireRole(['officer']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('review-rubric-proposals');
            return;
        }

        $proposalId = intval($_POST['proposal_id'] ?? 0);
        $rejectionNotes = trim($_POST['rejection_notes'] ?? 'Rejected by admin.');

        $proposalModel = new RubricProposalModel($this->pdo);
        $proposal = $proposalModel->getById($proposalId);
        if ($proposal) {
            $proposalModel->updateStatus($proposalId, 'rejected', $rejectionNotes);

            $notificationModel = new NotificationModel($this->pdo);
            $notificationModel->create($proposal['evaluator_id'], 'Your rubric proposal for tender "' . $proposal['tender_title'] . '" was rejected by the administrator. Reason: ' . $rejectionNotes);

            $audit = new AuditModel($this->pdo);
            $audit->log($_SESSION['user']['user_id'], 'reject_rubric_proposal', 'tender_rubric_proposals', $proposalId, 'Rejected rubric proposal for tender ' . $proposal['tender_id'] . ': ' . $rejectionNotes);
        }

        $this->redirect('review-rubric-proposals');
    }

    public function amendTender()
    {
        $this->requireRole(['officer']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('officer-dashboard');
        }
        $tenderId = intval($_POST['tender_id'] ?? 0);
        $notes = trim($_POST['amendment_notes'] ?? 'Tender amendment made.');
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $budget = floatval($_POST['budget'] ?? 0);
        $requiredGrade = trim($_POST['required_grade'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $region = trim($_POST['region'] ?? '');
        $deadline = trim($_POST['deadline'] ?? '');
        $evaluationDeadline = trim($_POST['evaluation_deadline'] ?? '');

        $tenderModel = new TenderModel($this->pdo);
        $tender = $tenderModel->getById($tenderId);
        if (!$tender) {
            $this->redirect('officer-dashboard');
        }

        $bidModel = new BidModel($this->pdo, $this->config['bid_encryption_key']);
        $existingBids = $bidModel->getByTender($tenderId);
        if ($tender['status'] !== 'draft' || !empty($existingBids)) {
            $audit = new AuditModel($this->pdo);
            $audit->log($_SESSION['user']['user_id'], 'blocked_tender_amendment', 'tenders', $tenderId, 'Blocked amendment attempt on immutable tender');
            $this->redirect('manage-tender&id=' . $tenderId);
        }

        $amendmentModel = new TenderAmendmentModel($this->pdo);
        $amendmentModel->create([
            'tender_id' => $tenderId,
            'changed_by' => $_SESSION['user']['user_id'],
            'previous_title' => $tender['title'],
            'previous_description' => $tender['description'],
            'previous_budget' => $tender['budget'],
            'previous_required_grade' => $tender['required_grade'],
            'previous_deadline' => $tender['deadline'],
            'previous_category' => $tender['category'],
            'previous_region' => $tender['region'],
            'version_number' => $tender['version'],
            'notes' => $notes,
        ]);

        $tenderModel->updateTender($tenderId, [
            'title' => $title ?: $tender['title'],
            'description' => $description ?: $tender['description'],
            'budget' => $budget ?: $tender['budget'],
            'required_grade' => $requiredGrade ?: $tender['required_grade'],
            'category' => $category ?: $tender['category'],
            'region' => $region ?: $tender['region'],
            'deadline' => $deadline ?: $tender['deadline'],
            'evaluation_deadline' => $evaluationDeadline ?: $tender['evaluation_deadline'],
            'status' => 'published',
        ]);

        $audit = new AuditModel($this->pdo);
        $audit->log($_SESSION['user']['user_id'], 'amend_tender', 'tenders', $tenderId, 'Tender amended and version incremented');
        $this->redirect('manage-tender&id=' . $tenderId);
    }

    public function releaseTenderForEvaluation()
    {
        $this->requireRole(['officer', 'admin']);
        $role = $_SESSION['user']['role'] ?? 'officer';
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect($role === 'admin' ? 'admin-dashboard' : 'officer-dashboard');
        }

        $tenderId = intval($_POST['tender_id'] ?? 0);
        $role = $_SESSION['user']['role'] ?? 'officer';
        $tenderModel = new TenderModel($this->pdo);
        $tender = $tenderModel->getById($tenderId);
        if (!$tender || !in_array($tender['status'], ['published', 'closed'], true)) {
            $this->redirect(($role === 'admin' ? 'tender-opening-room' : 'manage-tender') . '&id=' . $tenderId);
        }

        $bidModel = new BidModel($this->pdo, $this->config['bid_encryption_key']);
        $bids = $bidModel->getByTender($tenderId);
        if (empty($bids)) {
            $this->redirect(($role === 'admin' ? 'tender-opening-room' : 'manage-tender') . '&id=' . $tenderId);
        }

        if ($tender['status'] === 'published') {
            $tenderModel->updateStatus($tenderId, 'closed');
        }
        $tenderModel->markReleasedForEvaluation($tenderId);

        // Create and start an evaluation session so evaluators can mark "done evaluating"
        $sessionModel = new EvaluationSessionModel($this->pdo);
        $existingSession = $sessionModel->getByTender($tenderId);
        if (!$existingSession) {
            $sessionId = $sessionModel->create($tenderId, 120);
            $sessionModel->start($sessionId);
            // Link session to tender
            $this->pdo->prepare('UPDATE tenders SET evaluation_session_id = :sid WHERE tender_id = :tid')
                ->execute(['sid' => $sessionId, 'tid' => $tenderId]);
        }

        $notificationModel = new NotificationModel($this->pdo);
        $evaluatorProfileModel = new EvaluatorProfileModel($this->pdo);
        $approvedEvaluators = $evaluatorProfileModel->getApproved();
        foreach ($approvedEvaluators as $evaluator) {
            $notificationModel->create(
                (int)$evaluator['user_id'],
                'Tender "' . $tender['title'] . '" has been released for evaluation. Please review anonymized bids using the approved rubric.'
            );
        }

        $audit = new AuditModel($this->pdo);
        $audit->log($_SESSION['user']['user_id'], 'release_tender_evaluation', 'tenders', $tenderId, 'Tender released to approved evaluators with ' . count($bids) . ' bid(s)');

        $this->redirect(($role === 'admin' ? 'tender-opening-room' : 'manage-tender') . '&id=' . $tenderId);
    }

    public function manageTender()
    {
        $this->requireRole(['officer', 'admin']);

        $tenderId = intval($_GET['id'] ?? 0);

        $tenderModel = new TenderModel($this->pdo);
        $tender = $tenderModel->getById($tenderId);
        if (!$tender) {
            $this->redirect($_SESSION['user']['role'] . '-dashboard');
        }

        $bidModel        = new BidModel($this->pdo, $this->config['bid_encryption_key']);
        $evaluationModel = new EvaluationModel($this->pdo);
        $tenderDocModel  = new TenderDocumentModel($this->pdo);
        $bids = $bidModel->getByTender($tenderId);
        foreach ($bids as &$bid) {
            $bid['decrypted_amount'] = $bidModel->decryptAmount($bid['encrypted_amount']);
        }
        unset($bid);
        $evaluations    = $evaluationModel->getByTender($tenderId);
        $tenderDocuments = $tenderDocModel->getByTender($tenderId);

        $consensusModel = new ConsensusModel($this->pdo);
        $consensus      = $consensusModel->getByTender($tenderId);

        $this->view('admin/manage_tender', [
            'tender'          => $tender,
            'bids'            => $bids,
            'evaluations'     => $evaluations,
            'tenderDocuments' => $tenderDocuments,
            'consensus'       => $consensus,
        ]);
    }

    public function uploadTenderDocument()
    {
        $this->requireRole(['officer']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('officer-dashboard');
        }

        $tenderId    = intval($_POST['tender_id'] ?? 0);
        $docName     = trim($_POST['document_name'] ?? 'Tender Document');
        $allowedExts = ['pdf', 'jpg', 'jpeg', 'png', 'docx', 'xlsx'];
        $allowedMimes = [
            'application/pdf', 'image/jpeg', 'image/png',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        if (!isset($_FILES['tender_document']) || $_FILES['tender_document']['error'] !== UPLOAD_ERR_OK) {
            $this->redirect('manage-tender&id=' . $tenderId);
        }

        $tenderModel = new TenderModel($this->pdo);
        $tender = $tenderModel->getById($tenderId);
        if (!$tender || $tender['status'] !== 'draft') {
            $audit = new AuditModel($this->pdo);
            $audit->log($_SESSION['user']['user_id'], 'blocked_tender_document_upload', 'tenders', $tenderId, 'Blocked tender document upload on immutable tender');
            $this->redirect('manage-tender&id=' . $tenderId);
        }

        $fileTmp  = $_FILES['tender_document']['tmp_name'];
        $fileName = $_FILES['tender_document']['name'];
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $mime     = mime_content_type($fileTmp);

        if (!in_array($fileExt, $allowedExts, true) || !in_array($mime, $allowedMimes, true)) {
            $this->redirect('manage-tender&id=' . $tenderId);
        }

        $uploadDir = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'tender_docs' . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
            file_put_contents($uploadDir . '.htaccess', 'Deny from all');
        }

        $newFileName = sprintf('tender_%d_%s_%s.%s', $tenderId, preg_replace('/[^a-z0-9]/', '_', strtolower($docName)), time(), $fileExt);
        $destination = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmp, $destination)) {
            $docModel = new TenderDocumentModel($this->pdo);
            $docModel->create([
                'tender_id'     => $tenderId,
                'document_name' => $docName,
                'document_path' => $newFileName,
                'uploaded_by'   => $_SESSION['user']['user_id'],
            ]);
            $audit = new AuditModel($this->pdo);
            $audit->log($_SESSION['user']['user_id'], 'upload_tender_doc', 'tender_documents', $tenderId, "Tender document '{$docName}' uploaded");
        }

        $this->redirect('manage-tender&id=' . $tenderId);
    }

    public function awardTender()
    {
        $this->requireRole(['officer', 'admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect($_SESSION['user']['role'] . '-dashboard');
        }

        $tenderId = intval($_POST['tender_id'] ?? 0);
        $bidId    = intval($_POST['bid_id'] ?? 0);
        $tenderModel     = new TenderModel($this->pdo);
        $bidModel        = new BidModel($this->pdo, $this->config['bid_encryption_key']);

        // Fix: fetch tender before using it
        $tender = $tenderModel->getById($tenderId);
        $bid    = $bidModel->getById($bidId);
        if (
            !$tender ||
            !$bid ||
            (int)$bid['tender_id'] !== $tenderId ||
            !in_array($tender['status'], ['closed', 'evaluated'], true)
        ) {
            $this->redirect($_SESSION['user']['role'] . '-dashboard');
        }

        $tenderModel->updateStatus($tenderId, 'awarded');
        $audit = new AuditModel($this->pdo);
        $audit->log($_SESSION['user']['user_id'], 'award_tender', 'tenders', $tenderId, 'Tender awarded to bid ' . $bidId);

        $contractorModel = new ContractorModel($this->pdo);
        $contractor      = $contractorModel->getById($bid['contractor_id']);
        if ($contractor) {
            $notificationModel = new NotificationModel($this->pdo);
            $notificationModel->create(
                $contractor['user_id'],
                'Congratulations! Your bid has been selected as the winner for tender "' . $tender['title'] . '".' 
            );
            // Send email notification
            $userModel = new UserModel($this->pdo);
            $winnerUser = $userModel->findById($contractor['user_id']);
            if ($winnerUser) {
                $mailer = new MailerService($this->config);
                $mailer->send(
                    $winnerUser['email'],
                    $winnerUser['username'],
                    'Tender Award Notification — MicroProjects E-Tendering',
                    "Dear {$winnerUser['username']},\n\nCongratulations! Your bid has been selected as the winner for the tender: \"{$tender['title']}\".\n\nPlease log into the system for further instructions.\n\nMicroProjects Eswatini E-Tendering System"
                );
            }
        }

        $this->redirect('manage-tender&id=' . $tenderId);
    }

    public function manageConsensus()
    {
        $this->requireRole(['officer', 'evaluator', 'admin']);
        $tenderId = intval($_GET['id'] ?? 0);
        
        $tenderModel = new TenderModel($this->pdo);
        $tender = $tenderModel->getById($tenderId);
        
        $bidModel = new BidModel($this->pdo, $this->config['bid_encryption_key']);
        $bids = $bidModel->getByTender($tenderId);
        foreach ($bids as &$bid) {
            $bid['decrypted_amount'] = $bidModel->decryptAmount($bid['encrypted_amount']);
        }
        unset($bid);

        $evaluationModel = new EvaluationModel($this->pdo);

        // Aggregate: one averaged row per bid (excluding recused evaluators)
        $aggregateScores = $evaluationModel->getAggregateByTender($tenderId);

        // Build recommendation tally: how many evaluators recommended each bid
        $recStmt = $this->pdo->prepare('
            SELECT recommended_bid_id,
                   COUNT(*) AS vote_count,
                   GROUP_CONCAT(justification SEPARATOR " | ") AS justifications
            FROM evaluator_recommendations
            WHERE tender_id = :tender_id
            GROUP BY recommended_bid_id
            ORDER BY vote_count DESC
        ');
        $recStmt->execute(['tender_id' => $tenderId]);
        $recommendationTally = $recStmt->fetchAll(PDO::FETCH_ASSOC);

        // Index tally by bid_id for easy lookup in view
        $tallyByBid = [];
        foreach ($recommendationTally as $r) {
            $tallyByBid[(int)$r['recommended_bid_id']] = $r;
        }
        
        $consensusModel = new ConsensusModel($this->pdo);
        $existing = $consensusModel->getByTender($tenderId);

        $this->view('admin/consensus_meeting', [
            'tender'             => $tender,
            'bids'               => $bids,
            'evaluations'        => $aggregateScores,   // now aggregated
            'recommendationTally'=> $tallyByBid,
            'existing'           => $existing
        ]);
    }

    public function submitConsensus()
    {
        $this->requireRole(['officer', 'admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect($_SESSION['user']['role'] . '-dashboard');
        }

        $tenderId = intval($_POST['tender_id'] ?? 0);
        $winnerBidId = intval($_POST['winner_bid_id'] ?? 0);
        $justification = trim($_POST['justification'] ?? '');
        $evaluators = trim($_POST['evaluator_names'] ?? '');

        $consensusModel = new ConsensusModel($this->pdo);
        $consensusModel->create([
            'tender_id' => $tenderId,
            'winner_bid_id' => $winnerBidId,
            'justification' => $justification,
            'evaluator_ids' => $evaluators
        ]);

        $audit = new AuditModel($this->pdo);
        $audit->log($_SESSION['user']['user_id'], 'submit_consensus', 'tenders', $tenderId, 'Evaluator consensus reached for winner bid ' . $winnerBidId);

        $this->redirect('manage-tender&id=' . $tenderId);
    }

    public function scheduleConsensusMeeting()
    {
        $this->requireRole(['officer', 'admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect($_SESSION['user']['role'] . '-dashboard');
            return;
        }

        $tenderId = intval($_POST['tender_id'] ?? 0);
        $meetingLink = trim($_POST['meeting_link'] ?? '');

        $tenderModel = new TenderModel($this->pdo);
        $tender = $tenderModel->getById($tenderId);
        if (!$tender || empty($meetingLink)) {
            $this->redirect('manage-tender&id=' . $tenderId);
            return;
        }

        $tenderModel->updateConsensusMeetingLink($tenderId, $meetingLink);

        $userModel = new UserModel($this->pdo);
        $activeUsers = $userModel->getActiveUsers();
        $notificationModel = new NotificationModel($this->pdo);
        $message = 'Consensus evaluation meeting for "' . $tender['title'] . '" is scheduled. Join here: ' . $meetingLink;
        foreach ($activeUsers as $user) {
            $notificationModel->create($user['user_id'], $message);
        }

        $audit = new AuditModel($this->pdo);
        $audit->log($_SESSION['user']['user_id'], 'schedule_consensus_meeting', 'tenders', $tenderId, 'Consensus meeting scheduled: ' . $meetingLink);

        $this->redirect('manage-tender&id=' . $tenderId);
    }

    public function openEvaluationSession()
    {
        $this->requireRole(['officer']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('officer-dashboard');
        }

        $tenderId = intval($_POST['tender_id'] ?? 0);
        $tenderModel = new TenderModel($this->pdo);
        $tender = $tenderModel->getById($tenderId);

        if (!$tender || $tender['status'] !== 'closed') {
            $this->redirect('manage-tender&id=' . $tenderId);
            return;
        }

        // Create evaluation session
        $sessionModel = new EvaluationSessionModel($this->pdo);
        $sessionId = $sessionModel->create($tenderId, 120); // 2-hour session

        // Start the session
        $sessionModel->start($sessionId);

        // Update tender to link with session
        $tenderModel->startEvaluationSession($tenderId, $sessionId);

        // Initialize evaluator readiness tracking
        $evaluatorModel = new EvaluatorProfileModel($this->pdo);
        $evaluators = $evaluatorModel->getApproved();
        $readinessModel = new EvaluatorReadinessModel($this->pdo);
        
        foreach ($evaluators as $evaluator) {
            $readinessModel->create($sessionId, $tenderId, $evaluator['user_id']);
        }

        // Notify all evaluators that evaluation session has opened
        $notificationModel = new NotificationModel($this->pdo);
        foreach ($evaluators as $evaluator) {
            $notificationModel->create(
                $evaluator['user_id'],
                'Evaluation session for tender "' . $tender['title'] . '" has opened. ' .
                'You have 2 hours to evaluate all bids. Session ends at ' . 
                date('Y-m-d H:i', strtotime('+2 hours'))
            );
        }

        $audit = new AuditModel($this->pdo);
        $audit->log($_SESSION['user']['user_id'], 'open_evaluation_session', 'evaluation_sessions', $sessionId, 
                   'Opened evaluation session for tender ' . $tenderId);

        $this->redirect('manage-tender&id=' . $tenderId);
    }

    public function submitEvaluationComment()
    {
        $this->requireRole(['evaluator']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request method');
            return;
        }

        $tenderId = intval($_POST['tender_id'] ?? 0);
        $bidId = intval($_POST['bid_id'] ?? 0);
        $comment = trim($_POST['comment_text'] ?? '');
        $commentType = trim($_POST['comment_type'] ?? 'general_comment');

        if (!$tenderId || !$bidId || !$comment) {
            jsonResponse(false, 'Missing required fields');
            return;
        }

        // Get evaluation session for this tender
        $tenderModel = new TenderModel($this->pdo);
        $tender = $tenderModel->getWithEvaluationSession($tenderId);

        if (!$tender || !$tender['session_id'] || $tender['session_status'] !== 'in_progress') {
            jsonResponse(false, 'Evaluation session not active');
            return;
        }

        // Create comment
        $commentModel = new EvaluationCommentModel($this->pdo);
        $commentId = $commentModel->create([
            'tender_id' => $tenderId,
            'session_id' => $tender['session_id'],
            'evaluator_id' => $_SESSION['user']['user_id'],
            'bid_id' => $bidId,
            'comment_text' => $comment,
            'comment_type' => $commentType,
            'is_public' => 1,
        ]);

        $audit = new AuditModel($this->pdo);
        $audit->log($_SESSION['user']['user_id'], 'submit_evaluation_comment', 'evaluation_comments', $commentId,
                   'Submitted comment for tender ' . $tenderId . ', bid ' . $bidId);

        // Notify admins about flagged issues during evaluation
        if ($commentType === 'flag' || strpos(strtolower($comment), 'issue') !== false) {
            $userModel = new UserModel($this->pdo);
            $admins = $userModel->getActiveByRole('admin');
            $notificationModel = new NotificationModel($this->pdo);
            
            $evaluatorModel = new EvaluatorProfileModel($this->pdo);
            $evaluator = $evaluatorModel->getByUserId($_SESSION['user']['user_id']);
            $evaluatorName = $evaluator ? htmlspecialchars($evaluator['full_name']) : 'Evaluator ' . $_SESSION['user']['user_id'];
            
            foreach ($admins as $admin) {
                $notificationModel->create(
                    $admin['user_id'],
                    "⚠️ Flagged Comment: Evaluator {$evaluatorName} flagged an issue for tender '{$tender['title']}': " . substr($comment, 0, 50) . (strlen($comment) > 50 ? '...' : '')
                );
            }
        }

        jsonResponse(true, 'Comment submitted successfully', ['comment_id' => $commentId]);
    }

    public function markEvaluatorReady()
    {
        $this->requireRole(['evaluator']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request method');
            return;
        }

        $tenderId = intval($_POST['tender_id'] ?? 0);
        $isReady = isset($_POST['is_ready']) && $_POST['is_ready'] === '1';

        $tenderModel = new TenderModel($this->pdo);
        $tender = $tenderModel->getWithEvaluationSession($tenderId);

        if (!$tender || !$tender['session_id']) {
            jsonResponse(false, 'Evaluation session not found');
            return;
        }

        $readinessModel = new EvaluatorReadinessModel($this->pdo);
        $evaluatorId = $_SESSION['user']['user_id'];

        if ($isReady) {
            // First mark all evaluations as done
            $readinessModel->markAllEvaluationsDone($evaluatorId, $tender['session_id']);
            // Then mark as ready
            $success = $readinessModel->markReadyToClose($evaluatorId, $tender['session_id']);
        } else {
            $success = $readinessModel->markNotReady($evaluatorId, $tender['session_id']);
        }

        if ($success) {
            $audit = new AuditModel($this->pdo);
            $audit->log($_SESSION['user']['user_id'], 'mark_evaluator_ready', 'evaluator_readiness', 
                       $tender['session_id'], 'Marked as ' . ($isReady ? 'ready' : 'not ready'));

            jsonResponse(true, 'Status updated successfully');
        } else {
            jsonResponse(false, 'Failed to update status');
        }
    }

    public function closeEvaluation()
    {
        $this->requireRole(['officer']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('officer-dashboard');
        }

        $tenderId = intval($_POST['tender_id'] ?? 0);
        $tenderModel = new TenderModel($this->pdo);
        $tender = $tenderModel->getWithEvaluationSession($tenderId);

        if (!$tender || !$tender['session_id']) {
            $this->redirect('manage-tender&id=' . $tenderId);
            return;
        }

        // Close the evaluation session
        $sessionModel = new EvaluationSessionModel($this->pdo);
        $sessionModel->close($tender['session_id']);

        // Update tender status to "evaluated"
        $tenderModel->updateStatus($tenderId, 'evaluated');

        // Notify all users that evaluation is complete
        $notificationModel = new NotificationModel($this->pdo);
        $userModel = new UserModel($this->pdo);
        $activeUsers = $userModel->getActiveUsers();

        foreach ($activeUsers as $user) {
            $notificationModel->create(
                $user['user_id'],
                'Evaluation for tender "' . $tender['title'] . '" has been closed. ' .
                'The winning bid will be published soon.'
            );
        }

        $audit = new AuditModel($this->pdo);
        $audit->log($_SESSION['user']['user_id'], 'close_evaluation', 'evaluation_sessions', 
                   $tender['session_id'], 'Closed evaluation for tender ' . $tenderId);

        $this->redirect('manage-tender&id=' . $tenderId);
    }

    public function requestZoomMeeting()
    {
        $this->requireRole(['evaluator', 'officer']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request method');
            return;
        }

        $tenderId = intval($_POST['tender_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if (!$tenderId || !$reason) {
            jsonResponse(false, 'Missing required fields');
            return;
        }

        $tenderModel = new TenderModel($this->pdo);
        $tender = $tenderModel->getWithEvaluationSession($tenderId);

        if (!$tender || !$tender['session_id'] || $tender['session_status'] !== 'in_progress') {
            jsonResponse(false, 'Evaluation session not active');
            return;
        }

        // Create zoom meeting request
        $zoomModel = new ZoomMeetingRequestModel($this->pdo);
        $requestId = $zoomModel->create([
            'tender_id' => $tenderId,
            'session_id' => $tender['session_id'],
            'requested_by' => $_SESSION['user']['user_id'],
            'reason' => $reason,
            'status' => 'pending',
        ]);

        // Notify admins of pending request
        $userModel = new UserModel($this->pdo);
        $admins = $userModel->getActiveByRole('admin');
        $notificationModel = new NotificationModel($this->pdo);

        foreach ($admins as $admin) {
            $notificationModel->create(
                $admin['user_id'],
                'A zoom meeting has been requested during evaluation of tender "' . $tender['title'] . '". ' .
                'Reason: ' . $reason
            );
        }

        $audit = new AuditModel($this->pdo);
        $audit->log($_SESSION['user']['user_id'], 'request_zoom_meeting', 'zoom_meeting_requests', $requestId,
                   'Requested zoom meeting for tender ' . $tenderId);

        jsonResponse(true, 'Zoom meeting request submitted. Awaiting admin approval.', ['request_id' => $requestId]);
    }

    public function approveZoomMeeting()
    {
        $this->requireRole(['officer']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('officer-dashboard');
        }

        $requestId = intval($_POST['request_id'] ?? 0);
        $meetingLink = trim($_POST['meeting_link'] ?? '');

        if (!$requestId || !$meetingLink) {
            jsonResponse(false, 'Missing required fields');
            return;
        }

        $zoomModel = new ZoomMeetingRequestModel($this->pdo);
        $request = $zoomModel->getById($requestId);

        if (!$request) {
            jsonResponse(false, 'Request not found');
            return;
        }

        // Approve and set meeting link
        $zoomModel->approve($requestId, $_SESSION['user']['user_id'], $meetingLink);

        // Notify requester
        $notificationModel = new NotificationModel($this->pdo);
        $notificationModel->create(
            $request['requested_by'],
            'Your zoom meeting request has been approved. Join link: ' . $meetingLink
        );

        // Notify all evaluators
        $sessionModel = new EvaluationSessionModel($this->pdo);
        $readinessModel = new EvaluatorReadinessModel($this->pdo);
        $evaluators = $readinessModel->getBySession($request['session_id']);

        foreach ($evaluators as $evaluator) {
            $notificationModel->create(
                $evaluator['evaluator_id'],
                'A zoom meeting has been scheduled. Join link: ' . $meetingLink
            );
        }

        $audit = new AuditModel($this->pdo);
        $audit->log($_SESSION['user']['user_id'], 'approve_zoom_meeting', 'zoom_meeting_requests', $requestId,
                   'Approved zoom meeting: ' . $meetingLink);

        jsonResponse(true, 'Zoom meeting approved');
    }

    public function tenderOpeningRoom()
    {
        $this->requireRole(['officer', 'evaluator', 'contractor', 'admin']);

        $tenderId    = intval($_GET['id'] ?? 0);
        $tenderModel = new TenderModel($this->pdo);
        $tender      = $tenderModel->getWithEvaluationSession($tenderId);

        if (!$tender || !in_array($tender['status'], ['closed', 'evaluated', 'awarded'])) {
            $this->redirect($_SESSION['user']['role'] . '-dashboard');
            return;
        }

        $bidModel            = new BidModel($this->pdo, $this->config['bid_encryption_key']);
        $portfolioModel      = new PortfolioModel($this->pdo);
        $bidPortfolioModel   = new BidPortfolioModel($this->pdo);
        $contractorDocModel  = new ContractorDocumentModel($this->pdo);
        $bidDocModel         = new BidDocumentModel($this->pdo);
        $evaluationModel     = new EvaluationModel($this->pdo);
        $rubricModel         = new RubricModel($this->pdo);
        $consensusModel      = new ConsensusModel($this->pdo);

        $bids = $bidModel->getByTender($tenderId);

        foreach ($bids as &$bid) {
            // Decrypt amount
            $bid['decrypted_amount'] = $bidModel->decryptAmount($bid['encrypted_amount']);

            // Anonymous code (same algorithm as EvaluatorController)
            $seed = ($bid['tender_id'] ?? '') . '|' . ($bid['bid_id'] ?? '') . '|' . ($bid['digital_signature'] ?? '');
            $bid['anonymous_code'] = 'BID-' . strtoupper(substr(hash('sha256', $seed), 0, 10));

            // Verify digital signature integrity
            $reconstructed = implode('|', [
                $bid['tender_id'],
                $bid['contractor_id'],
                $bid['encrypted_amount'],
                $bid['submitted_at'],
                (int)($bid['eligibility_confirmed'] ?? 0),
                (int)($bid['conflict_declared'] ?? 0),
                md5($bid['method_statement'] ?? ''),
                (int)($bid['form_of_tender_accepted'] ?? 0),
            ]);
            $expected = hash_hmac('sha256', $reconstructed, $this->config['bid_encryption_key']);
            $bid['signature_valid'] = hash_equals($expected, $bid['digital_signature'] ?? '');

            // Envelope status defaults
            if (empty($bid['envelope_status'])) {
                $bid['envelope_status'] = 'sealed';
            }

            // Document checklist (Phase 1 + Phase 2 contractor documents)
            $allRequired = array_merge(
                $contractorDocModel->getRequiredDocumentsForPhase('phase1'),
                $contractorDocModel->getRequiredDocumentsForPhase('phase2')
            );
            $bid['doc_checklist'] = [];
            foreach ($allRequired as $docName => $cfg) {
                $latestDoc = $contractorDocModel->getLatestByName($bid['contractor_id'], $docName);
                $bid['doc_checklist'][] = [
                    'name'  => $docName,
                    'valid' => $contractorDocModel->isDocumentValid(
                        $bid['contractor_id'], $docName, $cfg['max_age_days'] ?? null
                    ),
                    'document_id' => $latestDoc ? $latestDoc['document_id'] : null,
                    'is_fraudulent' => $latestDoc ? (int)$latestDoc['is_fraudulent'] : 0,
                    'is_incorrect'  => $latestDoc ? (int)$latestDoc['is_incorrect'] : 0,
                    'flag_reason'   => $latestDoc ? $latestDoc['flag_reason'] : '',
                ];
            }

            // Phase 3 Documents
            $bid['tender_security']    = $bidDocModel->getLatestByName($bid['bid_id'], 'Tender Security');
            $bid['proof_of_funds']     = $bidDocModel->getLatestByName($bid['bid_id'], 'Proof of Funds');
            $bid['tender_fee_receipt'] = $bidDocModel->getLatestByName($bid['bid_id'], 'Proof of Payment (Tender Fee)');

            // Bid-specific portfolio media (images/videos uploaded at bid time)
            $bid['bid_media'] = $bidPortfolioModel->getMediaByBid($bid['bid_id']);

            // General company portfolio (past projects)
            $bid['portfolio'] = $portfolioModel->getByContractorWithMedia($bid['contractor_id']);
        }
        unset($bid);

        // Evaluations – ranked
        $evaluations = $evaluationModel->getByTender($tenderId);
        foreach ($evaluations as &$ev) {
            $seed = ($ev['tender_id'] ?? '') . '|' . ($ev['bid_id'] ?? '') . '|' . ($ev['digital_signature'] ?? '');
            $ev['anonymous_code'] = 'BID-' . strtoupper(substr(hash('sha256', $seed), 0, 10));
        }
        unset($ev);
        $ranked = $evaluationModel->rankCandidates($evaluations);

        $rubric    = $rubricModel->getByTender($tenderId);
        $consensus = $consensusModel->getByTender($tenderId);

        // Award specifications (if already awarded) — create table on first use if missing
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS tender_award_specifications (
                spec_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                tender_id      INT UNSIGNED NOT NULL,
                bid_id         INT UNSIGNED NOT NULL,
                awarded_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                project_start  DATE         DEFAULT NULL,
                project_end    DATE         DEFAULT NULL,
                notes          TEXT         DEFAULT NULL,
                created_by     INT UNSIGNED DEFAULT NULL,
                INDEX idx_tender_id (tender_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $stmtSpecs = $this->pdo->prepare('SELECT * FROM tender_award_specifications WHERE tender_id = :tid LIMIT 1');
        $stmtSpecs->execute(['tid' => $tenderId]);
        $awardSpecs = $stmtSpecs->fetch() ?: null;

        $this->view('tenders/opening_room', [
            'tender'      => $tender,
            'bids'        => $bids,
            'evaluations' => $ranked,
            'rubric'      => $rubric,
            'consensus'   => $consensus,
            'awardSpecs'  => $awardSpecs,
            'role'        => $_SESSION['user']['role'],
        ]);
    }

    public function apiBreakSeal()
    {
        $this->requireRole(['officer', 'evaluator', 'admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect($_SESSION['user']['role'] . '-dashboard');
            return;
        }

        $bidId    = intval($_POST['bid_id'] ?? 0);
        $tenderId = intval($_POST['tender_id'] ?? 0);

        $stmt = $this->pdo->prepare(
            "UPDATE bids SET envelope_status = 'opened' WHERE bid_id = :bid_id AND envelope_status = 'sealed'"
        );
        $stmt->execute(['bid_id' => $bidId]);

        $audit = new AuditModel($this->pdo);
        $audit->log(
            $_SESSION['user']['user_id'],
            'break_bid_seal', 'bids', $bidId,
            'Bid envelope opened in public opening session for tender ' . $tenderId
        );

        $this->redirect('tender-opening-room&id=' . $tenderId);
    }

    public function apiVerifyLegitimacy()
    {
        $this->requireRole(['officer', 'evaluator', 'admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect($_SESSION['user']['role'] . '-dashboard');
            return;
        }

        $bidId        = intval($_POST['bid_id'] ?? 0);
        $tenderId     = intval($_POST['tender_id'] ?? 0);
        $rawStatus    = $_POST['legitimacy_status'] ?? 'invalid';
        $legStatus    = in_array($rawStatus, ['legit', 'invalid'], true) ? $rawStatus : 'invalid';
        $notes        = trim($_POST['legitimacy_notes'] ?? '');
        $envStatus    = $legStatus === 'legit' ? 'verified' : 'rejected';

        $stmt = $this->pdo->prepare(
            'UPDATE bids SET legitimacy_status = :ls, legitimacy_notes = :ln, envelope_status = :es
             WHERE bid_id = :bid_id'
        );
        $stmt->execute([
            'ls'     => $legStatus,
            'ln'     => $notes,
            'es'     => $envStatus,
            'bid_id' => $bidId,
        ]);

        $audit = new AuditModel($this->pdo);
        $audit->log(
            $_SESSION['user']['user_id'],
            'verify_bid_legitimacy', 'bids', $bidId,
            'Bid legitimacy set to ' . $legStatus . '. Notes: ' . $notes
        );

        $this->redirect('tender-opening-room&id=' . $tenderId);
    }

    public function apiAwardPublicly()
    {
        $this->requireRole(['officer', 'admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect($_SESSION['user']['role'] . '-dashboard');
            return;
        }

        $tenderId    = intval($_POST['tender_id'] ?? 0);
        $bidId       = intval($_POST['bid_id'] ?? 0);
        $winnerNotes = trim($_POST['winner_notes'] ?? '');

        $tenderModel = new TenderModel($this->pdo);
        $bidModel    = new BidModel($this->pdo, $this->config['bid_encryption_key']);
        $tender      = $tenderModel->getById($tenderId);
        $bid         = $bidModel->getById($bidId);

        if (!$tender || !$bid || (int)$bid['tender_id'] !== $tenderId) {
            $this->redirect('tender-opening-room&id=' . $tenderId);
            return;
        }

        // Update tender status
        $tenderModel->updateStatus($tenderId, 'awarded');

        // Persist award specifications
        $stmtUpsert = $this->pdo->prepare('
            INSERT INTO tender_award_specifications (tender_id, bid_id, winner_notes, status, submitted_at)
            VALUES (:tender_id, :bid_id, :notes, :status, NOW())
            ON DUPLICATE KEY UPDATE
                bid_id       = VALUES(bid_id),
                winner_notes = VALUES(winner_notes),
                status       = VALUES(status)
        ');
        $stmtUpsert->execute([
            'tender_id' => $tenderId,
            'bid_id'    => $bidId,
            'notes'     => $winnerNotes,
            'status'    => 'pending',
        ]);

        // Notify the winning contractor
        $contractorModel = new ContractorModel($this->pdo);
        $contractor      = $contractorModel->getById($bid['contractor_id']);
        if ($contractor) {
            $notificationModel = new NotificationModel($this->pdo);
            $notificationModel->create(
                $contractor['user_id'],
                'Congratulations! Your bid has been selected as the winner for tender "' .
                $tender['title'] .
                '". Please log in to review the project specifications and expectations from your dashboard.'
            );
        }

        $audit = new AuditModel($this->pdo);
        $audit->log(
            $_SESSION['user']['user_id'],
            'award_tender_publicly', 'tenders', $tenderId,
            'Tender publicly awarded to bid #' . $bidId . ' during opening session.'
        );

        $this->redirect('tender-opening-room&id=' . $tenderId . '&awarded=1');
    }

    public function winnerSpecifications()
    {
        $this->requireRole(['officer', 'contractor']);
        $tenderId = intval($_GET['id'] ?? 0);

        $stmt = $this->pdo->prepare('
            SELECT tas.*, t.title AS tender_title, t.description AS tender_description,
                   b.contractor_id, b.submitted_at AS bid_submitted_at
            FROM   tender_award_specifications tas
            JOIN   tenders t ON tas.tender_id = t.tender_id
            JOIN   bids    b ON tas.bid_id    = b.bid_id
            WHERE  tas.tender_id = :tender_id
            LIMIT  1
        ');
        $stmt->execute(['tender_id' => $tenderId]);
        $specs = $stmt->fetch();

        if (!$specs) {
            $this->redirect($_SESSION['user']['role'] === 'contractor' ? 'contractor-dashboard' : 'admin-dashboard');
            return;
        }

        // Contractors may only view their own award
        if ($_SESSION['user']['role'] === 'contractor') {
            $contractorModel = new ContractorModel($this->pdo);
            $contractor      = $contractorModel->getByUserId($_SESSION['user']['user_id']);
            if (!$contractor || (int)$specs['contractor_id'] !== (int)$contractor['contractor_id']) {
                $this->redirect('contractor-dashboard');
                return;
            }
        }

        $submitted = !empty($_GET['submitted']);
        $this->view('tenders/winner_specifications', [
            'specs'     => $specs,
            'tenderId'  => $tenderId,
            'submitted' => $submitted,
        ]);
    }

}
