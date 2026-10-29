<?php
class ContractorController extends BaseController
{
    public function dashboard()
    {
        $this->requireRole(['contractor']);

        $contractorModel = new ContractorModel($this->pdo);
        $tenderModel = new TenderModel($this->pdo);
        $bidModel = new BidModel($this->pdo, $this->config['bid_encryption_key']);

        $userId = $_SESSION['user']['user_id'];
        $contractor = $contractorModel->getByUserId($userId);
        if (!$contractor) {
            $this->redirect('login');
            return;
        }
        // Show published, closed, and tenders currently open for evaluation
        $published = $tenderModel->getPublished();
        $closed = $tenderModel->getByStatus('closed');
        $evaluated = $tenderModel->getByStatus('evaluated');
        $tenders = array_merge($published, $closed, $evaluated);
        $myBids = $bidModel->getByContractor($contractor['contractor_id']);

        $documentModel = new ContractorDocumentModel($this->pdo);
        $taxDoc = $documentModel->getLatestByName($contractor['contractor_id'], 'Tax Clearance Certificate');
        $enpfDoc = $documentModel->getLatestByName($contractor['contractor_id'], 'SNPF/ENPF Compliance');
        $policeDoc = $documentModel->getLatestByName($contractor['contractor_id'], 'Police Clearance');
        $labourDoc = $documentModel->getLatestByName($contractor['contractor_id'], 'Labour Compliance Certificate');

        $taxValid = $documentModel->isDocumentValid($contractor['contractor_id'], 'Tax Clearance Certificate', 30);
        $enpfValid = $documentModel->isDocumentValid($contractor['contractor_id'], 'SNPF/ENPF Compliance', 90);
        $policeValid = $documentModel->isDocumentValid($contractor['contractor_id'], 'Police Clearance', 90);
        $labourValid = $labourDoc && !$labourDoc['is_expired'];

        // Check Phase 1 compliance for account status
        $phase1Compliance = $documentModel->checkPhaseCompliance($contractor['contractor_id'], 'phase1');
        $phase1Valid = !in_array(false, array_column($phase1Compliance, 'valid'));

        $this->view('dashboard_contractor', [
            'contractor' => $contractor,
            'tenders' => $tenders,
            'myBids' => $myBids,
            'compliance' => [
                'phase1_valid' => $phase1Valid,
                'phase1_docs' => $phase1Compliance,
                'phase2' => [
                    'tax_clearance' => [
                        'valid' => $taxValid,
                        'uploaded_at' => $taxDoc['uploaded_at'] ?? null,
                        'expires_in_days' => $taxDoc ? ceil((strtotime($taxDoc['uploaded_at'] . ' +30 days') - time()) / 86400) : null,
                    ],
                    'snpf_enpf' => [
                        'valid' => $enpfValid,
                        'uploaded_at' => $enpfDoc['uploaded_at'] ?? null,
                        'expires_in_days' => $enpfDoc ? ceil((strtotime($enpfDoc['uploaded_at'] . ' +90 days') - time()) / 86400) : null,
                    ],
                    'police_clearance' => [
                        'valid' => $policeValid,
                        'uploaded_at' => $policeDoc['uploaded_at'] ?? null,
                        'expires_in_days' => $policeDoc ? ceil((strtotime($policeDoc['uploaded_at'] . ' +90 days') - time()) / 86400) : null,
                    ],
                    'labour_compliance' => [
                        'valid' => $labourValid,
                        'uploaded_at' => $labourDoc['uploaded_at'] ?? null,
                    ],
                ],
            ],
        ]);
    }

