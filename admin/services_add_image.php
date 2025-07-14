<?php
include 'header.php'; // includes DB $conn and $db object

$upload_base_dir = __DIR__ . '/uploads/gallery/';
$chunk_dir = $upload_base_dir . 'chunks/';
if (!is_dir($chunk_dir)) mkdir($chunk_dir, 0777, true);

// Chunked Upload using Resumable.js
if (isset($_REQUEST['resumableIdentifier'])) {
    $service_id = intval($_REQUEST['service_id'] ?? 0);
    $identifier = preg_replace('/[^0-9A-Za-z_-]/', '', $_REQUEST['resumableIdentifier']);
    $filename = basename($_REQUEST['resumableFilename']);
    $chunk_number = intval($_REQUEST['resumableChunkNumber']);
    $total_chunks = intval($_REQUEST['resumableTotalChunks']);

    $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $is_video = in_array($file_ext, ['mp4', 'webm', 'ogg']);
    $upload_dir = $is_video ? $upload_base_dir . 'videos/' : $upload_base_dir . 'images/';
    $file_type = $is_video ? 'video' : 'image';

    $chunk_file_path = $chunk_dir . "chunk_{$identifier}_{$chunk_number}";
    $final_file_name = time() . '_' . uniqid() . '.' . $file_ext;
    $final_file_path = $upload_dir . $final_file_name;

    // Check chunk (GET)
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        http_response_code(file_exists($chunk_file_path) ? 200 : 204);
        exit;
    }

    // Upload chunk (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['file']['tmp_name'])) {
        move_uploaded_file($_FILES['file']['tmp_name'], $chunk_file_path);
        http_response_code(200);
    }

    // Merge if complete
    $all_uploaded = true;
    for ($i = 1; $i <= $total_chunks; $i++) {
        if (!file_exists($chunk_dir . "chunk_{$identifier}_{$i}")) {
            $all_uploaded = false;
            break;
        }
    }

    if ($all_uploaded) {
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $final = fopen($final_file_path, 'wb');
        for ($i = 1; $i <= $total_chunks; $i++) {
            $chunk = fopen($chunk_dir . "chunk_{$identifier}_{$i}", 'rb');
            stream_copy_to_stream($chunk, $final);
            fclose($chunk);
            unlink($chunk_dir . "chunk_{$identifier}_{$i}");
        }
        fclose($final);

        // Upload to Cloudinary
        $cloudinary = $db->uploadToCloudinary($final_file_path, $file_type);
        if ($cloudinary['success']) {
            $cloudinary_url = $cloudinary['url'];
            $cloudinary_public_id = $cloudinary['cloudinary_public_id'] ?? null;
            $gallery_title = mysqli_real_escape_string($conn, $_POST['gallery_title'] ?? 'Untitled');
            $gallery_desc  = mysqli_real_escape_string($conn, $_POST['gallery_desc'] ?? '');

            $stmt = $conn->prepare("INSERT INTO gallery_tbl (svs_id, gallery_image, glr_title, glr_description, file_type, cloudinary_public_id, archive) VALUES (?, ?, ?, ?, ?, ?, 'not')");
            $stmt->bind_param("isssss", $service_id, $cloudinary_url, $gallery_title, $gallery_desc, $file_type, $cloudinary_public_id);

            $stmt->execute();
            $stmt->close();

            unlink($final_file_path); // Clean up local file
            echo 'Upload complete';
        } else {
            echo 'Cloudinary Upload Failed: ' . $cloudinary['message'];
        }
        exit;
    }

    exit;
}

// Traditional form upload fallback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['gallery_media'])) {
    $service_id     = intval($_POST['service_id']);
    $gallery_title  = mysqli_real_escape_string($conn, $_POST['gallery_title']);
    $gallery_desc   = mysqli_real_escape_string($conn, $_POST['gallery_desc']);
    $file           = $_FILES['gallery_media'];

    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_image_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $allowed_video_types = ['mp4', 'webm', 'ogg'];

    if (in_array($file_ext, $allowed_image_types)) {
        $upload_dir = $upload_base_dir . 'images/';
        $file_type = 'image';
    } elseif (in_array($file_ext, $allowed_video_types)) {
        $upload_dir = $upload_base_dir . 'videos/';
        $file_type = 'video';
    } else {
        $_SESSION['swal'] = ['title' => 'Invalid File', 'text' => 'Only image and video files are allowed.', 'icon' => 'error'];
        header("Location: services_view.php?id=$service_id");
        exit;
    }

    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $file_name = time() . '_' . uniqid() . '.' . $file_ext;
    $target_path = $upload_dir . $file_name;

    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        $_SESSION['swal'] = ['title' => 'Upload Failed', 'text' => 'Failed to upload the file.', 'icon' => 'error'];
        header("Location: services_view.php?id=$service_id");
        exit;
    }

  // Upload to Cloudinary
        $cloudinary = $db->uploadToCloudinary($target_path, $file_type);

        if ($cloudinary['success']) {
            $cloudinary_url = $cloudinary['url'];
            $cloudinary_public_id = $cloudinary['cloudinary_public_id'] ?? null;

            $stmt = $conn->prepare("INSERT INTO gallery_tbl (svs_id, gallery_image, glr_title, glr_description, file_type, cloudinary_public_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssss", $service_id, $cloudinary_url, $gallery_title, $gallery_desc, $file_type, $cloudinary_public_id);
            $stmt->execute();
            $stmt->close();

            // Delete local file
            unlink($target_path);

            $_SESSION['swal'] = [
                'title' => 'Success',
                'text' => ucfirst($file_type) . ' uploaded to Cloudinary.',
                'icon' => 'success'
            ];
        } else {
            $_SESSION['swal'] = [
                'title' => 'Cloudinary Upload Failed',
                'text' => $cloudinary['message'],
                'icon' => 'error'
            ];
        }


    header("Location: services_view.php?id=$service_id");
    exit;
}

// Fallback for wrong access
header("Location: services_ctrl.php");
exit;
?>
