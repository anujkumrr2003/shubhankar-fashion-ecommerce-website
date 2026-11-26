<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location:login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Order Submitted Successfully | Shubhankar India</title>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta name="title" content="Order Submitted Successfully | Shubhankar India">

<meta name="description" content="Your B2B enquiry has been submitted successfully. Thank you for choosing Shubhankar India. Our team will contact you shortly regarding your bulk order request.">

<meta name="robots" content="noindex,nofollow,noarchive,nosnippet">

<link rel="canonical" href="https://shubhankarfashion.com/in/b2b-success">

<meta name="author" content="Shubhankar">
<meta name="publisher" content="Shubhankar">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="Shubhankar India">
<meta property="og:title" content="Order Submitted Successfully | Shubhankar India">
<meta property="og:description" content="Your B2B enquiry has been submitted successfully.">
<meta property="og:url" content="https://shubhankarfashion.com/in/b2b-success">
<meta property="og:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:secure_url" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:type" content="image/png">
<meta property="og:image:alt" content="Shubhankar India Logo">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Order Submitted Successfully | Shubhankar India">
<meta name="twitter:description" content="Your B2B enquiry has been submitted successfully.">
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
  "name":"B2B Order Success",
  "url":"https://shubhankarfashion.com/in/b2b-success",
  "description":"Confirmation page displayed after a successful B2B enquiry submission.",
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


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons/css/flag-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">


    <style>
        body{
            background:#f8f9fa;
        }

        .success-box{
            max-width:650px;
            margin:80px auto;
            background:#fff;
            border-radius:12px;
            padding:50px;
            box-shadow:0 10px 30px rgba(0,0,0,.08);
            text-align:center;
        }

        .success-icon{
            width:90px;
            height:90px;
            border-radius:50%;
            background:#198754;
            color:#fff;
            font-size:45px;
            line-height:90px;
            margin:auto;
            margin-bottom:25px;
        }

        h2{
            font-weight:700;
        }

        p{
            /* color:#666; */
            margin-top:15px;
        }

        .btn-dark{
            margin-top:25px;
            padding:12px 35px;
        }
    </style>

</head>

<body>
<?php include "header.php"; ?>
<div class="container">

    <div class="success-box">

        <div class="success-icon">
            ✓
        </div>

        <h2>B2B Inquiry Submitted Successfully</h2>

        <p>
            Thank you for your inquiry.
            Our sales team has received your request and will contact you shortly
            with pricing, availability, and further details.
        </p>

        <div class="mt-4">

            <a href="categories" class="btn btn-dark text-white">
                Continue Shopping
            </a>

             

        </div>

    </div>

</div>
    <?php include "footer.php"; ?>
</body>
</html>