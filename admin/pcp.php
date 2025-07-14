<?php
include 'header.php';

// Add SweetAlert2 script if not already in header.php
echo <<<HTML
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
HTML;

$db = new db_class();
$company = $db->getCompanyDetails();

if (empty($cd_id) || empty($cd_title)) {
    echo <<<JS
    <script>
    Swal.fire({
        icon: 'error',
        title: 'Company details not found.',
        confirmButtonText: 'OK'
    }).then(() => { window.location.href = 'index.php'; });
    </script>
    JS;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pcp_image'])) {
    $pcpTitle = trim($_POST['pcp_title']); // Title from user input
    $image = $_FILES['pcp_image'];

    // Check for duplicate title for this cd_id
    $stmtCheckTitle = $conn->prepare("SELECT COUNT(*) as count FROM project_cover_photo_tbl WHERE cd_id = ? AND pcp_title = ?");
    $stmtCheckTitle->bind_param("is", $cd_id, $pcpTitle);
    $stmtCheckTitle->execute();
    $resultCheckTitle = $stmtCheckTitle->get_result()->fetch_assoc();
    if ($resultCheckTitle['count'] > 0) {
        echo <<<JS
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Duplicate Title',
            text: 'The title "{$pcpTitle}" already exists for this company. Please choose a different title.',
            confirmButtonText: 'OK'
        });
        </script>
        JS;
        exit;
    }

    $imageName = basename($image['name']);
    $targetDir = '../assets/pcp_images/';
    $ext = pathinfo($imageName, PATHINFO_EXTENSION);

    // Count existing images for this cd_id
    $stmtCount = $conn->prepare("SELECT COUNT(*) as count FROM project_cover_photo_tbl WHERE cd_id = ?");
    $stmtCount->bind_param("i", $cd_id);
    $stmtCount->execute();
    $resultCount = $stmtCount->get_result()->fetch_assoc();
    $count = $resultCount['count'] + 1;

    // Get current time HHMM
    $timeNow = date('Hi'); // 24-hour format, e.g. 1345 for 1:45 PM

    // Generate safe image filename with cd_title, count, and time
    $fileBaseName = $cd_title . ' ' . $count . ' ' . $timeNow;
    $safeFileName = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($fileBaseName));
    $newFileName = $safeFileName . '.' . $ext;
    $targetFile = $targetDir . $newFileName;

    // Optional: check if file already exists (unlikely with time included)
    if (file_exists($targetFile)) {
        echo <<<JS
        <script>
        Swal.fire({
            icon: 'error',
            title: 'File Exists',
            text: 'A file with the generated name already exists. Please try again.',
            confirmButtonText: 'OK'
        });
        </script>
        JS;
        exit;
    }

    if (move_uploaded_file($image['tmp_name'], $targetFile)) {
        // Upload to Cloudinary (cover_photo category)
        $cloudinary = $db->uploadToCloudinary($targetFile, 'cover_photo');

        if ($cloudinary['success']) {
            $cloudinary_url = $cloudinary['url'];
            $cloudinary_public_id = $cloudinary['cloudinary_public_id'];

            unlink($targetFile); // Delete local copy after successful upload

            // Insert into database
            $insert = $conn->prepare("INSERT INTO project_cover_photo_tbl (pcp_title, pcp_image, cloudinary_public_id, cd_id) VALUES (?, ?, ?, ?)");
            $insert->bind_param("sssi", $pcpTitle, $cloudinary_url, $cloudinary_public_id, $cd_id);

            if ($insert->execute()) {
                echo <<<JS
                <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Image uploaded and saved successfully.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = window.location.href;
                });
                </script>
                JS;
            } else {
                $errorMsg = $conn->error;
                echo <<<JS
                <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Database Error',
                    text: 'Database insert failed: {$errorMsg}',
                    confirmButtonText: 'OK'
                });
                </script>
                JS;
            }
        } else {
            $errorMsg = $cloudinary['message'];
            echo <<<JS
            <script>
            Swal.fire({
                icon: 'error',
                title: 'Cloudinary Upload Failed',
                text: '{$errorMsg}',
                confirmButtonText: 'OK'
            });
            </script>
            JS;
        }
    } else {
        echo <<<JS
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Upload Failed',
            text: 'Failed to upload image to server.',
            confirmButtonText: 'OK'
        });
        </script>
        JS;
    }


}

?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Upload Cover Photo for <?= htmlspecialchars($cd_title) ?></h2>
    <div class="row justify-content-center">
        <!-- Upload Form -->
        <div class="col-lg-4 col-md-5 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Add Title to this image</label>
                            <input type="text" name="pcp_title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Select Image</label>
                            <input type="file" name="pcp_image" class="form-control" accept="image/*" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Upload</button>
                        <a href="index.php" class="btn btn-secondary ms-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
        <!-- Gallery -->
