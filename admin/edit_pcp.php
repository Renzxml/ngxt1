<?php
include 'header.php';

$db = new db_class();
$company = $db->getCompanyDetails();

if (empty($cd_id) || empty($cd_title)) {
    echo "<script>
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Company details not found.'
    });
    </script>";
    exit;
}

if (!isset($_GET['id'])) {
    echo "<script>
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No image ID specified.'
    });
    </script>";
    exit;
}

$pcp_id = intval($_GET['id']);

// Fetch existing image data
$stmt = $conn->prepare("SELECT pcp_title, pcp_image FROM project_cover_photo_tbl WHERE pcp_id = ? AND cd_id = ?");
$stmt->bind_param("ii", $pcp_id, $cd_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Image not found or does not belong to this company.'
    });
    </script>";
    exit;
}

$imageData = $result->fetch_assoc();
$targetDir = '../assets/pcp_images/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newTitle = trim($_POST['pcp_title']);
    $newFileName = $imageData['pcp_image']; // default to existing image filename

    // Check if the new title already exists (excluding current image)
    $stmtCheck = $conn->prepare("SELECT COUNT(*) as count FROM project_cover_photo_tbl WHERE cd_id = ? AND pcp_title = ? AND pcp_id != ?");
    $stmtCheck->bind_param("isi", $cd_id, $newTitle, $pcp_id);
    $stmtCheck->execute();
    $checkResult = $stmtCheck->get_result()->fetch_assoc();

    if ($checkResult['count'] > 0) {
        echo "<script>
        Swal.fire({
            icon: 'warning',
            title: 'Duplicate Title',
            text: 'The title \"". addslashes($newTitle) ."\" already exists for this company. Please choose a different title.'
        });
        </script>";
    } else {
        // Check if a new image file was uploaded
        if (isset($_FILES['pcp_image']) && $_FILES['pcp_image']['error'] === UPLOAD_ERR_OK) {
            $image = $_FILES['pcp_image'];
            $imageName = basename($image['name']);
            $ext = pathinfo($imageName, PATHINFO_EXTENSION);

            // Generate safe filename with cd_title, timestamp, and random part for uniqueness
            $timeNow = date('Hi'); // HHMM
            $randomStr = bin2hex(random_bytes(4)); // 8 hex chars random string
            $fileBaseName = $cd_title . ' ' . $timeNow . ' ' . $randomStr;
            $safeFileName = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($fileBaseName));
            $newFileName = $safeFileName . '.' . $ext;
            $targetFile = $targetDir . $newFileName;

            // Move uploaded file
            if (move_uploaded_file($image['tmp_name'], $targetFile)) {
                // Delete old image file if exists and different from new
                $oldFile = $targetDir . $imageData['pcp_image'];
                if (file_exists($oldFile) && $oldFile !== $targetFile) {
                    unlink($oldFile);
                }
            } else {
                echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Upload Failed',
                    text: 'Failed to upload new image file.'
                });
                </script>";
                exit;
            }
        }

        // Update database record with new title and new image filename (if changed)
        $stmtUpdate = $conn->prepare("UPDATE project_cover_photo_tbl SET pcp_title = ?, pcp_image = ? WHERE pcp_id = ? AND cd_id = ?");
        $stmtUpdate->bind_param("ssii", $newTitle, $newFileName, $pcp_id, $cd_id);

        if ($stmtUpdate->execute()) {
            echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Image and title updated successfully.'
            });
            </script>";
            $imageData['pcp_title'] = $newTitle;
            $imageData['pcp_image'] = $newFileName;
        } else {
            $errorMsg = addslashes($conn->error);
            echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: 'Failed to update record: {$errorMsg}'
            });
            </script>";
        }
    }
}
?>

<div class="container mt-5">
    <h3>Edit Cover Photo for <?= htmlspecialchars($cd_title) ?></h3>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Current Image:</label><br>
            <img src="../assets/pcp_images/<?= htmlspecialchars($imageData['pcp_image']) ?>" alt="Cover Photo" style="max-width: 300px; height: auto; margin-bottom: 10px;">
        </div>
        <div class="mb-3">
            <label>Change Image (optional):</label>
            <input type="file" name="pcp_image" accept="image/*" class="form-control">
            <small class="text-muted">Upload a new image to replace the current one.</small>
        </div>
        <div class="mb-3">
            <label>Image Title</label>
            <input type="text" name="pcp_title" class="form-control" value="<?= htmlspecialchars($imageData['pcp_title']) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="pcp.php?cd_id=<?= $cd_id ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include 'footer.php'; ?>
