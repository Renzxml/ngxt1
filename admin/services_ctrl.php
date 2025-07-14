<?php
include 'header.php';

// Handle ADD
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['svs_title'];
    $desc = $_POST['svs_description'];

    // Handle logo upload
    $logo = '';
    if (isset($_FILES['svs_logo']) && $_FILES['svs_logo']['name'] != '') {
        $logo = basename($_FILES['svs_logo']['name']);
        move_uploaded_file($_FILES['svs_logo']['tmp_name'], "uploads/" . $logo);
    }

    // Only save title, description, and logo
    mysqli_query($conn, "INSERT INTO services_tbl (svs_title, svs_description, svs_logo) VALUES ('$title', '$desc', '$logo')");
    $_SESSION['swal'] = ['title' => 'Added!', 'text' => 'Service added successfully.', 'icon' => 'success'];

    header("Location: services_ctrl.php");
    exit;
}

// Handle DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT svs_logo FROM services_tbl WHERE svs_id=$id"));

    // Delete logo
    if ($row && file_exists("uploads/" . $row['svs_logo'])) {
        unlink("uploads/" . $row['svs_logo']);
    }

    mysqli_query($conn, "DELETE FROM services_tbl WHERE svs_id=$id");
    $_SESSION['swal'] = ['title' => 'Deleted!', 'text' => 'Service deleted successfully.', 'icon' => 'success'];
    header("Location: services_ctrl.php");
    exit;
}
?>
<!-- <link rel="stylesheet" href="../assets/admin-style.css" /> -->
<style>
body {
    margin-top: 120px;
    margin-bottom: 30px;
}
.gallery-section {
    margin-top: 20px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
}
.gallery-images {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 15px;
}
.gallery-image {
    position: relative;
    width: 100px;
    height: 100px;
}
.gallery-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 5px;
}
.gallery-image .delete-btn {
    position: absolute;
    top: 5px;
    right: 5px;
    background: red;
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
.file-input-container {
    margin-top: 10px;
}
.file-input-container input[type="file"] {
    display: block;
    margin-bottom: 5px;
}
.gallery-count {
    background: #f0f0f0;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 12px;
}
.card-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 30px;
    margin: 40px auto 0;
    max-width: 1100px;
}
.service-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    width: 320px;
    height: 320px;
    margin-bottom: 30px;
    display: flex;
    flex-direction: column;
    align-items: stretch;
    padding: 24px 18px 18px;
    position: relative;
    box-sizing: border-box;
}
.add-card {
    border: 2px dashed #bbb;
    background: #fafbfc;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 320px;
    min-width: 320px;
    height: 320px;
}
.add-card .service-form {
    width: 100%;
}
.card-logo {
    width: 70px;
    height: 70px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 12px;
    border: 1px solid #eee;
    background: #f8f8f8;
}
.card-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 8px;
    color: #222;
}
.card-desc {
    font-size: 0.98rem;
    color: #444;
    margin-bottom: 12px;
    min-height: 48px;
    overflow: hidden;
    text-overflow: ellipsis;
}
.card-gallery {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 10px;
}
.card-gallery img {
    width: 38px;
    height: 38px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid #eee;
}
.card-actions {
    margin-top: auto;
    display: flex;
    gap: 12px;
}
.card-actions a {
    text-decoration: none;
    color: #1976d2;
    font-weight: 500;
    font-size: 0.97rem;
}
.card-actions a.delete {
    color: #d32f2f;
}
@media (max-width: 1100px) {
    .card-container { max-width: 750px; }
}
@media (max-width: 800px) {
    .card-container { max-width: 500px; }
    .service-card, .add-card { width: 98vw; min-width: 0; height: auto; min-height: 320px; }
}
</style>

<h2 style="text-align:center;margin-top:10px;">Services</h2>

