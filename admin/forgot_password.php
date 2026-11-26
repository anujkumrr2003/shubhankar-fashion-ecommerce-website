<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="utf-8" />
<title> Admin Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta content="A fully responsive admin theme which can be used to build CRM, CMS,ERP etc." name="description" />
<meta content="Techzaa" name="author" />

<!-- App favicon -->
<link rel="shortcut icon" href="img/biz.png">

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


<!--frjubpqvvdwgmnmb-->
     <?php
include 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer library files
require 'phpMailer/Exception.php';
require 'phpMailer/PHPMailer.php';
require 'phpMailer/SMTP.php';

// Fetch email credentials from the database
$sql = "SELECT * FROM email_credentials ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);
$credentials = $result->fetch_assoc();

$dynamicEmail = $credentials['email'];
$dynamicPassword = $credentials['password'];

// Function to send OTP via email
function sendEmail($to, $otp, $fromEmail, $fromPassword) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $fromEmail;
        $mail->Password = $fromPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($fromEmail, 'Support');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = 'Your Password is Reset';
        $mail->Body = "Your new password is: <b>$otp</b><br>Use it to reset your password.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail error: " . $mail->ErrorInfo);
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username_or_email'])) {
    $username_or_email = mysqli_real_escape_string($conn, $_POST['username_or_email']);

    // Check if user exists
    $check_user = $conn->query("SELECT * FROM admin_login WHERE email = '$username_or_email' OR username = '$username_or_email'");
    if ($check_user && $check_user->num_rows > 0) {
        $user = $check_user->fetch_assoc();
        $email_to_send = $user['email'];
        $existing_password = $user['password']; // Assumes plaintext password

        if (sendEmail($email_to_send, $existing_password, $dynamicEmail, $dynamicPassword)) {
            echo '<div style="color: green; font-size: 18px; text-align:center;">Your password has been sent to your email!</div>';
        } else {
            echo '<div style="color: red; font-size: 18px;">Failed to send email!</div>';
        }
    } else {
        echo '<div style="color: red; font-size: 18px;">User not found!</div>';
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
</head>
<body>

<div class="form" style="display: flex; flex-direction: column; align-items: center; justify-content: center; border: 2px solid #4CAF50; border-radius: 15px; max-width: 450px; margin: 20px auto; padding: 20px; box-shadow: 0 6px 12px rgba(0, 128, 0, 0.2); font-family: Arial, sans-serif; background: linear-gradient(to bottom, #ffffff, #e8f5e9);">
<h2 style="text-align: center; font-size: 28px; color: #333; margin-bottom: 20px;">Forgot Password</h2>

<form action="" method="POST" style="max-width: 400px; margin: 0 auto; padding: 20px;   box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); text-align: center; font-family: Arial, sans-serif;">
    <input type="text" name="username_or_email" placeholder="Enter your username or email" required 
        style="width: 100%; padding: 12px; margin-bottom: 15px; border: 2px solid #4CAF50; border-radius: 8px; font-size: 15px; background: #f9f9f9;">
        
    
    <button type="submit" 
        style="background-color: #ff9933; color: white; border: none; padding: 12px 25px; border-radius: 8px; font-size: 18px; cursor: pointer; width: 100%; ">
        Reset Password
    </button>
    <a href="index.php" style="font-size: 18px; margin-top: 10px; display: block; text-align: left;">Back to login.</a>

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













