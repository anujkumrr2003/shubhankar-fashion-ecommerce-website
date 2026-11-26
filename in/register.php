<?php

// Guarantee a session is active before anything else touches $_SESSION.
// This must run before ANY output (including whitespace) is sent, and
// before config.php, in case config.php's own session handling is
// missing, duplicated, or running after some stray output.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get redirect information
$redirect = $_POST['redirect'] ?? '';

$addcart = $_POST['addcart'] ?? '';
$product_id = $_POST['product_id'] ?? '';
$qty = $_POST['qty'] ?? '';
$variant_id = $_POST['variant_id'] ?? '';
$size = $_POST['size'] ?? '';
$color = $_POST['color'] ?? '';


include 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "phpMailer/Exception.php";
require "phpMailer/PHPMailer.php";
require "phpMailer/SMTP.php";

/*
|--------------------------------------------------------------------------
| AJAX ENDPOINTS
| All requests with an "action" field are handled here and return JSON.
| Flow: send_otp -> verify_otp -> register
|--------------------------------------------------------------------------
*/
/**
 * Generates a fresh OTP, stores it in the session, and emails it to $email.
 * Returns ['status' => 'success'|'error', 'message' => '...'].
 * Reused by both the initial send and the "resend" / auto-recovery paths.
 */
function issueOtp(string $email, string $fullNameForEmail = ''): array
{
    $otp = random_int(100000, 999999);

    $_SESSION['register_otp'] = $otp;
    $_SESSION['otp_email']    = $email;
    $_SESSION['otp_verified'] = false;
    $_SESSION['otp_time']     = time();

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username = 'vipanujkumarlmp@gmail.com';
        $mail->Password = 'xwju bgei lyfm gghg';  // set in config.php, see note below
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('vipanujkumarlmp@gmail.com', 'Shubhankar');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = "Your Shubhankar verification code";
        $mail->Body = "
            <div style='font-family:Arial,sans-serif;max-width:480px;margin:auto'>
                <h2 style='color:#111'>Verify your email</h2>
                <p>Hello,</p>
                <p>Use the code below to verify your email address. This code is valid for 5 minutes.</p>
                <p style='font-size:32px;font-weight:700;letter-spacing:6px;background:#f4f4f4;
                    padding:14px 20px;border-radius:8px;display:inline-block'>{$otp}</p>
                <p style='color:#777;font-size:13px'>If you didn't request this, you can safely ignore this email.</p>
            </div>
        ";

        $mail->send();

        return ['status' => 'success', 'message' => 'OTP sent to ' . htmlspecialchars($email)];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => 'Could not send OTP right now. Please try again.'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    header('Content-Type: application/json');
    $action = $_POST['action'];

    // -------------------- STEP 1: SEND OTP --------------------
    if ($action === 'send_otp') {

        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address.']);
            exit;
        }

        $check = $conn->prepare("SELECT id FROM user WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'This email is already registered. Please login instead.']);
            exit;
        }

        echo json_encode(issueOtp($email));
        exit;
    }

    // -------------------- STEP 2: VERIFY OTP --------------------
    if ($action === 'verify_otp') {

        $otp = trim($_POST['otp'] ?? '');

        // No OTP on record for this session — verification simply can't proceed.
        // We do NOT auto-issue a new code here; the user must press Resend.
        if (!isset($_SESSION['register_otp'], $_SESSION['otp_email'])) {
            echo json_encode(['status' => 'error', 'message' => 'No active code found for this session. Please tap "Resend code".']);
            exit;
        }

        if (time() - $_SESSION['otp_time'] > 300) {
            unset($_SESSION['register_otp']);
            echo json_encode(['status' => 'error', 'message' => 'Your code expired. Please tap "Resend code".']);
            exit;
        }

        if ($otp === '' || $otp !== (string) $_SESSION['register_otp']) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid OTP. Please check and try again.']);
            exit;
        }

        // Correct code: mark verified but keep it on record (untouched) until
        // registration completes, in case the user needs to retry step 3.
        $_SESSION['otp_verified'] = true;

        echo json_encode(['status' => 'success', 'message' => 'Email verified successfully.']);
        exit;
    }

    // -------------------- STEP 3: COMPLETE REGISTRATION --------------------
    if ($action === 'register') {

        if (empty($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true || empty($_SESSION['otp_email'])) {
            echo json_encode(['status' => 'error', 'message' => 'Please verify your email before continuing.']);
            exit;
        }

        $full_name = trim($_POST['full_name'] ?? '');
        $phone     = trim($_POST['phone'] ?? '');
        $password  = trim($_POST['password'] ?? '');
        $email     = $_SESSION['otp_email'];

        if (empty($full_name) || empty($phone) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Please fill all fields.']);
            exit;
        }

        if (!preg_match('/^[0-9]{7,15}$/', $phone)) {
            echo json_encode(['status' => 'error', 'message' => 'Please enter a valid phone number.']);
            exit;
        }

        if (strlen($password) < 6) {
            echo json_encode(['status' => 'error', 'message' => 'Password must be at least 6 characters.']);
            exit;
        }

        $check = $conn->prepare("SELECT id FROM user WHERE phone=?");
        $check->bind_param("s", $phone);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'This phone number is already registered.']);
            exit;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $insert = $conn->prepare("
            INSERT INTO user(full_name, phone, email, password, email_verified)
            VALUES(?,?,?,?,1)
        ");
        $insert->bind_param("ssss", $full_name, $phone, $email, $hash);

        // if ($insert->execute()) {

        //     unset($_SESSION['register_otp'], $_SESSION['otp_email'], $_SESSION['otp_verified'], $_SESSION['otp_time']);

        //     echo json_encode(['status' => 'success', 'message' => 'Your account has been created.', 'redirect' => 'login']);
        // } 
        
        if ($insert->execute()) {

    // Get newly created user ID
    $new_user_id = mysqli_insert_id($conn);

    // Automatically login the newly created user
    $_SESSION['user_id'] = $new_user_id;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $full_name;

    // Clear registration OTP session
    unset(
        $_SESSION['register_otp'],
        $_SESSION['otp_email'],
        $_SESSION['otp_verified'],
        $_SESSION['otp_time']
    );

    // Get redirect information
    $redirect = $_POST['redirect'] ?? '';

    $addcart = $_POST['addcart'] ?? '';
    $product_id = $_POST['product_id'] ?? '';
    $qty = $_POST['qty'] ?? '';
    $variant_id = $_POST['variant_id'] ?? '';
    $size = $_POST['size'] ?? '';
    $color = $_POST['color'] ?? '';

    /*
     * If user came from Add To Cart,
     * return to the same product page with all cart information.
     */
    if ($addcart == '1' && !empty($redirect)) {

        $redirect_url =
            $redirect .
            '&addcart=1' .
            '&product_id=' . urlencode($product_id) .
            '&qty=' . urlencode($qty) .
            '&variant_id=' . urlencode($variant_id) .
            '&size=' . urlencode($size) .
            '&color=' . urlencode($color);

    } 
    elseif (!empty($redirect)) {

        $redirect_url = $redirect;

    } else {

        $redirect_url = 'shop';

    }
    
     
    echo json_encode([
    'status' => 'success',
    'message' => 'Your account has been created successfully.',
    'redirect' => $redirect_url
]);
     
}

        else {
            echo json_encode(['status' => 'error', 'message' => 'Something went wrong. Please try again.']);
        }
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Create Account | Shubhankar</title>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta name="title" content="Create Account | Shubhankar">
<meta name="description" content="Create your Shubhankar account to shop premium men's fashion, track orders, manage your profile, save addresses and enjoy a seamless shopping experience.">

<meta name="author" content="Shubhankar">
<meta name="publisher" content="Shubhankar">

<!-- Prevent Search Engine Indexing -->
<meta name="robots" content="noindex,follow">
<meta name="googlebot" content="noindex,follow">
<meta name="bingbot" content="noindex,follow">

<link rel="canonical" href="https://shubhankarfashion.com/register">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="Shubhankar">
<meta property="og:title" content="Create Account | Shubhankar">
<meta property="og:description" content="Register your Shubhankar account and enjoy secure online shopping.">
<meta property="og:url" content="https://shubhankarfashion.com/register">
<meta property="og:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:secure_url" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:type" content="image/png">
<meta property="og:image:alt" content="Shubhankar Logo">
<meta property="og:locale" content="en_IN">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Create Account | Shubhankar">
<meta name="twitter:description" content="Register your Shubhankar account for a better shopping experience.">
<meta name="twitter:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">

<!-- Favicon -->
<link rel="icon" type="image/png" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<link rel="shortcut icon" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<link rel="apple-touch-icon" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">

<meta name="theme-color" content="#000000">
<meta name="application-name" content="Shubhankar">
<meta name="apple-mobile-web-app-title" content="Shubhankar">
<meta name="format-detection" content="telephone=no">

<script type="application/ld+json">
{
  "@context":"https://schema.org",
  "@type":"WebPage",
  "name":"Create Account",
  "url":"https://shubhankarfashion.com/register",
  "description":"Create a customer account on Shubhankar to shop online and manage your orders.",
  "isPartOf":{
    "@type":"WebSite",
    "name":"Shubhankar",
    "url":"https://shubhankarfashion.com/"
  }
}
</script>
<script type="application/ld+json">
{
  "@context":"https://schema.org",
  "@type":"BreadcrumbList",
  "itemListElement":[
    {
      "@type":"ListItem",
      "position":1,
      "name":"Home",
      "item":"https://shubhankarfashion.com/"
    },
    {
      "@type":"ListItem",
      "position":2,
      "name":"Register",
      "item":"https://shubhankarfashion.com/register"
    }
  ]
}
</script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

    <style>
        .password-box {
            position: relative;
        }

        .password-box i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 20px;
        }

        .step {
            display: none;
        }

        .step.active {
            display: block;
            animation: fadeIn .35s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .step-dots {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 22px;
        }

        .step-dots span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #d9d9d9;
            transition: background .3s;
        }

        .step-dots span.done,
        .step-dots span.on {
            background: #111;
        }

        .btn-brand[disabled],
        .btn-outline-dark[disabled] {
            opacity: .7;
        }

        .spinner-border-sm-inline {
            width: 1rem;
            height: 1rem;
            border-width: .15em;
            margin-right: 6px;
        }

        .verified-badge {
            font-size: 14px;
            color: #198754;
        }

        .resend-note {
            font-size: 13px;
            color: #777;
        }

        .resend-note a {
            color: #111;
            text-decoration: underline;
            cursor: pointer;
        }

        .resend-note a.disabled {
            pointer-events: none;
            color: #aaa;
            text-decoration: none;
        }
    </style>
    
   

