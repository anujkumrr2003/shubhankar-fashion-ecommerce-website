<?php

// Guarantee a session is active before anything else touches $_SESSION.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
| Flow: send_reset_link -> (user opens link in email) -> reset-password.php
|--------------------------------------------------------------------------
*/

/**
 * Builds the reset link and emails it to $email.
 * Returns ['status' => 'success'|'error', 'message' => '...'].
 * Reused by both the initial send and the "resend" path.
 */
function issueResetLink(mysqli $conn, string $email, string $fullName): array
{
    $token   = bin2hex(random_bytes(32));
    $expiry  = date('Y-m-d H:i:s', time() + 1800); // 30 minutes

    $update = $conn->prepare("UPDATE user SET reset_token=?, reset_token_expiry=? WHERE email=?");
    $update->bind_param("sss", $token, $expiry, $email);

    if (!$update->execute()) {
        return ['status' => 'error', 'message' => 'Could not generate reset link. Please try again.'];
    }

    $scheme     = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $resetLink  = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/in/reset-password?token=' . $token;

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'vipanujkumarlmp@gmail.com';
        $mail->Password   = 'xwju bgei lyfm gghg';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('vipanujkumarlmp@gmail.com', 'Shubhankar');
        $mail->addAddress($email, $fullName);

        $mail->isHTML(true);
        $mail->Subject = "Reset your Shubhankar password";
        $mail->Body = "
            <div style='font-family:Arial,sans-serif;max-width:480px;margin:auto'>
                <h2 style='color:#111'>Reset your password</h2>
                <p>Hello" . ($fullName ? ' ' . htmlspecialchars($fullName) : '') . ",</p>
                <p>We received a request to reset your Shubhankar account password. Click the button below to choose a new one. This link is valid for 30 minutes.</p>
                <p style='margin:28px 0'>
                    <a href='{$resetLink}' style='background:#111;color:#fff;text-decoration:none;
                        padding:14px 28px;border-radius:8px;font-weight:600;display:inline-block'>
                        Reset Password
                    </a>
                </p>
                <p style='color:#777;font-size:13px'>If the button doesn't work, copy and paste this link into your browser:<br>{$resetLink}</p>
                <p style='color:#777;font-size:13px'>If you didn't request this, you can safely ignore this email — your password will remain unchanged.</p>
            </div>
        ";

        $mail->send();

        return ['status' => 'success', 'message' => 'Reset link sent to ' . htmlspecialchars($email)];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => 'Could not send the reset link right now. Please try again.'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    header('Content-Type: application/json');
    $action = $_POST['action'];

    // -------------------- SEND / RESEND RESET LINK --------------------
    if ($action === 'send_reset_link') {

        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address.']);
            exit;
        }

        $check = $conn->prepare("SELECT id, full_name FROM user WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'No account found with this email address.']);
            exit;
        }

        $row = $result->fetch_assoc();

        echo json_encode(issueResetLink($conn, $email, $row['full_name'] ?? ''));
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

    <title>Forgot Password | Shubhankar</title>

    <meta name="title" content="Forgot Password | Shubhankar">
    <meta name="description" content="Reset your Shubhankar account password securely via email.">

    <meta name="author" content="Shubhankar">
    <meta name="publisher" content="Shubhankar">

    <!-- Prevent Search Engine Indexing -->
    <meta name="robots" content="noindex,follow">
    <meta name="googlebot" content="noindex,follow">
    <meta name="bingbot" content="noindex,follow">

    <link rel="canonical" href="https://shubhankarfashion.com/forgot-password">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">
    <link rel="shortcut icon" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">
    <link rel="apple-touch-icon" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">

    <meta name="theme-color" content="#000000">
    <meta name="application-name" content="Shubhankar">
    <meta name="apple-mobile-web-app-title" content="Shubhankar">
    <meta name="format-detection" content="telephone=no">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

    <style>
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

        .sent-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: #f4f4f4;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            font-size: 32px;
            color: #198754;
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

        </div>
    </div>
</header>

<section class="section">

    <div class="container" style="max-width:520px;">

        <div class="card-panel">

            <!-- STEP 1: EMAIL -->
            <div class="step active" id="step-1">

                <h2 class="text-center mb-2">Forgot your password?</h2>
                <p class="text-center text-muted mb-4">Enter your registered email and we'll send you a reset link.</p>

                <div id="alertBox" class="alert d-none" role="alert"></div>

                <div class="mb-3">
                    <label class="form-label">Email address</label>
                    <input type="email" id="email" class="form-control" placeholder="you@example.com" required>
                </div>

                <button type="button" id="btnSendLink" class="btn btn-brand w-100">
                    Send Reset Link
                </button>

                <p class="text-center mt-3">
                    Remembered your password? <a href="login">Login</a>
                </p>

            </div>

            <!-- STEP 2: LINK SENT -->
            <div class="step" id="step-2">

                <div class="sent-icon"><i class="bi bi-envelope-check"></i></div>

                <h2 class="text-center mb-2">Check your email</h2>
                <p class="text-center text-muted mb-4">
                    We've sent a password reset link to <strong id="sentEmailLabel"></strong>.
                    The link expires in 30 minutes.
                </p>

                <p class="resend-note text-center mb-3">
                    Didn't get it? <a id="resendLink">Resend link</a>
                    <span id="resendTimer"></span>
                </p>

                <button type="button" id="btnChangeEmail" class="btn btn-outline-secondary w-100">
                    Use a different email
                </button>

            </div>

            <p class="text-center mt-3">
                Don't have an account? <a href="register">Create one</a>
            </p>

        </div>

    </div>

</section>

<?php include 'footer.php'; ?>

<script>
(function () {

    const alertBox = document.getElementById('alertBox');

    function showStep(n) {
        document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
        document.getElementById('step-' + n).classList.add('active');
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

    // ---------- STEP 1: SEND RESET LINK ----------
    const btnSendLink = document.getElementById('btnSendLink');

    btnSendLink.addEventListener('click', async function () {
        hideAlert();
        const email = document.getElementById('email').value.trim();

        if (!email) {
            showAlert('Please enter your email address.', 'danger');
            return;
        }

        setLoading(btnSendLink, true, ' Sending link...');

        try {
            const result = await callApi('send_reset_link', { email });

            if (result.status === 'success') {
                document.getElementById('sentEmailLabel').textContent = email;
                showStep(2);
                startResendTimer();
            } else {
                showAlert(result.message, 'danger');
            }
        } catch (e) {
            showAlert('Network error. Please try again.', 'danger');
        } finally {
            setLoading(btnSendLink, false, 'Send Reset Link');
        }
    });

    document.getElementById('btnChangeEmail').addEventListener('click', function () {
        hideAlert();
        showStep(1);
    });

    // ---------- RESEND LINK ----------
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

        const email = document.getElementById('email').value.trim();
        resendLink.classList.add('disabled');

        const result = await callApi('send_reset_link', { email });

        if (result.status === 'success') {
            startResendTimer();
        } else {
            showAlert(result.message, 'danger');
            resendLink.classList.remove('disabled');
        }
    });

})();
</script>

</body>

</html>