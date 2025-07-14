<?php
include "includes/classes.php";

$db = new db_class();
$conn = $db->conn;

$company = $db->getCompanyDetails();

// Get service ID from URL
$service_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch gallery items
$service['title'] = 'Gallery';
$service['gallery'] = [];

$result = mysqli_query($conn, "SELECT * FROM gallery_tbl WHERE svs_id = $service_id");
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        if (!empty($row['gallery_image'])) {
            $file = $row['gallery_image'];
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $is_video = in_array($ext, ['mp4', 'webm', 'ogg']);

            $service['gallery'][] = [
                'file' => $file,
                'type' => $is_video ? 'video' : 'image',
                'path' => $is_video ? "./admin/uploads/gallery/videos/$file" : "./admin/uploads/gallery/images/$file"
            ];
        }
    }
} else {
    $service['title'] = 'Service Not Found';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($service['title']) ?> Gallery</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: transparent;
            color: white;
        }
        .service-gallery-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .service-title {
            font-size: 28px;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
            font-weight: 600;
        }
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            aspect-ratio: 1/1;
            transition: all 0.3s ease;
            background: #fff;
            cursor: pointer;
        }
        .gallery-item:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 16px rgba(0,0,0,0.12);
        }
        .gallery-item img,
        .gallery-item video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .empty-gallery {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            color: #888;
            background: #f5f5f5;
            border-radius: 12px;
            border: 1px dashed #ddd;
        }
        @media (max-width: 900px) {
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }
        @media (max-width: 600px) {
            .service-title {
                font-size: 24px;
            }
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }
    </style>
</head>
<body>
<div class="service-gallery-container">
    <div class="service-section">
        <h2 class="service-title"><?= htmlspecialchars($service['title']) ?></h2>
        <?php if (!empty($service['gallery'])): ?>
            <div class="gallery-grid">
                <?php foreach ($service['gallery'] as $index => $item): ?>
                    <div class="gallery-item" onclick="openLightbox(<?= $index ?>)">
                        <?php if ($item['type'] === 'video'): ?>
                            <video src="<?= $item['path'] ?>" muted></video>
                        <?php else: ?>
                            <img src="<?= $item['path'] ?>" alt="Gallery Image">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-gallery">No gallery images available for this service</div>
        <?php endif; ?>
    </div>
</div>

<!-- Lightbox Modal -->
<div id="lightbox" class="lightbox">
    <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
    <div class="lightbox-content" id="lightbox-content"></div>
    <div class="lightbox-nav">
        <span class="lightbox-prev" onclick="changeSlide(-1)">&#10094;</span>
        <span class="lightbox-next" onclick="changeSlide(1)">&#10095;</span>
    </div>
</div>

    <script>
        const lightbox = document.getElementById('lightbox');
        const lightboxContent = document.getElementById('lightbox-content');
        let currentIndex = 0;

        // Prepare gallery data from PHP (each item should have 'type' and 'path')
        const gallery = <?= json_encode($service['gallery']) ?>;

        function openLightbox(index) {
            currentIndex = index;
            renderLightboxItem();
            lightbox.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function renderLightboxItem() {
            const item = gallery[currentIndex];
            if (item.type === 'video') {
                lightboxContent.innerHTML = `
                    <video src="${item.path}" controls autoplay style="max-width:90vw;max-height:80vh;border-radius:8px;"></video>
                `;
            } else {
                lightboxContent.innerHTML = `
                    <img src="${item.path}" alt="Image" style="max-width:90vw;max-height:80vh;border-radius:8px;">
                `;
            }
        }

        function closeLightbox() {
            // Stop any playing video
            const video = lightboxContent.querySelector('video');
            if (video) {
                video.pause();
                video.currentTime = 0;
                video.removeAttribute('src');
                video.load(); // Fully resets the player
            }

            lightboxContent.innerHTML = ''; // Clear content
            lightbox.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function changeSlide(direction) {
            
            currentIndex += direction;

            if (currentIndex >= gallery.length) currentIndex = 0;
            if (currentIndex < 0) currentIndex = gallery.length - 1;

            renderLightboxItem();
        }

       
        // Close when clicking outside the media area
        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox) closeLightbox();
        });

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (lightbox.style.display === 'flex') {
                if (e.key === 'Escape') closeLightbox();
                else if (e.key === 'ArrowLeft') changeSlide(-1);
                else if (e.key === 'ArrowRight') changeSlide(1);
            }
        });
    </script>



<style>
    .lightbox {
        display: none;
        position: fixed;
        top: 0; left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
        z-index: 3000;
        justify-content: center;
        align-items: center;
    }
    .lightbox-content {
        position: relative;
        max-width: 90%;
        max-height: 90%;
    }
    .lightbox-close {
        position: absolute;
        top: -40px;
        right: 0;
        color: white;
        font-size: 30px;
        font-weight: bold;
        cursor: pointer;
    }
    .lightbox-nav {
        position: absolute;
        top: 50%;
        width: 100%;
        display: flex;
        justify-content: space-between;
        transform: translateY(-50%);
    }
    .lightbox-prev, .lightbox-next {
        color: white;
        font-size: 40px;
        font-weight: bold;
        padding: 0 20px;
        cursor: pointer;
    }
</style>
</body>
</html>
