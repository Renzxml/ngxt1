<!-- ngxtmediagroup@gmail.com -->
<?php 
 include "header.php";
?>

<body>
    <!--Home Section-->
<section id="home" class="home">
  <div class="home-image">
    <div class="swiper mySwiper">
      <div class="swiper-wrapper">
        <?php
          $query = "SELECT pcp_image FROM project_cover_photo_tbl";
          $result = mysqli_query($conn, $query);
          while ($row = mysqli_fetch_assoc($result)) {
            // Use the Cloudinary URL directly
            $imageURL = $row['pcp_image'];
            echo '<div class="swiper-slide"><img src="' . htmlspecialchars($imageURL) . '" alt="Slide Image" /></div>';
          }
          ?>

      </div>
      <div class="swiper-pagination"></div>
    </div>
  </div>

  <div class="home-text">
            <h1><?php echo htmlspecialchars($cd_subtitle); ?></h1>
            <div class="home-text2">
            <h1><?php echo htmlspecialchars($cd_subtitle1); ?></h1>
            </div>
            <p><?php echo htmlspecialchars($cd_description); ?></p>
        </div>
</section>

        
    <!--Services Section-->
    <section  id="services" class="services">
    <h2 class="section-title">Services</h2>
      <?php include "services.php"; ?>  
    </section>

    <!--Partners Section-->
    <section id="partners" class="partners-section">
    <h2>Partners</h2>
    <!-- <p class="partners-subtext">
      Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus lacinia odio vitae vestibulum.
      Quisque vitae ex euismod, aliquam arcu a, volutpat mauris. Sed nec ullamcorper velit.
    </p> -->

    <?php 
      include "partners.php";
    ?>

</section>

<?php include "contact_us.php"; ?>


</body>

<script>
  const swiper = new Swiper(".mySwiper", {
    loop: true,
    pagination: {
      el: ".swiper-pagination",
      dynamicBullets: true,
    },
    autoplay: {
      delay: 3000,
      disableOnInteraction: false,
    },
  });
</script>


    <!-- Footer Section -->
<?php
include "footer.php";
?>
