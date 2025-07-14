<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Contact Message</title>
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

    hr {
      border: none;
      border-top: 1px solid #eee;
      margin: 20px 0;
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
      <h2>New Contact Message</h2>
    </div>
    <div class="body">
      <p><strong>Name:</strong> <?= htmlspecialchars($name) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
      <hr>
      <p><?= $body /* already nl2br‑escaped */ ?></p>
    </div>
    <div class="footer">&copy; <?= date('Y') ?> NGXT | All Rights Reserved</div>
  </div>
</body>
</html>
