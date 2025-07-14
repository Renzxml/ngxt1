<?php
// Fetch all service records
$query = "SELECT svs_id, svs_title, svs_description, svs_logo FROM services_tbl";
$result = mysqli_query($conn, $query);
?>

<div class="services-cards">
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <?php
        $id = $row['svs_id'];
        $title = $row['svs_title'] ?? 'Default Title';
        $description = $row['svs_description'] ?? 'Default description text.';
        $logo = $row['svs_logo'] ?? 'default.png';
        ?>
            <div class="card" style="background-image: url('./admin/uploads/<?php echo htmlspecialchars($logo); ?>'); background-size: cover; background-position: center;">
            <p class="heading" style="
               color: white;
               margin: auto;
               text-align: center;
               background: rgba(255, 255, 255, 0.1);
               backdrop-filter: blur(10px);
               -webkit-backdrop-filter: blur(10px);
               padding: 10px 20px;
               border-radius: 10px;
               border: 1px solid rgba(255, 255, 255, 0.3);
               ">
             <?php echo htmlspecialchars($title); ?>
            </p>
            <button class="card-btn" onclick="openServiceGallery(<?= $id ?>)">+</button>
            <div class="card-modal">
                <p class="modal-description"><?php echo htmlspecialchars($description); ?></p>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<!-- Modal Container for Service Gallery -->
<div id="serviceGalleryModal" class="service-gallery-modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeServiceGallery()">&times;</span>
        <iframe id="galleryFrame" src="" frameborder="0"></iframe>
    </div>
</div>

<style>
    /* Modal Styles */
    .service-gallery-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.9);
        z-index: 1000;
    }
    
    .modal-content {
        position: relative;
        width: 90%;
        max-width: 1200px;
        height: 90vh;
        margin: 5vh auto;
    }
    
    .close-modal {
        position: absolute;
        top: -40px;
        right: 0;
        color: white;
        font-size: 30px;
        font-weight: bold;
        cursor: pointer;
        z-index: 1001;
    }
    
    #galleryFrame {
        width: 100%;
        height: 100%;
        background: transparent;
    }
</style>

<script>
    function openServiceGallery(serviceId) {
        const modal = document.getElementById('serviceGalleryModal');
        const frame = document.getElementById('galleryFrame');
        
        // Load the gallery page for this specific service
        frame.src = `services_gallery.php?id=${serviceId}`;
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
    
    function closeServiceGallery() {
        document.getElementById('serviceGalleryModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    // Close modal when clicking outside content
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('serviceGalleryModal');
        if (event.target === modal) {
            closeServiceGallery();
        }
    });
</script>