<div class="card-container">
    <!-- Add New Service Button -->
    <div class="service-card add-card">
        <button type="button" id="openAddServiceModal" style="font-size:1.1rem;padding:18px 32px;border-radius:8px;border:2px dashed #1976d2;background:#f5faff;color:#1976d2;cursor:pointer;">
            + Add New Service
        </button>
    </div>

    <!-- Add Service Modal -->
    <div id="addServiceModal" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.25);align-items:center;justify-content:center;">
        <div style="background:#fff;padding:32px 28px 24px;border-radius:12px;min-width:340px;max-width:98vw;box-shadow:0 8px 32px rgba(0,0,0,0.18);position:relative;">
            <button type="button" id="closeAddServiceModal" style="position:absolute;top:12px;right:16px;background:none;border:none;font-size:1.5rem;cursor:pointer;color:#888;">&times;</button>
            <form class="service-form" method="post" enctype="multipart/form-data" autocomplete="off">
                <h2 style="margin-bottom:18px;">Add New Service</h2>
                <div class="form-group">
                    <label>Title:</label>
                    <input type="text" name="svs_title" required style="width:100%;padding:7px 10px;margin-bottom:10px;">
                </div>
                <div class="form-group">
                    <label>Description:</label>
                    <textarea name="svs_description" required style="width:100%;padding:7px 10px;margin-bottom:10px;"></textarea>
                </div>
                <div class="form-group">
                    <label>Logo:</label>
                    <input type="file" name="svs_logo" accept="image/*" style="margin-bottom:10px;">
                </div>
                <div class="form-buttons" style="margin-top:16px;">
                    <button type="submit" class="btn-submit" style="padding:8px 22px;border-radius:6px;background:#1976d2;color:#fff;border:none;font-size:1rem;cursor:pointer;">Save</button>
                    <button type="button" id="cancelAddServiceModal" class="btn-cancel" style="margin-left:10px;padding:8px 22px;border-radius:6px;background:#eee;color:#333;border:none;font-size:1rem;cursor:pointer;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <script>
    document.getElementById('openAddServiceModal').onclick = function() {
        document.getElementById('addServiceModal').style.display = 'flex';
    };
    document.getElementById('closeAddServiceModal').onclick = function() {
        document.getElementById('addServiceModal').style.display = 'none';
    };
    document.getElementById('cancelAddServiceModal').onclick = function() {
        document.getElementById('addServiceModal').style.display = 'none';
    };
    // Optional: close modal on background click
    document.getElementById('addServiceModal').onclick = function(e) {
        if (e.target === this) this.style.display = 'none';
    };
    </script>

    <!-- Service Cards -->
    <?php
    $result = mysqli_query($conn, "SELECT * FROM services_tbl");
    $cards = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Prepare gallery images
        $gallery_images = [];
        if (!empty($row['svs_gallery'])) {
            $gallery_images = json_decode($row['svs_gallery'], true);
            if ($gallery_images === null) {
                $gallery_images = explode(',', $row['svs_gallery']);
            }
            $gallery_images = array_filter($gallery_images);
        }
        ob_start();
        ?>
        <div class="service-card">
            <img src="uploads/<?= htmlspecialchars($row['svs_logo']) ?>" class="card-logo" alt="Logo">
            <div class="card-title"><?= htmlspecialchars($row['svs_title']) ?></div>
            <div class="card-desc"><?= htmlspecialchars($row['svs_description']) ?></div>
            <?php if (count($gallery_images)): ?>
            <div class="card-gallery">
                <?php foreach ($gallery_images as $img): ?>
                <img src="uploads/gallery/<?= htmlspecialchars($img) ?>" alt="Gallery">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <div style="font-size:12px;color:#888;margin-bottom:8px;">
            <?php
                // Get gallery count from gallery_tbl where svs_id = svs_id of this card
                $svs_id = (int)$row['svs_id'];
                $gallery_count_result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM gallery_tbl WHERE svs_id = $svs_id");
                $gallery_count_row = mysqli_fetch_assoc($gallery_count_result);
                $gallery_count = $gallery_count_row ? $gallery_count_row['cnt'] : 0;
            ?>
            Gallery: <span class="gallery-count"><?= $gallery_count ?></span>
            </div>
            <div class="card-actions">
            <a href="services_view.php?id=<?= $row['svs_id'] ?>">View</a>
            <a href="?delete=<?= $row['svs_id'] ?>" class="delete" onclick="return confirm('Delete this service?')">Delete</a>
            </div>
        </div>
        <?php
        $cards[] = ob_get_clean();
    }
    // Output cards in rows of 3
    foreach ($cards as $i => $card) {
        echo $card;
    }
    ?>
</div>

<?php include 'footer.php'; ?>