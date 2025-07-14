<?php
require_once '../includes/db_config.php';


$db = new db_connect();
if (!$db->connect()) {
    die('❌ DB connection failed: ' . $db->conn->connect_error);
}
$conn = $db->conn;

$token = $_GET['token'] ?? '';
$step  = 'verify';        // verify → form → done
$message = $type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* POST: actually reset */
    $token = $_POST['token'];
    $pass1 = $_POST['password'];
    $pass2 = $_POST['cpassword'];

    if ($pass1 !== $pass2) {
        $type='error'; $message='Passwords do not match.'; $step='form';
    } else {
        $stmt = $conn->prepare(
            'SELECT email FROM password_resets WHERE token = ? AND expires_at >= NOW() LIMIT 1'
        );
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row) {
            $hash = password_hash($pass1, PASSWORD_DEFAULT);

            /* update user password */
            $up = $conn->prepare('UPDATE users SET password=? WHERE email=?');
            $up->bind_param('ss', $hash, $row['email']);
            $up->execute();

            /* delete token */
            $del = $conn->prepare('DELETE FROM password_resets WHERE token=?');
            $del->bind_param('s', $token);
            $del->execute();

            $type='success'; $message='Password updated. You can now log in.'; $step='done';
        } else {
            $type='error'; $message='Invalid or expired link.'; $step='done';
        }
    }
}
/* GET verify */
elseif ($token) {
    $stmt = $conn->prepare(
        'SELECT 1 FROM password_resets WHERE token = ? AND expires_at >= NOW() LIMIT 1'
    );
    $stmt->bind_param('s', $token);
    $stmt->execute();
    if ($stmt->get_result()->num_rows) {
        $step='form';
    } else {
        $type='error'; $message='Invalid or expired link.'; $step='done';
    }
} else {
    header('Location: login_register.php'); exit;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Reset Password</title>
<link rel="stylesheet" href="../assets/login-style.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="reset-page">
<div class="reset-container">
<?php if ($step === 'form'): ?>
  <h2>Create New Password</h2>
  <form method="POST">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
    <input type="password" name="password"  placeholder="New password"     required>
    <input type="password" name="cpassword" placeholder="Confirm password" required>
    <button type="submit">Reset Password</button>
  </form>
<?php else: ?>
  <p><?= htmlspecialchars($message) ?></p>
  <a href="login_register.php">Go to login</a>
<?php endif; ?>,
</div>

<?php if ($message && $step !== 'form'): ?>
<script>
Swal.fire({ icon:'<?= $type ?>', title:'<?= $type==="success"?"Success":"Error" ?>', text:'<?= $message ?>' });
</script>
<?php endif; ?>
</body>
</html>
