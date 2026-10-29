<?php
/**
 * EvaluatorController
 *
 * Handles all evaluator-facing actions: dashboard, bid evaluation,
 * rubric proposals, and evaluation submission. Extracted from
 * AdminController for cleaner separation of concerns.
 */
class EvaluatorController extends BaseController
{
    private function anonymousBidCode(array $bid): string
    {
        $seed = ($bid['tender_id'] ?? '') . '|' . ($bid['bid_id'] ?? '') . '|' . ($bid['digital_signature'] ?? '');
        return 'BID-' . strtoupper(substr(hash('sha256', $seed), 0, 10));
    }

    public function dashboard()
    {
        $this->requireRole(['evaluator']);

        $tenderModel = new TenderModel($this->pdo);
        $tenderModel->closeExpiredTenders();
        // Include tenders that are closed or already opened for evaluation
        $pendingEvaluationTenders = array_merge(
            $tenderModel->getByStatus('closed'),
            $tenderModel->getByStatus('evaluated')
        );

        $proposalModel = new RubricProposalModel($this->pdo);
        $proposals = $proposalModel->getByEvaluator($_SESSION['user']['user_id']);

        $this->view('dashboard_evaluator', [
            'pendingEvaluationTenders' => $pendingEvaluationTenders,
            'proposalCount'            => count($proposals),
        ]);
    }

    public function evaluateTender()
    {
        $this->requireRole(['evaluator']);

        $tenderModel = new TenderModel($this->pdo);
        $tenderModel->closeExpiredTenders();

        $tenderId = intval($_GET['id'] ?? 0);
        $tender   = $tenderModel->getWithEvaluationSession($tenderId);
        
        if (!$tender || !in_array($tender['status'], ['closed', 'evaluated'])) {
            $this->redirect('evaluator-dashboard');
        }

        $bidModel        = new BidModel($this->pdo, $this->config['bid_encryption_key']);
        $evaluationModel = new EvaluationModel($this->pdo);
        $portfolioModel  = new PortfolioModel($this->pdo);
        $commentModel    = new EvaluationCommentModel($this->pdo);
        $sessionModel    = new EvaluationSessionModel($this->pdo);
        $readinessModel  = new EvaluatorReadinessModel($this->pdo);
        $bidDocumentModel = new BidDocumentModel($this->pdo);
        $contractorDocModel = new ContractorDocumentModel($this->pdo);
        
        $bids            = $bidModel->getByTender($tenderId);
        foreach ($bids as &$bid) {
            $bid['decrypted_amount'] = $bidModel->decryptAmount($bid['encrypted_amount']);
            $bid['portfolio']        = $portfolioModel->getByContractorWithMedia($bid['contractor_id']);
            $bid['anonymous_code']   = $this->anonymousBidCode($bid);
            // Get public comments for this bid
            $bid['comments']         = $commentModel->getByBid($bid['bid_id']);

            // Attach bid documents and contractor documents with download URLs and SHA256 checksums (if file exists)
            $bid['documents'] = $bidDocumentModel->getByBid($bid['bid_id']);
            foreach ($bid['documents'] as &$d) {
                $d['download_url'] = 'index.php?route=download-bid-doc&id=' . intval($d['bid_document_id']);
                $fileCandidates = [
                    BASE_PATH . '/storage/uploads/' . basename($d['document_path']),
                    BASE_PATH . '/storage/uploads/' . ltrim($d['document_path'], '/\\'),
                ];
                $d['sha256'] = null;
                foreach ($fileCandidates as $fc) {
                    if (file_exists($fc) && is_file($fc)) {
                        $d['sha256'] = hash_file('sha256', $fc);
                        break;
                    }
                }
            }
            unset($d);

            $bid['contractor_documents'] = $contractorDocModel->getByContractor($bid['contractor_id']);
            foreach ($bid['contractor_documents'] as &$cd) {
                $cd['download_url'] = 'index.php?route=download-contractor-doc&id=' . intval($cd['document_id']);
                $fileCandidates = [
                    BASE_PATH . '/storage/uploads/' . basename($cd['document_path']),
                    BASE_PATH . '/storage/uploads/' . ltrim($cd['document_path'], '/\\'),
                ];
                $cd['sha256'] = null;
                foreach ($fileCandidates as $fc) {
                    if (file_exists($fc) && is_file($fc)) {
                        $cd['sha256'] = hash_file('sha256', $fc);
                        break;
                    }
                }
            }
            unset($cd);
        }
        unset($bid);

        $evaluations     = $evaluationModel->getByTender($tenderId);
        foreach ($evaluations as &$evaluation) {
            $evaluation['anonymous_code'] = $this->anonymousBidCode($evaluation);
        }
        unset($evaluation);
        
        $ranked          = $evaluationModel->rankCandidates($evaluations);
        $rubricModel     = new RubricModel($this->pdo);
        $rubric          = $rubricModel->getByTender($tenderId);

        // Get evaluation session info if it exists
        $session = null;
        $remainingMinutes = 0;
        $isSessionActive = false;
        $evaluatorReady = false;
        
        if ($tender['session_id']) {
            $session = $sessionModel->getById($tender['session_id']);
            $remainingMinutes = $sessionModel->getRemainingMinutes($tender['session_id']);
            $isSessionActive = $sessionModel->isActive($tender['session_id']);
            
            // Get this evaluator's readiness status
            $readiness = $readinessModel->getByEvaluatorAndSession(
                $_SESSION['user']['user_id'], 
                $tender['session_id']
            );
            $evaluatorReady = $readiness && $readiness['is_ready_to_close'];
        }

        $this->view('evaluator/evaluate_tender', [
            'tender'              => $tender,
            'bids'                => $bids,
            'evaluations'         => $ranked,
            'rubric'              => $rubric,
            'session'             => $session,
            'remainingMinutes'    => $remainingMinutes,
            'isSessionActive'     => $isSessionActive,
            'evaluatorReady'      => $evaluatorReady,
        ]);
    }

