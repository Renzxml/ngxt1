<?php
include 'header.php';

$conn = $db->conn;

$id = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$id) {
    echo "<div class='alert alert-danger'>Invalid company ID.</div>";
    exit;
}

$alert = null;

function sanitizeFileName($string) {
    $string = strtolower(trim($string));
    return preg_replace('/[^a-z0-9\-]/', '', str_replace(' ', '-', $string));
}

function uploadErrorMsg(int $err): string {
    return [
        UPLOAD_ERR_INI_SIZE   => 'The file exceeds upload_max_filesize in php.ini.',
        UPLOAD_ERR_FORM_SIZE  => 'The file exceeds MAX_FILE_SIZE in the form.',
        UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Temporary folder is missing.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the upload.',
    ][$err] ?? 'Unknown upload error.';
}

// Fetch current company data
$stmt = $conn->prepare("SELECT * FROM company_details_tbl WHERE cd_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$company = $stmt->get_result()->fetch_assoc();

if (!$company) {
    echo "<div class='alert alert-danger'>Company not found.</div>";
    exit;
}
$currentLogo = $company['cd_logo'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['cd_title']);
    $subtitle = trim($_POST['cd_subtitle']);
    $subtitle1 = trim($_POST['cd_subtitle1']);
    $description = trim($_POST['cd_description']);
    $logoFilename = $currentLogo;

    // Handle logo upload
    if (isset($_FILES['cd_logo']) && $_FILES['cd_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadDir = __DIR__ . '/../assets/resources/';
        $publicDir = '../assets/resources/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        if (!is_writable($uploadDir)) {
            $alert = ['type' => 'error', 'text' => 'Upload directory is not writable.'];
        } else {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file = $_FILES['cd_logo'];

            if (!in_array($file['type'], $allowedTypes, true)) {
                $alert = ['type' => 'error', 'text' => 'Only JPG, PNG, GIF, and WEBP files are allowed.'];
            } elseif ($file['size'] > 2 * 1024 * 1024) {
                $alert = ['type' => 'error', 'text' => 'File size must be less than 2MB.'];
            } elseif ($file['error'] !== UPLOAD_ERR_OK) {
                $alert = ['type' => 'error', 'text' => uploadErrorMsg($file['error'])];
            } else {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $titleSlug = sanitizeFileName($title);
                $newFileName = "logo_{$titleSlug}.{$ext}";
                $destination = $uploadDir . $newFileName;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    if (!empty($currentLogo) && file_exists($uploadDir . $currentLogo)) {
                        unlink($uploadDir . $currentLogo);
                    }
                    $logoFilename = $newFileName;
                } else {
                    $alert = ['type' => 'error', 'text' => 'Failed to move uploaded file. Check folder permissions.'];
                }
            }
        }
    }

    if (!$alert) {
        $stmt = $conn->prepare("UPDATE company_details_tbl SET cd_title = ?, cd_subtitle = ?, cd_subtitle1 = ?, cd_description = ?, cd_logo = ? WHERE cd_id = ?");
        $stmt->bind_param("sssssi", $title, $subtitle, $subtitle1, $description, $logoFilename, $id);

        if ($stmt->execute()) {
            echo <<<HTML
            <script>
            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: 'Company details updated successfully.',
                timer: 2000,
                timerProgressBar: true,
                willClose: () => window.location.href = 'index.php?updated=1'
            });
            </script>
            HTML;
            exit;
        } else {
            $alert = ['type' => 'error', 'text' => 'Update failed: ' . $stmt->error];
        }
    }
}
?>

<div class="edit-company-container">
  <link rel="stylesheet" href="../assets/admin-style.css"/>

  <h3 class="edit-company-title">Edit Company Details</h3>

  <?php if ($alert): ?>
    <script>
      Swal.fire({
        icon: '<?= $alert['type'] ?>',
        title: '<?= $alert['type'] === 'error' ? 'Error' : 'Notice' ?>',
        text: <?= json_encode($alert['text']) ?>,
      });
    </script>
  <?php endif; ?>

  <form class="edit-company-form" method="POST" enctype="multipart/form-data">
    <div class="edit-company-group">
      <label>Title</label>
      <input type="text" name="cd_title" class="edit-company-input" value="<?= htmlspecialchars($company['cd_title']) ?>" required>
    </div>
    <div class="edit-company-group">
      <label>Subtitle</label>
      <input type="text" name="cd_subtitle" class="edit-company-input" value="<?= htmlspecialchars($company['cd_subtitle']) ?>">
    </div>
    <div class="edit-company-group">
      <label>Subtitle 1</label>
      <input type="text" name="cd_subtitle1" class="edit-company-input" value="<?= htmlspecialchars($company['cd_subtitle1']) ?>">
    </div>
    <div class="edit-company-group">
      <label>Description</label>
      <textarea name="cd_description" class="edit-company-textarea"><?= htmlspecialchars($company['cd_description']) ?></textarea>
    </div>
    <div class="edit-company-group">
      <label>Logo Image</label>
      <?php if (!empty($company['cd_logo'])): ?>
        <div class="edit-company-image-preview">
          <img src="../assets/resources/<?= htmlspecialchars($company['cd_logo']) ?>" alt="Current Logo" style="max-height: 100px;">
        </div>
      <?php endif; ?>
      <input type="file" name="cd_logo" accept="image/*" class="edit-company-input">
      <small class="edit-company-note">Upload a new logo to replace the existing one. Max size: 2MB.</small>
    </div>
    <div class="edit-company-buttons">
      <button type="submit" class="edit-company-btn-primary">Update</button>
      <a href="index.php" class="edit-company-btn-secondary">Cancel</a>
    </div>
  </form>
</div>

<?php include 'footer.php'; ?>
