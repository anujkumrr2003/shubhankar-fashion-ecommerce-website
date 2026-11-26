<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$sql = mysqli_query($conn, "SELECT * FROM user WHERE id='$user_id' LIMIT 1");

if(mysqli_num_rows($sql)==0){
    die("User not found");
}

$user = mysqli_fetch_assoc($sql);

$message = "";
$type = "";

if(isset($_POST['change_password'])){

    $current_password = trim($_POST['current_password']);
    $new_password     = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if(empty($current_password) || empty($new_password) || empty($confirm_password)){

        $message = "All fields are required.";
        $type = "danger";

    }elseif(!password_verify($current_password,$user['password'])){

        $message = "Current password is incorrect.";
        $type = "danger";

    }elseif(strlen($new_password) < 6){

        $message = "New password must be at least 6 characters.";
        $type = "warning";

    }elseif($new_password != $confirm_password){

        $message = "Confirm password does not match.";
        $type = "warning";

    }else{

        $hash = password_hash($new_password,PASSWORD_DEFAULT);

        mysqli_query($conn,"UPDATE user SET password='$hash' WHERE id='$user_id'");

        $message = "Password changed successfully.";
        $type = "success";

        $sql = mysqli_query($conn,"SELECT * FROM user WHERE id='$user_id'");
        $user = mysqli_fetch_assoc($sql);

    }

}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Change Password | Shubhankar</title>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta name="title" content="Change Password | Shubhankar">

<meta name="description" content="Securely update your Shubhankar account password to keep your account protected and manage your account safely.">

<meta name="robots" content="noindex,nofollow,noarchive,nosnippet">

<link rel="canonical" href="https://shubhankarfashion.com/change-password">

<meta name="author" content="Shubhankar">
<meta name="publisher" content="Shubhankar">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="Shubhankar">
<meta property="og:title" content="Change Password | Shubhankar">
<meta property="og:description" content="Securely change your Shubhankar account password.">
<meta property="og:url" content="https://shubhankarfashion.com/change-password">
<meta property="og:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:secure_url" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:type" content="image/png">
<meta property="og:image:alt" content="Shubhankar Logo">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Change Password | Shubhankar">
<meta name="twitter:description" content="Securely update your Shubhankar account password.">
<meta name="twitter:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">

<!-- Favicon -->
<link rel="icon" type="image/png" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<link rel="shortcut icon" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<link rel="apple-touch-icon" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">

<meta name="theme-color" content="#000000">
<meta name="application-name" content="Shubhankar">
<meta name="apple-mobile-web-app-title" content="Shubhankar">


<script type="application/ld+json">
{
  "@context":"https://schema.org",
  "@type":"WebPage",
  "name":"Change Password",
  "url":"https://shubhankarfashion.com/change-password",
  "description":"Secure page for updating your Shubhankar account password.",
  "isPartOf":{
    "@type":"WebSite",
    "name":"Shubhankar",
    "url":"https://shubhankarfashion.com/"
  }
}
</script>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<link rel="stylesheet" href="css/style.css">

<style>

.password-card{
    max-width:550px;
    margin:50px auto;
}

.password-box{
    position:relative;
}

.password-box i{
    position:absolute;
    right:15px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
    font-size:20px;
}

</style>

</head>

<body>

<?php include "header.php"; ?>

<div class="container">

<div class="card shadow-sm password-card">

<div class="card-body p-4">

<h3 class="mb-4">Change Password</h3>

<?php if($message!=""){ ?>

<div class="alert alert-<?php echo $type; ?>">
<?php echo $message; ?>
</div>

<?php } ?>

<form method="post">

<div class="mb-3">

<label class="form-label">Current Password</label>

<div class="password-box">

<input
type="password"
name="current_password"
id="current_password"
class="form-control"
required>

<i class="bi bi-eye toggle-password" data-target="current_password"></i>

</div>

</div>

<div class="mb-3">

<label class="form-label">New Password</label>

<div class="password-box">

<input
type="password"
name="new_password"
id="new_password"
class="form-control"
required>

<i class="bi bi-eye toggle-password" data-target="new_password"></i>

</div>

</div>

<div class="mb-4">

<label class="form-label">Confirm Password</label>

<div class="password-box">

<input
type="password"
name="confirm_password"
id="confirm_password"
class="form-control"
required>

<i class="bi bi-eye toggle-password" data-target="confirm_password"></i>

</div>

</div>

<button
type="submit"
name="change_password"
class="btn btn-brand w-100">

Change Password

</button>

</form>

</div>

</div>

</div>

<?php include "footer.php"; ?>

<script>

document.querySelectorAll(".toggle-password").forEach(function(icon){

icon.addEventListener("click",function(){

const input=document.getElementById(this.dataset.target);

if(input.type==="password"){

input.type="text";

this.classList.remove("bi-eye");
this.classList.add("bi-eye-slash");

}else{

input.type="password";

this.classList.remove("bi-eye-slash");
this.classList.add("bi-eye");

}

});

});

</script>

</body>

</html>