<?php 
// session_start();
// //print_r($_SESSION);
// include("frame/header.php");

// if (!isset($_SESSION['user_id'])) {
//   // Redirect to total-entry.php using JavaScript
//   echo "<script>window.location.href = 'index.php';</script>";
//   exit(); // Ensure no further code is executed
// }

?>


<?php
session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

include("frame/header.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title> Admin Panel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta content="" name="description" />
  <meta content="" name="author" />

  <!-- App favicon --\ -->
  <!-- <link rel="shortcut icon" href="ohm.png"> -->
  <!-- <link rel="icon" type="image/png" href="ohm.png"> -->


   
  <link rel="stylesheet" href="assets/vendor/daterangepicker/daterangepicker.css">

  <!-- Vector Map css -->
  <link rel="stylesheet" href="assets/vendor/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css">

  <!-- Theme Config Js -->
  <script src="assets/js/config.js"></script>

  <!-- App css -->
  <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

  <!-- Icons css -->
  <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
</head>

<body>
  <!-- Begin page -->
  <div class="wrapper">

    
    <!-- ============================================================== -->
    <!-- Start Page Content here -->
    <!-- ============================================================== -->

    <div class="content-page">
      <div class="content">

        <!-- Start Content-->
        <div class="container-fluid">

          <!-- start page title -->
          <div class="row">
            <div class="col-12">
              <div class="page-title-box">
                <div class="page-title-right">
                  <ol class="breadcrumb m-0">
                    <!-- <li class="breadcrumb-item"><a href="javascript: void(0);">Velonic</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboards</a></li> -->
                    <li class="breadcrumb-item active">Welcome!</li>
                  </ol>
                </div>
                <h4 class="page-title animated-title">
    <a href="dashboard.php" style="text-decoration:none; color:inherit;">
        Admin Panel Dashboard !
    </a>
</h4>

<style>
.animated-title a {
    font-weight: 500 !important;
    font-size: 22px;
    text-shadow:2px 2px 5px rgba(0,0,0,0.3);
    animation: colorBlink 3s infinite;
}

/* Animation: blink colors and change shadow */
/* @keyframes colorBlink {
    0%   { color: #ff0000; text-shadow: 2px 2px 5px #ff0000; }
    20%  { color: #ff9900; text-shadow: 2px 2px 5px #ff9900; }
    40%  { color: #33cc33; text-shadow: 2px 2px 5px #33cc33; }
    60%  { color: #3399ff; text-shadow: 2px 2px 5px #3399ff; }
    80%  { color: #cc33ff; text-shadow: 2px 2px 5px #cc33ff; }
    100% { color: #ff0000; text-shadow: 2px 2px 5px #ff0000; }
} */
</style>


              </div>
            </div>
          </div>

