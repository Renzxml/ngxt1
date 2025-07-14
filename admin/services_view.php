<?php
include 'header.php';

if (!isset($_GET['id'])) {
    echo "<p>Service ID is missing.</p>";
    exit;
}

$id = intval($_GET['id']);
$result = mysqli_query($conn, "SELECT * FROM services_tbl WHERE svs_id = $id");
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "<p>Service not found.</p>";
    exit;
}

$gallery_data = [];
$archive = "not";
$stmt = $conn->prepare("SELECT * FROM gallery_tbl WHERE svs_id = ? AND archive = ? ORDER BY created_at DESC");
$stmt->bind_param("is", $id, $archive);
$stmt->execute();
$result_gallery = $stmt->get_result();

while ($row = mysqli_fetch_assoc($result_gallery)) {
    $gallery_data[] = [
        'glr_title' => $row['glr_title'],
        'glr_description' => $row['glr_description'],
        'gallery_image' => $row['gallery_image'],
        'file_type' => $row['file_type']
    ];
}
?>
<title><?= htmlspecialchars($data['svs_title']) ?> - Service Details</title>
<style>

        body {
            font-family: Arial, sans-serif;
            background: #fafafa;
            margin-top: 120px;
            padding: 0;
        }
        .gallery-container {
            max-width: 900px;
            margin: 40px auto;
            display: flex;
            flex-wrap: wrap;
            gap: 24px;
            justify-content: center;
        }
        .service-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            width: 280px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: box-shadow 0.2s;
        }
        .service-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.13);
        }
        .service-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }
        .service-name {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .service-description {
            color: #666;
            font-size: 0.98em;
        }
        .add-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #f5f5f5;
            border: 2px dashed #bbb;
            border-radius: 12px;
            width: 280px;
            height: 320px;
            cursor: pointer;
            transition: border-color 0.2s, background 0.2s;
        }
        .add-card:hover {
            border-color: #888;
            background: #f0f0f0;
        }
        .add-card button {
            margin-top: 18px;
            padding: 10px 22px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 1em;
            cursor: pointer;
            transition: background 0.2s;
        }
        .add-card button:hover {
            background: #0056b3;
        }
        .add-icon {
            font-size: 3em;
            color: #007bff;
        }

        .delete-btn {
            background: #dc3545;
            color: #fff;
            border: none;
            padding: 8px 14px;
            margin: 12px;
            border-radius: 6px;
            font-size: 0.9em;
            align-self: flex-end;
            cursor: pointer;
            transition: background 0.2s;
        }
        .delete-btn:hover {
            background: #b52a38;
        }


         .swal2-container {
        z-index: 3000 !important;
    }
</style>
<body>
<!-- Hero Header -->
<div style="width: 100%; min-height: 320px; background: linear-gradient(to bottom, rgba(0,0,0,0.45) 60%, rgba(0,0,0,0.05) 100%), url('<?= htmlspecialchars($data['svs_logo']) ?>') center/cover no-repeat; display: flex; flex-direction: column; justify-content: center; align-items: center; color: #fff; text-align: center; margin-bottom: 40px;">
    <h1 style="font-size: 2.5em; margin-bottom: 12px; text-shadow: 0 2px 12px rgba(0,0,0,0.25);">
        <?= htmlspecialchars($data['svs_title']) ?>
    </h1>
    <p style="font-size: 1.2em; max-width: 600px; text-shadow: 0 1px 6px rgba(0,0,0,0.18);">
        <?= nl2br(htmlspecialchars($data['svs_description'])) ?>
    </p>
</div>


<!-- Gallery Container -->
<div class="gallery-container">
    <!-- Upload Card -->
    <div class="add-card" onclick="openAddImageModal()">
        <div class="add-icon">+</div>
        <div style="margin-top: 12px; font-size: 1.1em; color: #444;">Add <?= htmlspecialchars($data['svs_title']) ?> Content</div>
    </div>

    <!-- Media Cards -->
    <?php foreach ($gallery_data as $img): ?>
        <?php
            $file_url = htmlspecialchars($img['gallery_image']);
            $file_ext = strtolower(pathinfo($file_url, PATHINFO_EXTENSION));
            $is_video = in_array($file_ext, ['mp4', 'webm', 'ogg']);
        ?>
        <div class="service-card">
            <div style="position: relative; cursor: pointer;" onclick="openFullscreenMedia('<?= $file_url ?>', '<?= $is_video ? 'video' : 'image' ?>')">
                <?php if ($is_video): ?>
                    <video class="service-image" muted>
                        <source src="<?= $file_url ?>" type="video/mp4">
                    </video>
                <?php else: ?>
                    <img class="service-image" src="<?= $file_url ?>" alt="Gallery image">
                <?php endif; ?>
                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to bottom, rgba(0,0,0,0.45) 60%, rgba(0,0,0,0.05) 100%); color: #fff; display: flex; flex-direction: column; justify-content: flex-end; align-items: flex-start; padding: 18px 16px;">
                    <div class="service-name"><?= htmlspecialchars($img['glr_title']) ?></div>
                    <div class="service-description"><?= htmlspecialchars($img['glr_description']) ?></div>
                </div>
            </div>
            <!-- Delete Button -->
            <button class="delete-btn" onclick="deleteMedia('<?= addslashes($file_url) ?>', event)">Delete</button>
        </div>

    <?php endforeach; ?>