    public function submitEvaluation()
    {
        $this->requireRole(['evaluator']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('evaluator-dashboard');
        }

        $bidId         = intval($_POST['bid_id'] ?? 0);
        $technical     = floatval($_POST['technical_score'] ?? 0);
        $financial     = floatval($_POST['financial_score'] ?? 0);
        $localBonus    = floatval($_POST['local_bonus'] ?? 0);
        $responsiveness = isset($_POST['responsiveness_pass']);
        $conflictDeclared = intval($_POST['conflict_of_interest_declared'] ?? 0) === 1;
        $coiRecused    = intval($_POST['coi_recused'] ?? 0) === 1;
        $coiReason     = trim($_POST['coi_reason'] ?? '');
        $remarks       = trim($_POST['remarks'] ?? '');

        $bidModel        = new BidModel($this->pdo, $this->config['bid_encryption_key']);
        $bid             = $bidModel->getById($bidId);
        $tenderId        = $bid['tender_id'] ?? 0;

        $rubricModel     = new RubricModel($this->pdo);
        $rubric          = $rubricModel->getByTender($tenderId);

        $evaluationModel = new EvaluationModel($this->pdo);
        $totalScore      = $coiRecused ? 0.0 : $evaluationModel->calculateScore($technical, $financial, $localBonus, $responsiveness, $rubric);

        $evaluationId = $evaluationModel->create([
            'bid_id'               => $bidId,
            'evaluator_id'         => $_SESSION['user']['user_id'],
            'technical_score'      => $technical,
            'financial_score'      => $financial,
            'responsiveness_pass'  => $responsiveness,
            'local_bonus'          => $localBonus,
            'total_score'          => $totalScore,
            'conflict_of_interest_declared' => $conflictDeclared,
            'coi_recused'          => $coiRecused,
            'coi_reason'           => $coiReason,
            'remarks'              => $remarks,
        ]);

        $audit = new AuditModel($this->pdo);
        $audit->log($_SESSION['user']['user_id'], 'submit_evaluation', 'evaluations', $evaluationId, 'Evaluation submitted for bid ' . $bidId);

        // Notify the contractor whose bid was evaluated
        if ($bid) {
            $contractorModel = new ContractorModel($this->pdo);
            $contractor      = $contractorModel->getById($bid['contractor_id']);
            if ($contractor) {
                $notificationModel = new NotificationModel($this->pdo);
                $notificationModel->create(
                    $contractor['user_id'],
                    'Your bid has been evaluated for tender ID ' . $bid['tender_id'] . '. Score: ' . number_format($totalScore, 2)
                );
            }
        }

        // Notify admins that an evaluation has been submitted
        if ($bid) {
            $tenderModel = new TenderModel($this->pdo);
            $tender = $tenderModel->getById($tenderId);
            if ($tender) {
                $userModel = new UserModel($this->pdo);
                $admins = $userModel->getActiveByRole('admin');
                $notificationModel = new NotificationModel($this->pdo);
                
                $evaluatorModel = new EvaluatorProfileModel($this->pdo);
                $evaluator = $evaluatorModel->getByUserId($_SESSION['user']['user_id']);
                $evaluatorName = $evaluator ? htmlspecialchars($evaluator['full_name']) : 'Evaluator ' . $_SESSION['user']['user_id'];
                
                foreach ($admins as $admin) {
                    $notificationModel->create(
                        $admin['user_id'],
                        "Evaluator {$evaluatorName} has submitted an evaluation for tender '{$tender['title']}'. Score: " . number_format($totalScore, 2)
                    );
                }
            }
        }

        $this->redirect(
            !empty($_POST['return_to']) && $_POST['return_to'] === 'opening-room'
                ? 'tender-opening-room&id=' . intval($_POST['tender_id_return'] ?? $tenderId)
                : 'evaluate-tender&id=' . $tenderId
        );
    }

