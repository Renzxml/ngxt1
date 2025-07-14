<?php
/*
|--------------------------------------------------------------------------
| Email template:  New Registration Notification
| Variables available:
|   $user  – associative array  (id, fname, lname, email, role_status)
|   $edit  – full URL to edit page
|--------------------------------------------------------------------------
*/
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>New Registration</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, Helvetica, sans-serif;
      background-image: linear-gradient(-45deg, #00317B 0%, #F1002C 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .card {
      max-width: 520px;
      width: 90%;
      margin: 30px auto;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
      overflow: hidden;
    }

    .header {
      display: flex;
      align-items: center;
      gap: 15px;
      background: #00317B;
      color: #fff;
      padding: 20px 24px;
    }

    .header img {
      width: 20px;
      height: 20px;
      object-fit: contain;
    }

    .header h2 {
      font-size: 20px;
      margin: 0;
    }

    .body {
      padding: 24px;
    }

    .body p {
      margin: 10px 0;
      font-size: 15px;
      color: #333;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin: 15px 0;
    }

    table td {
      padding: 8px 4px;
      border-bottom: 1px solid #eee;
    }

    .btn {
      display: inline-block;
      margin-top: 18px;
      padding: 10px 18px;
      border-radius: 4px;
      text-decoration: none;
      font-weight: bold;
      color: white;
      transition: all 0.3s ease;
    }

    .btn-accept {
      background: #4CAF50;
      margin-right: 8px;
    }

    .btn-reject {
      background: #F44336;
    }

    .btn:hover {
      opacity: 0.9;
      transform: translateY(-1px);
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .footer {
      font-size: 12px;
      color: #777;
      padding: 18px 24px 24px;
      text-align: center;
      background: #f5f5f5;
    }
  </style>
</head>
<body>
  <div class="card">
    <div class="header">
      <img src="../assets/resources/logo4.png" alt="Logo">
      <h2>New User Registration</h2>
    </div>
    <div class="body">
      <p>Hello reviewer,</p>
      <p>A new user has just registered and is awaiting approval:</p>

      <table>
        <tr><td><strong>Name:</strong></td><td><?= htmlspecialchars($user['fname'].' '.$user['lname']) ?></td></tr>
        <tr><td><strong>Email:</strong></td><td><?= htmlspecialchars($user['email']) ?></td></tr>
        <tr><td><strong>Role Status:</strong></td><td><?= htmlspecialchars($user['role_status']) ?></td></tr>
        <tr><td><strong>User ID:</strong></td><td><?= (int)$user['id'] ?></td></tr>
      </table>

      <p>
        <a href="<?= htmlspecialchars($acceptUrlForTpl) ?>" class="btn btn-accept">Accept</a>
        <a href="<?= htmlspecialchars($rejectUrlForTpl) ?>" class="btn btn-reject">Reject</a>
      </p>
    </div>

    <div class="footer">
      You're receiving this e-mail because you're listed as a reviewer.<br>
      &copy; <?= date('Y') ?> NGXT
    </div>
  </div>
</body>
</html>