<?php
session_start();
include 'config.php';

$message = "";
$type = "";

if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {

        $message = "Please enter Email and Password.";
        $type = "danger";
    } else {

        $stmt = $conn->prepare("SELECT * FROM user WHERE email=? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $user = $result->fetch_assoc();

            if ($user['status'] != "Active") {

                $message = "Your account is inactive.";
                $type = "warning";
            } elseif (password_verify($password, $user['password'])) {

                // $_SESSION['user_id'] = $user['id'];
                // $_SESSION['user_name'] = $user['full_name'];
                // $_SESSION['user_email'] = $user['email'];

                // header("Location: index.php");
                // exit;

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];

                if (!empty($_SESSION['redirect_url'])) {

                    $redirect = $_SESSION['redirect_url'];
                    unset($_SESSION['redirect_url']);

                    header("Location: " . $redirect);
                    exit;
                } else {

if (!empty($_SESSION['redirect_url'])) {

    $redirect = $_SESSION['redirect_url'];
    unset($_SESSION['redirect_url']);

    header("Location: " . $redirect);
    exit;

} else {

    header("Location: index");
    exit;

}
                    exit;
                }
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

   <title>Login | Shubhankar</title>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta name="title" content="Login | Shubhankar">
<meta name="description" content="Securely log in to your Shubhankar account to manage orders, wishlist, addresses and your profile.">

<meta name="author" content="Shubhankar">
<meta name="publisher" content="Shubhankar">

<!-- Prevent indexing -->
<meta name="robots" content="noindex,follow">
<meta name="googlebot" content="noindex,follow">
<meta name="bingbot" content="noindex,follow">

<link rel="canonical" href="https://shubhankarfashion.com/login">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="Shubhankar">
<meta property="og:title" content="Login | Shubhankar">
<meta property="og:description" content="Sign in securely to your Shubhankar account.">
<meta property="og:url" content="https://shubhankarfashion.com/login">
<meta property="og:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:secure_url" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:type" content="image/png">
<meta property="og:image:alt" content="Shubhankar Logo">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Login | Shubhankar">
<meta name="twitter:description" content="Securely access your Shubhankar account.">
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
  "name":"Login",
  "url":"https://shubhankarfashion.com/login",
  "description":"Secure login page for Shubhankar customer accounts.",
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
      "name":"Login",
      "item":"https://shubhankarfashion.com/login"
    }
  ]
}
</script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="css/style.css">

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

                <p class="text-center mt-3">

                    New here?

                    <a href="register">
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