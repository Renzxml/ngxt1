<?php
include 'header.php';      // or whatever your connection file is


// 1️⃣ Validate the incoming id
$svs_id = filter_input(INPUT_GET, 'svs_id', FILTER_VALIDATE_INT);
if (!$svs_id) {
    http_response_code(400);          // Bad request
    echo json_encode([]);
    exit;
}

// 2️⃣ Fetch images (prepared statement prevents SQL‑injection)
$imgs  = [];
$stmt  = $conn->prepare('SELECT image_path FROM gallery_tbl WHERE svs_id = ?');
$stmt->bind_param('i', $svs_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $imgs[] = $row['image_path'];
}
$stmt->close();

// 3️⃣ Return JSON
header('Content-Type: application/json');
echo json_encode($imgs);