</div>

<!-- Fullscreen Modal -->
<div id="fullscreenImageModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.92); z-index:2000; align-items:center; justify-content:center;">
    <span onclick="closeFullscreenImage()" style="position:absolute;top:24px;right:38px;font-size:2.5em;color:#fff;cursor:pointer;">&times;</span>
    <div id="fullscreenContent" style="max-width:90vw;max-height:80vh;"></div>
</div>

<!-- Upload Modal -->
<div id="addImageModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.35); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:10px; padding:32px 28px 24px 28px; max-width:400px; width:95%;">
        <button onclick="closeAddImageModal()" style="position:absolute; top:10px; right:14px; background:none; border:none; font-size:1.5em;">&times;</button>
        <h2>Add <?= htmlspecialchars($data['svs_title']) ?> Content</h2>
        <form id="addImageForm">
            <input type="hidden" name="service_id" value="<?= $id ?>">
            <label>Content Title</label>
            <input type="text" name="gallery_title" id="gallery_title" required>
            <label>Description</label>
            <textarea name="gallery_desc" id="gallery_desc" required></textarea>
            <label>Select File</label>
            <button type="button" id="browseButton">Choose File</button>
            <div id="fileProgress"></div>
            <button type="button" id="uploadButton">Upload</button>
        </form>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/resumablejs@1.1.0/resumable.min.js"></script>
<script>
    const resumable = new Resumable({
        target: 'services_add_image.php',
        chunkSize: 2 * 1024 * 1024,
        simultaneousUploads: 1,
        testChunks: true,
        fileType: ['mp4','webm','ogg','jpg','jpeg','png','gif','webp'],
        maxFiles: 1,
        query: () => ({
            service_id: document.querySelector('input[name="service_id"]').value,
            gallery_title: document.getElementById('gallery_title').value,
            gallery_desc: document.getElementById('gallery_desc').value
        })
    });

    resumable.assignBrowse(document.getElementById('browseButton'));

    resumable.on('fileAdded', file => {
        document.getElementById('fileProgress').innerText = 'Selected: ' + file.fileName;
    });

    resumable.on('fileProgress', file => {
        const percent = Math.floor(file.progress() * 100);
        document.getElementById('fileProgress').innerText = `Uploading... ${percent}%`;
    });

    resumable.on('fileSuccess', (file, response) => {
        Swal.fire('Success', response, 'success').then(() => {
            closeAddImageModal();
            location.reload();
        });
    });

    resumable.on('fileError', (file, response) => {
        Swal.fire('Upload Failed', response, 'error');
    });

    document.getElementById('uploadButton').addEventListener('click', () => {
        if (!document.getElementById('gallery_title').value || !document.getElementById('gallery_desc').value) {
            Swal.fire('Missing Info', 'Please fill in title and description.', 'warning');
            return;
        }
        if (resumable.files.length > 0) {
            resumable.upload();
        } else {
            Swal.fire('No File', 'Please choose a file first.', 'warning');
        }
    });

    function openAddImageModal() {
        document.getElementById('addImageModal').style.display = 'flex';
    }

    function closeAddImageModal() {
        document.getElementById('addImageModal').style.display = 'none';
        document.getElementById('addImageForm').reset();
        document.getElementById('fileProgress').innerText = '';
        resumable.cancel();
    }

    function openFullscreenMedia(src, type) {
        const modal = document.getElementById('fullscreenImageModal');
        const content = document.getElementById('fullscreenContent');
        content.innerHTML = type === 'video'
            ? `<video src="${src}" controls autoplay style="max-width:90vw;max-height:80vh;border-radius:10px;"></video>`
            : `<img src="${src}" style="max-width:90vw;max-height:80vh;border-radius:10px;">`;
        modal.style.display = 'flex';
    }

    function closeFullscreenImage() {
        const modal = document.getElementById('fullscreenImageModal');
        const content = document.getElementById('fullscreenContent');
        content.innerHTML = '';
        modal.style.display = 'none';
    }


    function deleteMedia(fileUrl, event) {
        event.stopPropagation(); // Prevent triggering the fullscreen modal

        Swal.fire({
            title: 'Are you sure?',
            text: "This will delete the media to Cloudinary's Archived folder.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('services_delete_media.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'media_path=' + encodeURIComponent(fileUrl)
                })
                .then(response => response.text())
                .then(msg => {
                    Swal.fire('Deleted!', msg, 'success').then(() => location.reload());
                })
                .catch(err => {
                    Swal.fire('Error', 'Failed to delete media.', 'error');
                });
            }
        });
    }



    document.getElementById('fullscreenImageModal').addEventListener('click', function(e) {
        if (e.target === this) closeFullscreenImage();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeFullscreenImage();
    });
</script>

<?php include 'footer.php'; ?>