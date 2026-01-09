<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class Mailer
{
    private PHPMailer $mail;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/mail.php';

        $this->mail = new PHPMailer(true);

        $this->mail->isSMTP();
        $this->mail->Host       = $config['host'];
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = $config['username'];
        $this->mail->Password   = $config['password'];
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port       = $config['port'];

        $this->mail->setFrom(
            $config['from_email'],
            $config['from_name']
        );

        $this->mail->isHTML(true);
        $this->mail->CharSet = 'UTF-8';
    }

    public function send(
        string $to,
        string $subject,
        string $html
    ): void {
        $this->mail->clearAddresses();
        $this->mail->addAddress($to);
        $this->mail->Subject = $subject;
        $this->mail->Body    = $html;

        $this->mail->send();
    }
}
?>