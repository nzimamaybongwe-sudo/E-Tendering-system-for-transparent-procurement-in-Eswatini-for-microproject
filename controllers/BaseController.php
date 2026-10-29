<?php
require_once __DIR__ . '/../models/BaseModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/ContractorModel.php';
require_once __DIR__ . '/../models/TenderModel.php';
require_once __DIR__ . '/../models/BidModel.php';
require_once __DIR__ . '/../models/BidDocumentModel.php';
require_once __DIR__ . '/../models/EvaluationModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../models/AuditModel.php';

class BaseController
{
    protected $pdo;
    protected $config;

    public function __construct(PDO $pdo, array $config)
    {
        $this->pdo = $pdo;
        $this->config = $config;
    }

    protected function view(string $template, array $data = [])
    {
        $config = $this->config;
        $unreadNotificationsCount = $this->getUnreadNotificationsCount();
        extract($data);
        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/' . $template . '.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    protected function getUnreadNotificationsCount(): int
    {
        if (empty($_SESSION['user'])) {
            return 0;
        }

        $notificationModel = new NotificationModel($this->pdo);
        $notifications = $notificationModel->getUnreadForUser($_SESSION['user']['user_id']);
        return count($notifications);
    }

    protected function redirect(string $route)
    {
        header('Location: index.php?route=' . $route);
        exit;
    }

    protected function requireRole(array $allowedRoles)
    {
        if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], $allowedRoles, true)) {
            $this->redirect('login');
        }
    }
}
