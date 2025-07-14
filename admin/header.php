<?php

include "../includes/classes.php";

// Account validation: Check if user is logged in and has the 'partner' role
if (!isset($_SESSION['role_status']) || $_SESSION['role_status'] !== 'partners') {
    // User not logged in or not a partner, redirect to login page
    header("Location: login.php");
    exit();
}

$db = new db_class();

global $db;
$conn = $db->conn;


$company = $db->getCompanyDetails();

// Save company details into variables (with fallback empty strings)
$cd_id = $company['cd_id'] ?? '';
$cd_title = $company['cd_title'] ?? '';
$cd_subtitle = $company['cd_subtitle'] ?? '';
$cd_subtitle1 = $company['cd_subtitle1'] ?? '';
$cd_description = $company['cd_description'] ?? '';
$cd_logo = $company['cd_logo'] ?? '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($cd_title); ?> </title>
  <link rel="stylesheet" href="../assets/admin-style.css">
  <link rel="icon" type="image/png" href="../assets/resources/logo8.jpg">

  <!-- Swiper CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/freeps2/a7rarpress@main/swiper-bundle.min.css">

  


</head>

<body>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Header Section -->
<header>
  <div class="logo1">
    <img src="../assets/resources/logo1.png">
  </div>

  <div class="dropdown">
    <div class="logo" onclick="toggleMenu()">
      <img src="../assets/resources/user.png" alt="Profile Logo">
      <i class="fa-solid fa-caret-down"></i>
    </div>

    <ul id="menuList" class="dropdown-menu">
      <li><a href="index.php">Information</a></li>
      <li><a href="services_ctrl.php">Services</a></li>
      <li><a href="pcp.php">Cover photo</a></li>
      <li><a href="portfolio.php">Portfolio</a></li>
      <li><a href="partners.php">Partners</a></li>
      <li>
        <a href="logout.php">
          <img src="../assets/resources/logout.png" class="logout-icon" alt="Logout"> Logout
        </a>
      </li>
    </ul>
  </div>
</header>
    <main class="main">


<!-- Swiper CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>

<!-- Bootstrap CDN -->

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/resumablejs@1/resumable.min.js"></script>


<!-- SweetAlert2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  function toggleMenu() {
    document.getElementById("menuList").classList.toggle("active");
  }

  window.onclick = function (e) {
    if (!e.target.closest('.dropdown')) {
      document.getElementById("menuList").classList.remove("active");
    }
  };
</script>