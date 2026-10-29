<?php
class AuthController extends BaseController
{
    // =========================================================
    //  Login
    // =========================================================

    public function login()
    {
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Rate-limit check
            $lockError = $this->checkLoginRateLimit();
            if ($lockError) {
                $error = $lockError;
            } else {
                $identifier = trim($_POST['email'] ?? '');
                $password   = $_POST['password'] ?? '';

                $result = $this->attemptLogin($identifier, $password);
                if ($result['success']) {
                    $this->clearLoginAttempts();
                    session_regenerate_id(true);
                    $_SESSION['user'] = $result['user'];

                    $audit = new AuditModel($this->pdo);
                    $audit->log($result['user']['user_id'], 'login', 'users', $result['user']['user_id'], 'User logged in');

                    switch ($result['user']['role']) {
                        case 'admin':     $this->redirect('admin-dashboard');     break;
                        case 'evaluator': $this->redirect('evaluator-dashboard'); break;
                        case 'officer':   $this->redirect('officer-dashboard');   break;
                        default:          $this->redirect('contractor-dashboard');break;
                    }
                } else {
                    $this->recordLoginFailure();
                    $error = $result['error'];
                }
            }
        }

        $remainingSeconds = $this->getLockoutRemainingSeconds();
        $this->view('auth/login', ['error' => $error, 'lockoutSeconds' => $remainingSeconds]);
    }

    public function apiLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request method.');
        }

        $lockError = $this->checkLoginRateLimit();
        if ($lockError) {
            jsonResponse(false, $lockError);
        }

        $identifier = trim($_POST['email'] ?? '');
        $password   = $_POST['password'] ?? '';

        $result = $this->attemptLogin($identifier, $password);
        if (!$result['success']) {
            $this->recordLoginFailure();
            jsonResponse(false, $result['error']);
        }

        $this->clearLoginAttempts();
        session_regenerate_id(true);
        $_SESSION['user'] = $result['user'];

        $audit = new AuditModel($this->pdo);
        $audit->log($result['user']['user_id'], 'login_api', 'users', $result['user']['user_id'], 'User logged in via API');

        $redirect = 'index.php?route=contractor-dashboard';
        if ($result['user']['role'] === 'admin')     $redirect = 'index.php?route=admin-dashboard';
        if ($result['user']['role'] === 'evaluator') $redirect = 'index.php?route=evaluator-dashboard';
        if ($result['user']['role'] === 'officer')   $redirect = 'index.php?route=officer-dashboard';

        session_write_close();
        jsonResponse(true, 'Login successful.', ['redirect' => $redirect]);
    }

    public function logout()
    {
        if (isset($_SESSION['user']['user_id'])) {
            $userId    = (int)$_SESSION['user']['user_id'];
            $userModel = new UserModel($this->pdo);
            $user      = $userModel->findById($userId);
            if ($user) {
                $audit = new AuditModel($this->pdo);
                $audit->log($userId, 'logout', 'users', $userId, 'User logged out');
            }
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        $this->redirect('home');
    }

    // =========================================================
    //  Registration
    // =========================================================

    public function apiRegister()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'Invalid request method.');
        }

        $email          = trim($_POST['email'] ?? '');
        $password       = $_POST['password'] ?? '';
        $companyName    = trim($_POST['company_name'] ?? '');
        $taxPin         = trim($_POST['tax_pin'] ?? '');
        $categoryInput  = $_POST['category'] ?? [];
        if (!is_array($categoryInput)) {
            $categoryInput = $categoryInput === '' ? [] : [$categoryInput];
        }
        $category       = implode(', ', array_filter(array_map('trim', $categoryInput)));
        $cicGrade       = trim($_POST['cic_grade'] ?? '');
        $localOwnership = isset($_POST['local_ownership']) ? 1 : 0;
        $contactPerson  = trim($_POST['contact_person'] ?? '');
        $phone          = trim($_POST['phone'] ?? '');
        $address        = trim($_POST['address'] ?? '');
        $username       = trim($contactPerson ?: explode('@', $email)[0] ?? '');

        $validationError = $this->validateRegistrationInput($username, $email, $password);
        if ($validationError) {
            jsonResponse(false, $validationError);
        }

        $userModel = new UserModel($this->pdo);
        if ($userModel->findByEmail($email)) {
            jsonResponse(false, 'Email is already registered.');
        }
        if (!$companyName || !$taxPin || !$cicGrade) {
            jsonResponse(false, 'Contractor registration requires company information and credentials.');
        }

        $docValidationError = $this->validateContractorUploads();
        if ($docValidationError) {
            jsonResponse(false, $docValidationError);
        }

        try {
            $this->pdo->beginTransaction();
            $userId = $userModel->create([
                'username'      => $username,
                'email'         => $email,
                'password_hash' => password_hash($password, PASSWORD_BCRYPT),
                'role'          => 'contractor',
                'status'        => 'pending',
            ]);

            $contractorModel = new ContractorModel($this->pdo);
            $contractorId    = $contractorModel->create([
                'user_id'             => $userId,
                'company_name'        => $companyName,
                'category'            => $category,
                'cic_grade'           => $cicGrade,
                'tax_pin'             => $taxPin,
                'form_j_number'       => '',
                'local_ownership'     => $localOwnership,
                'contact_person'      => $contactPerson,
                'phone'               => $phone,
                'address'             => $address,
                'verification_status' => 'pending',
            ]);

            $uploadError = $this->handleContractorUploads($contractorId);
            if ($uploadError) {
                $this->pdo->rollBack();
                jsonResponse(false, $uploadError);
            }

            $this->pdo->commit();

            $audit = new AuditModel($this->pdo);
            $audit->log($userId, 'register_api', 'users', $userId, 'New contractor registered via API');

            session_write_close();
            jsonResponse(true, 'Registration successful. Please wait for admin approval.', ['redirect' => 'index.php?route=login']);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            jsonResponse(false, 'An error occurred during registration. Please try again.');
        }
    }

    public function register()
    {
        $error   = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email          = trim($_POST['email'] ?? '');
            $password       = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $companyName    = trim($_POST['company_name'] ?? '');
            $taxPin         = trim($_POST['tax_pin'] ?? '');
            $categoryInput  = $_POST['category'] ?? [];
            if (!is_array($categoryInput)) {
                $categoryInput = $categoryInput === '' ? [] : [$categoryInput];
            }
            $category       = implode(', ', array_filter(array_map('trim', $categoryInput)));
            $cicGrade       = trim($_POST['cic_grade'] ?? '');
            $localOwnership = isset($_POST['local_ownership']) ? 1 : 0;
            $contactPerson  = trim($_POST['contact_person'] ?? '');
            $phone          = trim($_POST['phone'] ?? '');
            $address        = trim($_POST['address'] ?? '');
            $username       = trim($contactPerson ?: explode('@', $email)[0] ?? '');

            $validationError = $this->validateRegistrationInput($username, $email, $password);
            if ($validationError) {
                $error = $validationError;
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } else {
                $userModel = new UserModel($this->pdo);
                if ($userModel->findByEmail($email)) {
                    $error = 'Email is already registered.';
                } elseif (!$companyName || !$taxPin || !$cicGrade) {
                    $error = 'Contractor registration requires company information and credentials.';
                } else {
                    $docValidationError = $this->validateContractorUploads();
                    if ($docValidationError) {
                        $error = $docValidationError;
                    } else {
                        try {
                            $this->pdo->beginTransaction();
                            $userId = $userModel->create([
                                'username'      => $username,
                                'email'         => $email,
                                'password_hash' => password_hash($password, PASSWORD_BCRYPT),
                                'role'          => 'contractor',
                                'status'        => 'pending',
                            ]);

                            $contractorModel = new ContractorModel($this->pdo);
                            $contractorId    = $contractorModel->create([
                                'user_id'             => $userId,
                                'company_name'        => $companyName,
                                'category'            => $category,
                                'cic_grade'           => $cicGrade,
                                'tax_pin'             => $taxPin,
                                'form_j_number'       => '',
                                'local_ownership'     => $localOwnership,
                                'contact_person'      => $contactPerson,
                                'phone'               => $phone,
                                'address'             => $address,
                                'verification_status' => 'pending',
                            ]);

                            $uploadError = $this->handleContractorUploads($contractorId);
                            if ($uploadError) {
                                $this->pdo->rollBack();
                                $error = $uploadError;
                            } else {
                                $this->pdo->commit();
                                $audit = new AuditModel($this->pdo);
                                $audit->log($userId, 'register', 'users', $userId, 'Contractor registered');
                                $success = 'Contractor registration successful. Your company is pending admin verification.';
                            }
                        } catch (\Exception $e) {
                            $this->pdo->rollBack();
                            $error = 'Registration failed due to a system error. Please try again.';
                        }
                    }
                }
            }
        }

        $this->view('auth/register', ['error' => $error, 'success' => $success]);
    }

    public function registerEvaluator()
    {
        $error   = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username            = trim($_POST['username'] ?? '');
            $email               = trim($_POST['email'] ?? '');
            $password            = $_POST['password'] ?? '';
            $confirmPassword     = $_POST['confirm_password'] ?? '';
            $rolePasscode        = trim($_POST['role_passcode'] ?? '');
            $fullName            = trim($_POST['full_name'] ?? '');
            $accreditationNumber = trim($_POST['accreditation_number'] ?? '');
            $specialization      = trim($_POST['specialization'] ?? '');
            $institution         = trim($_POST['institution'] ?? '');
            $experienceSummary   = trim($_POST['experience_summary'] ?? '');

            $roleKeys  = $this->config['role_registration_keys'] ?? [];
            $userModel = new UserModel($this->pdo);

            $validationError = $this->validateRegistrationInput($username, $email, $password);
            if ($validationError) {
                $error = $validationError;
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } elseif (!isset($roleKeys['evaluator']) || $rolePasscode !== $roleKeys['evaluator']) {
                $error = 'Invalid evaluator registration code.';
            } elseif ($userModel->findByEmail($email)) {
                $error = 'Email is already registered.';
            } elseif (!$fullName || !$accreditationNumber || !$specialization || !$institution) {
                $error = 'Evaluator registration requires accreditation and work details.';
            } elseif (!isset($_FILES['accreditation_document']) || $_FILES['accreditation_document']['error'] !== UPLOAD_ERR_OK) {
                $error = 'Accreditation document is required and must be uploaded successfully.';
            } else {
                $file = $_FILES['accreditation_document'];
                $allowedExts = ['pdf', 'jpg', 'jpeg', 'png'];
                $allowedMimes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $mime = @mime_content_type($file['tmp_name']);
                
                if (!in_array($ext, $allowedExts, true) || !in_array($mime, $allowedMimes, true)) {
                    $error = 'Invalid file type. Only PDF, JPG, and PNG documents are allowed.';
                } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB
                    $error = 'Accreditation document must be smaller than 5MB.';
                } else {
                    try {
                        $this->pdo->beginTransaction();

                        $userId = $userModel->create([
                            'username'      => $username,
                            'email'         => $email,
                            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
                            'role'          => 'evaluator',
                            'status'        => 'pending',
                        ]);

                        $uploadDir = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR;
                        if (!is_dir($uploadDir)) {
                            if (!mkdir($uploadDir, 0755, true)) {
                                throw new Exception('Failed to create upload directory.');
                            }
                            file_put_contents($uploadDir . '.htaccess', 'Deny from all');
                        }

                        $newFileName = sprintf('evaluator_%d_accreditation_%d.%s', $userId, time(), $ext);
                        $destination = $uploadDir . $newFileName;

                        if (!move_uploaded_file($file['tmp_name'], $destination)) {
                            throw new Exception('Failed to save uploaded document.');
                        }

                        $documentPath = 'documents/' . $newFileName;

                        $evaluatorModel = new EvaluatorProfileModel($this->pdo);
                        $evaluatorModel->create([
                            'user_id'              => $userId,
                            'full_name'            => $fullName,
                            'accreditation_number' => $accreditationNumber,
                            'specialization'       => $specialization,
                            'institution'          => $institution,
                            'experience_summary'   => $experienceSummary,
                            'accreditation_document' => $documentPath,
                        ]);

                        $this->pdo->commit();

                        $audit = new AuditModel($this->pdo);
                        $audit->log($userId, 'register', 'users', $userId, 'Evaluator registered');
                        $success = 'Evaluator registration submitted. Your credentials will be reviewed before login.';
                    } catch (Exception $e) {
                        $this->pdo->rollBack();
                        if (isset($destination) && file_exists($destination)) {
                            @unlink($destination);
                        }
                        $error = 'Registration failed: ' . $e->getMessage();
                    }
                }
            }
        }

        $this->view('auth/register_evaluator', ['error' => $error, 'success' => $success]);
    }

    public function registerOfficer()
    {
        $error   = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username        = trim($_POST['username'] ?? '');
            $email           = trim($_POST['email'] ?? '');
            $password        = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $rolePasscode    = trim($_POST['role_passcode'] ?? '');
            $fullName        = trim($_POST['full_name'] ?? '');
            $employeeId      = trim($_POST['employee_id'] ?? '');
            $department      = trim($_POST['department'] ?? '');
            $title           = trim($_POST['title'] ?? '');

            $roleKeys  = $this->config['role_registration_keys'] ?? [];
            $userModel = new UserModel($this->pdo);

            $validationError = $this->validateRegistrationInput($username, $email, $password);
            if ($validationError) {
                $error = $validationError;
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } elseif (!isset($roleKeys['officer']) || $rolePasscode !== $roleKeys['officer']) {
                $error = 'Invalid officer registration code.';
            } elseif ($userModel->findByEmail($email)) {
                $error = 'Email is already registered.';
            } elseif (!$fullName || !$employeeId || !$department || !$title) {
                $error = 'Officer registration requires employee details.';
            } else {
                $userId = $userModel->create([
                    'username'      => $username,
                    'email'         => $email,
                    'password_hash' => password_hash($password, PASSWORD_BCRYPT),
                    'role'          => 'officer',
                    'status'        => 'pending',
                ]);

                require_once __DIR__ . '/../models/OfficerProfileModel.php';
                $officerModel = new OfficerProfileModel($this->pdo);
                $officerModel->create([
                    'user_id'         => $userId,
                    'full_name'       => $fullName,
                    'employee_id'     => $employeeId,
                    'department'      => $department,
                    'title'           => $title,
                ]);

                $audit = new AuditModel($this->pdo);
                $audit->log($userId, 'register', 'users', $userId, 'Procurement Officer registered');
                $success = 'Officer registration submitted. Your credentials will be reviewed before login.';
            }
        }

        $this->view('auth/register_officer', ['error' => $error, 'success' => $success]);
    }

    public function registerAdmin()
    {
        $error   = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username               = trim($_POST['username'] ?? '');
            $email                  = trim($_POST['email'] ?? '');
            $password               = $_POST['password'] ?? '';
            $confirmPassword        = $_POST['confirm_password'] ?? '';
            $rolePasscode           = trim($_POST['role_passcode'] ?? '');
            $fullName               = trim($_POST['full_name'] ?? '');
            $governmentId           = trim($_POST['government_id'] ?? '');
            $authorizationReference = trim($_POST['authorization_reference'] ?? '');
            $officePhone            = trim($_POST['office_phone'] ?? '');

            $roleKeys  = $this->config['role_registration_keys'] ?? [];
            $userModel = new UserModel($this->pdo);

            $validationError = $this->validateRegistrationInput($username, $email, $password);
            if ($validationError) {
                $error = $validationError;
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } elseif (!isset($roleKeys['admin']) || $rolePasscode !== $roleKeys['admin']) {
                $error = 'Invalid administrator registration code.';
            } elseif ($userModel->findByEmail($email)) {
                $error = 'Email is already registered.';
            } elseif (!$fullName || !$governmentId || !$authorizationReference) {
                $error = 'Administrator registration requires official ID and authorization reference.';
            } else {
                // Smart Admin Logic: first admin gets active status (super admin bootstrap)
                $activeAdminCount = $userModel->countActiveRole('admin');
                $initialStatus    = ($activeAdminCount === 0) ? 'active' : 'pending';

                $userId = $userModel->create([
                    'username'      => $username,
                    'email'         => $email,
                    'password_hash' => password_hash($password, PASSWORD_BCRYPT),
                    'role'          => 'admin',
                    'status'        => $initialStatus,
                ]);

                $adminProfile = new AdminProfileModel($this->pdo);
                $adminProfile->create([
                    'user_id'                 => $userId,
                    'full_name'               => $fullName,
                    'government_id'           => $governmentId,
                    'authorization_reference' => $authorizationReference,
                    'office_phone'            => $officePhone,
                ]);

                $audit = new AuditModel($this->pdo);
                $audit->log($userId, 'register', 'users', $userId, 'Administrator registered');
                $success = 'Administrator registration submitted. Your credentials will be reviewed before login.';
            }
        }

        $this->view('auth/register_admin', ['error' => $error, 'success' => $success]);
    }

    // =========================================================
    //  Password Reset Flow
    // =========================================================

    public function forgotPassword()
    {
        $message = null;
        $error   = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email     = trim($_POST['email'] ?? '');
            $userModel = new UserModel($this->pdo);
            $user      = $userModel->findByEmail($email);

            if ($user) {
                $token     = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $resetModel = new PasswordResetModel($this->pdo);
                $resetModel->createToken($user['user_id'], $token, $expiresAt);

                $resetLink = (isset($_SERVER['HTTPS']) ? 'https' : 'http')
                    . '://' . $_SERVER['HTTP_HOST']
                    . '/E-Tendering system project/public/index.php?route=reset-password&token=' . $token;

                $mailer = new MailerService($this->config);
                $mailer->send(
                    $email,
                    $user['username'],
                    'Password Reset — MicroProjects E-Tendering',
                    "Hello {$user['username']},\n\nYou requested a password reset. Click the link below (valid for 1 hour):\n\n{$resetLink}\n\nIf you did not request this, please ignore this email."
                );
            }

            // Generic message — don't reveal whether the email exists
            $message = 'If that email exists in our system, a reset link has been sent.';
        }

        $this->view('auth/forgot_password', ['message' => $message, 'error' => $error]);
    }

    public function resetPassword()
    {
        $token   = trim($_GET['token'] ?? '');
        $error   = null;
        $success = null;

        $resetModel = new PasswordResetModel($this->pdo);
        $record     = $resetModel->findValidToken($token);

        if (!$record) {
            $error = 'This reset link is invalid or has expired.';
            $this->view('auth/reset_password', ['error' => $error, 'success' => $success, 'token' => $token]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm  = $_POST['confirm_password'] ?? '';

            $strengthError = $this->validatePasswordStrength($password);
            if ($strengthError) {
                $error = $strengthError;
            } elseif ($password !== $confirm) {
                $error = 'Passwords do not match.';
            } else {
                $userModel = new UserModel($this->pdo);
                $userModel->updatePassword($record['user_id'], password_hash($password, PASSWORD_BCRYPT));
                $resetModel->invalidateToken($token);
                $audit = new AuditModel($this->pdo);
                $audit->log($record['user_id'], 'password_reset', 'users', $record['user_id'], 'Password reset via token');
                $success = 'Your password has been reset. You may now log in.';
            }
        }

        $this->view('auth/reset_password', ['error' => $error, 'success' => $success, 'token' => $token]);
    }

    // =========================================================
    //  Private Helpers
    // =========================================================

    /**
     * Shared login logic used by both login() and apiLogin().
     * Returns ['success' => true, 'user' => [...]] or ['success' => false, 'error' => '...'].
     */
    private function attemptLogin(string $identifier, string $password): array
    {
        if (empty($identifier) || empty($password)) {
            return ['success' => false, 'error' => 'Please enter your email/username and password.'];
        }

        $userModel = new UserModel($this->pdo);
        $user      = $userModel->findByLogin($identifier);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Invalid email or password.'];
        }

        // Auto-activate if approved but still marked pending
        if ($user['status'] !== 'active') {
            if ($user['role'] === 'contractor') {
                $contractorModel = new ContractorModel($this->pdo);
                $contractor      = $contractorModel->getByUserId($user['user_id']);
                if ($contractor && $contractor['verification_status'] === 'approved' && empty($contractor['blacklisted'])) {
                    $userModel->updateStatus($user['user_id'], 'active');
                    $user['status'] = 'active';
                }
            } elseif ($user['role'] === 'evaluator') {
                $evaluatorModel = new EvaluatorProfileModel($this->pdo);
                $evaluator      = $evaluatorModel->getByUserId($user['user_id']);
                if ($evaluator && $evaluator['approval_status'] === 'approved') {
                    $userModel->updateStatus($user['user_id'], 'active');
                    $user['status'] = 'active';
                }
            } elseif ($user['role'] === 'officer') {
                require_once __DIR__ . '/../models/OfficerProfileModel.php';
                $officerModel = new OfficerProfileModel($this->pdo);
                $officer      = $officerModel->getByUserId($user['user_id']);
                if ($officer && $officer['approval_status'] === 'approved') {
                    $userModel->updateStatus($user['user_id'], 'active');
                    $user['status'] = 'active';
                }
            }
        }

        if ($user['status'] !== 'active') {
            return ['success' => false, 'error' => 'Your account is not active yet. Please wait for admin approval.'];
        }

        return [
            'success' => true,
            'user'    => [
                'user_id'  => $user['user_id'],
                'username' => $user['username'],
                'role'     => $user['role'],
            ],
        ];
    }

    /**
     * Validates common registration fields: username length, email format,
     * password strength.
     */
    private function validateRegistrationInput(string $username, string $email, string $password): ?string
    {
        if (empty($username) || strlen($username) > 80) {
            return 'Username is required and must be 80 characters or fewer.';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            return 'A valid email address (max 150 characters) is required.';
        }
        return $this->validatePasswordStrength($password);
    }

    /**
     * Enforces password strength: at least 8 characters, one uppercase letter,
     * one digit, and one special character.
     */
    private function validatePasswordStrength(string $password): ?string
    {
        if (strlen($password) < 8) {
            return 'Password must be at least 8 characters long.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return 'Password must contain at least one uppercase letter.';
        }
        if (!preg_match('/[0-9]/', $password)) {
            return 'Password must contain at least one number.';
        }
        if (!preg_match('/[\W_]/', $password)) {
            return 'Password must contain at least one special character (e.g. !@#$%).';
        }
        return null;
    }

    // ── Login Rate Limiting (session-based) ─────────────────────────────────

    private const MAX_ATTEMPTS     = 5;
    private const LOCKOUT_SECONDS  = 300; // 5 minutes

    private function checkLoginRateLimit(): ?string
    {
        $attempts  = $_SESSION['login_attempts'] ?? 0;
        $lockUntil = $_SESSION['login_lock_until'] ?? 0;

        if ($lockUntil > time()) {
            $remaining = ceil(($lockUntil - time()) / 60);
            return "Too many failed login attempts. Please wait {$remaining} minute(s) before trying again.";
        }
        // Reset stale lockout
        if ($lockUntil > 0 && $lockUntil <= time()) {
            $this->clearLoginAttempts();
        }
        return null;
    }

    private function recordLoginFailure(): void
    {
        $attempts = ($_SESSION['login_attempts'] ?? 0) + 1;
        $_SESSION['login_attempts'] = $attempts;

        if ($attempts >= self::MAX_ATTEMPTS) {
            $_SESSION['login_lock_until'] = time() + self::LOCKOUT_SECONDS;
        }
    }

    private function clearLoginAttempts(): void
    {
        unset($_SESSION['login_attempts'], $_SESSION['login_lock_until']);
    }

    private function getLockoutRemainingSeconds(): int
    {
        $lockUntil = $_SESSION['login_lock_until'] ?? 0;
        return ($lockUntil > time()) ? (int)($lockUntil - time()) : 0;
    }

    // ── Email Helpers ────────────────────────────────────────────────────────

    /**
     * Sends a confirmation email after a successful registration submission.
     */
    private function sendRegistrationConfirmationEmail(string $email, string $username, string $role): void
    {
        // Mail delivery is currently disabled to avoid registration blocking.
        return;
    }

    // ── Upload Validation & Handling ─────────────────────────────────────────

    /**
     * Pre-validates that all required contractor documents are present and have valid types.
     * Returns error string on failure, null on success.
     */
    private function validateContractorUploads(): ?string
    {
        $expectedDocs = [
            'cert_incorporation' => 'Certificate of Incorporation',
            'form_j_c'           => 'Form J & Form C',
            'cic_registration'   => 'CIC Registration Certificate',
            'vat_registration'   => 'VAT Registration Certificate',
            'trading_licence'    => 'Trading Licence',
            'tax_clearance'      => 'Tax Clearance Certificate',
            'enpf_compliance'    => 'SNPF/ENPF Compliance',
            'police_clearance'   => 'Police Clearance',
            'labour_compliance'  => 'Labour Compliance Certificate',
        ];

        $allowedExts  = ['pdf', 'jpg', 'jpeg', 'png'];
        $allowedMimes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];

        foreach ($expectedDocs as $inputName => $docTitle) {
            if (!isset($_FILES[$inputName])) {
                return "Missing required document: {$docTitle}.";
            }

            $errors = is_array($_FILES[$inputName]['error']) ? $_FILES[$inputName]['error'] : [$_FILES[$inputName]['error']];
            $names  = is_array($_FILES[$inputName]['name'])  ? $_FILES[$inputName]['name']  : [$_FILES[$inputName]['name']];
            $tmps   = is_array($_FILES[$inputName]['tmp_name']) ? $_FILES[$inputName]['tmp_name'] : [$_FILES[$inputName]['tmp_name']];

            $hasValidFile = false;
            foreach ($errors as $i => $errCode) {
                if ($errCode === UPLOAD_ERR_OK && !empty($names[$i])) {
                    $ext  = strtolower(pathinfo($names[$i], PATHINFO_EXTENSION));
                    $mime = mime_content_type($tmps[$i]);
                    if (!in_array($ext, $allowedExts, true)) {
                        return "Invalid file type for {$docTitle}. Allowed: PDF, JPG, PNG.";
                    }
                    if (!in_array($mime, $allowedMimes, true)) {
                        return "Invalid file content for {$docTitle}. Allowed: PDF, JPG, PNG.";
                    }
                    $hasValidFile = true;
                }
            }

            if (!$hasValidFile) {
                return "Please upload a valid file for: {$docTitle}.";
            }
        }

        return null;
    }

    /**
     * Moves validated upload files to secure storage outside public/ and records them in the DB.
     * Returns error string on failure, null on success.
     */
    private function handleContractorUploads(int $contractorId): ?string
    {
        $uploadDir = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                return 'Failed to create upload directory.';
            }
            file_put_contents($uploadDir . '.htaccess', 'Deny from all');
        }

        $expectedDocs = [
            'cert_incorporation' => ['name' => 'Certificate of Incorporation',   'phase' => 'phase1'],
            'form_j_c'           => ['name' => 'Form J & Form C',                'phase' => 'phase1'],
            'cic_registration'   => ['name' => 'CIC Registration Certificate',   'phase' => 'phase1'],
            'vat_registration'   => ['name' => 'VAT Registration Certificate',   'phase' => 'phase1'],
            'trading_licence'    => ['name' => 'Trading Licence',                'phase' => 'phase1'],
            'tax_clearance'      => ['name' => 'Tax Clearance Certificate',      'phase' => 'phase2'],
            'enpf_compliance'    => ['name' => 'SNPF/ENPF Compliance',           'phase' => 'phase2'],
            'police_clearance'   => ['name' => 'Police Clearance',               'phase' => 'phase2'],
            'labour_compliance'  => ['name' => 'Labour Compliance Certificate',  'phase' => 'phase2'],
        ];

        $allowedExts        = ['pdf', 'jpg', 'jpeg', 'png'];
        $allowedMimes       = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        $contractorDocModel = new ContractorDocumentModel($this->pdo);

        foreach ($expectedDocs as $inputName => $docConfig) {
            if (!isset($_FILES[$inputName])) continue;

            $docTitle  = $docConfig['name'];
            $filesData = [];

            if (is_array($_FILES[$inputName]['name'])) {
                $count = count($_FILES[$inputName]['name']);
                for ($i = 0; $i < $count; $i++) {
                    if ($_FILES[$inputName]['error'][$i] === UPLOAD_ERR_OK && !empty($_FILES[$inputName]['name'][$i])) {
                        $filesData[] = ['name' => $_FILES[$inputName]['name'][$i], 'tmp_name' => $_FILES[$inputName]['tmp_name'][$i]];
                    }
                }
            } else {
                if ($_FILES[$inputName]['error'] === UPLOAD_ERR_OK && !empty($_FILES[$inputName]['name'])) {
                    $filesData[] = ['name' => $_FILES[$inputName]['name'], 'tmp_name' => $_FILES[$inputName]['tmp_name']];
                }
            }

            if (empty($filesData)) continue;

            foreach ($filesData as $index => $fileItem) {
                $fileTmp  = $fileItem['tmp_name'];
                $fileName = $fileItem['name'];
                $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if (!in_array($fileExt, $allowedExts, true)) {
                    return "Invalid file type for {$docTitle}.";
                }
                $mime = mime_content_type($fileTmp);
                if (!in_array($mime, $allowedMimes, true)) {
                    return "Invalid file content for {$docTitle}.";
                }

                $expiryDate = null;
                if ($docConfig['phase'] === 'phase2') {
                    if ($inputName === 'tax_clearance') {
                        $expiryDate = date('Y-m-d', strtotime('+30 days'));
                    } elseif (in_array($inputName, ['enpf_compliance', 'police_clearance'], true)) {
                        $expiryDate = date('Y-m-d', strtotime('+90 days'));
                    }
                }

                $newFileName = sprintf('contractor_%d_%s_%d_%s.%s', $contractorId, $inputName, $index, time(), $fileExt);
                $destination = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmp, $destination)) {
                    $contractorDocModel->create([
                        'contractor_id'  => $contractorId,
                        'document_name'  => $docTitle . (count($filesData) > 1 ? ' (Part ' . ($index + 1) . ')' : ''),
                        'document_path'  => 'documents/' . $newFileName,
                        'document_phase' => $docConfig['phase'],
                        'expiry_date'    => $expiryDate,
                    ]);
                } else {
                    return "Failed to save uploaded file for {$docTitle}.";
                }
            }
        }

        return null;
    }
}
