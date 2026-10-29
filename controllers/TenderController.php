<?php
class TenderController extends BaseController
{
    public function list()
    {
        $tenderModel = new TenderModel($this->pdo);
        // Show published, closed, and tenders currently open for evaluation so users can see all active opportunities
        $published = $tenderModel->getPublished();
        $closed = $tenderModel->getByStatus('closed');
        $evaluated = $tenderModel->getByStatus('evaluated');
        $tenders = array_merge($published, $closed, $evaluated);

        // Filter/search params
        $filterCategory = trim($_GET['category'] ?? '');
        $filterGrade    = trim($_GET['grade'] ?? '');
        $filterRegion   = trim($_GET['region'] ?? '');
        $filterSearch   = trim($_GET['search'] ?? '');

        if ($filterCategory !== '') {
            $tenders = array_filter($tenders, fn($t) => $t['category'] === $filterCategory);
        }
        if ($filterRegion !== '') {
            $tenders = array_filter($tenders, fn($t) => $t['region'] === $filterRegion);
        }
        if ($filterGrade !== '') {
            $tenders = array_filter($tenders, fn($t) => stripos($t['required_grade'], $filterGrade) !== false);
        }
        if ($filterSearch !== '') {
            $tenders = array_filter($tenders, fn($t) =>
                stripos($t['title'], $filterSearch) !== false ||
                stripos($t['description'], $filterSearch) !== false
            );
        }

        $categories = ['Construction', 'Infrastructure', 'Health Facilities', 'Roads', 'Schools'];
        $regions = ['Hhohho', 'Manzini', 'Shiselweni', 'Lubombo'];

        $this->view('tenders/list', [
            'tenders'        => array_values($tenders),
            'categories'     => $categories,
            'regions'        => $regions,
            'filterCategory' => $filterCategory,
            'filterRegion'   => $filterRegion,
            'filterGrade'    => $filterGrade,
            'filterSearch'   => $filterSearch,
        ]);
    }

    public function show()
    {
        $tenderId = intval($_GET['id'] ?? 0);
        $tenderModel = new TenderModel($this->pdo);
        $tenderModel->closeExpiredTenders();
        $tender = $tenderModel->getById($tenderId);
        if (!$tender) {
            $this->redirect('tenders');
        }

        // Load tender documents
        $tenderDocModel = new TenderDocumentModel($this->pdo);
        $tenderDocuments = $tenderDocModel->getByTender($tenderId);

        // Load scoring rubric
        $rubricModel = new RubricModel($this->pdo);
        $rubric = $rubricModel->getByTender($tenderId);

        $contractor = null;
        $canSubmitBid = false;
        $alreadyBid = false;
        $complianceIssues = [];
        $requiredDocs = [];
        $phase1Valid = false;
        $phase2Valid = false;

        if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'contractor') {
            $contractorModel = new ContractorModel($this->pdo);
            $contractor = $contractorModel->getByUserId($_SESSION['user']['user_id']);
            if ($contractor && $contractor['verification_status'] === 'approved' && !$contractor['blacklisted']) {
                $documentModel = new ContractorDocumentModel($this->pdo);

                // Check Phase 1 compliance (account status)
                $phase1Compliance = $documentModel->checkPhaseCompliance($contractor['contractor_id'], 'phase1');
                $phase1Valid = !in_array(false, array_column($phase1Compliance, 'valid'));

                // Check Phase 2 compliance (bidding requirements)
                $phase2Compliance = $documentModel->checkPhaseCompliance($contractor['contractor_id'], 'phase2');
                $phase2Valid = !in_array(false, array_column($phase2Compliance, 'valid'));

                // Build compliance issues and required docs list
                $allRequiredDocs = array_merge(
                    $documentModel->getRequiredDocumentsForPhase('phase1'),
                    $documentModel->getRequiredDocumentsForPhase('phase2')
                );

                foreach ($allRequiredDocs as $docName => $config) {
                    $doc = $documentModel->getLatestByName($contractor['contractor_id'], $docName);
                    $isValid = $documentModel->isDocumentValid($contractor['contractor_id'], $docName, $config['max_age_days'] ?? null);
                    $requiredDocs[] = [
                        'name' => $docName,
                        'uploaded' => (bool)$doc,
                        'valid' => $isValid,
                        'uploaded_at' => $doc['uploaded_at'] ?? null,
                        'max_age_days' => $config['max_age_days'] ?? null,
                    ];

                    if (!$isValid) {
                        if ($config['max_age_days']) {
                            $complianceIssues[] = "{$docName} is expired or missing (required every {$config['max_age_days']} days).";
                        } else {
                            $complianceIssues[] = "{$docName} is required.";
                        }
                    }
                }

                $bidModel = new BidModel($this->pdo, $this->config['bid_encryption_key']);
                $alreadyBid = $bidModel->existsForTender($tenderId, $contractor['contractor_id']);
                $canSubmitBid = !$alreadyBid && $tender['status'] === 'published' && strtotime($tender['deadline']) >= time();
            }
        }


