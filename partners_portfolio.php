<?php
include "includes/classes.php";

$db = new db_connect();
if (!$db->connect()) {
    die("DB connection failed: " . $db->error);
}
$conn = $db->conn;

// Get user info from URL parameter
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$stmt = $conn->prepare("SELECT id, fname, lname, email, role_status, profile_pic, facebook, instagram, twitter FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fallbacks if user not found
if (!$user) {
    die("User not found");
}

// Fetch user's gallery images
$stmt = $conn->prepare("SELECT image_path, category FROM gallery_tbl WHERE user_id = ? ORDER BY created_at ASC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$images = $result->fetch_all(MYSQLI_ASSOC);

// Split images into two rows for animation
$total = count($images);
$half = ceil($total / 2);
$row1 = array_slice($images, 0, $half);
$row2 = array_slice($images, $half);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="icon" type="image/png" href="./assets/resources/logo8.jpg">
  <title><?= htmlspecialchars($user['fname'] . ' ' . $user['lname']) ?>'s Gallery</title>
  <link rel="stylesheet" href="./assets/portfolio-style.css"/>
</head>
<body>

<div class="profile">
  <div class="profile-img" style="background-image: url('../uploads/<?= htmlspecialchars($user['profile_pic'] ?: 'default-profile.jpg') ?>')"></div>
  <div class="profile-info">
    <span><?= htmlspecialchars($user['fname'] . ' ' . $user['lname']) ?></span>
    <p><?= htmlspecialchars($user['role_status']) ?></p>
  </div>

  <div class="social-links">
    <div id="twitter" class="social-btn flex-center">
      <a href="<?= htmlspecialchars($user['facebook'] ?: '#') ?>" target="_blank"><img src="./assets/resources/fbicons.png" class="facebook"></a>
      <span><?= htmlspecialchars($user['facebook'] ?: 'Facebook') ?></span>
    </div>

    <div id="linkedin" class="social-btn flex-center">
      <a href="<?= htmlspecialchars($user['instagram'] ?: '#') ?>" target="_blank"><img src="./assets/resources/igicon.png" class="instagram"></a>
      <span><?= htmlspecialchars($user['instagram'] ?: 'Instagram') ?></span>
    </div>

    <div id="github" class="social-btn flex-center">
      <a href="<?= htmlspecialchars($user['twitter'] ?: '#') ?>" target="_blank"><img src="./assets/resources/twiticon.png" class="xtwitter"></a>
      <span><?= htmlspecialchars($user['twitter'] ?: 'Twitter') ?></span>
    </div>
  </div>
</div>

<?php if (!empty($images)): ?>
<section class="gallery">
  <div class="row">
    <?php foreach ($row1 as $img): ?>
      <img 
        src="../gallery_uploads/<?= htmlspecialchars($img['image_path']) ?>" 
        class="<?= htmlspecialchars($img['category']) ?> gallery-img" 
        alt="<?= htmlspecialchars(pathinfo($img['image_path'], PATHINFO_FILENAME)) ?>"
      >
    <?php endforeach; ?>
  </div>

  <div class="row reverse">
    <?php foreach ($row2 as $img): ?>
      <img 
        src="../gallery_uploads/<?= htmlspecialchars($img['image_path']) ?>" 
        class="<?= htmlspecialchars($img['category']) ?> gallery-img" 
        alt="<?= htmlspecialchars(pathinfo($img['image_path'], PATHINFO_FILENAME)) ?>"
      >
    <?php endforeach; ?>
  </div>
</section>
<?php else: ?>
<p style="text-align: center; margin: 2rem;">No images found in this gallery.</p>
<?php endif; ?>

<!-- Modal Viewer -->
<div id="lightbox" class="lightbox hidden">
  <span class="close" id="closeBtn">&times;</span>
  <img class="lightbox-content" id="lightboxImg" src="" alt="">
  <button class="nav-btn prev" id="prevBtn">&#10094;</button>
  <button class="nav-btn next" id="nextBtn">&#10095;</button>
</div>

<script src="./assets/portfolio-script.js"></script>
</body>
</html>