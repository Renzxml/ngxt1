<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Reset Password</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, Helvetica, sans-serif;
      background-image: linear-gradient(-45deg, #00317B 0%, #F1002C 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
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
      line-height: 1.5;
    }
    .btn {
      display: inline-block;
      margin-top: 18px;
      background: #00317B;
      color: #fff;
      padding: 12px 24px;
      border-radius: 4px;
      text-decoration: none;
      font-weight: bold;
      transition: all 0.3s ease;
    }
    .btn:hover {
      background: #F1002C;
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .footer {
      font-size: 12px;
      color: #777;
      padding: 18px 24px 24px;
      text-align: center;
      background: #f5f5f5;
      border-top: 1px solid #eee;
    }
    .footer a {
      color: #00317B;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <div class="card">
    <div class="header">
      <img src="../assets/resources/logo4.png" alt="NGXT Logo">
      <h2>Password Reset</h2>
    </div>
    <div class="body">
      <p>Hello <?= htmlspecialchars($name ?? '') ?>,</p>
      <p>We received a request to reset your password. Click the button below to create a new password. This link will expire in 1 hour:</p>
      
      <a href="<?= htmlspecialchars($link ?? '#') ?>" class="btn">Reset Password</a>
      
      <p>If you didn't request this password reset, please ignore this email or contact support if you have questions.</p>
    </div>
    <div class="footer">
    &copy; <?= date('Y') ?> NGXT | All Rights Reserved
    </div>
  </div>
</body>
</html>