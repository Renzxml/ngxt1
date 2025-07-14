<?php
require '../vendor/autoload.php'; // Include Cloudinary and dependencies

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

// 🔐 Set your Cloudinary credentials
Configuration::instance([
    'cloud' => [
        'cloud_name' => 'dlrhffijd',
        'api_key'    => '927768898319942',
        'api_secret' => 'n2o3pJSA-P72T1irAZCXWvkg1oA'
    ],
    'url' => ['secure' => true]
]);

// 📁 Replace with your file path
$localFilePath = 'uploads/gallery/videos/1752415975_6873bee7de52f.mp4';  // or 'image.jpg'

// 📤 Upload to Cloudinary
try {
    $response = (new UploadApi())->upload($localFilePath, [
        'resource_type' => 'auto'  // auto-detects image or video
    ]);

    // ✅ Output the media URL
    echo "Upload successful!<br>";
    echo "URL: <a href='" . $response['secure_url'] . "' target='_blank'>" . $response['secure_url'] . "</a><br>";

    // 🔁 Embed if video
    if (str_starts_with($response['resource_type'], 'video')) {
        echo "<video width='400' controls><source src='" . $response['secure_url'] . "' type='video/mp4'></video>";
    } else {
        echo "<img src='" . $response['secure_url'] . "' width='300'>";
    }
} catch (Exception $e) {
    echo "Upload failed: " . $e->getMessage();
}
?>
