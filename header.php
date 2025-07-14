<?php

include "includes/classes.php";

$db = new db_class();

global $db;
$conn = $db->conn;

$company = $db->getCompanyDetails();

// Save company details into variables (with fallback empty strings)
$cd_id = $company['cd_id'];
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
  <link rel="stylesheet" href="assets/styles.css">
  <link rel="icon" type="image/png" href="assets/resources/logo8.jpg">

  <!-- Swiper CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/freeps2/a7rarpress@main/swiper-bundle.min.css">


</head>

<body>

<!-- Header Section -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>




<header>
  <div class="logo">
    <img src="assets/resources/logo1.png" alt="Logo">
  </div>
  <nav>
    <div class="menu-icon">
      <i class="fa-solid fa-bars" onclick="toggleMenu()"></i>
    </div>
    <ul id="menuList">
      <li><a href="#home">Home</a></li>
      <li><a href="#services">Services</a></li>
      <li><a href="#partners">Partners</a></li>
      <li><a href="#contact">Contact Us</a></li>
    </ul> 
  </nav>
</header>


    <main class="main">


<!-- Swiper CSS -->
<link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css"
/>

<!-- Swiper JS (place before closing body tag or after your HTML) -->
<script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>

<script>
  function toggleMenu() {
    const menuList = document.getElementById('menuList');
    menuList.classList.toggle('active');
  }
</script>


