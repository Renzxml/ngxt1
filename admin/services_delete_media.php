<?php
include 'header.php'; // Includes $db and $conn

$db = new db_class();
$conn = $db->conn;

if (!isset($_POST['media_path'])) {
    echo "No media specified.";
    exit;
}

$cloudinaryUrl = $_POST['media_path'];

// Step 1: Fetch public_id from the database
$stmt = $conn->prepare("SELECT cloudinary_public_id FROM gallery_tbl WHERE gallery_image = ?");
$stmt->bind_param("s", $cloudinaryUrl);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    echo "Media not found in database.";
    exit;
}

$row = $result->fetch_assoc();
$publicId = $row['cloudinary_public_id'];

// Step 2: Determine resource type based on file extension
$ext = strtolower(pathinfo($cloudinaryUrl, PATHINFO_EXTENSION));
$resourceType = in_array($ext, ['mp4', 'webm', 'ogg']) ? 'video' : 'image';

// Step 3: Delete from Cloudinary
try {
    $uploadApi = new \Cloudinary\Api\Upload\UploadApi();
    $uploadApi->destroy($publicId, [
        'resource_type' => $resourceType,
        'invalidate' => true
    ]);

    // Step 4: Delete from database
    $deleteStmt = $conn->prepare("DELETE FROM gallery_tbl WHERE cloudinary_public_id = ?");
    $deleteStmt->bind_param("s", $publicId);
    $deleteStmt->execute();

    echo "Deleted!";
} catch (Exception $e) {
    echo "Cloudinary deletion failed: " . $e->getMessage();
}
?>