</head>

<body>

     

<!-- ============ TOP UTILITY BAR ============ -->
<div class="topbar py-2 d-none d-md-block" style="background-color: #00005f !important;">
    <div class="container d-flex justify-content-between align-items-center">
        <div><i class="bi bi-truck"></i>&nbsp; Free shipping on prepaid orders above ₹999 / equivalent</div>
         <div>
      <a href="contact" class="me-3 text-white"><i class="bi bi-headset"></i> Support</a>
      <a href="faq" class="text-white"><i class="bi bi-question-circle"></i> Help &amp; FAQ</a>
    </div>
    </div>
</div>

<!-- ============ MAIN HEADER ============ -->
<header class="site-header">
    <div class="container py-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">

            <a href="https://shubhankarfashion.com/in/" class="brand-mark d-flex align-items-center text-decoration-none">
    <img src="../img/logo-removebg-preview.png" alt="Shubhankar" style="height:50px;width:auto;margin-right:10px;">
    
</a>

            <nav class="main-nav d-none d-lg-flex">
                <ul class="nav">
                    <li class="nav-item"><a class="nav-link" href="https://shubhankarfashion.com/in/">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="shop">Shop</a></li>
                    <li class="nav-item"><a class="nav-link" href="categories">Categories</a></li>
                    <li class="nav-item"><a class="nav-link" href="about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact">Contact</a></li>
                </ul>
            </nav>

            <div class="d-flex align-items-center gap-2">





                <span class="mode-badge js-mode-badge d-none d-sm-inline-block">B2B Pricing</span>

                <!-- <a href="login" class="icon-btn d-none d-sm-inline-flex" title="Account"><i class="bi bi-person"></i></a> -->
                <?php if (isset($_SESSION['user_id'])) { ?>

                    <div class="dropdown">

                        <button class="icon-btn d-none d-sm-inline-flex"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">

                            <i class="bi bi-person-circle"></i>

                        </button>

                        <div class="dropdown-menu dropdown-menu-end shadow p-3" style="width:280px;">

                            <div class="text-center mb-3">

                                <div class="rounded-circle bg-light d-inline-flex justify-content-center align-items-center"
                                    style="width:65px;height:65px;">

                                    <i class="bi bi-person fs-2"></i>

                                </div>

                                <h6 class="mt-2 mb-0">
                                    <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                                </h6>

                                <small class="text-muted">
                                    <?php echo htmlspecialchars($_SESSION['user_email']); ?>
                                </small>

                            </div>

                            <hr>

                            <a class="dropdown-item" href="my-profile">
                                <i class="bi bi-person me-2"></i>
                                My Profile
                            </a>

                            <a class="dropdown-item" href="b2b-my-orders">
                                <i class="bi bi-bag-check me-2"></i>
                                My Orders
                            </a>
                            <a class="dropdown-item" href="my-address">
                                <i class="bi bi-geo-alt me-2"></i>
                                My Address
                            </a>

                            <a class="dropdown-item" href="change-password">
                                <i class="bi bi-lock me-2"></i>
                                Change Password
                            </a>

                            <hr>

                            <a class="dropdown-item text-danger" href="logout">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Logout
                            </a>

                        </div>

                    </div>

                <?php } else { ?>

                    <a href="login"
                        class="icon-btn d-none d-sm-inline-flex"
                        title="Login">

                        <i class="bi bi-person"></i>

                    </a>

                <?php } ?>

                <?php
                if (!isset($conn)) {
                    include 'config.php';
                }

                $cartCount = 0;



                if (isset($_SESSION['user_id'])) {

                    $user_id = (int)$_SESSION['user_id'];

                    $cartResult = mysqli_query($conn, "
        SELECT SUM(qty) AS total
        FROM cart
        WHERE user_id='$user_id'
    ");

                    if ($cartResult) {

                        $cartRow = mysqli_fetch_assoc($cartResult);

                        $cartCount = (int)$cartRow['total'];
                    }
                }


                ?>

                <a href="b2b-cart" class="icon-btn position-relative" title="Cart">

                    <i class="bi bi-bag"></i>

                    <span
                        class="cart-count"
                        id="cartCount"
                        <?= $cartCount == 0 ? 'style="display:none"' : '' ?>>

                        <?= $cartCount ?>

                    </span>

                </a>
                <button class="icon-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>
    </div>
</header>

<!-- Mobile nav offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="mobileNav">
    <div class="offcanvas-header">
           <a href="index" class="brand-mark d-flex align-items-center text-decoration-none">
    <img src="../img/logo-removebg-preview.png" alt="Shubhankar" style="height:40px;width:auto;margin-right:10px;">
    
</a>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <ul class="nav flex-column">

        <li class="nav-item">
            <a class="nav-link" href="index">Home</a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="shop">Shop</a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="categories">Categories</a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="about">About</a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="contact">Contact</a>
        </li>

        <?php if (isset($_SESSION['user_id'])) { ?>

            <li class="nav-item mt-3 mb-2">
                <div class="text-center">

                    <div class="rounded-circle bg-light d-inline-flex justify-content-center align-items-center"
                        style="width:70px;height:70px;">

                        <i class="bi bi-person-circle fs-1"></i>

                    </div>

                    <h6 class="mt-2 mb-0">
                        <?= htmlspecialchars($_SESSION['user_name']); ?>
                    </h6>

                    <small class="text-muted">
                        <?= htmlspecialchars($_SESSION['user_email']); ?>
                    </small>

                </div>
            </li>

            <hr>

            <li class="nav-item">
                <a class="nav-link" href="my-profile">
                    <i class="bi bi-person me-2"></i>
                    My Profile
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="b2b-my-orders">
                    <i class="bi bi-bag-check me-2"></i>
                    My Orders
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="my-address">
                    <i class="bi bi-geo-alt me-2"></i>
                    My Address
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="change-password">
                    <i class="bi bi-lock me-2"></i>
                    Change Password
                </a>
            </li>

            <hr>

            <li class="nav-item">
                <a class="nav-link text-danger" href="logout">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Logout
                </a>
            </li>

        <?php } else { ?>

            <li class="nav-item">
                <a class="nav-link" href="login">
                    <i class="bi bi-person me-2"></i>
                    Login / Register
                </a>
            </li>

        <?php } ?>

    </ul>
</div>



<style>
    /* Mobile Menu */
    #mobileNav .offcanvas-header {
        border-bottom: 1px solid #eee;
    }

    #mobileNav .offcanvas-body {
        padding: 20px;
    }

    .mobile-user-box {
        background: #f8f9fa;
        border-radius: 16px;
        padding: 18px;
        text-align: center;
        margin-bottom: 20px;
    }

    .mobile-user-box .avatar {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: auto;
        box-shadow: 0 5px 15px rgba(0, 0, 0, .08);
    }

    .mobile-user-box .avatar i {
        font-size: 42px;
        color: #c50000;
    }

    .mobile-user-box h6 {
        margin-top: 12px;
        margin-bottom: 2px;
        font-weight: 600;
    }

    .mobile-user-box small {
        color: #777;
    }

    #mobileNav .nav-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 15px;
        border-radius: 10px;
        color: #333;
        font-weight: 500;
        transition: .3s;
    }

    #mobileNav .nav-link i {
        width: 22px;
        font-size: 18px;
    }

    #mobileNav .nav-link:hover {
        background: #c50000;
        color: #fff;
        transform: translateX(5px);
    }

    #mobileNav .logout {
        color: #dc3545;
    }

    #mobileNav .logout:hover {
        background: #dc3545;
        color: #fff;
    }
