<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}







$user_id = $_SESSION['user_id'];

$sql = mysqli_query($conn, "SELECT * FROM user WHERE id='$user_id' LIMIT 1");

if (mysqli_num_rows($sql) == 0) {
    die('
    <div style="
        max-width:500px;
        margin:80px auto;
        padding:30px;
        background:#fff3f3;
        border:1px solid #f5c2c7;
        border-radius:10px;
        text-align:center;
        font-family:Arial,sans-serif;
        box-shadow:0 4px 15px rgba(0,0,0,.08);
    ">
        <h3 style="margin:0 0 10px;color:#dc3545;">
            User Not Found
        </h3>

        <p style="margin:0 0 20px;color:#555;">
            Your account could not be found. Please login to continue.
        </p>

        <a href="login"
           style="
                display:inline-block;
                padding:10px 22px;
                background:#000;
                color:#fff;
                text-decoration:none;
                border-radius:6px;
                font-weight:600;
           ">
            Login Now
        </a>
    </div>
    ');
}

$user = mysqli_fetch_assoc($sql);
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>My Profile | Shubhankar India</title>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta name="title" content="My Profile | Shubhankar India">

<meta name="description" content="Manage your Shubhankar India account, personal information, saved addresses and account settings securely.">

<meta name="robots" content="noindex,nofollow,noarchive,nosnippet">

<link rel="canonical" href="https://shubhankarfashion.com/in/my-profile">

<meta name="author" content="Shubhankar">
<meta name="publisher" content="Shubhankar">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="Shubhankar India">
<meta property="og:title" content="My Profile | Shubhankar India">
<meta property="og:description" content="Manage your Shubhankar India account securely.">
<meta property="og:url" content="https://shubhankarfashion.com/in/my-profile">
<meta property="og:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:secure_url" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:type" content="image/png">
<meta property="og:image:alt" content="Shubhankar India Logo">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="My Profile | Shubhankar India">
<meta name="twitter:description" content="Manage your Shubhankar India account securely.">
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
  "name":"My Profile",
  "url":"https://shubhankarfashion.com/in/my-profile",
  "isPartOf":{
    "@type":"WebSite",
    "name":"Shubhankar India",
    "url":"https://shubhankarfashion.com/in/"
  }
}
</script>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Site styles -->
    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

    <?php include 'header.php'; ?>




    <style>
        .profile-wrapper {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
            font-family: inherit;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e5e5;
        }

        .profile-avatar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: #f0eee8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 600;
            color: #333;
        }

        .profile-name h2 {
            margin: 0 0 5px 0;
            font-size: 22px;
        }

        .profile-name p {
            margin: 0;
            color: #777;
            font-size: 14px;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        @media (max-width: 640px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
        }

        .profile-card {
            background: #fafafa;
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 20px;
        }

        .profile-card h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #444;
        }

        .profile-field {
            margin-bottom: 12px;
        }

        .profile-field label {
            display: block;
            font-size: 12px;
            color: #888;
            margin-bottom: 3px;
        }

        .profile-field span {
            font-size: 15px;
            color: #222;
        }

        .profile-actions {
            display: flex;
            gap: 12px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: #1a1a2e;
            color: #fff;
            padding: 10px 22px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            border: none;
            cursor: pointer;
            display: inline-block;
        }

        .btn-outline {
            background: transparent;
            color: #1a1a2e;
            padding: 10px 22px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            border: 1px solid #1a1a2e;
            cursor: pointer;
            display: inline-block;
        }
    </style>

    <div class="profile-wrapper">

        <!-- Profile Header -->
        <div class="profile-header">

            <div class="profile-avatar">

               <?php
$image = "uploads/profile/" . $user['profile_image'];

if (!empty($user['profile_image']) && file_exists($image)) {
?>
    <img src="<?= $image ?>?v=<?= time(); ?>"
         width="90"
         height="90"
         style="border-radius:50%;object-fit:cover;">
<?php
} else {
?>
    <?= strtoupper(substr($user['full_name'],0,1)); ?>
<?php
}
?>

            </div>

            <div class="profile-name">
                <h2><?= htmlspecialchars($user['full_name']); ?></h2>
                <p><?= htmlspecialchars($user['email']); ?></p>
            </div>

        </div>



        <form action="update-profile.php"
            method="POST"
            enctype="multipart/form-data">
            <div class="profile-grid">

                <!-- Personal Details -->
                <div class="profile-card">

                    <h3>Personal Details</h3>

                    <div class="profile-field">

                        <label>Profile Image</label>

                        <input
                            type="file"
                            name="profile_image"
                            class="form-control">

                    </div>

                    <div class="profile-field">

                        <label>Full Name</label>

                        <input
                            type="text"
                            name="full_name"
                            class="form-control"
                            value="<?= htmlspecialchars($user['full_name']); ?>">

                    </div>

                    <div class="profile-field">

                        <label>Email</label>

                        <input
                            type="email"
                            class="form-control"
                            value="<?= htmlspecialchars($user['email']); ?>"
                            readonly>

                    </div>

                    <div class="profile-field">

                        <label>Phone</label>

                        <input
                            type="text"
                            name="phone"
                            class="form-control"
                            value="<?= htmlspecialchars($user['phone']); ?>">

                    </div>

                </div>

                <!-- Shipping Address -->
                <div class="profile-card">

                    <h3>Shipping Address</h3>

                    <div class="profile-field">

                        <label>Address</label>

                        <textarea
                            name="address"
                            class="form-control"
                            rows="1"><?= htmlspecialchars($user['address']); ?></textarea>

                    </div>

                    <div class="profile-field">

                        <label>City</label>

                        <input
                            type="text"
                            name="city"
                            class="form-control"
                            value="<?= htmlspecialchars($user['city']); ?>">

                    </div>

                    <div class="profile-field">

                        <label>Address Line </label>

                        <input
                            type="text"
                            name="state"
                            class="form-control"
                            value="<?= htmlspecialchars($user['state']); ?>">

                    </div>

                    <div class="profile-field">

                        <label>Country</label>

                        <input
                            type="text"
                            name="country"
                            class="form-control"
                            value="<?= htmlspecialchars($user['country']); ?>">

                    </div>

                    <div class="profile-field">

                        <label>Zip</label>

                        <input
                            type="text"
                            name="pincode"
                            class="form-control"
                            value="<?= htmlspecialchars($user['pincode']); ?>">

                    </div>

                </div>

            </div>

 <?php if (isset($_SESSION['success'])) { ?>
                <div class="alert alert-success">
                    <?= $_SESSION['success']; ?>
                </div>
            <?php unset($_SESSION['success']);
            } ?>

            <?php if (isset($_SESSION['error'])) { ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['error']; ?>
                </div>
            <?php unset($_SESSION['error']);
            } ?>


            <div class="text-center mt-4">

                <button
                    type="submit"
                    class="btn btn-dark px-5">

                    <i class="bi bi-save"></i>

                    Update Profile

                </button>

            </div>

           
        </form>



    </div>

    <!-- ============ FOOTER ============ -->
    <?php include 'footer.php'; ?>

</body>

</html>