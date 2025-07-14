<?php
require_once '../includes/db_config.php';

$db = new db_connect();
if (!$db->connect()) {
    die('DB connection failed: ' . $db->error);
}
$conn = $db->conn;


if (!isset($_POST['media_path'])) {
    echo "No media specified.";
    exit;
}

$path = $_POST['media_path'];
$filename = basename($path);

// Delete from DB (gallery_tbl)
mysqli_query($conn, "DELETE FROM gallery_tbl WHERE gallery_image = '$filename'");

// Delete file from server
if (file_exists($path)) {
    unlink($path);
    echo "Media deleted successfully.";
} else {
    echo "File not found, but database record removed.";
}
