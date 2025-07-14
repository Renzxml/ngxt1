<?php
require_once '../includes/db_config.php';          // for $mailCfg path
require_once '../includes/mail_helpers.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status'=>'error','msg'=>'Invalid request']); exit;
}

$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$message = trim($_POST['message'] ?? '');

if (!$name || !$email || !$message || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status'=>'error','msg'=>'Please complete all fields correctly.']); exit;
}

if (sendContactMail($email, $name, $message)) {
    echo json_encode(['status'=>'ok']);
} else {
    echo json_encode(['status'=>'error','msg'=>'Could not send e‑mail. Try later.']);
}
