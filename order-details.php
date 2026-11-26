<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location:login.php");
    exit;
}

if (!isset($_GET['order'])) {
    header("Location:my-orders.php");
    exit;
}

$user_id  = $_SESSION['user_id'];
$order_id = (int)$_GET['order'];

$orderQuery = mysqli_query($conn, "
SELECT *
FROM orders
WHERE id='$order_id'
AND user_id='$user_id'
LIMIT 1
");

if (mysqli_num_rows($orderQuery) == 0) {
    die("Order not found.");
}

$order = mysqli_fetch_assoc($orderQuery);

// Base item info + base product image/name as a fallback.
$itemQuery = mysqli_query($conn, "
SELECT
    oi.*,
    p.name,
    p.image AS product_image
FROM order_items oi
LEFT JOIN products p
ON p.id = oi.product_id
WHERE oi.order_id='$order_id'
");

// For any line that has a variant_id, look up the variant's name + image
// separately (same pattern used on cart.php / checkout.php). We deliberately
// do NOT re-fetch variant_price here -- oi.price already holds what the
// customer was actually charged at order time, and that must never change
// even if the variant's price changes later.
$variantStmt = $conn->prepare("
    SELECT variant_name, images
    FROM product_variants
    WHERE id=? AND product_id=?
    LIMIT 1
");

$items = [];

while ($item = mysqli_fetch_assoc($itemQuery)) {

    $item['isVariant']    = false;
    $item['variant_name'] = null;
    $item['display_image'] = "admin/uploads/" . $item['product_image'];

    if ((int)($item['variant_id'] ?? 0) > 0) {

        $vId = (int)$item['variant_id'];
        $pId = (int)$item['product_id'];

        $variantStmt->bind_param("ii", $vId, $pId);
        $variantStmt->execute();
        $vResult = $variantStmt->get_result();

        if ($vResult && $vResult->num_rows > 0) {

            $vRow = $vResult->fetch_assoc();

            $item['isVariant']    = true;
            $item['variant_name'] = $vRow['variant_name'];

            $variantImgs     = explode(',', $vRow['images']);
            $firstVariantImg = trim($variantImgs[0]);

            if ($firstVariantImg !== '') {
                $item['display_image'] = "admin/img/" . $firstVariantImg;
            }
        }
        // If the variant no longer exists, silently falls back to the base
        // product image/name set above.
    }

    $items[] = $item;
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Shubhankar</title>
    <meta name="description" content="Manage your Shubhankar account profile.">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Site styles -->
    <link rel="stylesheet" href="css/style.css">
<link rel="icon" type="image/png" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">
</head>

<body>

    <?php include 'header.php'; ?>


    <style>
        .box {
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .product-img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #eee;
        }
    </style>


    <div class="container py-5">

        <a href="my-orders.php" class="btn btn-outline-dark mb-4 text-secondry">
            ← Back to Orders
        </a>

        <div class="box">

            <h3 class="mb-3">
                Order Details
            </h3>

            <div class="row">

                <div class="col-md-6">

                    <p><strong>Order No :</strong> <?= $order['order_no']; ?></p>

                    <p><strong>Order Date :</strong>
                        <?= date("d M Y h:i A", strtotime($order['created_at'])); ?>
                    </p>

                    <p><strong>Payment Status :</strong>
                        <?= $order['payment_status']; ?>
                    </p>

                    <p><strong>Order Status :</strong>
                        <?= $order['order_status']; ?>
                    </p>

                </div>

                <div class="col-md-6">

                    <p><strong>Name :</strong>
                        <?= $order['full_name']; ?>
                    </p>

                    <p><strong>Email :</strong>
                        <?= $order['email']; ?>
                    </p>

                    <p><strong>Phone :</strong>
                        <?= $order['phone']; ?>
                    </p>

                    <p><strong>Address :</strong><br>

                        <?= $order['address']; ?><br>

                        <?= $order['city']; ?>,
                        <?= $order['state']; ?><br>

                        <?= $order['country']; ?> -
                        <?= $order['pincode']; ?>

                    </p>

                </div>

            </div>

        </div>

        <div class="box">

            <h4 class="mb-3">
                Products
            </h4>

            <div class="table-responsive">

                <table class="table align-middle">

                    <thead>
<tr>
    <th>Image</th>
    <th>Product</th>
    <th>Size</th>
    <th>Color</th>
    <th>Price</th>
    <th>Qty</th>
    <th>Total</th>
</tr>
</thead>

                    <tbody>

                        <?php foreach ($items as $item) { ?>

                            <?php $lineTotal = $item['price'] * $item['qty']; ?>

                            <tr>

                                <td>

                                    <img
                                        src="<?= htmlspecialchars($item['display_image']); ?>"
                                        class="product-img">

                                </td>

                                <td>

    <?= htmlspecialchars($item['name']); ?>

    <?php if ($item['isVariant']) { ?>
        <br>
        <small class="text-muted">
            <?= htmlspecialchars($item['variant_name']); ?>
        </small>
    <?php } ?>

</td>

<td>
    <?= htmlspecialchars($item['size']); ?>
</td>

<td>
    <span style="
        display:inline-block;
        width:14px;
        height:14px;
        border-radius:50%;
        border:1px solid #ccc;
        background:<?= htmlspecialchars($item['color']); ?>;
        vertical-align:middle;
        margin-right:6px;
    "></span>

    <?= htmlspecialchars($item['color']); ?>
</td>
                                <td>

                                    <span>







                                        <?= $order['currency']; ?>
                                        <?= number_format($item['price'], 2); ?>
                                    </span>

                                </td>

                                <td>

                                    <?= $item['qty']; ?>

                                </td>

                                <td>

                                    <span>


                                     

                                        <?= $order['currency']; ?>
                                        <?= number_format($lineTotal, 2); ?>

                                    </span>

                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

        <div class="box">

            <h4 class="mb-3">

                Payment Summary

            </h4>

            <table class="table">

                <tr>

                    <th>Subtotal</th>

                    <td class="text-end">

                        <span>


                            <?= $order['currency']; ?>
                            <?= number_format($order['subtotal'], 2); ?>

                        </span>

                    </td>

                </tr>

                <tr>

                    <th>Discount</th>

                    <td class="text-end">

                        <span>


                            <?= $order['currency']; ?>

                            <?= number_format($order['discount'], 2); ?>


                        </span>

                    </td>

                </tr>

                <tr>

                    <th>Total</th>

                    <td class="text-end fw-bold">

                        <span>

                            <?= $order['currency']; ?>

                            <?= number_format($order['total'], 2); ?>

                        </span>

                    </td>

                </tr>

            </table>

        </div>

    </div>



    <!-- <script>
        document.addEventListener("DOMContentLoaded", function() {

            if (typeof NEH !== "undefined") {

                NEH.updatePriceEls();

            }

        });

        document.addEventListener("neh:localechange", function() {

            if (typeof NEH !== "undefined") {

                NEH.updatePriceEls();

            }

        });
    </script> -->




    <!-- ============ FOOTER ============ -->
    <?php include 'footer.php'; ?>

</body>

</html>