    public function finishTenderEvaluation()
    {
        $this->requireRole(['evaluator']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('evaluator-dashboard');
            return;
        }

        $tenderId = intval($_POST['tender_id'] ?? 0);
        $evaluatorId = $_SESSION['user']['user_id'];
        $tenderModel = new TenderModel($this->pdo);
        $tender = $tenderModel->getWithEvaluationSession($tenderId);

        if (!$tender) {
            $this->redirect('evaluator-dashboard');
            return;
        }

        $audit = new AuditModel($this->pdo);

        // If a proper session exists, use the readiness model
        if (!empty($tender['session_id'])) {
            $readinessModel = new EvaluatorReadinessModel($this->pdo);
            $sessionId = $tender['session_id'];

            $readiness = $readinessModel->getByEvaluatorAndSession($evaluatorId, $sessionId);
            if (!$readiness) {
                $readinessModel->create($sessionId, $tenderId, $evaluatorId);
            }

            $readinessModel->markAllEvaluationsDone($evaluatorId, $sessionId);
            $readinessModel->markReadyToClose($evaluatorId, $sessionId);
            $audit->log($evaluatorId, 'finish_tender_evaluation', 'evaluator_readiness', $sessionId, 'Finished evaluating tender ' . $tenderId);

            if ($readinessModel->areAllReady($sessionId)) {
                $tenderModel->updateStatus($tenderId, 'evaluated');
                $this->notifyAllReadyAdminsAndOfficers($tender);
            }
        } else {
            // No session — mark tender as evaluated directly if evaluations exist for this tender
            $audit->log($evaluatorId, 'finish_tender_evaluation', 'tenders', $tenderId, 'Evaluator marked done for tender ' . $tenderId . ' (no session)');

            $stmt = $this->pdo->prepare('
                SELECT COUNT(DISTINCT e.evaluator_id) as eval_count
                FROM evaluations e
                JOIN bids b ON e.bid_id = b.bid_id
                WHERE b.tender_id = :tender_id
            ');
            $stmt->execute(['tender_id' => $tenderId]);
            $evalCount = (int)$stmt->fetch()['eval_count'];

            if ($evalCount > 0 && $tender['status'] === 'closed') {
                $tenderModel->updateStatus($tenderId, 'evaluated');
                $this->notifyAllReadyAdminsAndOfficers($tender);
            }
        }

        $this->redirect('evaluator-dashboard');
    }

    private function notifyAllReadyAdminsAndOfficers(array $tender): void
    {
        $userModel = new UserModel($this->pdo);
        $notificationModel = new NotificationModel($this->pdo);
        $tenderId = $tender['tender_id'];
        $tenderTitle = $tender['title'] ?? 'Tender #' . $tenderId;

        $admins = $userModel->getActiveByRole('admin');
        foreach ($admins as $admin) {
            $notificationModel->create(
                $admin['user_id'],
                "All evaluators have finished grading tender '{$tenderTitle}'. You can now review and award the winner."
            );
        }
        $officers = $userModel->getActiveByRole('officer');
        foreach ($officers as $officer) {
            $notificationModel->create(
                $officer['user_id'],
                "Evaluation complete for tender '{$tenderTitle}'. Please proceed to consensus and award the winner."
            );
        }
    }

    public function apiMarkEvaluatorReady()
    {
        $this->requireRole(['evaluator']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request method');
        }

        $tenderId = intval($_POST['tender_id'] ?? 0);
        $isReady = intval($_POST['is_ready'] ?? 0);
        
        $tenderModel = new TenderModel($this->pdo);
        $tender = $tenderModel->getWithEvaluationSession($tenderId);
        
        if ($tender && $tender['session_id']) {
            $readinessModel = new EvaluatorReadinessModel($this->pdo);
            $evaluatorId = $_SESSION['user']['user_id'];
            $sessionId = $tender['session_id'];

            // Ensure there is a record
            $readiness = $readinessModel->getByEvaluatorAndSession($evaluatorId, $sessionId);
            if (!$readiness) {
                $readinessModel->create($sessionId, $tenderId, $evaluatorId);
            }
            
            if ($isReady) {
                $readinessModel->markAllEvaluationsDone($evaluatorId, $sessionId);
                $readinessModel->markReadyToClose($evaluatorId, $sessionId);

                $audit = new AuditModel($this->pdo);
                $audit->log($evaluatorId, 'mark_evaluator_ready', 'evaluator_readiness', $sessionId, 'Evaluator marked ready to close tender ' . $tenderId);

                // Check if all evaluators are ready
                if ($readinessModel->areAllReady($sessionId)) {
                    // Mark tender as evaluated
                    $tenderModel->updateStatus($tenderId, 'evaluated');

                    $userModel = new UserModel($this->pdo);
                    $admins = $userModel->getActiveByRole('admin');
                    $notificationModel = new NotificationModel($this->pdo);
                    foreach ($admins as $admin) {
                        $notificationModel->create(
                            $admin['user_id'],
                            "All evaluators have finished grading tender {$tenderId} ('{$tender['title']}'). You can now review and publish the winner."
                        );
                    }
                }
            }
            jsonResponse(true, 'Marked as ready');
        }

        jsonResponse(false, 'Tender or session not found');
    }

    public function proposeRubric()
    {
        $this->requireRole(['evaluator']);

        $tenderModel   = new TenderModel($this->pdo);
        $tenders       = $tenderModel->getByStatus('published');

        $proposalModel = new RubricProposalModel($this->pdo);
        $proposals     = $proposalModel->getByEvaluator($_SESSION['user']['user_id']);

        $this->view('evaluator/propose_rubric', [
            'tenders'   => $tenders,
            'proposals' => $proposals,
        ]);
    }

    public function submitRubricProposal()
    {
        $this->requireRole(['evaluator']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('propose-rubric');
            return;
        }

        $tenderId  = intval($_POST['tender_id'] ?? 0);
        $technical = floatval($_POST['technical_weight'] ?? 0);
        $financial = floatval($_POST['financial_weight'] ?? 0);
        $local     = floatval($_POST['local_weight'] ?? 0);
        $notes     = trim($_POST['notes'] ?? '');
        $total     = $technical + $financial + $local;

        if ($tenderId <= 0 || $total !== 100.0) {
            $this->redirect('propose-rubric');
            return;
        }

        $proposalModel = new RubricProposalModel($this->pdo);
        $proposalId    = $proposalModel->createProposal([
            'tender_id'        => $tenderId,
            'evaluator_id'     => $_SESSION['user']['user_id'],
            'technical_weight' => $technical,
            'financial_weight' => $financial,
            'local_weight'     => $local,
            'notes'            => $notes,
            'status'           => 'submitted',
        ]);

        $audit = new AuditModel($this->pdo);
        $audit->log($_SESSION['user']['user_id'], 'submit_rubric_proposal', 'tender_rubric_proposals', $proposalId, 'Submitted rubric proposal for tender ' . $tenderId);

        // Notify all admins about the new proposal
        $userModel         = new UserModel($this->pdo);
        $admins            = $userModel->getActiveByRole('admin');
        $notificationModel = new NotificationModel($this->pdo);
        foreach ($admins as $admin) {
            $notificationModel->create(
                $admin['user_id'],
                'A new rubric proposal has been submitted by ' . $_SESSION['user']['username'] . ' for review.'
            );
        }

        $this->redirect('propose-rubric');
    }

    public function submitRecommendation()
    {
        $this->requireRole(['evaluator']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('evaluator-dashboard');
            return;
        }
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $this->redirect('evaluator-dashboard');
            return;
        }

        $tenderId        = intval($_POST['tender_id'] ?? 0);
        $recommendedBidId = intval($_POST['recommended_bid_id'] ?? 0);
        $justification   = trim($_POST['justification'] ?? '');

        if (!$tenderId || !$recommendedBidId || empty($justification)) {
            $_SESSION['error'] = 'Please fill in all fields before submitting your recommendation.';
            $this->redirect('evaluate-tender&id=' . $tenderId);
            return;
        }

        // Upsert: one recommendation per evaluator per tender
        $stmt = $this->pdo->prepare('
            INSERT INTO evaluator_recommendations (tender_id, evaluator_id, recommended_bid_id, justification)
            VALUES (:tender_id, :evaluator_id, :recommended_bid_id, :justification)
            ON DUPLICATE KEY UPDATE
                recommended_bid_id = VALUES(recommended_bid_id),
                justification      = VALUES(justification),
                submitted_at       = CURRENT_TIMESTAMP
        ');
        $stmt->execute([
            'tender_id'          => $tenderId,
            'evaluator_id'       => $_SESSION['user']['user_id'],
            'recommended_bid_id' => $recommendedBidId,
            'justification'      => $justification,
        ]);

        $audit = new AuditModel($this->pdo);
        $audit->log($_SESSION['user']['user_id'], 'submit_recommendation', 'evaluator_recommendations', $tenderId,
            'Evaluator recommended bid ' . $recommendedBidId . ' for tender ' . $tenderId);

        // Notify all officers of the new recommendation
        $tenderModel = new TenderModel($this->pdo);
        $tender = $tenderModel->getById($tenderId);
        $userModel = new UserModel($this->pdo);
        $officers = $userModel->getActiveByRole('officer');
        $notificationModel = new NotificationModel($this->pdo);
        foreach ($officers as $officer) {
            $notificationModel->create(
                $officer['user_id'],
                'An evaluator has submitted their winner recommendation for "' . ($tender['title'] ?? 'Tender #' . $tenderId) . '". Review the tally on the consensus page.'
            );
        }

        $_SESSION['success'] = 'Your recommendation has been submitted confidentially.';
        $this->redirect('evaluate-tender&id=' . $tenderId);
    }
}
