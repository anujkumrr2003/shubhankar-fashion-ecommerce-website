<?php
session_start();
include 'config.php';

$message = "";
$type = "";

if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $redirect = trim($_POST['redirect'] ?? '');

    if (empty($email) || empty($password)) {

        $message = "Please enter Email and Password.";
        $type = "danger";
    } else {

        $stmt = $conn->prepare("SELECT * FROM user WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $user = $result->fetch_assoc();

            if ($user['status'] != "Active") {

                $message = "Your account is inactive.";
                $type = "warning";
            } elseif (password_verify($password, $user['password'])) {

                // Login Session
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_name']  = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];

                /* -------------------------------
   Auto Add To Cart After Login
-------------------------------- */

                if (isset($_GET['addcart'])) {

                    $product_id = (int)($_GET['product_id'] ?? 0);
                    $qty = (int)($_GET['qty'] ?? 1);
                    $variant_id = (int)($_GET['variant_id'] ?? 0);

                    if ($product_id > 0) {

                        $check = $conn->prepare("
            SELECT id, qty
            FROM cart
            WHERE user_id=? AND product_id=? AND variant_id=?
        ");

                        $check->bind_param(
                            "iii",
                            $_SESSION['user_id'],
                            $product_id,
                            $variant_id
                        );

                        $check->execute();

                        $result = $check->get_result();

                        if ($result->num_rows) {

                            $row = $result->fetch_assoc();

                            $newQty = $row['qty'] + $qty;

                            $update = $conn->prepare("
                UPDATE cart
                SET qty=?
                WHERE id=?
            ");

                            $update->bind_param(
                                "ii",
                                $newQty,
                                $row['id']
                            );

                            $update->execute();
                        } else {

                            $insert = $conn->prepare("
                INSERT INTO cart
                (user_id,product_id,variant_id,qty)
                VALUES(?,?,?,?)
            ");

                            $insert->bind_param(
                                "iiii",
                                $_SESSION['user_id'],
                                $product_id,
                                $variant_id,
                                $qty
                            );

                            $insert->execute();
                        }
                    }
                }

                // Redirect after login
                if (!empty($redirect)) {

                    // Security: allow only your own site URLs
                    if (strpos($redirect, 'http') === 0) {

                        $host = parse_url($redirect, PHP_URL_HOST);

                        if ($host == $_SERVER['HTTP_HOST']) {

                            header("Location: " . $redirect);
                            exit;
                        }
                    } else {

                        header("Location: " . $redirect);
                        exit;
                    }
                }

                // Default redirect
                $redirect = $_POST['redirect'] ?? '';

                if (!empty($redirect)) {

                    header("Location: " . $redirect);
                    exit;
                }

                header("Location:https://shubhankarfashion.com/in/");
                exit;
            } else {

                $message = "Incorrect password.";
                $type = "danger";
            }
        } else {

            $message = "Email not registered.";
            $type = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

   <title>Login | Shubhankar India</title>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta name="title" content="Login | Shubhankar India">

<meta name="description" content="Sign in securely to your Shubhankar India account to manage orders, addresses, profile and B2B purchases.">

<meta name="robots" content="noindex,nofollow,noarchive,nosnippet">

<link rel="canonical" href="https://shubhankarfashion.com/in/login">

<meta name="author" content="Shubhankar">
<meta name="publisher" content="Shubhankar">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="Shubhankar India">
<meta property="og:title" content="Login | Shubhankar India">
<meta property="og:description" content="Securely log in to your Shubhankar India account.">
<meta property="og:url" content="https://shubhankarfashion.com/in/login">
<meta property="og:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:secure_url" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:type" content="image/png">
<meta property="og:image:alt" content="Shubhankar India Logo">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Login | Shubhankar India">
<meta name="twitter:description" content="Securely log in to your Shubhankar India account.">
<meta name="twitter:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">

<!-- Favicon -->
<link rel="icon" type="image/png" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<link rel="shortcut icon" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<link rel="apple-touch-icon" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">

<meta name="theme-color" content="#000000">
<meta name="application-name" content="Shubhankar India">
<meta name="apple-mobile-web-app-title" content="Shubhankar India">

<script type="application/ld+json">
{
  "@context":"https://schema.org",
  "@type":"WebPage",
  "name":"Login",
  "url":"https://shubhankarfashion.com/in/login",
  "isPartOf":{
    "@type":"WebSite",
    "name":"Shubhankar India",
    "url":"https://shubhankarfashion.com/in/"
  }
}
</script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../css/style.css">

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
    </style>

</head>

<body>

    <?php include 'header.php'; ?>

    <section class="section">

        <div class="container" style="max-width:440px;">

            <div class="card-panel">

                <h2 class="text-center mb-2">Welcome Back</h2>

                <p class="text-center text-muted mb-4">
                    Login to your Shubhankar account
                </p>

                <?php if ($message != "") { ?>

                    <div class="alert alert-<?php echo $type; ?>">
                        <?php echo $message; ?>
                    </div>

                <?php } ?>

                <form method="POST">
                    <input
                        type="hidden"
                        name="redirect"
                        value="<?= htmlspecialchars($_GET['redirect'] ?? '') ?>">

                    <div class="mb-3">

                        <label>Email Address</label>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            required>

                    </div>

                    <div class="mb-3">

                        <label>Password</label>

                        <div class="password-box">

                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control"
                                required>

                            <i class="bi bi-eye" id="togglePassword"></i>

                        </div>

                    </div>

                    <div class="d-flex justify-content-between mb-3">

                         <a href="forgot-password.php" class="small">
Forgot Password?
</a> 

                    </div>

                    <button
                        type="submit"
                        name="login"
                        class="btn btn-brand w-100">

                        Login

                    </button>

                </form>

                <!--<p class="text-center mt-3">-->

                <!--    New here?-->

                <!--    <a href="register">-->
                <!--        Create an Account-->
                <!--    </a>-->

                <!--</p>-->
                
               <p class="text-center mt-3">

    New here?

    <a href="register?redirect=<?= urlencode($_GET['redirect'] ?? '') ?>&addcart=<?= urlencode($_GET['addcart'] ?? '') ?>&product_id=<?= urlencode($_GET['product_id'] ?? '') ?>&qty=<?= urlencode($_GET['qty'] ?? '') ?>&variant_id=<?= urlencode($_GET['variant_id'] ?? '') ?>&size=<?= urlencode($_GET['size'] ?? '') ?>&color=<?= urlencode($_GET['color'] ?? '') ?>">
        Create an Account
    </a>

</p>

            </div>

        </div>

    </section>

    <?php include 'footer.php'; ?>

    <script>
        const toggle = document.getElementById("togglePassword");
        const password = document.getElementById("password");

        toggle.addEventListener("click", function() {

            const type = password.type === "password" ? "text" : "password";

            password.type = type;

            this.classList.toggle("bi-eye");
            this.classList.toggle("bi-eye-slash");

        });
    </script>

</body>

</html>