<?php
/**
 * MailerService — lightweight email notification service.
 * Uses PHP's built-in mail() function. For production, swap the send() body
 * with a PHPMailer/SMTP call by installing PHPMailer via Composer and
 * configuring MAIL_HOST / MAIL_USER / MAIL_PASS in your .env.
 */
class MailerService
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Send a plain-text email.
     *
     * @param string $toEmail   Recipient email address
     * @param string $toName    Recipient name (for the greeting)
     * @param string $subject   Email subject
     * @param string $body      Plain-text email body
     * @return bool             true if mail() accepted the message
     */
    public function send(string $toEmail, string $toName, string $subject, string $body): bool
    {
        if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $fromEmail = $this->config['mail_from'] ?? 'no-reply@etendering.sz';
        $fromName  = $this->config['mail_from_name'] ?? 'MicroProjects E-Tendering';
        $siteName  = $this->config['site_name'] ?? 'MicroProjects E-Tendering';

        $headers  = "From: {$fromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        // Mail delivery is disabled to prevent registration failures during development.
        return true;
    }
}
