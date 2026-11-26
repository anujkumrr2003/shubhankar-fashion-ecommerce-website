<?php

session_start();
include "config.php";

/*
|--------------------------------------------------------------------------
| Check Order Session
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['order_no'])) {
    header("Location: https://shubhankarfashion.com/");
    exit;
}

$order_no = mysqli_real_escape_string($conn, $_SESSION['order_no']);

/*
|--------------------------------------------------------------------------
| Get Order
|--------------------------------------------------------------------------
*/

$sql = mysqli_query($conn, "
    SELECT *
    FROM orders
    WHERE order_no='$order_no'
    LIMIT 1
");

if (!$sql || mysqli_num_rows($sql) == 0) {
    die("Order not found.");
}

$order = mysqli_fetch_assoc($sql);

$order_id = (int)$order['id'];
$user_id  = (int)$order['user_id'];

/*
|--------------------------------------------------------------------------
| Get Order Items
|--------------------------------------------------------------------------
*/

$order_items = mysqli_query($conn, "
    SELECT
        oi.*,
        p.name,
        p.image
    FROM order_items oi
    LEFT JOIN products p
        ON p.id = oi.product_id
    WHERE oi.order_id = '$order_id'
");

/*
|--------------------------------------------------------------------------
| Final Order Values
|--------------------------------------------------------------------------
|
| These values are already FINAL values saved by save-order.php.
| Do not convert or modify them again.
|
*/

$subtotal = (float)$order['subtotal'];
$discount = (float)$order['discount'];
$total    = (float)$order['total'];

$currency = strtoupper(trim($order['currency']));

/*
|--------------------------------------------------------------------------
| Empty Cart
|--------------------------------------------------------------------------
*/

mysqli_query($conn, "
    DELETE FROM cart
    WHERE user_id='$user_id'
");

/*
|--------------------------------------------------------------------------
| Clear Checkout Session
|--------------------------------------------------------------------------
*/

unset($_SESSION['order_id']);
unset($_SESSION['order_no']);
unset($_SESSION['coupon']);
unset($_SESSION['discount']);

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Order Success | Royal Blue</title>

    <meta
        name="title"
        content="Order Success | Royal Blue"
    >

    <meta
        name="description"
        content="Your order has been successfully placed with Royal Blue. Thank you for shopping with us."
    >

    <meta
        name="robots"
        content="noindex, follow"
    >

    <link
        rel="canonical"
        href="https://shubhankarfashion.com/order-success"
    >

    <link
        rel="icon"
        type="image/png"
        href="https://shubhankarfashion.com/img/logo-removebg-preview.png"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
    >

    <link
        rel="stylesheet"
        href="css/style.css"
    >

    <style>

        .order-summary-table th,
        .order-summary-table td {
            vertical-align: middle;
        }

        .product-image {
            width: 65px;
            height: 65px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #eee;
        }

        .color-dot {
            display: inline-block;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 1px solid #999;
            vertical-align: middle;
            margin-right: 6px;
        }

        @media (max-width: 767px) {

            .order-summary-table {
                min-width: 700px;
            }

        }

    </style>

</head>

<body>

<?php include 'header.php'; ?>


<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-10">

            <div class="card shadow border-0">

                <div class="card-body p-4 p-md-5">


                    <!-- =========================================
                         SUCCESS MESSAGE
                    ========================================== -->

                    <div class="text-center mb-4">

                        <div class="mb-3">

                            <i
                                class="bi bi-check-circle-fill text-success"
                                style="font-size:70px;"
                            ></i>

                        </div>

                        <h2 class="text-success mb-2">
                            Thank You!
                        </h2>

                        <p class="text-muted mb-0">
                            Your order has been placed successfully.
                        </p>

                    </div>


                    <hr>


                    <!-- =========================================
                         ORDER INFORMATION
                    ========================================== -->

                    <h4 class="mb-3">
                        Order Details
                    </h4>

                    <div class="table-responsive">

                        <table class="table align-middle">

                            <tbody>

                                <tr>

                                    <th style="width:35%;">
                                        Order Number
                                    </th>

                                    <td>
                                        <?= htmlspecialchars($order['order_no']); ?>
                                    </td>

                                </tr>


                                <tr>

                                    <th>
                                        Payment Status
                                    </th>

                                    <td>

                                        <?= htmlspecialchars(
                                            $order['payment_status']
                                        ); ?>

                                    </td>

                                </tr>


                                <tr>

                                    <th>
                                        Order Status
                                    </th>

                                    <td>

                                        <?php

                                        if ($order['order_status'] == 'Pending') {

                                            echo 'Order Placed';

                                        } else {

                                            echo htmlspecialchars(
                                                $order['order_status']
                                            );

                                        }

                                        ?>

                                    </td>

                                </tr>


                                <tr>

                                    <th>
                                        Customer
                                    </th>

                                    <td>
                                        <?= htmlspecialchars($order['full_name']); ?>
                                    </td>

                                </tr>


                                <tr>

                                    <th>
                                        Email
                                    </th>

                                    <td>
                                        <?= htmlspecialchars($order['email']); ?>
                                    </td>

                                </tr>


                                <tr>

                                    <th>
                                        Phone
                                    </th>

                                    <td>
                                        <?= htmlspecialchars($order['phone']); ?>
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>


                    <hr>


                    <!-- =========================================
                         ORDER SUMMARY
                         PRODUCT + SIZE + COLOR + QTY + PRICE
                         ALL INSIDE SAME SECTION
                    ========================================== -->

                    <h4 class="mb-3">
                        Order Summary
                    </h4>


                    <div class="table-responsive">

                        <table class="table table-bordered align-middle order-summary-table">

                            <thead class="table-dark">

                                <tr>

                                    <th>
                                        Product
                                    </th>

                                    <th>
                                        Size
                                    </th>

                                    <th>
                                        Color
                                    </th>

                                    <th>
                                        Qty
                                    </th>

                                    <th>
                                        Price
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                            <?php

                            if ($order_items && mysqli_num_rows($order_items) > 0):

                                while ($item = mysqli_fetch_assoc($order_items)):

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Color
                                    |--------------------------------------------------------------------------
                                    */

                                    $color = strtolower(
                                        trim((string)$item['color'])
                                    );


                                    $colorMap = [

                                        "black"    => "#000000",
                                        "white"    => "#ffffff",
                                        "red"      => "#ff0000",
                                        "blue"     => "#0066ff",
                                        "green"    => "#28a745",
                                        "yellow"   => "#ffd700",
                                        "orange"   => "#ff7f00",
                                        "pink"     => "#ff69b4",
                                        "purple"   => "#800080",
                                        "brown"    => "#8b4513",
                                        "grey"     => "#808080",
                                        "gray"     => "#808080",
                                        "navy"     => "#001f54",
                                        "maroon"   => "#800000",
                                        "olive"    => "#808000",
                                        "sky blue" => "#87ceeb",
                                        "beige"    => "#f5f5dc",
                                        "cream"    => "#fffdd0",
                                        "gold"     => "#d4af37",
                                        "silver"   => "#c0c0c0"

                                    ];


                                    $colorCode =
                                        $colorMap[$color] ?? $color;


                                    /*
                                    |--------------------------------------------------------------------------
                                    | IMPORTANT
                                    |--------------------------------------------------------------------------
                                    |
                                    | order_items.price is already the FINAL saved
                                    | item price.
                                    |
                                    | Do NOT apply:
                                    |
                                    | +20%
                                    | +70%
                                    | exchange rate
                                    |
                                    | again here.
                                    |
                                    */

                                    $itemPrice =
                                        (float)$item['price'];

                            ?>

                                <tr>


                                    <!-- PRODUCT -->

                                    <td>

                                        <div class="d-flex align-items-center">

                                            <?php if (!empty($item['image'])): ?>

                                                <img
                                                    src="admin/uploads/<?= htmlspecialchars($item['image']); ?>"
                                                    class="product-image me-3"
                                                    alt="<?= htmlspecialchars($item['name']); ?>"
                                                >

                                            <?php endif; ?>


                                            <div>

                                                <strong>
                                                    <?= htmlspecialchars(
                                                        $item['name']
                                                    ); ?>
                                                </strong>

                                            </div>

                                        </div>

                                    </td>


                                    <!-- SIZE -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $item['size']
                                        ); ?>

                                    </td>


                                    <!-- COLOR -->

                                    <td>

                                        <span
                                            class="color-dot"
                                            style="
                                                background:<?= htmlspecialchars($colorCode); ?>;
                                            "
                                        ></span>

                                        <?= htmlspecialchars(
                                            $item['color']
                                        ); ?>

                                    </td>


                                    <!-- QTY -->

                                    <td>

                                        <?= (int)$item['qty']; ?>

                                    </td>


                                    <!-- PRICE -->

                                    <td>

                                        <strong>

                                            <?= htmlspecialchars(
                                                $currency
                                            ); ?>

                                            <?= number_format(
                                                $itemPrice,
                                                2
                                            ); ?>

                                        </strong>

                                    </td>


                                </tr>


                            <?php

                                endwhile;

                            else:

                            ?>

                                <tr>

                                    <td
                                        colspan="5"
                                        class="text-center text-muted py-4"
                                    >

                                        No order items found.

                                    </td>

                                </tr>

                            <?php endif; ?>

                            </tbody>

                        </table>

                    </div>


                    <!-- =========================================
                         PRICE SUMMARY
                    ========================================== -->

                    <div class="row justify-content-end mt-4">

                        <div class="col-md-6 col-lg-5">

                            <table class="table mb-0">

                                <!-- SUBTOTAL -->

                                <tr>

                                    <th>
                                        Subtotal
                                    </th>

                                    <td class="text-end">

                                        <?= htmlspecialchars(
                                            $currency
                                        ); ?>

                                        <?= number_format(
                                            $subtotal,
                                            2
                                        ); ?>

                                    </td>

                                </tr>


                                <!-- DISCOUNT -->

                                <?php if ($discount > 0): ?>

                                    <tr>

                                        <th>
                                            Discount
                                        </th>

                                        <td class="text-end text-success">

                                            -

                                            <?= htmlspecialchars(
                                                $currency
                                            ); ?>

                                            <?= number_format(
                                                $discount,
                                                2
                                            ); ?>

                                        </td>

                                    </tr>

                                <?php endif; ?>


                                <!-- TOTAL -->

                                <tr class="table-light">

                                    <th class="fs-5">
                                        Total
                                    </th>

                                    <td class="text-end">

                                        <strong
                                            class="fs-5 text-success"
                                        >

                                            <?= htmlspecialchars(
                                                $currency
                                            ); ?>

                                            <?= number_format(
                                                $total,
                                                2
                                            ); ?>

                                        </strong>

                                    </td>

                                </tr>

                            </table>

                        </div>

                    </div>


                    <hr class="my-4">


                    <!-- =========================================
                         CONTINUE SHOPPING
                    ========================================== -->

                    <div class="text-center">

                        <a
                            href="shop"
                            class="btn btn-dark text-white px-4"
                        >

                            Continue Shopping

                        </a>

                    </div>


                </div>

            </div>

        </div>

    </div>

</div>


<?php include 'footer.php'; ?>


</body>

</html>