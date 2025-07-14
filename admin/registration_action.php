<?php
require_once '../includes/db_config.php';

$token = $_GET['token'] ?? '';
if (!$token) die('Invalid token.');

$db = new db_connect();
if (!$db->connect()) die('DB error: ' . $db->error);
$conn = $db->conn;

/* 1 ▸ find token */
$sql = 'SELECT user_id, `act`
        FROM registration_tokens
        WHERE token = ? AND expires_at >= NOW() LIMIT 1';

$stmt = $conn->prepare($sql) or die('SQL prepare error: ' . $conn->error);
$stmt->bind_param('s', $token);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) die('Token expired or invalid.');

/* 2 ▸ update role_status */
$update = $conn->prepare('UPDATE users SET role_status = ? WHERE id = ?')
         or die('Prepare failed: ' . $conn->error);

$newStatus = ($row['act'] === 'accept') ? 'partners' : 'rejected';
$update->bind_param('si', $newStatus, $row['user_id']);
$update->execute();

$msg = $newStatus === 'partners' ? '✅ User approved.' : '🚫 User rejected.';

/* 3 ▸ remove all tokens for that user */
$del = $conn->prepare('DELETE FROM registration_tokens WHERE user_id = ?')
       or die('Prepare failed: ' . $conn->error);
$del->bind_param('i', $row['user_id']);
$del->execute();

echo $msg;
