<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link rel="icon" type="image/png" href="ohm.png">
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- <link rel="icon" type="image/png" href="ohm.png"> -->
  <style>
@import url('https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap');


</style>

  <style>

    html, body {
      height: 100% !important;
    }
    body {
      /* font-family: 'Segoe UI', sans-serif; */
        font-family: "Nunito", sans-serif;
        display: flex;
      flex-direction: column;
  
    }
   
    main {
      flex: 1; /* takes available space, pushes footer down */
    }

    .header {
      padding: 15px 30px;
      background: #fff;
    }

    .logo-text {
      font-size: 24px;
      font-weight: bold;
      color: #f5a623;
    }

    .main-section {
      background: linear-gradient(to right, #822bdc, #311d9b);
      color: #fff;
      padding: 60px 0;
    }

    .btn-enquire {
      background-color: #f5a623;
      color: #fff;
      border-radius: 25px;
    }

    .btn-enquire:hover {
      background-color: #d68e1a;
    }

    .form-container {
      background: #fff;
      border-radius: 10px;
      padding: 30px;
      box-shadow: 0 0 15px rgba(0,0,0,0.2);
    }

    .form-container input {
      border-radius: 8px;
    }

    .forgot {
      text-align: right;
      font-size: 14px;
      color: red;
    }

    .signup a {
      color: #f5a623;
      font-weight: bold;
      text-decoration: none;
    }

  </style>
</head>
<body>
<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);

    // Hardcoded credentials for first-time initialization
    $hardcoded_username = "biztoindia";
    $hardcoded_password = "454545";
    $hardcoded_email = "anujkumrr2003@gmail.com";

    // Check if the hardcoded user exists in the database
    $query = "SELECT * FROM admin_login WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $hardcoded_username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Insert the hardcoded user into the database if not exists
        $insert_query = "INSERT INTO admin_login (username, password, email) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("sss", $hardcoded_username, $hardcoded_password, $hardcoded_email);
        $stmt->execute();
    }

    // Validate user credentials
    $query = "SELECT * FROM admin_login WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Directly compare plain password
        // if ($password === $user['password']) {
        //     $_SESSION['user_id'] = $user['id'];
        //     $_SESSION['username'] = $user['username'];
        //     header("Location: dashboard.php");
        //     exit();
        // }
        
       if ($password === $user['password']) {

    // Separate Admin Session
    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_username'] = $user['username'];
    $_SESSION['admin_email'] = $user['email'];
    $_SESSION['admin_logged_in'] = true;

    header("Location: dashboard.php");
    exit();
}
    }

    // If credentials are invalid
    $_SESSION['login_error'] = "Invalid username or password.";
    header("Location: index.php");
    exit();
}
?>

<!-- Header -->
<div class="container-fluid header">
  <div class="d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center">
      <img src="ohm.png" alt="Logo" style="height: 40px; margin-right: 10px;">
      <div class="logo-text">Bizto India</div>
    </div>
    <div class="d-flex flex-column flex-md-row">
  <a href="tel:+919910443978" class="btn btn-enquire mb-2 mb-md-0 me-md-2" style="color:#fff;">
    <i class="fas fa-phone-alt fa-flip-horizontal me-1"></i>

<b>Call Us</b>
  </a>
  <a href="mailto:info@biztoindia.com" class="btn btn-enquire" style="color:#fff;">
    <i class="fas fa-envelope me-1"></i><b>Email Us</b>
  </a>
</div>


  </div>
</div>

<!-- Main Section -->
 <main class="container-fluid main-section d-flex align-items-center justify-content-center">
  <div class="container">
   <div class="row align-items-center">
      <!-- Left Content -->
      <div class="col-lg-6 mb-5 mb-lg-0">
        <h1 class="fw-bold mb-4">Create Your Own<br>Online Presence</h1>
        <p class="mb-4">Join us today and explore endless possibilities to grow your business with our comprehensive online solutions.</p>
        <button class="btn btn-enquire px-4 py-2" data-bs-toggle="modal" data-bs-target="#"><b><i class="fas fa-paper-plane me-2"></i> Enquire Now</b></button>
      </div>
<!-- enquiryModal -->
      <!-- Right Form -->
      <?php if (isset($_SESSION['login_error'])): ?>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
      errorModal.show();
    });
  </script>

  <!-- Error Modal -->
  <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-3 shadow">
        <div class="modal-header border-0">
          <h5 class="modal-title text-danger fw-bold" id="errorModalLabel">⚠️ Login Error</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="mb-0 text-center text-danger fw-semibold">
            <?= $_SESSION['login_error']; ?>
          </p>
        </div>
      </div>
    </div>
  </div>

  <?php unset($_SESSION['login_error']); ?>
<?php endif; ?>

      <div class="col-lg-5 offset-lg-1">
        <div class="form-container">
          <h5 class="text-center mb-4" style="color:#000;"><i class="fas fa-user-circle me-2"></i>Login to manage your account and explore amazing features!</h5>
          <form action="" method="POST">
            <div class="mb-3">
              <label style="color: #000;"><b> <i class="fas fa-user" style="color: #f5a623;"></i> Username:</b></label>
              <input type="text" name="username" class="form-control" placeholder="Enter your username">
            </div>
            <div class="mb-2">
              <label style="color: #000;"><b><i class="fas fa-lock" style="color: #f5a623;"></i> Password:</b></label>
              <input type="password" name="password" class="form-control" placeholder="Enter your password">
            </div>
            <div class="forgot mb-3">
  <a href="forgot_password.php" style="color: #000; text-decoration: none;">
    Forgot password?
  </a>
</div>

            <button class="btn btn-enquire w-100 mb-2"><b><i class="fas fa-paper-plane me-2"></i> Login</b></button>
            <!-- <div class="text-center signup">Don't have an account? <a href="#">Create one</a></div> -->
          </form>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- Enquiry Modal -->
<div class="modal fade" id="enquiryModal" tabindex="-1" aria-labelledby="enquiryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content p-3" style="margin-top:150px;">
      <div class="modal-header">
        <h5 class="modal-title" id="enquiryModalLabel">Enquiry Form</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form>
          <div class="mb-3">
            <label>Name:</label>
            <input type="text" class="form-control" placeholder="Your name">
          </div>
          <div class="mb-3">
            <label>Email:</label>
            <input type="email" class="form-control" placeholder="Your email">
          </div>
          <div class="mb-3">
            <label>Message:</label>
            <textarea class="form-control" placeholder="Your message"></textarea>
          </div>
          <button class="btn btn-enquire w-100"><i class="fas fa-paper-plane me-2"></i> Submit</button>
        </form>
      </div>
    </div>
  </div>
</div>
  <footer class="bg-light py-2" style="margin-bottom:0px !important;">
    <p class="text-muted text-center mb-0">
      © 2025 BiztoIndia. All Rights Reserved.
    </p>
  </footer>

<!-- Bootstrap JS (required for modal) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