    public function profile()
    {
        $this->requireRole(['contractor']);

        $contractorModel = new ContractorModel($this->pdo);
        $documentModel = new ContractorDocumentModel($this->pdo);
        $userId = $_SESSION['user']['user_id'];
        $contractor = $contractorModel->getByUserId($userId);
        if (!$contractor) {
            $this->redirect('login');
            return;
        }

        $documents  = $documentModel->getByContractor($contractor['contractor_id']);

        $requiredDocNames = [
            'Certificate of Incorporation',
            'Form J & Form C',
            'CIC Registration Certificate',
            'VAT Registration Certificate',
            'Trading Licence',
            'Tax Clearance Certificate',
            'SNPF/ENPF Compliance',
            'Police Clearance',
            'Labour Compliance Certificate',
        ];
        $requiredDocs = [];
        foreach ($requiredDocNames as $docName) {
            $doc = $documentModel->getLatestByName($contractor['contractor_id'], $docName);
            $isValid = $documentModel->isDocumentValid($contractor['contractor_id'], $docName);
            $requiredDocs[] = [
                'name' => $docName,
                'uploaded' => (bool)$doc,
                'valid' => $isValid,
                'uploaded_at' => $doc['uploaded_at'] ?? null,
                'is_expired' => $doc['is_expired'] ?? false,
            ];
        }

        $portfolioModel = new PortfolioModel($this->pdo);
        $portfolio      = $portfolioModel->getByContractorWithMedia($contractor['contractor_id']);

        $this->view('contractor/profile', [
            'contractor' => $contractor,
            'documents'  => $documents,
            'portfolio'  => $portfolio,
            'requiredDocs' => $requiredDocs,
        ]);
    }

