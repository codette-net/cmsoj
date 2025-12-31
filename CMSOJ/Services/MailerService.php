<?php

namespace CMSOJ\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use CMSOJ\Core\Config;

class MailerService
{
    private PHPMailer $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);

        // DEV DEBUG: log SMTP conversation to PHP error_log
        // Comment out in production
        $this->mail->SMTPDebug  = 2;
        $this->mail->Debugoutput = 'error_log';

        // If SMTP mode enabled use it
        if (Config::get('SMTP') === 'true') {
            $this->mail->isSMTP();
            $this->mail->Host = Config::get('SMTP_HOST');
            $this->mail->Port = Config::get('SMTP_PORT');
            $this->mail->SMTPAuth = Config::get('SMTP_USER') !== '';
            $this->mail->SMTPSecure = false; // no TLS on port 1025 (mailpit)
            $this->mail->Username = Config::get('SMTP_USER');
            $this->mail->Password = Config::get('SMTP_PASS');
        }

        $this->mail->isHTML(true);
    }

    /**
     * Returns true on success, false on failure
     * and puts a readable error message in $error (by reference).
     */
    public function sendReservation(array $data, ?string &$error = null): bool
    {
        try {
            $this->mail->setFrom('jos@jos.com', $data['first_name'] ?? 'Reservation');
            $this->mail->addAddress('pol@pol.com', 'Reservations');

            if (!empty($data['email'])) {
                $this->mail->addReplyTo($data['email'], $data['first_name'] ?? '');
            }

            $this->mail->Subject = 'RESERVATION for ' . $data['first_name'] . ' - ' . $data['email'];
            $this->mail->Body    = $data['html'];
            $this->mail->AltBody = strip_tags($data['html']);

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            // Try ErrorInfo first, fall back to exception message
            $error = $this->mail->ErrorInfo ?: $e->getMessage();
            error_log('Reservation mail error: ' . $error);
            return false;
        }
    }
}