</style>





    <section class="section">

        <div class="container" style="max-width:520px;">

            <div class="card-panel">

                <h2 class="text-center mb-2">Create your account</h2>
                <p class="text-center text-muted mb-4">Register to start shopping with Shubhankar</p>

                <div class="step-dots">
                    <span id="dot-1" class="on"></span>
                    <span id="dot-2"></span>
                    <span id="dot-3"></span>
                </div>

                <div id="alertBox" class="alert d-none" role="alert"></div>

                <!-- STEP 1: EMAIL -->
                <div class="step active" id="step-1">

                    <div class="mb-3">
                        <label class="form-label">Email address</label>
                        <input type="email" id="email" class="form-control" placeholder="you@example.com" required>
                        <div class="form-text">We'll send a 6-digit code to verify it's you.</div>
                    </div>

                    <button type="button" id="btnSendOtp" class="btn btn-brand w-100">
                        Send Verification Code
                    </button>

                </div>

                <!-- STEP 2: OTP -->
                <div class="step" id="step-2">

                    <p class="mb-3">
                        Enter the 6-digit code sent to <strong id="otpEmailLabel"></strong>
                    </p>

                    <div class="mb-2">
                        <input type="text" id="otp" class="form-control text-center" maxlength="6"
                            inputmode="numeric" style="letter-spacing:8px;font-size:22px" placeholder="000000">
                    </div>

                    <p class="resend-note mb-3">
                        Didn't get it? <a id="resendLink">Resend code</a>
                        <span id="resendTimer"></span>
                    </p>

                    <div class="d-flex gap-2">
                        <button type="button" id="btnChangeEmail" class="btn btn-outline-secondary">Back</button>
                        <button type="button" id="btnVerifyOtp" class="btn btn-brand flex-grow-1">Verify Code</button>
                    </div>

                </div>

                <!-- STEP 3: DETAILS -->
                <div class="step" id="step-3">
