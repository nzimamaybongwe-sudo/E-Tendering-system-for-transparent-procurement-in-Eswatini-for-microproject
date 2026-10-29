<?php
/**
 * DownloadController — serves files from the secure storage directory.
 * Files are stored outside public/ so direct URL access is blocked.
 * This controller validates the session before serving any file.
 */
class DownloadController extends BaseController
{
    /**
     * Serve a contractor document — only accessible by the owning contractor or an admin.
     */
    public function contractorDocument()
    {
        // Allow evaluators to view contractor documents during evaluation for transparency
        $this->requireRole(['contractor', 'admin', 'evaluator', 'officer']);

        $documentId    = intval($_GET['id'] ?? 0);
        $docModel      = new ContractorDocumentModel($this->pdo);
        $doc           = $docModel->getById($documentId);

        if (!$doc) {
            http_response_code(404);
            exit('Document not found.');
        }

        // Contractors may only download their own documents
        if ($_SESSION['user']['role'] === 'contractor') {
            $contractorModel = new ContractorModel($this->pdo);
            $contractor      = $contractorModel->getByUserId($_SESSION['user']['user_id']);
            if (!$contractor || (int)$contractor['contractor_id'] !== (int)$doc['contractor_id']) {
                http_response_code(403);
                exit('Access denied.');
            }
        }

        $baseDir = realpath(__DIR__ . '/../storage/uploads');
        $documentPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($doc['document_path'] ?? '', '/\\'));
        $filePath = $this->resolveDocumentFilePath($baseDir, $documentPath);

        if (!$filePath) {
            http_response_code(404);
            exit('File not found.');
        }

        $this->serveFile($filePath, $doc['document_name']);
    }

    /**
     * Serve a tender document — accessible by any logged-in user.
     */
    public function tenderDocument()
    {
        $this->requireRole(['contractor', 'admin', 'evaluator', 'officer']);

        $documentId = intval($_GET['id'] ?? 0);
        $docModel   = new TenderDocumentModel($this->pdo);
        $doc        = $docModel->getById($documentId);

        if (!$doc) {
            http_response_code(404);
            exit('Document not found.');
        }

        $filePath = realpath(__DIR__ . '/../storage/uploads/tender_docs/' . basename($doc['document_path']));
        $this->serveFile($filePath, $doc['document_name']);
    }

    /**
     * Serve a bid document — accessible by evaluators, contractors and admins
     */
    public function bidDocument()
    {
        $this->requireRole(['contractor', 'admin', 'evaluator', 'officer']);

        $documentId = intval($_GET['id'] ?? 0);
        $docModel = new BidDocumentModel($this->pdo);
        $doc = $docModel->getById($documentId);

        if (!$doc) {
            http_response_code(404);
            exit('Document not found.');
        }

        $baseDir = realpath(__DIR__ . '/../storage/uploads');
        $documentPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($doc['document_path'] ?? '', '/\\'));
        $filePath = $this->resolveDocumentFilePath($baseDir, $documentPath);

        if (!$filePath) {
            http_response_code(404);
            exit('File not found.');
        }

        $this->serveFile($filePath, $doc['document_name']);
    }

    /**
     * Serve an evaluator accreditation document — accessible by admins or the evaluator themselves.
     */
    public function evaluatorDocument()
    {
        $this->requireRole(['admin', 'evaluator']);

        $profileId = intval($_GET['id'] ?? 0);
        $evaluatorModel = new EvaluatorProfileModel($this->pdo);
        $profile = $evaluatorModel->getById($profileId);

        if (!$profile) {
            http_response_code(404);
            exit('Profile not found.');
        }

        // Evaluators may only download their own document
        if ($_SESSION['user']['role'] === 'evaluator') {
            $evaluator = $evaluatorModel->getByUserId($_SESSION['user']['user_id']);
            if (!$evaluator || (int)$evaluator['evaluator_profile_id'] !== $profileId) {
                http_response_code(403);
                exit('Access denied.');
            }
        }

        if (empty($profile['accreditation_document'])) {
            http_response_code(404);
            exit('No accreditation document uploaded.');
        }

        $baseDir = realpath(__DIR__ . '/../storage/uploads');
        $documentPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($profile['accreditation_document'], '/\\'));
        $filePath = $this->resolveDocumentFilePath($baseDir, $documentPath);

        if (!$filePath) {
            http_response_code(404);
            exit('File not found.');
        }

        $this->serveFile($filePath, basename($profile['accreditation_document']));
    }

    /**
     * Validates and streams the file to the browser.
     */
    private function serveFile(?string $filePath, string $displayName): void
    {
        if (!$filePath || !file_exists($filePath)) {
            http_response_code(404);
            exit('File not found.');
        }

        // Prevent path traversal
        $storageBase = realpath(__DIR__ . '/../storage/');
        if (!$storageBase || strpos($filePath, $storageBase) !== 0) {
            http_response_code(403);
            exit('Access denied.');
        }

        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
        $safeDisposition = 'inline; filename="' . preg_replace('/[^a-zA-Z0-9._\- ]/', '_', $displayName) . '"';

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: ' . $safeDisposition);
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('X-Content-Type-Options: nosniff');

        readfile($filePath);
        exit;
    }

    private function resolveDocumentFilePath(?string $baseDir, string $documentPath): ?string
    {
        if (!$baseDir || $documentPath === '') {
            return null;
        }

        $documentPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $documentPath);

        $candidates = [
            $baseDir . DIRECTORY_SEPARATOR . $documentPath,
            $baseDir . DIRECTORY_SEPARATOR . basename($documentPath),
            $baseDir . DIRECTORY_SEPARATOR . 'contractor_docs' . DIRECTORY_SEPARATOR . basename($documentPath),
            $baseDir . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR . basename($documentPath),
        ];

        if (stripos($documentPath, 'contractor_docs' . DIRECTORY_SEPARATOR) !== 0) {
            $candidates[] = $baseDir . DIRECTORY_SEPARATOR . 'contractor_docs' . DIRECTORY_SEPARATOR . $documentPath;
        }
        if (stripos($documentPath, 'documents' . DIRECTORY_SEPARATOR) !== 0) {
            $candidates[] = $baseDir . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR . $documentPath;
        }

        foreach ($candidates as $candidate) {
            $candidate = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $candidate);
            if (file_exists($candidate) && is_file($candidate)) {
                return realpath($candidate) ?: $candidate;
            }
        }

        return null;
    }
}
