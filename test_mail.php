<?php
require 'vendor/autoload.php';
$mailCfg = require 'includes/email_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = $mailCfg['smtp_host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $mailCfg['smtp_user'];
    $mail->Password   = $mailCfg['smtp_pass'];
    $mail->SMTPSecure = $mailCfg['smtp_secure'];
    $mail->Port       = $mailCfg['smtp_port'];

    $mail->setFrom($mailCfg['from_email'], $mailCfg['from_name']);
    $mail->addAddress('your_email@gmail.com');

    $mail->isHTML(true);
    $mail->Subject = 'Test Email';
    $mail->Body    = '<h1>It works!</h1>';

    $mail->send();
    echo '✅ Email sent successfully!';
} catch (Exception $e) {
    echo '❌ Mailer Error: ' . $mail->ErrorInfo;
}