<div class="col-lg-8 col-md-7">
    <div class="row g-4">
        <?php
        // Fetch all images for this cd_id
        $stmtImages = $conn->prepare("SELECT * FROM project_cover_photo_tbl WHERE cd_id = ? ORDER BY cd_id DESC");
        $stmtImages->bind_param("i", $cd_id);
        $stmtImages->execute();
        $resultImages = $stmtImages->get_result();
        ?>
        <?php if ($resultImages->num_rows > 0): ?>
            <?php while ($imageRow = $resultImages->fetch_assoc()): ?>
                <div class="col-lg-4 col-md-5 mb-4">
                    <div class="card h-100 shadow-sm d-flex align-items-center justify-content-center">
                        <img 
                            src="<?= htmlspecialchars($imageRow['pcp_image']) ?>" 
                            class="card-img-top" 
                            alt="<?= htmlspecialchars($imageRow['pcp_title']) ?>"
                            style="height: 200px; object-fit: cover; cursor: pointer;"
                            onclick="openFullscreenModal('<?= htmlspecialchars($imageRow['pcp_image']) ?>')"
                            />

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-center"><?= htmlspecialchars($imageRow['pcp_title']) ?></h5>
                            <div class="mt-auto text-center">
                                <a href="edit_pcp.php?id=<?= $imageRow['pcp_id'] ?>" class="btn btn-sm btn-warning me-2">Edit</a>
                                <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $imageRow['pcp_id'] ?>)">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center">No images uploaded yet.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Fullscreen Modal -->
<div id="fullscreenModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.9); justify-content:center; align-items:center; z-index:1050;">
    <img id="fullscreenImg" src="" style="max-width:95%; max-height:95%; border:4px solid #fff; border-radius:10px; box-shadow:0 0 20px rgba(0,0,0,0.6);">
    <span onclick="closeFullscreenModal()" style="position:absolute; top:20px; right:30px; font-size:2em; color:#fff; cursor:pointer;">&times;</span>
</div>

<!-- JS for Fullscreen Preview -->
<script>
function openFullscreenModal(imgSrc) {
    const modal = document.getElementById('fullscreenModal');
    const modalImg = document.getElementById('fullscreenImg');
    modalImg.src = imgSrc; // Already a full URL from Cloudinary
    modal.style.display = 'flex';
}


function closeFullscreenModal() {
    document.getElementById('fullscreenModal').style.display = 'none';
}

// Allow ESC to close
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeFullscreenModal();
    }
});
</script>

    </div>
</div>


        


</div>
<?php


if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];

    // Fetch the image URL and Cloudinary public ID for the selected record
    $stmtImg = $conn->prepare("SELECT pcp_image, cloudinary_public_id FROM project_cover_photo_tbl WHERE pcp_id = ?");
    $stmtImg->bind_param("i", $deleteId);
    $stmtImg->execute();
    $resultImg = $stmtImg->get_result();

    if ($resultImg->num_rows > 0) {
        $rowImg = $resultImg->fetch_assoc();
        $cloudinaryPublicId = $rowImg['cloudinary_public_id'];

        // 🧼 Delete from Cloudinary if public ID exists
        if (!empty($cloudinaryPublicId)) {
            $cloudDelete = $db->deleteFromCloudinary($cloudinaryPublicId, 'image'); // or 'auto'

            if (!$cloudDelete['success']) {
                echo <<<JS
                <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Cloudinary Delete Failed',
                    text: '{$cloudDelete['message']}',
                    confirmButtonText: 'OK'
                });
                </script>
                JS;
                exit;
            }
        }

        // ✅ Delete the database record
        $stmtDel = $conn->prepare("DELETE FROM project_cover_photo_tbl WHERE pcp_id = ?");
        $stmtDel->bind_param("i", $deleteId);

        if ($stmtDel->execute()) {
            echo <<<JS
            <script>
            Swal.fire({
                icon: 'success',
                title: 'Deleted',
                text: 'Image deleted successfully.',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = window.location.pathname;
            });
            </script>
            JS;
            exit;
        } else {
            $error = $conn->error;
            echo <<<JS
            <script>
            Swal.fire({
                icon: 'error',
                title: 'Database Error',
                text: 'Failed to delete from database: {$error}',
                confirmButtonText: 'OK'
            });
            </script>
            JS;
            exit;
        }

    } else {
        echo <<<JS
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Not Found',
            text: 'Image not found or already deleted.',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = window.location.pathname;
        });
        </script>
        JS;
        exit;
    }
}


?>

<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "Do you really want to delete this image?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "?delete=" + id;
        }
    });
}
</script>

<?php include 'footer.php'; ?>
