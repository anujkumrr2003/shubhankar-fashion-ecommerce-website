
<?php
session_start();

// Sirf admin logout
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_email']);
unset($_SESSION['admin_logged_in']);

header("Location: index.php");
exit();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Logout</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta content="" name="description" />
<meta content="Dheerendra Verma" name="author" />

<!-- App favicon -->
<link rel="shortcut icon" href="img/biz.png">

<!-- Theme Config Js -->
<script src="assets/js/config.js"></script>

<!-- App css -->
<link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

<!-- Icons css -->
<link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
</head>

<body class="authentication-bg">
<div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5 position-relative">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-xxl-8 col-lg-10">
        <div class="card overflow-hidden">
          <div class="row g-0">
            <div class="col-lg-6 d-none d-lg-block p-2"> <img src="assets/images/auth-img.jpg" alt="" class="img-fluid rounded h-100"> </div>
            <div class="col-lg-6">
              <div class="d-flex flex-column h-100">
                <div class="auth-brand p-4"> <a href="index.php" class="logo-light"> <img src="img/biz.png" alt="logo" style="width:100px; height:60px; "> </a> <a href="index.php" class="logo-dark"> <img src="img/biz.png" style="width: 100px; height:60px;" alt="dark logo" height="22"> </a> </div>
                <div class="p-4 my-auto">
                  <div class="my-auto"> 
                    <!-- title-->
                    <div class="text-center">
                      <h4 class="mt-0 fs-20">See You Again !</h4>
                      <p class="text-muted mb-4">You are now successfully sign out.</p>
                    </div>
                    
                    <?php
// session_start();
// session_destroy(); // Destroy all session data
// header("Location: index.php"); // Redirect to login page
// exit();
?>


                    <!-- Logout icon -->
                    <div class="logout-icon m-auto"> 
                    
                      <img src="assets/images/svg/shield.gif" alt="" class="img-fluid"> </div>
                    <!-- end logout-icon--> 
                  </div>
                </div>
              </div>
            </div>
            <!-- end col --> 
          </div>
        </div>
      </div>
      <!-- end row --> 
    </div>
    <div class="row">
      <div class="col-12 text-center">
        <p class="text-dark-emphasis">Back To <a href="index.php"
                            class="text-dark fw-bold ms-1 link-offset-3 text-decoration-underline"><b>Log In</b></a></p>
      </div>
      <!-- end col --> 
    </div>
    <!-- end row --> 
  </div>
  <!-- end container --> 
</div>
<!-- end page -->

<footer class="footer footer-alt fw-medium"> <span class="text-dark"> 
   © N - Designed by BiztoIndia </span> </footer>
<!-- Vendor js --> 
<script src="assets/js/vendor.min.js"></script> 

<!-- App js --> 
<script src="assets/js/app.min.js"></script>
</body>

</html>