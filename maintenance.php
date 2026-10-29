<?php
// Maintenance script: Check contractor compliance and update account status
require __DIR__ . '/config/database.php';
require __DIR__ . '/config/app.php';
require __DIR__ . '/models/BaseModel.php';
require __DIR__ . '/models/ContractorModel.php';
require __DIR__ . '/models/ContractorDocumentModel.php';
require __DIR__ . '/models/TenderModel.php';

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $contractorModel = new ContractorModel($pdo);

    // Get all approved contractors
    $stmt = $pdo->query('SELECT contractor_id FROM contractors WHERE verification_status = "approved" OR verification_status = "suspended"');
    $contractors = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $suspendedCount = 0;
    $reactivatedCount = 0;

    foreach ($contractors as $contractorId) {
        $contractorModel->checkAndUpdateAccountStatus($contractorId);

        // Check the updated status
        $updatedContractor = $contractorModel->getById($contractorId);
        if ($updatedContractor['verification_status'] === 'suspended') {
            $suspendedCount++;
        } elseif ($updatedContractor['verification_status'] === 'approved') {
            $reactivatedCount++;
        }
    }

    $tenderModel = new TenderModel($pdo);
    $tenderModel->cleanupTenderLifecycle();

    echo "Maintenance completed successfully.\n";
    echo "Accounts suspended due to expired Phase 1 documents: $suspendedCount\n";
    echo "Accounts reactivated due to renewed Phase 1 documents: $reactivatedCount\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}