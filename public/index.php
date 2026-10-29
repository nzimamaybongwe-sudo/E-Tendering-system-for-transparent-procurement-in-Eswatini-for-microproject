<?php
// public/index.php – Front controller for E-Tendering System

define('BASE_PATH', dirname(__DIR__));ob_start();
// ── Load environment & config ───────────────────────────────────────────────
require_once BASE_PATH . '/config/env.php';
loadEnv(BASE_PATH . '/.env');

$config   = require BASE_PATH . '/config/app.php';
$dbConfig = require BASE_PATH . '/config/database.php';

// ── Database connection ─────────────────────────────────────────────────────
try {
    $pdo = new PDO(
        'mysql:host=' . $dbConfig['host'] . ';dbname=' . $dbConfig['dbname'] . ';charset=utf8mb4',
        $dbConfig['user'],
        $dbConfig['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}

// ── Session ─────────────────────────────────────────────────────────────────
session_start();

// ── Autoload: models → base controller → controllers ───────────────────────
require_once BASE_PATH . '/models/BaseModel.php';
foreach (glob(BASE_PATH . '/models/*.php') as $file) {
    if (basename($file) !== 'BaseModel.php') {
        require_once $file;
    }
}
require_once BASE_PATH . '/controllers/BaseController.php';
foreach (glob(BASE_PATH . '/controllers/*.php') as $file) {
    if (basename($file) !== 'BaseController.php') {
        require_once $file;
    }
}

// ── Services ─────────────────────────────────────────────────────────────────
foreach (glob(BASE_PATH . '/services/*.php') as $file) {
    require_once $file;
}

// ── Helper ──────────────────────────────────────────────────────────────────
if (!function_exists('jsonResponse')) {
    function jsonResponse(bool $success, string $message, array $data = []): void {
        if (ob_get_length() !== false) {
            ob_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $data), JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('t')) {
    function t(string $key): string {
        static $translations = null;
        if ($translations === null) {
            $langFile = BASE_PATH . '/config/lang.php';
            $translations = file_exists($langFile) ? require $langFile : [];
        }
        return $translations['en'][$key] ?? $key;
    }
}

// ── Route map: route-slug => [ControllerClass, method] ──────────────────────
$routes = [
    // Public / auth
    ''                          => ['HomeController',       'index'],
    'home'                      => ['HomeController',       'index'],
    'public-stats'              => ['HomeController',       'statistics'],
    'login'                     => ['AuthController',       'login'],
    'api-login'                 => ['AuthController',       'apiLogin'],
    'logout'                    => ['AuthController',       'logout'],
    'register'                  => ['AuthController',       'register'],
    'api-register'              => ['AuthController',       'apiRegister'],
    'register-evaluator'        => ['AuthController',       'registerEvaluator'],
    'register-officer'          => ['AuthController',       'registerOfficer'],
    'register-admin'            => ['AuthController',       'registerAdmin'],
    'forgot-password'           => ['AuthController',       'forgotPassword'],
    'reset-password'            => ['AuthController',       'resetPassword'],

    // Tenders (public)
    'tenders'                   => ['TenderController',     'list'],
    'tender-view'               => ['TenderController',     'show'],
    'watch-live'                => ['TenderController',     'watchLive'],
    'api-opening-status'        => ['TenderController',     'apiOpeningStatus'],
    'submit-bid'                => ['TenderController',     'submitBid'],
    'api-submit-bid'            => ['TenderController',     'apiSubmitBid'],
    'bid-receipt'               => ['TenderController',     'bidReceipt'],

    // Contractor
    'contractor-dashboard'      => ['ContractorController', 'dashboard'],
    'contractor-profile'        => ['ContractorController', 'profile'],
    'contractor-edit-profile'   => ['ContractorController', 'editProfile'],
    'add-portfolio-item'        => ['ContractorController', 'addPortfolioItem'],
    'delete-portfolio-item'     => ['ContractorController', 'deletePortfolioItem'],
    'my-appeals'                => ['ContractorController', 'myAppeals'],
    'submit-appeal'             => ['ContractorController', 'submitAppeal'],
    'upload-document'           => ['ContractorController', 'uploadDocument'],
    'api-submit-winner-ack'     => ['ContractorController', 'apiSubmitWinnerAck'],

    // Evaluator
    'evaluator-dashboard'               => ['EvaluatorController',  'dashboard'],
    'evaluate-tender'                   => ['EvaluatorController',  'evaluateTender'],
    'submit-evaluation'                 => ['EvaluatorController',  'submitEvaluation'],
    'finish-tender-evaluation'          => ['EvaluatorController',  'finishTenderEvaluation'],
    'mark-evaluator-ready'              => ['EvaluatorController',  'apiMarkEvaluatorReady'],
    'propose-rubric'                    => ['EvaluatorController',  'proposeRubric'],
    'submit-rubric-proposal'            => ['EvaluatorController',  'submitRubricProposal'],
    'submit-evaluator-recommendation'   => ['EvaluatorController',  'submitRecommendation'],

    // Admin
    'admin-dashboard'           => ['AdminController',      'dashboard'],
    'review-applicants'         => ['AdminController',      'reviewApplicants'],
    'view-applicant'            => ['AdminController',      'viewApplicant'],
    'evaluators'                => ['AdminController',      'listEvaluators'],
    'process-applicant'         => ['AdminController',      'processApplicant'],
    'notifications'             => ['AdminController',      'notifications'],
    'verify-contractor'         => ['AdminController',      'verifyContractor'],
    'blacklist-contractor'      => ['AdminController',      'blacklistContractor'],
    'contractor-blacklist'      => ['AdminController',      'contractorBlacklist'],
    'audit-log'                 => ['AdminController',      'auditLog'],
    'manage-appeals'            => ['AdminController',      'manageAppeals'],
    'respond-appeal'            => ['AdminController',      'respondAppeal'],

    // Procurement Officer
    'officer-dashboard'         => ['OfficerController',    'dashboard'],
    'manage-site-visits'        => ['OfficerController',    'manageSiteVisits'],
    'api-flag-document'         => ['OfficerController',    'apiFlagDocument'],
    'publish-tender'            => ['OfficerController',    'publishTender'],
    'api-publish-tender'        => ['OfficerController',    'apiPublishTender'],
    'review-rubric-proposals'   => ['OfficerController',    'reviewRubricProposals'],
    'approve-rubric-proposal'   => ['OfficerController',    'approveRubricProposal'],
    'reject-rubric-proposal'    => ['OfficerController',    'rejectRubricProposal'],
    'amend-tender'              => ['OfficerController',    'amendTender'],
    'release-tender-evaluation' => ['OfficerController',    'releaseTenderForEvaluation'],
    'manage-tender'             => ['OfficerController',    'manageTender'],
    'upload-tender-document'    => ['OfficerController',    'uploadTenderDocument'],
    'award-tender'              => ['OfficerController',    'awardTender'],
    'consensus-meeting'         => ['OfficerController',    'manageConsensus'],
    'submit-consensus'          => ['OfficerController',    'submitConsensus'],
    'schedule-consensus-meeting'=> ['OfficerController',    'scheduleConsensusMeeting'],
    'open-evaluation-session'   => ['OfficerController',    'openEvaluationSession'],
    'submit-evaluation-comment' => ['OfficerController',    'submitEvaluationComment'],
    'admin-mark-evaluator-ready'=> ['OfficerController',    'markEvaluatorReady'],
    'close-evaluation'          => ['OfficerController',    'closeEvaluation'],
    'request-zoom-meeting'      => ['OfficerController',    'requestZoomMeeting'],
    'approve-zoom-meeting'      => ['OfficerController',    'approveZoomMeeting'],
    'tender-opening-room'       => ['OfficerController',    'tenderOpeningRoom'],
    'api-break-seal'            => ['OfficerController',    'apiBreakSeal'],
    'api-verify-legitimacy'     => ['OfficerController',    'apiVerifyLegitimacy'],
    'api-award-publicly'        => ['OfficerController',    'apiAwardPublicly'],
    'api-set-meeting-link'      => ['AdminController',      'apiSetMeetingLink'],
    'winner-specifications'     => ['OfficerController',    'winnerSpecifications'],
    'create-tender'             => ['OfficerController',    'publishTender'],

    // Downloads
    'download-contractor-doc'   => ['DownloadController',   'contractorDocument'],
    'download-bid-doc'          => ['DownloadController',   'bidDocument'],
    'download-tender-doc'       => ['DownloadController',   'tenderDocument'],
    'download-evaluator-doc'    => ['DownloadController',   'evaluatorDocument'],

    // Guidance / Information
    'guidance'                  => ['InformationController','index'],
    'guidance-requirements'     => ['InformationController','requirements'],
    'guidance-guide'            => ['InformationController','biddingGuide'],
    'help'                      => ['InformationController','help'],
];

// ── Dispatch ─────────────────────────────────────────────────────────────────
$route = trim($_GET['route'] ?? '');

if (!isset($routes[$route])) {
    // Unknown route – redirect to home
    header('Location: index.php');
    exit;
}

[$controllerClass, $method] = $routes[$route];

if (!class_exists($controllerClass)) {
    http_response_code(500);
    die("Controller class '$controllerClass' not found.");
}

$controller = new $controllerClass($pdo, $config);

if (!method_exists($controller, $method)) {
    http_response_code(500);
    die("Method '$method' not found in '$controllerClass'.");
}

$controller->$method();
