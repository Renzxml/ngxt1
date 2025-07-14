<?php
/**
 * mail_helpers.php
 * ------------------------------------------------------------
 * Central mail helper for the whole project:
 *   • notifyReviewers()     – alert admins when a user registers
 *   • sendPasswordReset()   – send password‑reset link to a user
 * ------------------------------------------------------------
 * Requires:
 *   • composer require phpmailer/phpmailer
 *   • config/email_config.php  (SMTP settings & reviewer list)
 */

require_once __DIR__ . '/../vendor/autoload.php';               // Composer autoloader
$mailCfg = require 'email_config.php';     // << correct path

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* ------------------------------------------------------------------
   COMMON: returns a configured PHPMailer instance
-------------------------------------------------------------------*/
function createMailer(): PHPMailer
{
    global $mailCfg;

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = $mailCfg['smtp_host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $mailCfg['smtp_user'];
    $mail->Password   = $mailCfg['smtp_pass'];
    $mail->SMTPSecure = $mailCfg['smtp_secure'];   // 'tls' or 'ssl'
    $mail->Port       = $mailCfg['smtp_port'];
    $mail->CharSet    = 'UTF-8';

    return $mail;
}

/* ------------------------------------------------------------------
   1) Notify reviewers of a new registration
-------------------------------------------------------------------*/
function notifyReviewers(array $user): bool
{
    global $mailCfg;

    /* 1 ▸ DB connection */
    $db = new db_connect();
    if (!$db->connect()) {
        error_log('DB connect error in notifyReviewers');
        return false;
    }
    $conn = $db->conn;

    /* 2 ▸ Create two tokens (24 h) */
    $acceptToken = bin2hex(random_bytes(32));
    $rejectToken = bin2hex(random_bytes(32));
    $expires     = date('Y-m-d H:i:s', strtotime('+24 hours'));

    $tok = $conn->prepare(
        'INSERT INTO registration_tokens (user_id, token, act, expires_at)
         VALUES (?, ?, "accept", ?), (?, ?, "reject", ?)'
    );
    $tok->bind_param('ississ',
        $user['id'], $acceptToken, $expires,
        $user['id'], $rejectToken, $expires
    );
    $tok->execute();

    /* 3 ▸ Build links */
    $base = rtrim($mailCfg['system_base_url'], '/');
    $acceptUrl = $base . '/admin/registration_action.php?token=' . $acceptToken;
    $rejectUrl  = $base . '/admin/registration_action.php?token=' . $rejectToken;

    /* 4 ▸ Send e‑mail */
    try {
        $mail = createMailer();
        $mail->setFrom($mailCfg['from_email'], $mailCfg['from_name']);
        foreach ($mailCfg['reviewers'] as $addr) $mail->addAddress($addr);
        $mail->addReplyTo($user['email'], "{$user['fname']} {$user['lname']}");

        $mail->isHTML(true);
        $mail->Subject = "[New Registration] {$user['fname']} {$user['lname']}";

        ob_start();
        $tpl = __DIR__ . '/../email_templates/new_registration.php';
        if (file_exists($tpl)) {
            // variables for template
            $acceptUrlForTpl = $acceptUrl;
            $rejectUrlForTpl = $rejectUrl;
            require $tpl;
            $html = ob_get_clean();
        } else {
            ob_end_clean();
            $html = "<p>Template not found.</p>";
        }

        $mail->Body    = $html;
        $mail->AltBody = "Accept: $acceptUrl\r\nReject: $rejectUrl";

        return $mail->send();
    } catch (Exception $e) {
        error_log('PHPMailer notify error: ' . $e->getMessage());
        return false;
    }
}


/* ------------------------------------------------------------------
   2) Send password‑reset link
-------------------------------------------------------------------*/
function sendPasswordReset(string $toEmail, string $toName, string $resetLink): bool
{
    global $mailCfg;

    try {
        $mail = createMailer();

        $mail->setFrom($mailCfg['from_email'], $mailCfg['from_name']);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'Reset Your Password';

        /* HTML template */
        ob_start();
        $tpl = __DIR__ . '/../email_templates/password_reset.php';
        if (file_exists($tpl)) {
            $name = $toName;
            $link = $resetLink;
            require $tpl;
            $html = ob_get_clean();
        } else {
            ob_end_clean();
            $html = "<p>Click to reset your password: <a href='{$resetLink}'>{$resetLink}</a></p>";
        }

        $mail->Body    = $html;
        $mail->AltBody = "Reset your password: {$resetLink}";

        return $mail->send();
    } catch (Exception $e) {
        error_log('PHPMailer reset error: ' . $e->getMessage());
        return false;
    }
}



/* ------------------------------------------------------------------
   3) Contact‑form mailer
-------------------------------------------------------------------*/
function sendContactMail(string $fromEmail, string $fromName, string $msgBody): bool
{
    global $mailCfg;

    try {
        $mail = createMailer();

        // From visitor
        $mail->setFrom($fromEmail, $fromName);
        $mail->addReplyTo($fromEmail, $fromName);

        // To your site’s contact mailbox (can be same as reviewers or different)
        foreach ($mailCfg['contact_recipients'] as $addr) {
            $mail->addAddress($addr);
        }

        $mail->isHTML(true);
        $mail->Subject = "📬 New contact message from {$fromName}";

        /* HTML template */
        ob_start();
        $tpl = __DIR__ . '/../email_templates/contact_message.php';
        if (file_exists($tpl)) {
            $name = $fromName;  // expose vars
            $email = $fromEmail;
            $body = nl2br(htmlspecialchars($msgBody));
            require $tpl;
            $html = ob_get_clean();
        } else {
            ob_end_clean();
            $html = "<p>{$body}</p>";
        }

        $mail->Body    = $html;
        $mail->AltBody = "From: {$fromName} <{$fromEmail}>\n\n{$msgBody}";

        return $mail->send();
    } catch (Exception $e) {
        error_log('Contact mail error: ' . $e->getMessage());
        return false;
    }
}