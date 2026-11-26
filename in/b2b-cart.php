<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}




$user_id = $_SESSION['user_id'];

$sql = mysqli_query($conn, "
SELECT
    c.id AS cart_id,
    c.qty,
    c.variant_id,
    c.size,
    c.color,
    c.size_requirement,
    c.remarks,

    p.id AS product_id,
    p.name,
    p.price,
    p.image,

    pv.variant_price,
    pv.images
    
    
FROM cart c

INNER JOIN products p
ON c.product_id = p.id

LEFT JOIN product_variants pv
ON c.variant_id = pv.id

WHERE c.user_id='$user_id'

ORDER BY c.id DESC
");

// Pull everything into an array once so we can render both the
// desktop table and the mobile card list from the same data.
$cartItems = [];
$total = 0;

if ($sql) {
    while ($row = mysqli_fetch_assoc($sql)) {

        $image = $row['image'];

        if (!empty($row['images'])) {
            $imgs = explode(",", $row['images']);
            if (!empty($imgs[0])) {
                $image = trim($imgs[0]);
                $imagePath = "../admin/img/" . $image;
            }
        } else {
            $imagePath = "../admin/uploads/" . $image;
        }

        // $price = $row['price'];

        // if ($row['variant_id'] > 0 && $row['variant_price'] > 0) {
        //     $price = $row['variant_price'];
        // }
        
        $price = (float)$row['price'];

if ($row['variant_id'] > 0 && $row['variant_price'] > 0) {
    $price = (float)$row['variant_price'];
}

/*
|--------------------------------------------------------------------------
| Display / Cart Price = 20% Extra
|--------------------------------------------------------------------------
| Database ki original price change nahi hogi.
*/
$price = $price * 1.20;

        $lineTotal = $price * $row['qty'];
        $total += $lineTotal;

        $row['imagePath'] = $imagePath;
        $row['unitPrice'] = $price;
        $row['lineTotal'] = $lineTotal;

        $cartItems[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>B2B Cart | Shubhankar India</title>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta name="title" content="B2B Cart | Shubhankar India">

<meta name="description" content="Review your selected B2B products before requesting a quotation or placing your bulk order with Shubhankar India.">

<meta name="robots" content="noindex,nofollow,noarchive,nosnippet">

<link rel="canonical" href="https://shubhankarfashion.com/in/b2b-cart">

<meta name="author" content="Shubhankar">
<meta name="publisher" content="Shubhankar">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="Shubhankar India">
<meta property="og:title" content="B2B Cart | Shubhankar India">
<meta property="og:description" content="Review your selected products before submitting your B2B enquiry.">
<meta property="og:url" content="https://shubhankarfashion.com/in/b2b-cart">
<meta property="og:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:alt" content="Shubhankar India">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="B2B Cart | Shubhankar India">
<meta name="twitter:description" content="Review your B2B shopping cart before checkout.">
<meta name="twitter:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">

<!-- Favicon -->
<link rel="icon" type="image/png" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<link rel="apple-touch-icon" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">

<meta name="theme-color" content="#000000">
<script type="application/ld+json">
{
  "@context":"https://schema.org",
  "@type":"WebPage",
  "name":"B2B Cart",
  "url":"https://shubhankarfashion.com/in/b2b-cart",
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
        :root {
            --sb-forest: #2f4a3c;
            --sb-forest-dark: #233a2f;
            --sb-sage: #7c9070;
            --sb-ivory: #faf7f0;
            --sb-amber: #c9a05c;
            --sb-charcoal: #24302b;
            --sb-line: #e8e3d8;
        }

        body {
            background: var(--sb-ivory);
            color: var(--sb-charcoal);
            font-family: 'Inter', sans-serif;
        }

        .page-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 28px;
        }

        .page-head h2 {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            color: var(--sb-forest-dark);
            margin-bottom: 4px;
        }

        .page-head p {
            color: #6b7a70;
            margin-bottom: 0;
            font-size: 0.95rem;
        }

        .page-head .count-pill {
            background: var(--sb-forest);
            color: #fff;
            border-radius: 999px;
            padding: 6px 16px;
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .cart-box {
            background: #fff;
            border: 1px solid var(--sb-line);
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 6px 20px rgba(47, 74, 60, 0.05);
        }

        .product-img {
            width: 64px;
            height: 64px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--sb-line);
        }

        /* ---- Desktop table ---- */
        .sb-table thead th {
            font-family: 'Inter', sans-serif;
            font-size: 0.78rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #7a8a80;
            border-bottom: 2px solid var(--sb-line);
            background: transparent;
            font-weight: 600;
            padding: 14px 12px;
            white-space: nowrap;
        }

        .sb-table td {
            padding: 14px 12px;
            vertical-align: middle;
            border-color: var(--sb-line);
            font-size: 0.93rem;
        }

        .sb-table tbody tr {
            transition: background 0.15s ease;
        }

        .sb-table tbody tr:hover {
            background: rgba(124, 144, 112, 0.07);
        }

        .product-name {
            font-weight: 600;
            color: var(--sb-forest-dark);
            text-decoration: none;
        }

        .product-name:hover {
            color: var(--sb-amber);
        }

        .line-total {
            font-weight: 700;
            color: var(--sb-forest-dark);
        }

        .form-control.save-cart {
            font-size: 0.86rem;
            border-color: var(--sb-line);
        }

        .form-control.save-cart:focus {
            border-color: var(--sb-sage);
            box-shadow: 0 0 0 0.2rem rgba(124, 144, 112, 0.2);
        }

        .btn-remove {
            border: 1px solid #e3b3b3;
            background: #fff;
            color: #c0392b;
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 8px;
            padding: 6px 12px;
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .btn-remove:hover {
            background: #c0392b;
            color: #fff;
            border-color: #c0392b;
        }

        /* ---- Mobile card list ---- */
        .sb-cart-card {
            border: 1px solid var(--sb-line);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 14px;
            background: #fffdf9;
        }

        .sb-cart-card .sb-cart-top {
            display: flex;
            gap: 12px;
            margin-bottom: 14px;
        }

        .sb-cart-card .sb-cart-info {
            flex: 1;
            min-width: 0;
        }

        .sb-cart-card .sb-cart-price {
            color: #556259;
            font-size: 0.88rem;
            margin-top: 2px;
        }

        .sb-cart-card label {
            font-size: 0.78rem;
            font-weight: 600;
            color: #7a8a80;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-bottom: 4px;
            display: block;
        }

        .sb-cart-card .field-group {
            margin-bottom: 12px;
        }

        .sb-cart-card .sb-cart-footer-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px dashed var(--sb-line);
        }

        .sb-cart-card .sb-cart-qty {
            background: rgba(124, 144, 112, 0.15);
            color: var(--sb-forest-dark);
            font-weight: 600;
            font-size: 0.82rem;
            border-radius: 999px;
            padding: 4px 12px;
        }

        /* ---- Summary panel ---- */
        .summary-panel {
            background: #fff;
            border: 1px solid var(--sb-line);
            border-radius: 14px;
            padding: 20px 24px;
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            box-shadow: 0 6px 20px rgba(47, 74, 60, 0.05);
        }

        .summary-panel .grand-total-label {
            font-size: 0.85rem;
            color: #7a8a80;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-bottom: 2px;
        }

        .summary-panel .grand-total-value {
            font-family: 'Fraunces', serif;
            font-size: 1.6rem;
            font-weight: 600;
            color: var(--sb-forest-dark);
        }

        .btn-checkout {
            background: var(--sb-forest);
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 10px;
            padding: 12px 28px;
            font-size: 0.95rem;
            transition: background 0.15s ease;
        }

        .btn-checkout:hover:not(:disabled) {
            background: var(--sb-forest-dark);
            color: #fff;
        }

        .btn-checkout:disabled {
            background: #c9cfc8;
            cursor: not-allowed;
        }

        /* ---- Empty state ---- */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state .empty-icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: rgba(124, 144, 112, 0.15);
            color: var(--sb-forest);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 18px;
        }

        .empty-state h5 {
            font-family: 'Fraunces', serif;
            color: var(--sb-forest-dark);
            font-weight: 600;
        }

        .empty-state p {
            color: #7a8a80;
            max-width: 380px;
            margin: 0 auto 20px;
        }

        .error-toast {
            border: 1px solid #e3b3b3;
            background: #fdf1f1;
            color: #a12a2a;
            border-radius: 10px;
            padding: 14px 18px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }

        @media (max-width: 767.98px) {
            .cart-box {
                padding: 16px;
                border-radius: 12px;
            }

            .page-head h2 {
                font-size: 1.4rem;
            }

            .summary-panel {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }

            .btn-checkout {
                width: 100%;
            }
        }
    </style>

</head>


<body>

    <?php include "header.php"; ?>

    <div class="container py-5">

        <div class="page-head">
            <div>
                <h2>My Cart</h2>
                <p>Review quantities and add sizing or special requirements before checkout.</p>
            </div>

            <?php if (count($cartItems) > 0) { ?>
                <span class="count-pill">
                    <i class="bi bi-bag-check me-1"></i>
                    <?= count($cartItems); ?>
                    item<?= count($cartItems) > 1 ? 's' : ''; ?>
                </span>
            <?php } ?>
        </div>


        <?php if (count($cartItems) > 0) { ?>

            <div class="cart-box">

                <!-- =========================
                     DESKTOP / TABLET
                ========================== -->
                <div class="table-responsive d-none d-md-block">

                    <table class="table sb-table align-middle mb-0">

                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Product</th>
                                <th>Color</th>
                                <th>Price</th>
                                <th>Pieces</th>
                                <th>Requirement Size</th>
                                <th>Remarks</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($cartItems as $row) { ?>

                                <tr>

                                    <!-- IMAGE -->
                                    <td>
                                        <a href="b2b-product/<?= urlencode($row['name']) ?>">
                                            <img
                                                src="<?= htmlspecialchars($row['imagePath']); ?>"
                                                class="product-img"
                                                alt="<?= htmlspecialchars($row['name']); ?>">
                                        </a>
                                    </td>


                                    <!-- PRODUCT -->
                                    <td>
                                        <a
                                            href="b2b-product/<?= urlencode($row['name']) ?>"
                                            class="product-name">

                                            <?= htmlspecialchars($row['name']); ?>

                                        </a>
                                    </td>


                                    <!-- SELECTED COLOR -->
                                 <!-- SELECTED COLOR -->
<td>
    <?php if (!empty($row['color'])) { ?>

        <span style="
            display:inline-flex;
            align-items:center;
            gap:7px;
            font-weight:600;
            color:#2f4a3c;
        ">

            <!--<span style="-->
            <!--    width:16px;-->
            <!--    height:16px;-->
            <!--    border-radius:50%;-->
            <!--    background-color:#<?= htmlspecialchars(ltrim($row['color'], '#')); ?>;-->
            <!--    border:1px solid #bbb;-->
            <!--    display:inline-block;-->
            <!--    flex-shrink:0;-->
            <!--"></span>-->

            <?= htmlspecialchars($row['color']); ?>

        </span>

    <?php } else { ?>

        <span class="text-muted">N/A</span>

    <?php } ?>
</td>


                                    <!-- PRICE -->
                                    <td>
                                        ₹<?= number_format($row['unitPrice'], 2); ?>
                                    </td>


                                    <!-- QTY -->
                                    <td>
                                        <?= $row['qty']; ?>
                                    </td>


                                    <!-- REQUIREMENT SIZE -->
                                    <td style="width:220px;">

                                        <input
                                            type="text"
                                            class="form-control save-cart"
                                            data-id="<?= $row['cart_id']; ?>"
                                            data-type="size_requirement"
                                            value="<?= htmlspecialchars(
                                                !empty($row['size_requirement'])
                                                    ? $row['size_requirement']
                                                    : $row['size']
                                            ); ?>"
                                            placeholder="Example: S, M, L, XL or Custom Size"
                                            required>

                                    </td>


                                    <!-- REMARKS OPTIONAL -->
                                    <td style="width:280px;">

                                        <textarea
                                            class="form-control save-cart"
                                            data-id="<?= $row['cart_id']; ?>"
                                            data-type="remarks"
                                            rows="1"
                                            placeholder="Any special requirement..."><?= htmlspecialchars($row['remarks']); ?></textarea>

                                    </td>


                                    <!-- TOTAL -->
                                    <td class="line-total">
                                        ₹<?= number_format($row['lineTotal'], 2); ?>
                                    </td>


                                    <!-- REMOVE -->
                                    <td>

                                        <a
                                            href="b2b-remove-cart.php?id=<?= $row['cart_id']; ?>"
                                            class="btn-remove"
                                            onclick="return confirm('Remove this product?')">

                                            <i class="bi bi-trash3 me-1"></i>
                                            Remove

                                        </a>

                                    </td>

                                </tr>

                            <?php } ?>

                        </tbody>

                    </table>

                </div>


                <!-- =========================
                     MOBILE
                ========================== -->
                <div class="d-md-none">

                    <?php foreach ($cartItems as $row) { ?>

                        <div class="sb-cart-card">

                            <div class="sb-cart-top">

                                <!-- IMAGE -->
                                <a href="b2b-product/<?= urlencode($row['name']) ?>">

                                    <img
                                        src="<?= htmlspecialchars($row['imagePath']); ?>"
                                        class="product-img"
                                        alt="<?= htmlspecialchars($row['name']); ?>">

                                </a>


                                <!-- PRODUCT INFO -->
                                <div class="sb-cart-info">

                                    <a
                                        href="b2b-product/<?= urlencode($row['name']) ?>"
                                        class="product-name">

                                        <?= htmlspecialchars($row['name']); ?>

                                    </a>


                                    <div class="sb-cart-price">

                                        ₹<?= number_format($row['unitPrice'], 2); ?>

                                        <span class="sb-cart-qty ms-2">
                                            Qty: <?= $row['qty']; ?>
                                        </span>

                                    </div>


                                    <!-- SELECTED COLOR -->
                                    <!-- SELECTED COLOR -->
<?php if (!empty($row['color'])) { ?>

    <div style="
        margin-top:6px;
        font-size:0.85rem;
        color:#556259;
        font-weight:600;
        display:flex;
        align-items:center;
        gap:7px;
    ">

        <span>Color:</span>

        <!--<span style="-->
        <!--    width:15px;-->
        <!--    height:15px;-->
        <!--    border-radius:50%;-->
        <!--    background-color:#<?= htmlspecialchars(ltrim($row['color'], '#')); ?>;-->
        <!--    border:1px solid #bbb;-->
        <!--    display:inline-block;-->
        <!--"></span>-->

        <span>
            <?= htmlspecialchars($row['color']); ?>
        </span>

    </div>

<?php } ?>

                                </div>

                            </div>


                            <!-- REQUIREMENT SIZE -->
                            <div class="field-group">

                                <label>
                                    Requirement size
                                </label>

                                <input
                                    type="text"
                                    class="form-control save-cart"
                                    data-id="<?= $row['cart_id']; ?>"
                                    data-type="size_requirement"
                                    value="<?= htmlspecialchars(
                                        !empty($row['size_requirement'])
                                            ? $row['size_requirement']
                                            : $row['size']
                                    ); ?>"
                                    placeholder="Example: S, M, L, XL or Custom Size"
                                    required>

                            </div>


                            <!-- REMARKS OPTIONAL -->
                            <div class="field-group mb-0">

                                <label>
                                    Remarks
                                </label>

                                <textarea
                                    class="form-control save-cart"
                                    data-id="<?= $row['cart_id']; ?>"
                                    data-type="remarks"
                                    rows="2"
                                    placeholder="Any special requirement..."><?= htmlspecialchars($row['remarks']); ?></textarea>

                            </div>


                            <!-- FOOTER -->
                            <div class="sb-cart-footer-row">

                                <div class="line-total">

                                    ₹<?= number_format($row['lineTotal'], 2); ?>

                                </div>


                                <a
                                    href="b2b-remove-cart.php?id=<?= $row['cart_id']; ?>"
                                    class="btn-remove"
                                    onclick="return confirm('Remove this product?')">

                                    <i class="bi bi-trash3 me-1"></i>
                                    Remove

                                </a>

                            </div>

                        </div>

                    <?php } ?>

                </div>

            </div>


            <!-- =========================
                 SUMMARY
            ========================== -->
            <div class="summary-panel">

                <div>

                    <div class="grand-total-label">
                        Grand Total
                    </div>

                    <div class="grand-total-value">
                        ₹<?= number_format($total, 2); ?>
                    </div>

                </div>


                <!-- ALWAYS GREEN WHEN SIZE IS FILLED -->
                <button
                    type="button"
                    class="btn-checkout"
                    id="checkoutBtn">

                    Proceed to checkout
                    <i class="bi bi-arrow-right ms-1"></i>

                </button>

            </div>


        <?php } else { ?>


            <!-- =========================
                 EMPTY CART
            ========================== -->
            <div class="cart-box">

                <div class="empty-state">

                    <div class="empty-icon">
                        <i class="bi bi-cart-x"></i>
                    </div>

                    <h5>Your cart is empty</h5>

                    <p>
                        Browse our wholesale catalogue and add products to get started.
                    </p>

                    <a
                        href="shop.php"
                        class="btn btn-dark text-white">

                        Continue shopping

                    </a>

                </div>

            </div>


        <?php } ?>

    </div>


    <?php if (isset($_GET['error'])) { ?>

        <div class="container">

            <div class="error-toast">

                <i class="bi bi-exclamation-triangle-fill"></i>

                Please fill Requirement Size for all products before proceeding to checkout.

            </div>

        </div>

    <?php } ?>


    <script>

        const checkoutBtn = document.getElementById("checkoutBtn");


        /*
        =====================================================
        VALIDATE CART
        ONLY REQUIREMENT SIZE IS MANDATORY
        REMARKS IS OPTIONAL
        =====================================================
        */

        function validateCart() {

            let ok = true;


            document.querySelectorAll(".save-cart").forEach(function(el) {


                /*
                Desktop and mobile both contain the same fields.
                Skip hidden fields.
                */

                if (el.offsetParent === null) {
                    return;
                }


                /*
                ONLY SIZE REQUIREMENT IS REQUIRED
                */

                if (
                    el.dataset.type === "size_requirement" &&
                    el.value.trim() === ""
                ) {

                    ok = false;

                }

            });


            /*
            Checkout button remains green when:
            - Size filled
            - Remarks empty or filled
            */

            if (checkoutBtn) {

                checkoutBtn.disabled = !ok;

            }

        }


        /*
        =====================================================
        SAVE CART DETAILS
        =====================================================
        */

        document.querySelectorAll(".save-cart").forEach(function(el) {


            el.addEventListener("input", function() {


                /*
                Keep desktop and mobile fields synchronized
                */

                document.querySelectorAll(
                    '.save-cart[data-id="' +
                    this.dataset.id +
                    '"][data-type="' +
                    this.dataset.type +
                    '"]'
                ).forEach(function(twin) {

                    if (twin !== el) {

                        twin.value = el.value;

                    }

                });


                /*
                SAVE TO DATABASE
                */

                fetch("update-cart-details.php", {

                    method: "POST",

                    headers: {
                        "Content-Type":
                            "application/x-www-form-urlencoded"
                    },

                    body:
                        "id=" +
                        encodeURIComponent(this.dataset.id) +

                        "&type=" +
                        encodeURIComponent(this.dataset.type) +

                        "&value=" +
                        encodeURIComponent(this.value)

                });


                /*
                Re-check button state
                */

                validateCart();

            });

        });


        /*
        =====================================================
        CHECKOUT
        =====================================================
        */

        if (checkoutBtn) {


            checkoutBtn.onclick = function() {


                validateCart();


                /*
                If size is missing
                */

                if (this.disabled) {

                    alert(
                        "Please fill Requirement Size for all products."
                    );

                    return;

                }


                /*
                Go to checkout
                */

                window.location = "b2b-checkout";

            };


            /*
            Initial validation
            */

            validateCart();

        }

    </script>


    <?php include "footer.php"; ?>

</body>



</html>