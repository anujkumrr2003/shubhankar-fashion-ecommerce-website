<?php

// Guarantee a session is active before anything else touches $_SESSION.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config.php';

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');

/*
|--------------------------------------------------------------------------
| AJAX ENDPOINT
| action=reset_password -> validates token + sets new password
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    header('Content-Type: application/json');
    $action = $_POST['action'];

    if ($action === 'reset_password') {

        $password        = trim($_POST['password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        if (empty($token)) {
            echo json_encode(['status' => 'error', 'message' => 'Missing or invalid reset link.']);
            exit;
        }

        if (strlen($password) < 6) {
            echo json_encode(['status' => 'error', 'message' => 'Password must be at least 6 characters.']);
            exit;
        }

        if ($password !== $confirmPassword) {
            echo json_encode(['status' => 'error', 'message' => 'Passwords do not match.']);
            exit;
        }

        $check = $conn->prepare("SELECT id FROM user WHERE reset_token=? AND reset_token_expiry > NOW()");
        $check->bind_param("s", $token);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'This reset link is invalid or has expired. Please request a new one.']);
            exit;
        }

        $row  = $result->fetch_assoc();
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $update = $conn->prepare("UPDATE user SET password=?, reset_token=NULL, reset_token_expiry=NULL WHERE id=?");
        $update->bind_param("si", $hash, $row['id']);

        if ($update->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Your password has been reset successfully.', 'redirect' => 'login']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Something went wrong. Please try again.']);
        }
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit;
}

// -------------------- VALIDATE TOKEN FOR PAGE LOAD --------------------
$tokenValid = false;

if (!empty($token)) {
    $check = $conn->prepare("SELECT id FROM user WHERE reset_token=? AND reset_token_expiry > NOW()");
    $check->bind_param("s", $token);
    $check->execute();
    $tokenValid = $check->get_result()->num_rows > 0;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Reset Password | Shubhankar</title>

    <meta name="robots" content="noindex,nofollow">
    <meta name="googlebot" content="noindex,nofollow">
    <meta name="bingbot" content="noindex,nofollow">

    <link rel="canonical" href="https://shubhankarfashion.com/in/reset-password">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">
    <link rel="shortcut icon" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">
    <link rel="apple-touch-icon" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">

    <meta name="theme-color" content="#000000">

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

        .btn-brand[disabled] {
            opacity: .7;
        }

        .spinner-border-sm-inline {
            width: 1rem;
            height: 1rem;
            border-width: .15em;
            margin-right: 6px;
        }

        .state-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            font-size: 32px;
        }

        .state-icon.err {
            background: #fdecea;
            color: #dc3545;
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

            <?php if (!$tokenValid) { ?>

                <!-- INVALID / EXPIRED TOKEN -->
                <div class="state-icon err"><i class="bi bi-x-circle"></i></div>

                <h2 class="text-center mb-2">Link invalid or expired</h2>
                <p class="text-center text-muted mb-4">
                    This password reset link is no longer valid. Please request a new one.
                </p>

                <a href="forgot-password" class="btn btn-brand w-100">Request New Link</a>

            <?php } else { ?>

                <input type="hidden" id="token" value="<?= htmlspecialchars($token) ?>">

                <h2 class="text-center mb-2">Set a new password</h2>
                <p class="text-center text-muted mb-4">Choose a strong password you haven't used before.</p>

                <div id="alertBox" class="alert d-none" role="alert"></div>

                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <div class="password-box">
                        <input type="password" id="password" class="form-control" placeholder="At least 6 characters" required>
                        <i class="bi bi-eye" id="togglePassword"></i>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Confirm Password</label>
                    <div class="password-box">
                        <input type="password" id="confirm_password" class="form-control" placeholder="Re-enter password" required>
                        <i class="bi bi-eye" id="toggleConfirmPassword"></i>
                    </div>
                </div>

                <button type="button" id="btnReset" class="btn btn-brand w-100">Reset Password</button>

            <?php } ?>

            <p class="text-center mt-3">
                Remembered your password? <a href="login">Login</a>
            </p>

        </div>

    </div>

</section>

<?php include 'footer.php'; ?>

<?php if ($tokenValid) { ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
(function () {

    const alertBox = document.getElementById('alertBox');

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

    // ---------- RESET PASSWORD ----------
    const btnReset = document.getElementById('btnReset');

    btnReset.addEventListener('click', async function () {
        hideAlert();

        const token            = document.getElementById('token').value;
        const password         = document.getElementById('password').value.trim();
        const confirm_password = document.getElementById('confirm_password').value.trim();

        if (password.length < 6) {
            showAlert('Password must be at least 6 characters.', 'danger');
            return;
        }

        if (password !== confirm_password) {
            showAlert('Passwords do not match.', 'danger');
            return;
        }

        setLoading(btnReset, true, ' Resetting...');

        try {
            const result = await callApi('reset_password', { token, password, confirm_password });

            if (result.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Password Reset',
                    text: result.message,
                    confirmButtonText: 'Login now'
                }).then(() => {
                    window.location = result.redirect || 'login';
                });
            } else {
                showAlert(result.message, 'danger');
                setLoading(btnReset, false, 'Reset Password');
            }
        } catch (e) {
            showAlert('Network error. Please try again.', 'danger');
            setLoading(btnReset, false, 'Reset Password');
        }
    });

    // ---------- PASSWORD TOGGLES ----------
    function bindToggle(iconId, inputId) {
        const icon  = document.getElementById(iconId);
        const input = document.getElementById(inputId);

        icon.addEventListener('click', function () {
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
    }

    bindToggle('togglePassword', 'password');
    bindToggle('toggleConfirmPassword', 'confirm_password');

})();
</script>
<?php } ?>

</body>

</html>