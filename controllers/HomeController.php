<?php
class HomeController extends BaseController
{
    public function index()
    {
        $userModel = new UserModel($this->pdo);
        $tenderModel = new TenderModel($this->pdo);
        $tenderModel->cleanupTenderLifecycle();

        $analytics = [
            'total_tenders' => $tenderModel->countAll(),
            'open_tenders' => $tenderModel->countOpen(),
            'verified_contractors' => $userModel->countRole('contractor'),
            'awarded_projects' => $tenderModel->countAwarded(),
        ];

        $tenders = $tenderModel->getOpen();
        $winners = $tenderModel->getRecentWinners();

        $this->view('home', [
            'analytics' => $analytics,
            'tenders' => $tenders,
            'winners' => $winners,
        ]);
    }

    public function statistics()
    {
        $tenderModel = new TenderModel($this->pdo);
        $userModel = new UserModel($this->pdo);
        $bidModel = new BidModel($this->pdo, $this->config['bid_encryption_key']);

        $stats = [
            'total_tenders' => $tenderModel->countAll(),
            'published_tenders' => $tenderModel->countPublished(),
            'open_tenders' => $tenderModel->countOpen(),
            'awarded_projects' => $tenderModel->countAwarded(),
            'awarded_value' => $tenderModel->totalAwardedValue(),
            'contractor_count' => $userModel->countRole('contractor'),
            'category_breakdown' => $tenderModel->getCategoryBreakdown(),
        ];

        $this->view('public_statistics', ['stats' => $stats]);
    }
}
