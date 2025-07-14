<?php
require_once '../includes/db_config.php';
require_once '../includes/mail_helpers.php';      // contains sendPasswordReset()

$db = new db_connect();
if (!$db->connect()) {
    die('DB connection failed: ' . $db->error);
}
$conn = $db->conn;

$message = $type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    /* 1 ▸ Does this e‑mail exist? */
    $stmt = $conn->prepare('SELECT id, fname, lname FROM users WHERE email = ?');
    if (!$stmt) die('Prepare failed: ' . $conn->error);

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        /* 2 ▸ Generate token + expiry (1 hour) */
        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        /* 3A ▸ Remove old token (ignore if none) */
        $del = $conn->prepare('DELETE FROM password_resets WHERE email = ?');
        if (!$del) die('Prepare failed: ' . $conn->error);
        $del->bind_param('s', $email);
        $del->execute();
        $del->close();

        /* 3B ▸ Insert new token */
        $ins = $conn->prepare(
            'INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)'
        );
        if (!$ins) die('Prepare failed: ' . $conn->error);
        $ins->bind_param('sss', $email, $token, $expires);
        $ins->execute();
        $ins->close();

        /* 4 ▸ Build reset link (works on localhost too) */
        $protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host      = $_SERVER['HTTP_HOST'];
        $path      = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $resetLink = $protocol . $host . $path . '/password-reset.php?token=' . urlencode($token);

        /* 5 ▸ Send the e‑mail */
        $fullName = $user['fname'] . ' ' . $user['lname'];
        if (sendPasswordReset($email, $fullName, $resetLink)) {
            $type    = 'success';
            $message = 'A password‑reset link has been sent to your e‑mail.';
        } else {
            $type    = 'error';
            $message = 'Could not send e‑mail; please try again later.';
        }
    } else {
        $type    = 'error';
        $message = 'E‑mail address not found.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Forgot Password</title>
<link rel="stylesheet" href="../assets/login-style.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<style>
  body.forgot-page {
    background: linear-gradient(-45deg, #ee7752, #c60026, #001e4f);
    background-size: 400% 400%;
    animation: gradient 15s ease infinite;
    display: flex;
    align-items: center;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    justify-content: center;
    flex-direction: column;
    height: 100vh;
    margin: 0;
}

@keyframes gradient {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}
  .forgot-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    padding: 40px;
    width: 100%;
    max-width: 450px;
    text-align: center;
    animation: fadeIn 0.5s ease-in-out;
  }
  
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }
  
  .forgot-container h2 {
    color: #333;
    margin-bottom: 25px;
    font-weight: 600;
  }
  
  .forgot-container form {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }
  
  .forgot-container input {
    padding: 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.3s;
  }
  
  .forgot-container input:focus {
    border-color: #c60026;
    outline: none;
  }
  
  .forgot-container button {
    background: linear-gradient(to right, #F1002C, #c60026);
    color: white;
    border: none;
    padding: 15px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
  }
  
  .forgot-container button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
  }
  
  .forgot-container a {
    display: inline-block;
    margin-top: 20px;
    color: #000000;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s;
  }
  
  .forgot-container a:hover {
    color: #F1002C;
    text-decoration: underline;
  }
</style>

<body class="forgot-page">
  <div class="forgot-container">
    <h2>Password Reset</h2>
    <form method="POST">
      <input type="email" name="email" placeholder="Your admin e‑mail" required>
      <button type="submit">Send Reset Link</button>
    </form>
    <a href="login.php">Back to login</a>
  </div>

<?php if ($message): ?>
<script>
Swal.fire({
  icon: '<?= $type ?>',
  title: '<?= $type === "success" ? "Success" : "Error" ?>',
  text: '<?= $message ?>'
});
</script>
<?php endif; ?>
</body>
</html>