<input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
<input type="hidden" name="addcart" value="<?= htmlspecialchars($addcart) ?>">
<input type="hidden" name="product_id" value="<?= htmlspecialchars($product_id) ?>">
<input type="hidden" name="qty" value="<?= htmlspecialchars($qty) ?>">
<input type="hidden" name="variant_id" value="<?= htmlspecialchars($variant_id) ?>">
<input type="hidden" name="size" value="<?= htmlspecialchars($size) ?>">
<input type="hidden" name="color" value="<?= htmlspecialchars($color) ?>">
                    <p class="verified-badge mb-3"><i class="bi bi-check-circle-fill"></i> Email verified</p>

                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" id="full_name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="number" id="phone" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="password-box">
                            <input type="password" id="password" class="form-control" placeholder="At least 6 characters" required>
                            <i class="bi bi-eye" id="togglePassword"></i>
                        </div>
                    </div>

                    <div class="form-check mb-4">
                        <input type="checkbox" class="form-check-input" id="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="terms-conditions" target="_blank">Terms & Conditions</a>
                        </label>
                    </div>

                    <button type="button" id="btnRegister" class="btn btn-brand w-100">Create Account</button>

                </div>

                <p class="text-center mt-3">
                    Already have an account? <a href="login">Login</a>
                </p>

            </div>

        </div>

    </section>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    (function () {

        const alertBox   = document.getElementById('alertBox');
        const dots       = { 1: document.getElementById('dot-1'), 2: document.getElementById('dot-2'), 3: document.getElementById('dot-3') };

        function showStep(n) {
            document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
            document.getElementById('step-' + n).classList.add('active');

            [1, 2, 3].forEach(i => {
                dots[i].classList.remove('on', 'done');
                if (i < n) dots[i].classList.add('done');
                if (i === n) dots[i].classList.add('on');
            });
        }

        function showAlert(message, type) {
            alertBox.className = 'alert alert-' + type;
            alertBox.textContent = message;
            alertBox.classList.remove('d-none');
        }

        function hideAlert() {
            alertBox.classList.add('d-none');
        }

        function setLoading(btn, loading, label) {
            if (loading) {
                btn.dataset.originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm-inline" role="status"></span>' + label;
            } else {
                btn.disabled = false;
                btn.innerHTML = btn.dataset.originalText || label;
            }
        }

        async function callApi(action, data) {
            const form = new FormData();
            form.append('action', action);
            for (const key in data) form.append(key, data[key]);

            const res = await fetch(window.location.href, { method: 'POST', body: form });
            return res.json();
        }

        // ---------- STEP 1: SEND OTP ----------
        const btnSendOtp = document.getElementById('btnSendOtp');

        btnSendOtp.addEventListener('click', async function () {
            hideAlert();
            const email = document.getElementById('email').value.trim();

            if (!email) {
                showAlert('Please enter your email address.', 'danger');
                return;
            }

            setLoading(btnSendOtp, true, ' Sending code...');

            try {
                const result = await callApi('send_otp', { email });

                if (result.status === 'success') {
                    document.getElementById('otpEmailLabel').textContent = email;
                    showStep(2);
                    startResendTimer();
                } else {
                    showAlert(result.message, 'danger');
                }
            } catch (e) {
                showAlert('Network error. Please try again.', 'danger');
            } finally {
                setLoading(btnSendOtp, false, 'Send Verification Code');
            }
        });

        // ---------- STEP 2: VERIFY OTP ----------
        const btnVerifyOtp = document.getElementById('btnVerifyOtp');

        btnVerifyOtp.addEventListener('click', async function () {
            hideAlert();
            const otp = document.getElementById('otp').value.trim();

            if (otp.length !== 6) {
                showAlert('Please enter the 6-digit code.', 'danger');
                return;
            }

            setLoading(btnVerifyOtp, true, ' Verifying...');

            try {
                const result = await callApi('verify_otp', { otp });

                if (result.status === 'success') {
                    showStep(3);
                } else {
                    showAlert(result.message, 'danger');
                }
            } catch (e) {
                showAlert('Network error. Please try again.', 'danger');
            } finally {
                setLoading(btnVerifyOtp, false, 'Verify Code');
            }
        });

        document.getElementById('btnChangeEmail').addEventListener('click', function () {
            hideAlert();
            showStep(1);
        });

        // ---------- RESEND OTP ----------
        const resendLink = document.getElementById('resendLink');
        const resendTimer = document.getElementById('resendTimer');
        let resendInterval;

        function startResendTimer() {
            let seconds = 45;
            resendLink.classList.add('disabled');

            clearInterval(resendInterval);
            resendInterval = setInterval(() => {
                seconds--;
                resendTimer.textContent = seconds > 0 ? ' (' + seconds + 's)' : '';
                if (seconds <= 0) {
                    clearInterval(resendInterval);
                    resendLink.classList.remove('disabled');
                }
            }, 1000);
        }

        resendLink.addEventListener('click', async function () {
            if (resendLink.classList.contains('disabled')) return;
            hideAlert();

            const email = document.getElementById('email').value.trim();
            resendLink.classList.add('disabled');

            const result = await callApi('send_otp', { email });
            showAlert(result.message, result.status === 'success' ? 'success' : 'danger');

            if (result.status === 'success') startResendTimer();
            else resendLink.classList.remove('disabled');
        });

        // ---------- STEP 3: REGISTER ----------
        const btnRegister = document.getElementById('btnRegister');

        btnRegister.addEventListener('click', async function () {
            hideAlert();

            const full_name = document.getElementById('full_name').value.trim();
            const phone     = document.getElementById('phone').value.trim();
            const password  = document.getElementById('password').value.trim();
            const terms     = document.getElementById('terms').checked;

            if (!full_name || !phone || !password) {
                showAlert('Please fill all fields.', 'danger');
                return;
            }
            if (!terms) {
                showAlert('Please accept the Terms & Conditions.', 'danger');
                return;
            }

            setLoading(btnRegister, true, ' Creating account...');

            try {
                const result = await callApi('register', { full_name, phone, password });
                 

                if (result.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Registration Successful',
                        text: result.message,
                        confirmButtonText: 'OK'
                    })
                    // .then(() => {
                    //     window.location = result.redirect || 'login';
                    // });
                    .then(() => {
    window.history.back();
});
                } else {
                    showAlert(result.message, 'danger');
                    setLoading(btnRegister, false, 'Create Account');
                }
            } catch (e) {
                showAlert('Network error. Please try again.', 'danger');
                setLoading(btnRegister, false, 'Create Account');
            }
        });

        // ---------- PASSWORD TOGGLE ----------
        const toggle = document.getElementById('togglePassword');
        const password = document.getElementById('password');

        toggle.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });

    })();
    </script>

</body>

</html>