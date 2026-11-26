<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = mysqli_query($conn, "
SELECT *
FROM orders
WHERE user_id='$user_id'
ORDER BY id DESC
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | Shubhankar</title>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta name="title" content="My Orders | Shubhankar">

<meta name="description" content="View your order history, track current orders and manage your purchases securely in your Shubhankar account.">

<meta name="robots" content="noindex,nofollow,noarchive,nosnippet">

<link rel="canonical" href="https://shubhankarfashion.com/my-orders">

<meta name="author" content="Shubhankar">
<meta name="publisher" content="Shubhankar">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="Shubhankar">
<meta property="og:title" content="My Orders | Shubhankar">
<meta property="og:description" content="View and manage your Shubhankar orders securely.">
<meta property="og:url" content="https://shubhankarfashion.com/my-orders">
<meta property="og:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:secure_url" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:type" content="image/png">
<meta property="og:image:alt" content="Shubhankar Logo">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="My Orders | Shubhankar">
<meta name="twitter:description" content="Access your Shubhankar order history securely.">
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
  "name":"My Orders",
  "url":"https://shubhankarfashion.com/my-orders",
  "isPartOf":{
    "@type":"WebSite",
    "name":"Shubhankar",
    "url":"https://shubhankarfashion.com/"
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
    <link rel="stylesheet" href="css/style.css">
        <style>
        .order-box {
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 10px;
            padding: 20px;
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
        }
    </style>


</head>

<body>

    <?php include 'header.php'; ?>





   

      

        <div class="container py-5">

            <h2 class="mb-4">My Orders</h2>

            <div class="order-box">

                <?php
                if (mysqli_num_rows($sql) > 0) {
                ?>

                    <div class="table-responsive">

                        <table class="table table-bordered align-middle">

                            <thead>

                                <tr>

                                    <th>#</th>
                                    <th>Order No</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Payment</th>
                                    <th>Order Status</th>
                                    <th>Action</th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php

                                $i = 1;

                                while ($row = mysqli_fetch_assoc($sql)) {

                                    $paymentColor = "secondary";

                                    if ($row['payment_status'] == "Paid") {
                                        $paymentColor = "success";
                                    } elseif ($row['payment_status'] == "Pending") {
                                        $paymentColor = "warning";
                                    } elseif ($row['payment_status'] == "Failed") {
                                        $paymentColor = "danger";
                                    }

                                    $orderColor = "secondary";

                                    switch ($row['order_status']) {

                                        case "Pending":
                                            $orderColor = "warning";
                                            break;

                                        case "Confirmed":
                                            $orderColor = "info";
                                            break;

                                        case "Processing":
                                            $orderColor = "primary";
                                            break;

                                        case "Shipped":
                                            $orderColor = "primary";
                                            break;

                                        case "Delivered":
                                            $orderColor = "success";
                                            break;

                                        case "Cancelled":
                                            $orderColor = "danger";
                                            break;
                                    }

                                ?>

                                    <tr>

                                        <td><?= $i++; ?></td>

                                        <td><?= $row['order_no']; ?></td>

                                        <td><?= date("d M Y", strtotime($row['created_at'])); ?></td>

                                        <td>

                                            <?= $row['currency']; ?>

                                            <?= number_format($row['total'], 2); ?>

                                        </td>

                                        <td>

                                            <span class="badge bg-<?= $paymentColor; ?>">

                                                <?= $row['payment_status']; ?>

                                            </span>

                                        </td>

                                        <td>

                                            <span class="badge bg-<?= $orderColor; ?>">

                                                <?= $row['order_status']; ?>

                                            </span>

                                        </td>

                                        <td>

                                            <a href="order-details.php?order=<?= $row['id']; ?>" class="btn btn-sm btn-dark text-white">

                                                View

                                            </a>

                                        </td>

                                    </tr>

                                <?php
                                }
                                ?>

                            </tbody>

                        </table>

                    </div>

                <?php
                } else {
                ?>

                    <div class="text-center py-5">

                        <h5>No Orders Found</h5>

                        <p>You have not placed any orders yet.</p>

                        <a href="shop.php" class="btn btn-dark text-white">

                            Start Shopping

                        </a>

                    </div>

                <?php
                }
                ?>

            </div>

        </div>



        <!-- ============ FOOTER ============ -->
        <?php include 'footer.php'; ?>

    </body>

</html>