    public function editProfile()
    {
        $this->requireRole(['contractor']);

        $contractorModel = new ContractorModel($this->pdo);
        $userId = $_SESSION['user']['user_id'];
        $contractor = $contractorModel->getByUserId($userId);
        if (!$contractor) {
            $this->redirect('login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $companyName = trim($_POST['company_name'] ?? '');
            $cicGrade = trim($_POST['cic_grade'] ?? '');
            $localOwnership = isset($_POST['local_ownership']) ? 1 : 0;
            $profileSummary = trim($_POST['profile_summary'] ?? '');

            if (!$companyName || !$cicGrade) {
                $this->redirect('contractor-profile&error=' . urlencode('Company name and CIC grade are required.'));
            } else {
                $updated = $contractorModel->updateProfile($contractor['contractor_id'], [
                    'company_name' => $companyName,
                    'cic_grade' => $cicGrade,
                    'local_ownership' => $localOwnership,
                    'profile_summary' => $profileSummary,
                ]);

                if ($updated) {
                    // Notify all admins about the profile change
                    $userModel = new UserModel($this->pdo);
                    $admins = $userModel->getActiveByRole('admin');
                    $notificationModel = new NotificationModel($this->pdo);

                    $message = "Contractor '{$companyName}' (ID: {$contractor['contractor_id']}) has updated their profile. Please review the changes.";

                    foreach ($admins as $admin) {
                        $notificationModel->create($admin['user_id'], $message);
                    }

                    $audit = new AuditModel($this->pdo);
                    $audit->log($userId, 'update_profile', 'contractors', $contractor['contractor_id'], 'Contractor profile updated');

                    $this->redirect('contractor-profile&success=' . urlencode('Profile updated successfully. Admins have been notified.'));
                } else {
                    $this->redirect('contractor-profile&error=' . urlencode('Failed to update profile. Please try again.'));
                }
            }
        } else {
            $this->redirect('contractor-profile&edit=1');
        }
    }

    public function addPortfolioItem()
    {
        $this->requireRole(['contractor']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $contractorModel = new ContractorModel($this->pdo);
            $contractor = $contractorModel->getByUserId($_SESSION['user']['user_id']);
            if (!$contractor) {
                $this->redirect('login');
                return;
            }

            try {
                $this->pdo->beginTransaction();
                $portfolioModel = new PortfolioModel($this->pdo);
                $projectId = $portfolioModel->create([
                    'contractor_id'   => $contractor['contractor_id'],
                    'project_name'    => $_POST['project_name'],
                    'client_name'     => $_POST['client_name'],
                    'project_value'   => $_POST['project_value'],
                    'completion_year' => $_POST['completion_year'],
                    'description'     => $_POST['description'],
                ]);

                $uploadError = $this->handlePortfolioMediaUploads($projectId);
                if ($uploadError) {
                    $this->pdo->rollBack();
                    error_log('Portfolio media upload failed: ' . $uploadError);
                } else {
                    $this->pdo->commit();
                }
            } catch (Exception $e) {
                $this->pdo->rollBack();
                error_log('Portfolio upload transaction failed: ' . $e->getMessage());
            }
        }
        $this->redirect('contractor-profile');
    }

    public function deletePortfolioItem()
    {
        $this->requireRole(['contractor']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $projectId = intval($_POST['project_id'] ?? 0);
            $contractorModel = new ContractorModel($this->pdo);
            $contractor = $contractorModel->getByUserId($_SESSION['user']['user_id']);
            if (!$contractor) {
                $this->redirect('login');
                return;
            }
            
            $portfolioModel = new PortfolioModel($this->pdo);
            $portfolioModel->delete($projectId, $contractor['contractor_id']);
        }
        $this->redirect('contractor-profile');
    }

    private function handlePortfolioMediaUploads(int $projectId): ?string
    {
        if (empty($_FILES['media_files'])) {
            return null;
        }

        $uploadDir = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'portfolio' . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            return 'Failed to create portfolio uploads directory.';
        }

        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'webm', 'ogg'];
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'video/mp4', 'video/webm', 'video/ogg'];

        $mediaFiles = [];
        if (is_array($_FILES['media_files']['name'])) {
            $count = count($_FILES['media_files']['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['media_files']['error'][$i] === UPLOAD_ERR_OK && !empty($_FILES['media_files']['name'][$i])) {
                    $mediaFiles[] = [
                        'name'     => $_FILES['media_files']['name'][$i],
                        'tmp_name' => $_FILES['media_files']['tmp_name'][$i],
                    ];
                }
            }
        } else {
            if ($_FILES['media_files']['error'] === UPLOAD_ERR_OK && !empty($_FILES['media_files']['name'])) {
                $mediaFiles[] = [
                    'name'     => $_FILES['media_files']['name'],
                    'tmp_name' => $_FILES['media_files']['tmp_name'],
                ];
            }
        }

        if (empty($mediaFiles)) {
            return null;
        }

        $portfolioModel = new PortfolioModel($this->pdo);
        foreach ($mediaFiles as $index => $fileItem) {
            $fileName = basename($fileItem['name']);
            $fileTmp  = $fileItem['tmp_name'];
            $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $mimeType = mime_content_type($fileTmp);

            if (!in_array($fileExt, $allowedExts, true) || !in_array($mimeType, $allowedMimes, true)) {
                return 'Only JPG, PNG, GIF images and MP4/WEBM/OGG videos are allowed for portfolio proof.';
            }

            $safeName = sprintf('portfolio_%d_%s_%s.%s', $projectId, $index, bin2hex(random_bytes(4)), $fileExt);
            $destination = $uploadDir . $safeName;
            $publicPath  = 'uploads/portfolio/' . $safeName;

            if (!move_uploaded_file($fileTmp, $destination)) {
                return 'Failed to save portfolio proof file.';
            }

            $portfolioModel->createMedia($projectId, $publicPath, $mimeType);
        }

        return null;
    }

    public function myAppeals()
    {
        $this->requireRole(['contractor']);

        $appealModel = new AppealModel($this->pdo);
        $appeals = $appealModel->getByUser($_SESSION['user']['user_id']);
        $this->view('contractor/appeals', ['appeals' => $appeals]);
    }

    public function submitAppeal()
    {
        $this->requireRole(['contractor']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('contractor-dashboard');
        }
        $tenderId = intval($_POST['tender_id'] ?? 0);
        $bidId = intval($_POST['bid_id'] ?? 0);
        $subject = trim($_POST['subject'] ?? 'Tender appeal');
        $appealReason = trim($_POST['appeal_reason'] ?? '');

        $appealModel = new AppealModel($this->pdo);
        $appealId = $appealModel->create([
            'tender_id' => $tenderId,
            'user_id' => $_SESSION['user']['user_id'],
            'bid_id' => $bidId,
            'subject' => $subject,
            'appeal_reason' => $appealReason,
            'status' => 'submitted',
        ]);

        $audit = new AuditModel($this->pdo);
        $audit->log($_SESSION['user']['user_id'], 'submit_appeal', 'appeals', $appealId, 'Appeal submitted for tender ' . $tenderId);
        $this->redirect('my-appeals');
    }

    public function uploadDocument()
    {
        $this->requireRole(['contractor']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('contractor-dashboard');
            return;
        }

        $contractorModel = new ContractorModel($this->pdo);
        $userId = $_SESSION['user']['user_id'];
        $contractor = $contractorModel->getByUserId($userId);
        if (!$contractor) {
            $this->redirect('login');
            return;
        }

        $docType = trim($_POST['document_type'] ?? '');
        $allowedPhase1 = [
            'Certificate of Incorporation',
            'Form J & Form C',
            'CIC Registration Certificate',
            'VAT Registration Certificate',
            'Trading Licence'
        ];
        $allowedPhase2 = [
            'Tax Clearance Certificate',
            'SNPF/ENPF Compliance',
            'Police Clearance',
            'Labour Compliance Certificate'
        ];

        if (!in_array($docType, array_merge($allowedPhase1, $allowedPhase2), true)) {
            $this->redirect('contractor-dashboard');
            return;
        }
        $documentPhase = in_array($docType, $allowedPhase1, true) ? 'phase1' : 'phase2';

        if (empty($_FILES['document']['name'][0])) {
            $this->redirect('contractor-dashboard');
            return;
        }

        $allowedExts = ['pdf', 'jpg', 'jpeg', 'png'];
        $allowedMimes = [
            'application/pdf',
            'image/jpeg',
            'image/png'
        ];

        $uploadDir = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'contractor_docs' . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
            file_put_contents($uploadDir . '.htaccess', 'Deny from all');
        }

        $documentModel = new ContractorDocumentModel($this->pdo);
        $audit = new AuditModel($this->pdo);

        $count = count($_FILES['document']['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($_FILES['document']['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $fileTmp = $_FILES['document']['tmp_name'][$i];
            $fileName = $_FILES['document']['name'][$i];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $mime = mime_content_type($fileTmp);

            // Relaxed validation: allow any file type
            // if (!in_array($fileExt, $allowedExts, true) || !in_array($mime, $allowedMimes, true)) {
            //     continue;
            // }

            $newFileName = sprintf('contractor_%d_%s_%d_%s.%s', 
                $contractor['contractor_id'], 
                preg_replace('/[^a-z0-9]/', '_', strtolower(str_replace(' ', '_', $docType))), 
                $i,
                time(), 
                $fileExt
            );
            $destination = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmp, $destination)) {
                // Calculate expiry dates based on document type
                $expiryDate = null;
                if ($docType === 'Tax Clearance Certificate') {
                    $expiryDate = date('Y-m-d', strtotime('+30 days'));
                } elseif (in_array($docType, ['SNPF/ENPF Compliance', 'Police Clearance'], true)) {
                    $expiryDate = date('Y-m-d', strtotime('+90 days'));
                }
                
                $documentModel->create([
                    'contractor_id' => $contractor['contractor_id'],
                    'document_name' => $docType,
                    'document_path' => $newFileName,
                    'expiry_date' => $expiryDate,
                    'document_phase' => $documentPhase,
                ]);

                $audit->log($userId, 'upload_document', 'contractor_documents', $contractor['contractor_id'], "Document uploaded: {$docType}");
            }
        }

        $this->redirect('contractor-dashboard');
    }

    /**
     * POST: Contractor acknowledges the award specifications.
     * Stores their notes and marks the record as acknowledged.
     */
    public function apiSubmitWinnerAck()
    {
        $this->requireRole(['contractor']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('contractor-dashboard');
            return;
        }

        $tenderId        = intval($_POST['tender_id'] ?? 0);
        $contractorNotes = trim($_POST['contractor_notes'] ?? '');
        $acknowledged    = !empty($_POST['acknowledged']);

        if (!$acknowledged || $tenderId <= 0) {
            $this->redirect('winner-specifications&id=' . $tenderId);
            return;
        }

        // Verify this contractor is the winner
        $contractorModel = new ContractorModel($this->pdo);
        $contractor      = $contractorModel->getByUserId($_SESSION['user']['user_id']);
        if (!$contractor) {
            $this->redirect('contractor-dashboard');
            return;
        }

        $stmt = $this->pdo->prepare('
            UPDATE tender_award_specifications
            SET    status            = :status,
                   contractor_notes = :notes
            WHERE  tender_id = :tender_id
            AND    bid_id IN (
                SELECT bid_id FROM bids WHERE contractor_id = :contractor_id
            )
        ');
        $stmt->execute([
            'status'        => 'acknowledged',
            'notes'         => $contractorNotes,
            'tender_id'     => $tenderId,
            'contractor_id' => $contractor['contractor_id'],
        ]);

        $audit = new AuditModel($this->pdo);
        $audit->log(
            $_SESSION['user']['user_id'],
            'winner_ack_submitted', 'tender_award_specifications', $tenderId,
            'Winner acknowledged project specifications for tender ' . $tenderId
        );

        $this->redirect('winner-specifications&id=' . $tenderId . '&submitted=1');
    }
}

