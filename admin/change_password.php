<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="utf-8" />
<title> Admin Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta content="A fully responsive admin theme which can be used to build CRM, CMS,ERP etc." name="description" />
<meta content="Techzaa" name="author" />
<link rel="icon" type="image/png" href="ohm.png">

<!-- App favicon -->
<link rel="shortcut icon" href="ohm.png">

<!-- Theme Config Js -->
<script src="assets/js/config.js"></script>

<!-- App css -->
<link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

<!-- Icons css -->
<link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
</head>

<body class="authentication-bg position-relative">
<div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5 position-relative">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-xxl-8 col-lg-10">




    <?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        die("New passwords do not match.");
    }

    if (strlen($new_password) < 6) {
        die("Password must be at least 6 characters.");
    }

    // Fetch current password from DB
    $query = "SELECT password FROM admin_login WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Compare plain passwords
        if ($current_password === $user['password']) {
            // Store new password in plain text
            $update_query = "UPDATE admin_login SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("si", $new_password, $_SESSION['user_id']);

            if ($stmt->execute()) {
                echo '<div style="color: green; font-size: 18px; text-align:center;">Password changed successfully!</div>';
            } else {
                echo '<div style="color: red; font-size: 18px; text-align:center;">Error updating password!</div>';
            }
        } else {
            echo '<div style="color: red; font-size: 18px; text-align:center;">Current password is incorrect!</div>';
        }
    } else {
        echo '<div style="color: red; font-size: 18px; text-align:center;">User not found!</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
</head>
<body>
<div style="max-width: 400px; margin: 50px auto; padding: 30px; border: 2px solid #4CAF50; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 128, 0, 0.2); text-align: center; font-family: Arial, sans-serif; background-color:#f9f9f9;">
    <h2 style="margin-bottom: 20px;">Change Password</h2>
    <form action="" method="POST" style="width: 100%;">
        <input type="password" name="current_password" placeholder="Current Password" required style="width: 100%; padding: 12px; margin-bottom: 15px; border: 2px solid #4CAF50; border-radius: 8px; font-size: 15px; background: #f9f9f9; transition: border-color 0.3s;">
        <input type="password" name="new_password" placeholder="New Password" required style="width: 100%; padding: 12px; margin-bottom: 15px; border: 2px solid #4CAF50; border-radius: 8px; font-size: 15px; background: #f9f9f9; transition: border-color 0.3s;">
        <input type="password" name="confirm_password" placeholder="Confirm New Password" required style="width: 100%; padding: 12px; margin-bottom: 20px; border: 2px solid #4CAF50; border-radius: 8px; font-size: 15px; background: #f9f9f9; transition: border-color 0.3s;">
        <button type="submit" style="background-color: #ff9933; color: white; border: none; padding: 12px 25px; border-radius: 8px; font-size: 18px; cursor: pointer; width: 100%;  font-weight: bold; transition: background-color 0.3s;">Change Password</button>
        <a href="total-entry.php" style="font-size: 18px; margin-top: 10px; display: block; text-align: left;">Back to Home.</a>
    </form>
</div>

</body>
</html>




        
      </div>
      <!-- end row --> 
    </div>
    
    <!-- end row --> 
  </div>
  <!-- end container --> 
</div>
<!-- end page -->

<footer class="footer footer-alt fw-medium"> <span class="text-dark"> 
   </span> </footer>
<!-- Vendor js --> 
<script src="assets/js/vendor.min.js"></script> 

<!-- App js --> 
<script src="assets/js/app.min.js"></script>
</body>

</html>
























