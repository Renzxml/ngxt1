<?php

include "header.php";

if (!isset($_SESSION['admin_id'])) {
    die("Not logged in.");
}

$db = new db_class();  // ✅ must be db_class, not db_connect
$conn = $db->conn;

$user_id = $_SESSION['admin_id'];

// Handle profile image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_profile_image'])) {
    
    if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
        $message = "Upload failed: Invalid file upload.";
    } else {
        $tmpPath = $_FILES['profile_image']['tmp_name'];

        // Upload to Cloudinary
        $uploadResult = $db->uploadToCloudinary($tmpPath, 'profile_photo');


        if ($uploadResult['success']) {
            $cloudinaryUrl = $uploadResult['url'];

            // Insert Cloudinary image URL to DB
            $stmt = $conn->prepare("INSERT INTO profile_images_tbl (user_id, image_path, category) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $cloudinaryUrl, $category);
            $stmt->execute();

            $message = "Image uploaded successfully to Cloudinary.";
        } else {
            $message = "Cloudinary upload failed: " . $uploadResult['message'];
        }
    }
}


// Handle image deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_profile_image'])) {
    try {
        if (!isset($_POST['image_id'])) {
            throw new Exception('Image ID not provided');
        }

        $image_id = intval($_POST['image_id']);

        // First get the image path
        $stmt = $conn->prepare("SELECT image_path FROM profile_images_tbl WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $image_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Image not found or not owned by you');
        }
        
        $image = $result->fetch_assoc();
        $image_path = '../profile_uploads/' . $image['image_path'];
        
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM profile_images_tbl WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $image_id, $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Database error');
        }
        
        // Delete the file
        if (file_exists($image_path)) {
            if (!unlink($image_path)) {
                throw new Exception('Could not delete image file');
            }
        }
        
        echo json_encode(['success' => true]);
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// Handle profile update
if (isset($_POST['update_profile'])) {
    $fname = $_POST['name'];
    $lname = $_POST['subname'];

    try {
        $imgUrl = '';
        
        if (!empty($_FILES['profile_img']['tmp_name'])) {
            // Upload to Cloudinary
            $tmpPath = $_FILES['profile_img']['tmp_name'];
            $uploadResult = $db->uploadToCloudinary($tmpPath, 'profile_photo');

            if ($uploadResult['success']) {
                $imgUrl = $uploadResult['url']; // Cloudinary URL
            } else {
                throw new Exception("Cloudinary upload failed: " . $uploadResult['message']);
            }
        }

        // Update query
        if (!empty($imgUrl)) {
            $stmt = $conn->prepare("UPDATE users SET fname = ?, lname = ?, profile_pic = ? WHERE id = ?");
            $stmt->bind_param("sssi", $fname, $lname, $imgUrl, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET fname = ?, lname = ? WHERE id = ?");
            $stmt->bind_param("ssi", $fname, $lname, $user_id);
        }

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } catch (Exception $e) {
        die("Error updating profile: " . $e->getMessage());
    }
}


// Handle Facebook update
if (isset($_POST['update_facebook'])) {
    $fb = $_POST['facebook'];
    $stmt = $conn->prepare("UPDATE users SET facebook=? WHERE id=?");
    $stmt->bind_param("si", $fb, $user_id);
    $stmt->execute();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle Instagram update
if (isset($_POST['update_instagram'])) {
    $ig = $_POST['instagram'];
    $stmt = $conn->prepare("UPDATE users SET instagram=? WHERE id=?");
    $stmt->bind_param("si", $ig, $user_id);
    $stmt->execute();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle Twitter update
if (isset($_POST['update_twitter'])) {
    $tw = $_POST['twitter'];
    $stmt = $conn->prepare("UPDATE users SET twitter=? WHERE id=?");
    $stmt->bind_param("si", $tw, $user_id);
    $stmt->execute();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch uploaded profile images
$res = $conn->query("SELECT * FROM profile_images_tbl WHERE user_id = $user_id ORDER BY created_at DESC");
$images = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

// Fetch profile and social info
$profile_res = $conn->query("SELECT * FROM users WHERE id = $user_id");
$profile = $profile_res ? $profile_res->fetch_assoc() : [];

$social_res = $conn->query("SELECT facebook, instagram, twitter FROM users WHERE id = $user_id");
$social = $social_res ? $social_res->fetch_assoc() : [];



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_gallery'])) {
    $category = "profile_image"; // to match your switch case

    if (!isset($_FILES['gallery_image']) || $_FILES['gallery_image']['error'] !== UPLOAD_ERR_OK) {
        $message = "Upload failed: Invalid file upload.";
    } else {
        $tmpPath = $_FILES['gallery_image']['tmp_name'];

        // Upload to Cloudinary via your db_class method
        $uploadResult = $db->uploadToCloudinary($tmpPath, $category);

        if ($uploadResult['success']) {
            $cloudinaryUrl = $uploadResult['url'];

            $stmt = $conn->prepare("INSERT INTO profile_images_tbl (user_id, p_images) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $cloudinaryUrl);
            $stmt->execute();

            $message = "Image uploaded successfully to Cloudinary.";
        } else {
            $message = "Cloudinary upload failed: " . $uploadResult['message'];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image'])) {
    // Clean output buffer to prevent invalid JSON
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');

    try {
        $image_id = intval($_POST['image_id']);

        if (!$image_id || !$user_id) {
            throw new Exception("Missing image ID or user ID.");
        }

        $stmt = $conn->prepare("SELECT p_images FROM profile_images_tbl WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $image_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("Image not found or access denied.");
        }

        // No cloudinary deletion here — purely DB
        $stmt = $conn->prepare("DELETE FROM profile_images_tbl WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $image_id, $user_id);
        $stmt->execute();

        echo json_encode(['success' => true]);
        exit;

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}




// ✅ Fetch uploaded images from profile_images_tbl
$res = $conn->query("SELECT * FROM profile_images_tbl WHERE user_id = $user_id ORDER BY created_at DESC");
$images = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];


?>

<body>

<div class="container">
  <div class="profile-section">
    <div class="profile-photo">
      <div class="profile-photo-container" id="editProfileBtn">
        <!-- <img src="../uploads/<?= !empty($profile['profile_pic']) ? htmlspecialchars($profile['profile_pic']) : 'default-profile.jpg' ?>" alt="Profile Photo"> -->


        <?php if (!empty($profile['profile_pic'])): ?>
      <div style="text-align: center; margin-bottom: 10px;">
        <img src="<?= htmlspecialchars($profile['profile_pic']) ?>" alt="Profile Image">
      </div>
    <?php endif; ?>
      </div>
    </div>
    
    <div class="profile-info">
      <div class="profile-name">
        <h1><?= htmlspecialchars($profile['fname'] ?? 'First Name') ?></h1>
        <h1><?= htmlspecialchars($profile['lname'] ?? 'Last Name') ?></h1>
      </div>
      
      <div class="social-boxes">
        <div class="social-box" onclick="document.getElementById('facebookModal').classList.add('show')">
          <i class="fab fa-facebook"></i>
          <div class="social-input">
            <?= !empty($profile['facebook']) ? htmlspecialchars($profile['facebook']) : 'Add your Facebook here' ?>
          </div>
        </div>
        <div class="social-box" onclick="document.getElementById('instagramModal').classList.add('show')">
          <i class="fab fa-instagram"></i>
          <div class="social-input">
            <?= !empty($profile['instagram']) ? htmlspecialchars($profile['instagram']) : 'Add your Instagram here' ?>
          </div>
        </div>
        <div class="social-box" onclick="document.getElementById('twitterModal').classList.add('show')">
          <i class="fab fa-twitter"></i>
          <div class="social-input">
            <?= !empty($profile['twitter']) ? htmlspecialchars($profile['twitter']) : 'Add your Twitter here' ?>
          </div>
        </div>
      </div>
    </div>
    
    <div class="upload-form-container">
      <?php if (!empty($message)): ?>
        <p class="message"><?= $message ?></p>
      <?php endif; ?>
      <form class="upload-form" method="POST" enctype="multipart/form-data">
        <label for="category">Category</label>
        <select name="category" id="category" required>
          <option value="">Select Type</option>
          <option value="portrait">Portrait</option>
          <option value="landscape">Landscape</option>
        </select>
        <label for="gallery_image">Select Image</label>
        <input type="file" name="gallery_image" id="gallery_image" accept="image/*" required>
        <button type="submit" name="upload_gallery">Upload</button>
      </form>
    </div>
  </div>


<div class="gallery-container">
    <?php foreach ($images as $img): ?>
        <?php $cloudinaryUrl = htmlspecialchars($img['p_images']); ?>
        <div class="gallery-item" onclick="openFullScreen('<?= $cloudinaryUrl ?>')">
            <div class="delete-btn" onclick="event.stopPropagation(); deleteImage(<?= $img['id'] ?>, this)">
                <i class="fas fa-times"></i>
            </div>
            <img src="<?= $cloudinaryUrl ?>" alt="">
        </div>
    <?php endforeach; ?>
</div>

<!-- Fullscreen Modal -->
<div id="fullscreenModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.85); justify-content:center; align-items:center; z-index:9999;">
    <img id="fullscreenImg" src="" style="max-width:90%; max-height:90%; border:6px solid #fff; border-radius:8px; box-shadow:0 0 20px rgba(0,0,0,0.6);">
    <span onclick="closeFullScreen()" style="position:absolute; top:20px; right:30px; font-size:2em; color:#fff; cursor:pointer;">&times;</span>
</div>


<script>
function openFullScreen(imgSrc) {
    const modal = document.getElementById('fullscreenModal');
    const img = document.getElementById('fullscreenImg');
    img.src = imgSrc;
    modal.style.display = 'flex';
}

function closeFullScreen() {
    const modal = document.getElementById('fullscreenModal');
    modal.style.display = 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeFullScreen();
});
</script>


<!-- Profile Modal -->
<div class="modal" id="profileModal">
  <div class="modal-content">
    <span class="close" id="closeProfile">&times;</span>
    <h3>Edit Profile</h3>

    <?php if (!empty($profile['profile_pic'])): ?>
      <div style="text-align: center; margin-bottom: 10px;">
        <img src="<?= htmlspecialchars($profile['profile_pic']) ?>" alt="Profile Image" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover;">
      </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <input type="file" name="profile_img" accept="image/*">
      <input type="text" name="name" placeholder="First Name" value="<?= htmlspecialchars($profile['fname'] ?? '') ?>" required>
      <input type="text" name="subname" placeholder="Last Name" value="<?= htmlspecialchars($profile['lname'] ?? '') ?>" required>
      <button type="submit" name="update_profile">Save</button>
    </form>
  </div>
</div>


<!-- Facebook Modal -->
  <div class="modal" id="facebookModal">
    <div class="modal-content">
      <span class="close" onclick="document.getElementById('facebookModal').classList.remove('show')">&times;</span>
      <h3>Edit Facebook Link</h3>
      <form method="POST">
        <input type="text" name="facebook" placeholder="Facebook URL" value="<?= !empty($profile['facebook']) ? htmlspecialchars($profile['facebook']) : '' ?>">
        <button type="submit" name="update_facebook">Save</button>
      </form>
    </div>
  </div>

  <!-- Instagram Modal -->
  <div class="modal" id="instagramModal">
    <div class="modal-content">
      <span class="close" onclick="document.getElementById('instagramModal').classList.remove('show')">&times;</span>
      <h3>Edit Instagram Link</h3>
      <form method="POST">
        <input type="text" name="instagram" placeholder="Instagram URL" value="<?= !empty($profile['instagram']) ? htmlspecialchars($profile['instagram']) : '' ?>">
        <button type="submit" name="update_instagram">Save</button>
      </form>
    </div>
  </div>

  <!-- Twitter Modal -->
  <div class="modal" id="twitterModal">
    <div class="modal-content">
      <span class="close" onclick="document.getElementById('twitterModal').classList.remove('show')">&times;</span>
      <h3>Edit Twitter Link</h3>
      <form method="POST">
        <input type="text" name="twitter" placeholder="Twitter URL" value="<?= !empty($profile['twitter']) ? htmlspecialchars($profile['twitter']) : '' ?>">
        <button type="submit" name="update_twitter">Save</button>
      </form>
    </div>
  </div>


<script>
  // Modal functionality
  document.getElementById('editProfileBtn').onclick = function() {
    document.getElementById('profileModal').classList.add('show');
  };
  
  document.getElementById('closeProfile').onclick = function() {
    document.getElementById('profileModal').classList.remove('show');
  };
  
  // Close modal when clicking outside
  window.addEventListener('click', function(e) {
    if (e.target === document.getElementById('profileModal')) {
      document.getElementById('profileModal').classList.remove('show');
    }
    if (e.target === document.getElementById('facebookModal')) {
      document.getElementById('facebookModal').classList.remove('show');
    }
    if (e.target === document.getElementById('instagramModal')) {
      document.getElementById('instagramModal').classList.remove('show');
    }
    if (e.target === document.getElementById('twitterModal')) {
      document.getElementById('twitterModal').classList.remove('show');
    }
  });

// Delete image function 
function deleteImage(imageId, element) {
    if (!imageId) {
        console.error('No image ID provided');
        return;
    }

    if (confirm('Are you sure you want to delete this image?')) {
        // Create form data
        const formData = new FormData();
        formData.append('delete_image', '1');
        formData.append('image_id', imageId);
        
        fetch(window.location.href, {
          method: 'POST',
          body: formData
        })
        .then(response => response.text())
        .then(text => {
            console.log("RAW SERVER RESPONSE:", text); // 👈 Add this line

            try {
                const data = JSON.parse(text);
                if (data.success) {
                    element.closest('.gallery-item').remove();
                } else {
                    alert('Error deleting image: ' + (data.message || 'Unknown error'));
                }
            } catch (err) {
                console.error('JSON parse error:', err);
                alert('Invalid response from server.');
            }
        })

        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the image');
        });

    }
}
</script>

<style>
 body {
    font-family: 'Segoe UI', sans-serif;
    background: #fafafa;
    color: #333;
    margin: 0;
    padding: 0;
  }

  .container {
    max-width: 935px;
    margin: 30px auto;
    padding: 0 20px;
    margin-top: 100px;
  }

  .profile-section {
    display: flex;
    flex-direction: row;
    margin-bottom: 44px;
    align-items: flex-start;
  }

  .profile-photo {
    flex: 0 0 30%;
    display: flex;
    justify-content: center;
  }

  .profile-photo-container {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    overflow: hidden;
    border: 1px solid #ddd;
    cursor: pointer;
  }

  .profile-photo-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .profile-info {
    flex: 1;
    padding-left: 30px;
  }

  .profile-name {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
  }

  .profile-name h1 {
    font-size: 28px;
    font-weight: 300;
    margin: 0;
    padding-right: 20px;
  }

  .social-boxes {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-top: 20px;
    margin-bottom: 20px;
  }

  .social-box {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    border: 1px solid #dbdbdb;
    border-radius: 8px;
    background: #fff;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .social-box:hover {
    background: #f8f9fa;
  }

  .social-box i {
    font-size: 20px;
    width: 30px;
    text-align: center;
  }

  .social-box .social-input {
    flex: 1;
    padding: 8px;
    color: #8e8e8e;
  }

  .fa-facebook { color: #3b5998; }
  .fa-instagram { color: #e1306c; }
  .fa-twitter { color: #1da1f2; }

  /* Upload Form */
  .upload-form-container {
    flex: 0 0 30%;
    margin-left: 30px;
  }

  .upload-form {
    background: #ffffff;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #dbdbdb;
    height: 275px;
  }

  .upload-form label {
    font-weight: 600;
    margin-bottom: 5px;
    font-size: 14px;
    display: block;
    padding-top: 10px;
  }

  .upload-form select {
    width: 100%;
    padding: 8px;
    border: 1px solid #dbdbdb;
    border-radius: 4px;
    margin-bottom: 10px;
    background: #fafafa;
  }

  .upload-form input[type="file"] {
    width: 100%;
    margin-bottom: 10px;
  }

  .upload-form button {
    width: 100%;
    background: #0095f6;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 8px;
    font-weight: 600;
    cursor: pointer;
  }

  .upload-form button:hover {
    background: #0077cc;
  }

  /* Gallery Grid */
  .gallery-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
  }

  .gallery-item {
    position: relative;
    width: 100%;
    aspect-ratio: 1;
    overflow: hidden;
  }

  .gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
  }

  .gallery-item:hover img {
    transform: scale(1.05);
  }

  .gallery-category {
    position: absolute;
    bottom: 10px;
    left: 10px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
  }

  /* Delete Icon */
.delete-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(255, 255, 255, 0.9);
    color: #ff0000;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    z-index: 2;
    border: 1px solid rgba(255, 0, 0, 0.2);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
    opacity: 0;
}

.gallery-item:hover .delete-btn {
    opacity: 1;
}

.delete-btn:hover {
    background: rgba(255, 0, 0, 0.9);
    color: white;
    transform: scale(1.1);
}

.delete-btn i {
    font-size: 14px;
}

  /* Modal styles */
  .modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 1000;
  }

  .modal.show {
    display: flex;
  }

  .modal-content {
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    width: 400px;
    max-width: 90%;
    position: relative;
  }

  .modal-content h3 {
    margin-top: 0;
    margin-bottom: 20px;
    font-size: 20px;
    text-align: center;
  }

  .modal-content input {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #dbdbdb;
    border-radius: 4px;
  }

  .modal-content button {
    width: 100%;
    padding: 10px;
    background: #0095f6;
    color: white;
    border: none;
    border-radius: 4px;
    font-weight: 600;
    cursor: pointer;
  }

  .close {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 24px;
    cursor: pointer;
  }

  .message {
    background: #d4edda;
    color: #155724;
    padding: 10px 15px;
    border-radius: 4px;
    margin-bottom: 20px;
    font-size: 14px;
  }

  @media (max-width: 768px) {
    .profile-section {
      flex-direction: column;
    }
    
    .upload-form-container {
      margin-left: 0;
      margin-top: 30px;
      width: 100%;
    }
  }
</style>

</body>
<?php include 'footer.php'; ?>
</html>