        $this->view('tenders/view', [
            'tender'          => $tender,
            'tenderDocuments' => $tenderDocuments,
            'rubric'          => $rubric,
            'canSubmitBid'    => $canSubmitBid,
            'alreadyBid'      => $alreadyBid,
            'contractor'      => $contractor,
            'complianceIssues' => $complianceIssues,
            'requiredDocs'    => $requiredDocs,
        ]);
    }

    public function submitBid()
    {
        $this->requireRole(['contractor']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('contractor-dashboard');
            return;
        }

        try {
            $tenderId = intval($_POST['tender_id'] ?? 0);
            $amount = floatval($_POST['bid_amount'] ?? 0);
            
            // Validate bid amount
            $contractorModel = new ContractorModel($this->pdo);
            $userId = $_SESSION['user']['user_id'];
            $notificationModel = new NotificationModel($this->pdo);

            if ($amount <= 0) {
                $notificationModel->create($userId, 'Bid failed: Bid amount must be greater than zero.');
                $this->redirect('contractor-dashboard&error=' . urlencode('Bid amount must be greater than zero.'));
                return;
            }

            $contractor = $contractorModel->getByUserId($userId);

            if (!$contractor) {
                $notificationModel->create($userId, 'Bid failed: Contractor profile not found.');
                $this->redirect('contractor-dashboard&error=' . urlencode('Contractor profile not found.'));
                return;
            }

            $tenderModel = new TenderModel($this->pdo);
            $tenderModel->closeExpiredTenders();
            $tender = $tenderModel->getById($tenderId);

            if (!$tender) {
                $notificationModel->create($userId, 'Bid failed: Tender not found.');
                $this->redirect('contractor-dashboard&error=' . urlencode('Tender not found.'));
                return;
            }

            if ($tender['status'] !== 'published') {
                $notificationModel->create($userId, 'Bid failed: Tender not accepting bids (status: ' . $tender['status'] . ').');
                $this->redirect('contractor-dashboard&error=' . urlencode('This tender is not accepting bids (status: ' . $tender['status'] . ').'));
                return;
            }

            if (strtotime($tender['deadline']) < time()) {
                $notificationModel->create($userId, 'Bid failed: Tender deadline has passed.');
                $this->redirect('contractor-dashboard&error=' . urlencode('Tender deadline has passed.'));
                return;
            }

            // Bypass verification status check so contractors with missing docs can still bid
            /*
            if ($contractor['verification_status'] !== 'approved') {
                $notificationModel->create($userId, 'Bid failed: Company verification required (status: ' . $contractor['verification_status'] . ').');
                $this->redirect('contractor-dashboard&error=' . urlencode('Your company must be verified before bidding. Current status: ' . $contractor['verification_status']));
                return;
            }
            */

            if ($contractor['blacklisted']) {
                $notificationModel->create($userId, 'Bid failed: Company blacklisted.');
                $this->redirect('contractor-dashboard&error=' . urlencode('Your company is blacklisted. Reason: ' . ($contractor['blacklist_reason'] ?: 'Not specified')));
                return;
            }

            $documentModel = new ContractorDocumentModel($this->pdo);

            $allRequiredDocs = array_merge(
                $documentModel->getRequiredDocumentsForPhase('phase1'),
                $documentModel->getRequiredDocumentsForPhase('phase2')
            );

            $uploadDir = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'contractor_docs' . DIRECTORY_SEPARATOR;
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
                file_put_contents($uploadDir . '.htaccess', 'Deny from all');
            }

            $allowedExts = ['pdf', 'jpg', 'jpeg', 'png'];
            $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png'];

            foreach ($allRequiredDocs as $docName => $config) {
                $inputName = 'doc_' . preg_replace('/[^a-z0-9]/', '_', strtolower(str_replace(' ', '_', $docName)));
                if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
                    $fileTmp  = $_FILES[$inputName]['tmp_name'];
                    $fileName = $_FILES[$inputName]['name'];
                    $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) ?: 'pdf';
                    $mime = mime_content_type($fileTmp);

                    // Relaxed validation: allow any file type
                    // if (!in_array($fileExt, $allowedExts, true) || !in_array($mime, $allowedMimes, true)) {
                    //     $notificationModel->create($userId, 'Bid failed: Invalid file type for ' . $docName . '.');
                    //     $this->redirect('tender-view&id=' . $tenderId . '&error=' . urlencode("Invalid file type for {$docName}. Upload PDF, JPG, or PNG only."));
                    //     return;
                    // }

                    $newFileName = sprintf('contractor_%d_%s_%d.%s', $contractor['contractor_id'], preg_replace('/[^a-z0-9]/', '_', strtolower(str_replace(' ', '_', $docName))), time(), $fileExt);
                    $destination = $uploadDir . $newFileName;

                    if ($this->moveUploadedFile($fileTmp, $destination)) {
                        // Calculate expiry date if applicable
                        $expiryDate = null;
                        if ($docName === 'Tax Clearance Certificate') {
                            $expiryDate = date('Y-m-d', strtotime('+30 days'));
                        } elseif ($docName === 'SNPF/ENPF Compliance' || $docName === 'Police Clearance') {
                            $expiryDate = date('Y-m-d', strtotime('+90 days'));
                        }

                        // Identify phase
                        $phase1Requirements = $documentModel->getRequiredDocumentsForPhase('phase1');
                        $phase = isset($phase1Requirements[$docName]) ? 'phase1' : 'phase2';

                        $documentModel->create([
                            'contractor_id' => $contractor['contractor_id'],
                            'document_name' => $docName,
                            'document_path' => $newFileName,
                            'expiry_date'   => $expiryDate,
                            'document_phase' => $phase,
                        ]);
                    }
                }
            }

            // Now check compliance after processing any uploaded files.
            $missingDocuments = [];
            foreach ($allRequiredDocs as $docName => $config) {
                $isValid = $documentModel->isDocumentValid($contractor['contractor_id'], $docName, $config['max_age_days'] ?? null);
                if (!$isValid) {
                    $missingDocuments[] = $docName;
                }
            }

            // Removed compliance check that blocks bids for missing documents. 
            // The evaluation phase will catch fraudulent or missing documents.
            /*
            if (!empty($missingDocuments)) {
                $message = 'Please upload current required documents before bidding: ' . implode(', ', $missingDocuments) . '.';
                $notificationModel->create($userId, 'Bid failed: Missing compliance documents.');
                $this->redirect('tender-view&id=' . $tenderId . '&error=' . urlencode($message));
                return;
            }
            */

            $bidModel = new BidModel($this->pdo, $this->config['bid_encryption_key']);
            if ($bidModel->existsForTender($tenderId, $contractor['contractor_id'])) {
                $notificationModel->create($userId, 'Bid failed: Duplicate bid attempt.');
                $this->redirect('tender-view&id=' . $tenderId . '&error=' . urlencode('You have already submitted a bid for this tender.'));
                return;
            }

            // Create the bid
            $bidId = $bidModel->create([
                'tender_id'    => $tenderId,
                'contractor_id' => $contractor['contractor_id'],
                'amount'       => $amount,
                'submitted_at' => date('Y-m-d H:i:s'),
                'eligibility_confirmed' => isset($_POST['eligibility_confirmed']) ? 1 : 0,
                'conflict_declared'     => isset($_POST['conflict_declared']) ? 1 : 0,
                'conflict_details'      => $_POST['conflict_details'] ?? null,
                'method_statement'      => $_POST['method_statement'] ?? null,
                'form_of_tender_accepted' => isset($_POST['form_of_tender_accepted']) ? 1 : 0,
                'site_visit_date'       => !empty($_POST['site_visit_date']) ? $_POST['site_visit_date'] : null,
                'site_visit_officer'    => !empty($_POST['site_visit_officer']) ? $_POST['site_visit_officer'] : null,
            ]);

            // Handle bid portfolio media uploads (optional)
            if (!empty($_FILES['bid_portfolio_files']['name'][0])) {
                $portfolioUploadDir = realpath(__DIR__ . '/../public') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'bid_portfolio' . DIRECTORY_SEPARATOR;
                if (!is_dir($portfolioUploadDir)) {
                    mkdir($portfolioUploadDir, 0755, true);
                }
                $allowedPortfolioExts  = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'webm'];
                $allowedPortfolioMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'video/mp4', 'video/webm'];
                $bidPortfolioModel     = new BidPortfolioModel($this->pdo);
                $portfolioCount = count($_FILES['bid_portfolio_files']['name']);
                for ($pi = 0; $pi < $portfolioCount; $pi++) {
                    if ($_FILES['bid_portfolio_files']['error'][$pi] !== UPLOAD_ERR_OK) continue;
                    $pfName = $_FILES['bid_portfolio_files']['name'][$pi];
                    $pfTmp  = $_FILES['bid_portfolio_files']['tmp_name'][$pi];
                    $pfExt  = strtolower(pathinfo($pfName, PATHINFO_EXTENSION));
                    $pfMime = mime_content_type($pfTmp);
                    // Relaxed validation
                    // if (!in_array($pfExt, $allowedPortfolioExts, true) || !in_array($pfMime, $allowedPortfolioMimes, true)) continue;
                    $safePfName = sprintf('bidmedia_%d_%d_%s.%s', $bidId, $pi, bin2hex(random_bytes(4)), $pfExt);
                    if (move_uploaded_file($pfTmp, $portfolioUploadDir . $safePfName)) {
                        $bidPortfolioModel->createMedia($bidId, 'uploads/bid_portfolio/' . $safePfName, $pfMime);
                    }
                }
            }

            // Handle Phase 3 documents (per tender submission)
            $phase3UploadError = $this->handlePhase3Documents($bidId);
            if ($phase3UploadError) {
                $bidModel->delete($bidId); 
                $notificationModel->create($userId, 'Bid failed: ' . $phase3UploadError);
                $this->redirect('tender-view&id=' . $tenderId . '&error=' . urlencode('Bid submission failed: ' . $phase3UploadError));
                return;
            }

            // Check if all Phase 3 documents were uploaded
            // Removed strict Phase 3 requirement during bid submission so missing docs are caught at evaluation.
            /*
            $bidDocumentModel = new BidDocumentModel($this->pdo);
            if (!$bidDocumentModel->hasRequiredDocuments($bidId)) {
                $bidModel->delete($bidId);
                $notificationModel->create($userId, 'Bid failed: Tender Security document missing.');
                $this->redirect('tender-view&id=' . $tenderId . '&error=' . urlencode('Tender Security document is required for bid submission.'));
                return;
            }
            */

            $audit = new AuditModel($this->pdo);
            $audit->log($userId, 'submit_bid', 'bids', $bidId, 'Bid submitted for tender ' . $tenderId);

            // Fraud detection is deferred until tender opening/evaluation.
            $notificationModel->create($userId, 'Your bid for tender "' . $tender['title'] . '" was received.');

            $userModel = new UserModel($this->pdo);
            $admins = $userModel->getActiveByRole('admin');
            $adminMessage = 'New bid submitted by "' . $contractor['company_name'] . '" for tender "' . $tender['title'] . '". Review the tender bids and assign approved evaluators where needed.';
            foreach ($admins as $admin) {
                $notificationModel->create((int)$admin['user_id'], $adminMessage);
            }

            // Redirect to the printable receipt so the contractor can save/print confirmation
            $this->redirect('bid-receipt&id=' . $bidId);
        } catch (Exception $e) {
            // Log and notify user
            if (isset($notificationModel) && isset($userId)) {
                $notificationModel->create($userId, 'Bid failed: Unexpected error - ' . $e->getMessage());
            }
            $this->redirect('contractor-dashboard&error=' . urlencode('An unexpected error occurred: ' . $e->getMessage()));
        }
    }

    public function apiSubmitBid()
    {
        jsonResponse(false, 'Please submit bids from the tender page so the required documents and declarations are attached.');
    }

    /**
     * Runs the Python fraud detection script with REAL features derived from the database.
     * Returns the fraud probability float (0.0 = clean, 1.0 = very suspicious).
     */
    private function runFraudCheck(int $bidId, int $tenderId, float $submittedAmount, int $contractorId): float
    {
        // --- Compute real features from actual bid data ---

        // 1. price_similarity: how close this bid is to the average of all other bids for same tender
        //    Value near 0 = almost identical to others (suspicious), near 1 = very different
        $stmt = $this->pdo->prepare(
            'SELECT AVG(b2.encrypted_amount) AS avg_enc FROM bids b2 WHERE b2.tender_id = :tender_id AND b2.bid_id != :bid_id'
        );
        // We can't average encrypted values — instead count bids and compute spread ratio
        // Use: how many bids exist for this tender (more bids → lower suspicion)
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) AS bid_count, MIN(submitted_at) AS first_bid FROM bids WHERE tender_id = :tender_id'
        );
        $stmt->execute(['tender_id' => $tenderId]);
        $tenderBidData = $stmt->fetch();
        $totalBids = max(1, (int)$tenderBidData['bid_count']);

        // 2. submission_gap: minutes between first bid and this bid submission
        $firstBidTime = $tenderBidData['first_bid'] ? strtotime($tenderBidData['first_bid']) : time();
        $submissionGap = max(0, intval((time() - $firstBidTime) / 60)); // in minutes

        // 3. same_location: whether this contractor has bid on other tenders alongside the same contractor pool
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(DISTINCT b.tender_id) AS shared_tenders
             FROM bids b
             WHERE b.contractor_id != :contractor_id
               AND b.tender_id IN (SELECT tender_id FROM bids WHERE contractor_id = :contractor_id2)'
        );
        $stmt->execute(['contractor_id' => $contractorId, 'contractor_id2' => $contractorId]);
        $sharedRow = $stmt->fetch();
        $sameLocation = min(1, (int)($sharedRow['shared_tenders'] ?? 0));

        // 4. bid_age_days: how many days the tender has been open when this bid was submitted
        $stmt = $this->pdo->prepare('SELECT published_at, deadline FROM tenders WHERE tender_id = :tender_id');
        $stmt->execute(['tender_id' => $tenderId]);
        $tenderRow = $stmt->fetch();
        $publishedAt = $tenderRow['published_at'] ? strtotime($tenderRow['published_at']) : time();
        $bidAgeDays  = max(1, intval((time() - $publishedAt) / 86400));

        // 5. price_similarity: ratio of bid amount vs tender budget (closer to 1.0 = bid near budget = suspicious)
        $stmt = $this->pdo->prepare('SELECT budget FROM tenders WHERE tender_id = :tender_id');
        $stmt->execute(['tender_id' => $tenderId]);
        $budgetRow = $stmt->fetch();
        $budget = (float)($budgetRow['budget'] ?? 1);
        $priceSimilarity = $budget > 0 ? round(min(1, $submittedAmount / $budget), 4) : 0.5;

        // --- Call Python script ---
        $pythonScript = realpath(__DIR__ . '/../python/fraud_detection.py');
        if (!$pythonScript) {
            return 0.0;
        }

        $pythonScriptArg    = escapeshellarg($pythonScript);
        $priceSimilarityArg = escapeshellarg((string)$priceSimilarity);
        $submissionGapArg   = escapeshellarg((string)$submissionGap);
        $sameLocationArg    = escapeshellarg((string)$sameLocation);
        $bidAgeDaysArg      = escapeshellarg((string)$bidAgeDays);

        $command = "python $pythonScriptArg $priceSimilarityArg $submissionGapArg $sameLocationArg $bidAgeDaysArg 2>/dev/null";
        $output  = null;
        $retCode = 0;
        exec($command, $outputLines, $retCode);
        $fraudScoreStr = !empty($outputLines) ? trim(implode('', $outputLines)) : '0';
        return floatval($fraudScoreStr);
    }

    protected function handlePhase3Documents(int $bidId): ?string
    {
        $expectedDocs = [
            'tender_security' => 'Tender Security',
            'proof_of_funds'  => 'Proof of Funds',
        ];
        
        if (isset($_FILES['tender_fee_receipt']) && $_FILES['tender_fee_receipt']['error'] !== UPLOAD_ERR_NO_FILE) {
            $expectedDocs['tender_fee_receipt'] = 'Proof of Payment (Tender Fee)';
        }

        $allowedExts  = ['pdf', 'jpg', 'jpeg', 'png'];
        $allowedMimes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];

        // Store outside public/ for security
        $uploadDir = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                return 'Failed to create upload directory.';
            }
            // Protect directory
            file_put_contents($uploadDir . '.htaccess', 'Deny from all');
        }

        $bidDocumentModel = new BidDocumentModel($this->pdo);

        foreach ($expectedDocs as $inputName => $docTitle) {
            // Relaxed validation: do not return error if document is missing
            // if (!isset($_FILES[$inputName])) {
            //     return "Missing required document: {$docTitle}.";
            // }

            $errors = is_array($_FILES[$inputName]['error'])
                ? $_FILES[$inputName]['error']
                : [$_FILES[$inputName]['error']];
            $names  = is_array($_FILES[$inputName]['name'])
                ? $_FILES[$inputName]['name']
                : [$_FILES[$inputName]['name']];
            $tmps   = is_array($_FILES[$inputName]['tmp_name'])
                ? $_FILES[$inputName]['tmp_name']
                : [$_FILES[$inputName]['tmp_name']];

            $hasValidFile = false;
            foreach ($errors as $i => $errCode) {
                if ($errCode === UPLOAD_ERR_OK && !empty($names[$i])) {
                    $ext  = strtolower(pathinfo($names[$i], PATHINFO_EXTENSION)) ?: 'pdf';
                    $mime = mime_content_type($tmps[$i]);
                    // Relaxed validation
                    // if (!in_array($ext, $allowedExts, true) || !in_array($mime, $allowedMimes, true)) {
                    //     return "{$docTitle} must be a PDF, JPG, or PNG file.";
                    // }

                    $newFileName  = sprintf('bid_%d_%s_%d_%s.%s', $bidId, $inputName, $i, time(), $ext);
                    $destination  = $uploadDir . $newFileName;

                    if ($this->moveUploadedFile($tmps[$i], $destination)) {
                        $bidDocumentModel->create([
                            'bid_id' => $bidId,
                            'document_name' => $docTitle . (count($errors) > 1 ? " (Part " . ($i + 1) . ")" : ""),
                            'document_path' => 'documents/' . $newFileName,
                        ]);
                        $hasValidFile = true;
                    }
                }
            }

            // Removed block that returns error for missing document to allow missing docs
            // if (!$hasValidFile) {
            //     return "Missing required document: {$docTitle}.";
            // }
        }

        return null;
    }

    public function bidReceipt()
    {
        $this->requireRole(['contractor', 'admin']);
        $bidId = intval($_GET['id'] ?? 0);
        
        $bidModel = new BidModel($this->pdo, $this->config['bid_encryption_key']);
        $bid = $bidModel->getById($bidId);
        
        if (!$bid) {
            $this->redirect('contractor-dashboard');
            return;
        }

        // Security: Ensure contractors can only see their own receipts
        if ($_SESSION['user']['role'] === 'contractor') {
            $contractorModel = new ContractorModel($this->pdo);
            $contractor = $contractorModel->getByUserId($_SESSION['user']['user_id']);
            if ($bid['contractor_id'] !== $contractor['contractor_id']) {
                $this->redirect('contractor-dashboard');
                return;
            }
        }

        $tenderModel = new TenderModel($this->pdo);
        $tender = $tenderModel->getById($bid['tender_id']);

        $this->view('tenders/receipt', [
            'bid'    => $bid,
            'tender' => $tender,
            'amount' => $bidModel->decryptAmount($bid['encrypted_amount'])
        ], 'layouts/empty'); // Use an empty layout for clean printing
    }

    protected function moveUploadedFile(string $filename, string $destination): bool
    {
        return move_uploaded_file($filename, $destination);
    }

    // ── Guest Observer / Watch Live ──────────────────────────────────────────

    /**
     * Public page — no login required.
     * Guests can observe the tender opening session live via the meeting link.
     */
    public function watchLive()
    {
        $tenderId    = intval($_GET['id'] ?? 0);
        $tenderModel = new TenderModel($this->pdo);
        $tender      = $tenderModel->getById($tenderId);

        // Only show for tenders in an opening/post-bidding stage
        if (!$tender || !in_array($tender['status'], ['closed', 'evaluated', 'awarded'])) {
            $this->redirect('tenders');
            return;
        }

        // Aggregate stats — public-safe (counts only, no names or amounts)
        $stmt = $this->pdo->prepare(
            "SELECT
                COUNT(*) AS total_bids,
                SUM(envelope_status = 'sealed')   AS sealed,
                SUM(envelope_status = 'opened')   AS opened,
                SUM(envelope_status = 'verified') AS verified,
                SUM(envelope_status = 'rejected') AS rejected,
                SUM(envelope_status = 'scored')   AS scored
             FROM bids WHERE tender_id = :tid"
        );
        $stmt->execute(['tid' => $tenderId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->view('tenders/watch_live', [
            'tender' => $tender,
            'stats'  => $stats,
        ]);
    }

    /**
     * JSON endpoint — public-safe live status for the guest observer page.
     * Auto-polled every 10 s via fetch().
     */
    public function apiOpeningStatus()
    {
        $tenderId    = intval($_GET['id'] ?? 0);
        $tenderModel = new TenderModel($this->pdo);
        $tender      = $tenderModel->getById($tenderId);

        if (!$tender) {
            jsonResponse(false, 'Tender not found.');
        }

        $stmt = $this->pdo->prepare(
            "SELECT
                COUNT(*) AS total_bids,
                SUM(envelope_status = 'sealed')   AS sealed,
                SUM(envelope_status = 'opened')   AS opened,
                SUM(envelope_status = 'verified') AS verified,
                SUM(envelope_status = 'rejected') AS rejected,
                SUM(envelope_status = 'scored')   AS scored
             FROM bids WHERE tender_id = :tid"
        );
        $stmt->execute(['tid' => $tenderId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        $stageLabel = match($tender['status']) {
            'closed'    => 'Opening in progress',
            'evaluated' => 'Evaluation complete — awaiting award',
            'awarded'   => 'Tender awarded',
            default     => ucfirst($tender['status']),
        };

        jsonResponse(true, 'ok', [
            'status'      => $tender['status'],
            'stage_label' => $stageLabel,
            'total_bids'  => (int)($stats['total_bids'] ?? 0),
            'sealed'      => (int)($stats['sealed']     ?? 0),
            'opened'      => (int)($stats['opened']     ?? 0),
            'verified'    => (int)($stats['verified']   ?? 0),
            'rejected'    => (int)($stats['rejected']   ?? 0),
            'scored'      => (int)($stats['scored']     ?? 0),
        ]);
    }
}
