<?php



session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login");
  exit;
}

$user_id = (int)$_SESSION['user_id'];
$user = [];
$addresses = [];

/* ===========================
   USER DETAILS
=========================== */

$stmt = mysqli_prepare($conn, "
    SELECT full_name, phone, email, address, city, state, country, pincode
    FROM user
    WHERE id=?
    LIMIT 1
");

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
  $user = mysqli_fetch_assoc($result);
}

/* ===========================
   SAVED ADDRESSES
=========================== */

$stmt = mysqli_prepare($conn, "
    SELECT *
    FROM user_addresses
    WHERE user_id=?
    ORDER BY is_default DESC, id DESC
");

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$resultAddress = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($resultAddress)) {
  $addresses[] = $row;
}

/* ===========================
   IF NO SAVED ADDRESS
   USE USER TABLE ADDRESS
=========================== */

if (count($addresses) == 0) {

  if (
    !empty($user['address']) ||
    !empty($user['city']) ||
    !empty($user['state']) ||
    !empty($user['country'])
  ) {

    $addresses[] = [
      "id" => 0,
      "user_id" => $user_id,
      "full_name" => $user['full_name'],
      "phone" => $user['phone'],
      "address" => $user['address'],
      "city" => $user['city'],
      "state" => $user['state'],
      "country" => $user['country'],
      "pincode" => $user['pincode'],
      "address_type" => "Profile",
      "is_default" => 1
    ];
  }
}

/* ===========================
   CART
=========================== */
 
$sql = "
SELECT
    c.id,
    c.qty,
    c.variant_id,
    c.size,
    c.color,

    p.id AS product_id,
    p.name,
    p.price,
    p.image

FROM cart c

INNER JOIN products p
ON p.id = c.product_id

WHERE c.user_id='$user_id'
";

$result = mysqli_query($conn, $sql);

$variantStmt = $conn->prepare("
    SELECT variant_name, variant_price
    FROM product_variants
    WHERE id=? AND product_id=?
    LIMIT 1
");

$cartRows = [];

while ($row = mysqli_fetch_assoc($result)) {

  $row['isVariant'] = false;
  $row['variant_name'] = null;
  $row['variant_price'] = null;
  $row['size'] = !empty($row['size']) ? $row['size'] : '';
  $row['color'] = !empty($row['color']) ? $row['color'] : '';

  if ((int)$row['variant_id'] > 0) {

    $vId = (int)$row['variant_id'];
    $pId = (int)$row['product_id'];

    $variantStmt->bind_param("ii", $vId, $pId);
    $variantStmt->execute();

    $vResult = $variantStmt->get_result();

    if ($vResult && $vResult->num_rows > 0) {

      $vRow = $vResult->fetch_assoc();

      $row['isVariant'] = true;
      $row['variant_name'] = $vRow['variant_name'];
      $row['variant_price'] = $vRow['variant_price'];
    }
  }

  $cartRows[] = $row;
}

$subtotal = 0;

$couponResult = mysqli_query($conn, "
SELECT *
FROM promo_codes
WHERE status='1'
AND CURDATE() BETWEEN start_date AND end_date
ORDER BY id DESC
");
?>




<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <!-- Primary Meta Tags -->
<title>Checkout | Royal Blue</title>
<meta name="title" content="Checkout | Royal Blue">
<meta name="description" content="Complete your secure checkout process with Royal Blue. Shop premium products with easy payment and reliable delivery service.">
<meta name="keywords" content="Royal Blue checkout, online shopping checkout, secure payment, premium products, online store">
<meta name="author" content="Royal Blue">

<!-- Canonical URL -->
<link rel="canonical" href="https://shubhankarfashion.com/checkout">

<!-- Website URL -->
<meta property="og:url" content="https://shubhankarfashion.com/checkout">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:title" content="Checkout | Royal Blue">
<meta property="og:description" content="Complete your order securely with Royal Blue. Enjoy smooth checkout and trusted shopping experience.">
<meta property="og:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:site_name" content="Royal Blue">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Checkout | Royal Blue">
<meta name="twitter:description" content="Complete your order securely with Royal Blue.">
<meta name="twitter:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">

<!-- Robots -->
<meta name="robots" content="index, follow">

<!-- Favicon -->
<link rel="icon" type="image/png" href="https://shubhankarfashion.com/img/logo-removebg-preview.png">

<!-- Schema Markup -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebPage",
  "name": "Checkout | Royal Blue",
  "url": "https://shubhankarfashion.com/checkout",
  "image": "https://shubhankarfashion.com/img/logo-removebg-preview.png",
  "publisher": {
    "@type": "Organization",
    "name": "Royal Blue",
    "logo": {
      "@type": "ImageObject",
      "url": "https://shubhankarfashion.com/img/logo-removebg-preview.png"
    }
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
    /* ============ Saved Addresses Section ============ */

    .card.shadow-sm.border-0.rounded-4 {
      background: #fff;
    }

    .card-header h5 {
      color: #2d3436;
      font-size: 1.15rem;
    }

    .card-header .btn-success {
      padding: 6px 18px;
      font-weight: 500;
      box-shadow: 0 2px 8px rgba(40, 167, 69, 0.25);
      transition: all 0.2s ease;
    }

    .card-header .btn-success:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(40, 167, 69, 0.35);
    }

    /* Hide the native radio, we'll style the box instead */
    .address-card input[type="radio"] {
      position: absolute;
      opacity: 0;
      width: 0;
      height: 0;
    }

    .address-card {
      cursor: pointer;
      display: block;
    }

    .address-box {
      position: relative;
      border: 1px solid #e9ecef;
      /* border-radius: 16px; */
      padding: 9px 9px;
      background: #fafbfc;
      transition: all 0.25s ease;
    }

    /* Hover feedback */
    .address-card:hover .address-box {
      border-color: #c8d6e5;
      background: #ffffff;
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
      transform: translateY(-2px);
    }

    /* Selected state */
    .address-card input[type="radio"]:checked+.address-box {
      border-color: #28a745;
      background: #f3faf5;
      /* box-shadow: 0 4px 16px rgba(40, 167, 69, 0.15); */
    }

    /* Little check icon on selected card */
    .address-card input[type="radio"]:checked+.address-box::before {
      content: "✓";
      position: absolute;
      top: 1px;
      right: 3px;
      width: 17px;
      height: 17px;
      background: #28a745;
      color: #fff;
      border-radius: 50%;
      font-size: 11px;
      font-weight: bold;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .address-box h6 {
      font-size: 1rem;
      color: #212529;
    }

    .address-box .badge.bg-success {
      font-weight: 500;
      font-size: 0.7rem;
      vertical-align: middle;
    }

    .address-box .badge.bg-light {
      font-size: 0.72rem;
      font-weight: 500;
      padding: 5px 10px;
    }

    .address-box p.text-muted {
      font-size: 0.9rem;
      line-height: 1.5;
    }

    /* Action buttons */
    .address-box .btn-outline-primary,
    .address-box .btn-outline-danger {
      font-size: 0.82rem;
      font-weight: 500;
      transition: all 0.2s ease;
    }

    .address-box .btn-outline-primary:hover {
      background: #0d6efd;
      border-color: #0d6efd;
      transform: translateY(-1px);
    }

    .address-box .btn-outline-danger:hover {
      background: #dc3545;
      border-color: #dc3545;
      transform: translateY(-1px);
    }

    /* Responsive tweak */
    @media (max-width: 576px) {
      .address-box {
        padding: 14px;
      }

      .address-box .d-flex.gap-2 {
        flex-wrap: wrap;
      }
    }
  </style>

</head>

<body>

  <?php include 'header.php'; ?>

  <section class="section-sm bg-surface-2 border-bottom">
    <div class="container">
      <nav class="small text-muted-soft mb-3"><a href="index">Home</a> / <span>Checkout</span></nav>
      <div class="step-track">
        <div class="step active"><span class="num">1</span> Shipping</div>
        <div class="step-sep"></div>
        <div class="step"><span class="num">2</span> Payment</div>
        <div class="step-sep"></div>
        <div class="step"><span class="num">3</span> Confirmation</div>
      </div>
    </div>
  </section>
  <section class="section">
    <div class="container">
      <!-- <div class="form-check mt-3">
        <input
          class="form-check-input"
          type="checkbox"
          name="is_default"
          id="is_default">

        <label class="form-check-label" for="is_default">
          Set as Default Address
        </label>
      </div> -->
      <!-- <form action="place-order.php" method="POST"> -->
      <!-- <form id="checkoutForm" action="place-order.php" method="POST"> -->
      <form id="checkoutForm" action="save-order.php" method="POST">


        <input type="hidden" name="address_id" id="address_id">
        <input type="hidden" name="email" id="email" value="<?= htmlspecialchars($user['email']); ?>">
        <input type="hidden" name="full_name" id="full_name">
        <input type="hidden" name="phone" id="phone">
        <input type="hidden" name="address" id="address">
        <input type="hidden" name="city" id="city">
        <input type="hidden" name="state" id="state">
        <input type="hidden" name="country" id="country">
        <input type="hidden" name="pincode" id="pincode">

        <input type="hidden" name="billing_full_name" id="billing_full_name">
        <input type="hidden" name="billing_phone" id="billing_phone">
        <input type="hidden" name="billing_state" id="billing_state">
        <input type="hidden" name="billing_city" id="billing_city">
        <input type="hidden" name="billing_address" id="billing_address">
        <input type="hidden" name="billing_pincode" id="billing_pincode">
        <input type="hidden" name="billing_address_type" id="billing_address_type">
        <input type="hidden" name="billing_region" id="billing_region">
        <input type="hidden" name="same_as_shipping" id="same_as_shipping">

        <div class="row g-4">


          <div class="col-lg-7">

            <div class="card-panel mb-4">
              <div class="d-flex justify-content-between align-items-center mb-3">

                <h5> <i class="bi bi-geo-alt-fill text-danger me-2"></i> Saved Addresses</h5>

                <a
                  href="add-address?return=checkout"
                  class="btn btn-success text-white" style="padding: 2px 5px; border-radius:1px;">

                  <i class="bi bi-plus-lg"></i>

                  Add New

                </a>


              </div>
              <hr>
              <?php if (count($addresses) > 0) { ?>

                <div class="card  border-0 mb-4" style="padding:0px !important;">



                  <div class="card-body" style="padding:0px !important; border-radius:0px !important;">

                    <?php foreach ($addresses as $a) { ?>

                      <label class="address-card w-100 " style="padding:0px !important;">



                        <input
                          type="radio"
                          class="savedAddress"
                          name="saved_address"
                          value="<?= $a['id']; ?>"

                          data-id="<?= $a['id']; ?>"
                          data-name="<?= htmlspecialchars($a['full_name']); ?>"
                          data-phone="<?= htmlspecialchars($a['phone']); ?>"
                          data-address="<?= htmlspecialchars($a['address']); ?>"
                          data-city="<?= htmlspecialchars($a['city']); ?>"
                          data-state="<?= htmlspecialchars($a['state']); ?>"
                          data-country="<?= htmlspecialchars($a['country']); ?>"
                          data-pincode="<?= htmlspecialchars($a['pincode']); ?>"

                          data-billing_full_name="<?= htmlspecialchars($a['billing_full_name']); ?>"
                          data-billing_phone="<?= htmlspecialchars($a['billing_phone']); ?>"
                          data-billing_state="<?= htmlspecialchars($a['billing_state']); ?>"
                          data-billing_city="<?= htmlspecialchars($a['billing_city']); ?>"
                          data-billing_address="<?= htmlspecialchars($a['billing_address']); ?>"
                          data-billing_pincode="<?= htmlspecialchars($a['billing_pincode']); ?>"
                          data-billing_address_type="<?= htmlspecialchars($a['billing_address_type']); ?>"
                          data-billing_region="<?= htmlspecialchars($a['billing_region']); ?>"
                          data-same_as_shipping="<?= $a['same_as_shipping']; ?>"

                          <?= $a['is_default'] ? "checked" : ""; ?>>



                        <div class="address-box" style="margin-bottom: 4px;">

                          <div class="d-flex justify-content-between align-items-start">

                            <div>

                              <h6 class="mb-1 fw-bold">

                                <?= htmlspecialchars($a['full_name']); ?>

                                <?php if ($a['is_default']) { ?>
                                  <span class="badge bg-success ms-2" style="border-radius:2px;">
                                    Default
                                  </span>
                                <?php } ?>

                                <?php if ($a['id'] == 0) { ?>
                                  <span class="badge bg-warning text-dark ms-2" style="border-radius:2px;">
                                    User Profile
                                  </span>
                                <?php } ?>

                              </h6>

                              <small class="text-muted">
                                <?= htmlspecialchars($a['phone']); ?>
                              </small>

                            </div>

                            <span class="badge bg-light text-dark border">
                              <?= htmlspecialchars($a['address_type']); ?>
                            </span>

                          </div>

                          <p class="text-muted mb-2">

                            <?= htmlspecialchars($a['address']); ?><br>

                            <?= htmlspecialchars($a['city']); ?>,
                            <?= htmlspecialchars($a['state']); ?>,
                            <?= htmlspecialchars($a['country']); ?>

                            - <?= htmlspecialchars($a['pincode']); ?>

                          </p>

                          <div class="d-flex gap-2">

                            <!-- <a href="edit-address.php?id=<?= $a['id']; ?>"
                              class="btn btn-outline-primary btn-sm   px-2">
                              <i class="bi bi-pencil-square me-1"></i>  Edit
                            </a> -->
                            <?php if ($a['id'] == 0) { ?>

                              <a href="my-profile"
                                class="btn btn-outline-primary btn-sm px-2">
                                <i class="bi bi-pencil-square me-1"></i> Edit
                              </a>

                            <?php } else { ?>

                              <a href="edit-address?id=<?= $a['id']; ?>"
                                class="btn btn-outline-primary btn-sm px-2">
                                <i class="bi bi-pencil-square me-1"></i> Edit
                              </a>

                            <?php } ?>

                            <a href="delete-address?id=<?= $a['id']; ?>"
                              class="btn btn-outline-danger btn-sm px-2"
                              onclick="return confirm('Delete this address?')">
                              <i class="bi bi-trash3 me-1"></i> Delete
                            </a>

                          </div>

                        </div>

                      </label>

                    <?php } ?>

                  </div>

                </div>

              <?php } ?>


              <!-- <h2 class="h6 mb-3">Contact Information</h2> -->

              <!-- <div class="row g-3">

                <div class="col-md-6">
                  <label>Full Name <span class="text-danger">*</span></label>
                
                  <input
                    type="text"
                    name="full_name"
                    id="full_name"
                    class="form-control"
                    value="<?= htmlspecialchars($user['full_name'] ?? '') ?>"
                    required>
                </div>

                <div class="col-md-6">
                  <label>Email Address <span class="text-danger">*</span></label>
                  
                  <input
                    type="email"
                    name="email"
                    id="email"
                    class="form-control"
                    value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                    required>
                </div>

                <div class="col-md-6">
                  <label>Phone Number <span class="text-danger">*</span></label>
                 
                  <input
                    type="tel"
                    name="phone"
                    id="phone"
                    class="form-control"
                    value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                    required>
                </div>

                <div class="col-md-6">
                  <label>Country <span class="text-danger">*</span></label>

                  <select
                    class="form-select"
                    id="checkoutCountry"
                    name="country"
                    required>
                  </select>



                </div>

              </div> -->

            </div>


            <!-- <div class="card-panel mb-4">

              <h2 class="h6 mb-3">Shipping Address</h2>

              <div class="row g-3">

                <div class="col-12">
                  <label>Address Line 1 <span class="text-danger">*</span></label>
                 
                  <input
                    type="text"
                    name="address"
                    id="address"
                    class="form-control"
                    value="<?= htmlspecialchars($user['address'] ?? '') ?>"
                    required>
                </div>

                <div class="col-12">
                  <label>Address Line 2 (Optional)</label>
                 
                  <input
                    type="text"
                    name="city"
                    id="city"
                    class="form-control"
                    value="<?= htmlspecialchars($user['city'] ?? '') ?>"
                    required>
                </div>

                <div class="col-md-4">
                  <label>City / Town <span class="text-danger">*</span></label>
                  <input
                    type="text"
                    name="state"
                    id="state"
                    class="form-control"
                    value="<?= htmlspecialchars($user['state'] ?? '') ?>"
                    required>
                </div>

                <div class="col-md-4">
                  <label>State / Province / Region <span class="text-danger">*</span></label>
                 
                  <input
                    type="text"
                    name="country"
                    id="region"
                    class="form-control"
                    value="<?= htmlspecialchars($user['country'] ?? '') ?>"
                    required>
                </div>

               
                <div class="col-md-4">
                  <label>
                    Postal / ZIP Code
                    <span class="text-danger">*</span>
                  </label>

                 
                  <input
                    type="text"
                    id="pincode"

                    name="pincode"
                    class="form-control"
                    autocomplete="postal-code"
                    value="<?= htmlspecialchars($user['pincode'] ?? '') ?>"
                    required>
                </div>

              </div>

            </div> -->






            <div class="card-panel b2b-only d-none">

              <h2 class="h6 mb-3">Business Information (Optional)</h2>

              <div class="row g-3">

                <div class="col-md-6">
                  <label>Company Name</label>
                  <input type="text"
                    name="company_name"
                    class="form-control">
                </div>

                <div class="col-md-6">
                  <label>GSTIN / VAT Number</label>
                  <input type="text"
                    name="gstin"
                    class="form-control">
                </div>

              </div>

            </div>

          </div>

          <div class="col-lg-5">
            <div class="card-panel">
              <h2 class="h6 mb-3" style="border-bottom: 1px solid #000;
    padding-bottom: 5px;">Order Summary</h2>
              <?php foreach ($cartRows as $row) { ?>

                <?php
                $unitPrice = $row['isVariant'] ? $row['variant_price'] : $row['price'];

                $total = $unitPrice * $row['qty'];

                $subtotal += $total;
                ?>

                <div class="d-flex justify-content-between mb-2" style="background: #00005f;
    color: #fff;
    padding: 2px 5px 2px 5px;
    border-radius: 0px;">

                  <div>

                    Product Name: <?= htmlspecialchars($row['name']); ?>

<?php if(!empty($row['size'])) { ?>
    <br>
    Size:
    <strong><?= htmlspecialchars($row['size']); ?></strong>
<?php } ?>

<?php if(!empty($row['color'])) { ?>
    <br>
    Color:
    <strong><?= htmlspecialchars($row['color']); ?></strong>
<?php } ?>,


                    <?php if ($row['isVariant']) { ?>
                      <br>
                      <small class="text-muted"><?php echo htmlspecialchars($row['variant_name']); ?></small>
                    <?php } ?>



                    <small>Qty :
                      <?php echo (int) $row['qty']; ?>
                    </small>

                  </div>

                  <div class="js-price" data-price-inr="<?= $total; ?>">

                    ₹<?php echo number_format($total, 2); ?>

                  </div>

                </div>

              <?php } ?>
              <hr>

              <div class="mb-3">

                <label class="form-label">
                  Coupon Code
                </label>

                <div class="input-group">

                  <input
                    type="text"
                    class="form-control"
                    name="code"
                    id="coupon_code"
                    placeholder="Enter Coupon">

                  <button
                    type="button"
                    class="btn btn-dark"
                    id="applyCoupon">
                    <span id="couponBtnText">Apply</span>
                  </button>

                </div>

                <?php if (!empty($_SESSION['coupon_error'])) { ?>
                  <div class="alert alert-danger mt-2"><?= htmlspecialchars($_SESSION['coupon_error']); ?></div>
                  <?php unset($_SESSION['coupon_error']); ?>
                <?php } ?>

                <?php if (!empty($_SESSION['coupon_success'])) { ?>
                  <div class="alert alert-success mt-2"><?= htmlspecialchars($_SESSION['coupon_success']); ?></div>
                  <?php unset($_SESSION['coupon_success']); ?>
                <?php } ?>

                <div id="couponMessage" class="mt-2"></div>
                <?php if (mysqli_num_rows($couponResult) > 0) { ?>

                  <div class="dropdown mt-3">

                    <button class="btn btn-outline-dark w-100 dropdown-toggle"
                      type="button"
                      data-bs-toggle="dropdown">

                      <i class="bi bi-tags"></i> Available Coupons

                    </button>

                    <div class="dropdown-menu w-100 p-2">

                      <?php while ($coupon = mysqli_fetch_assoc($couponResult)) { ?>

                        <div class="border rounded p-2 mb-2">

                          <div class="d-flex justify-content-between align-items-center">

                            <div>

                              <strong class="text-success">
                                <?= strtoupper($coupon['code']); ?>
                              </strong>

                              <br>

                              <small class="text-muted">

                                <?php if (strtolower($coupon['discount_type']) == "flat") { ?>

                                  <span class="js-price"
                                    data-price-inr="<?= $coupon['discount_value']; ?>">
                                    ₹<?= number_format($coupon['discount_value'], 0); ?>
                                  </span>
                                  OFF

                                <?php } else { ?>

                                  <?= $coupon['discount_value']; ?>% OFF

                                <?php } ?>

                                <br>

                                Min Order

                                <span class="js-price"
                                  data-price-inr="<?= $coupon['minimum_amount']; ?>">
                                  ₹<?= number_format($coupon['minimum_amount'], 0); ?>
                                </span>

                              </small>

                            </div>

                            <button
                              type="button"
                              class="btn btn-sm btn-success copyCoupon"
                              data-code="<?= $coupon['code']; ?>">

                              <i class="bi bi-copy"></i>

                            </button>

                          </div>

                        </div>

                      <?php } ?>

                    </div>

                  </div>

                <?php } ?>

              </div>
            </div>

            <?php


            $total = $subtotal;

            $discount = isset($_SESSION['discount']) ? $_SESSION['discount'] : 0;

            $grandTotal = $total - $discount;

            if ($grandTotal < 0) {
              $grandTotal = 0;
            }

            ?>

            <div class="d-flex justify-content-between small mb-2">
              <span>Subtotal</span>
              <span class="js-price"
                id="cSubtotal"
                data-price-inr="<?= $subtotal; ?>">
                ₹<?= number_format($subtotal, 2); ?>
              </span>
            </div>



            <div
              id="discountRow"
              class="d-flex justify-content-between small mb-2 text-success"
              style="<?= $discount > 0 ? '' : 'display:none;' ?>">

              <span id="discountTitle">
                Discount
                <?php if (isset($_SESSION['coupon'])) { ?>
                  (<?= htmlspecialchars($_SESSION['coupon']); ?>)
                <?php } ?>
              </span>

              <span id="discountAmount"
                class="js-price"
                data-price-inr="<?= $discount; ?>">
                -₹<?= number_format($discount, 2); ?>
              </span>

            </div>

            <div class="d-flex justify-content-between fw-bold mb-3 totalArea">
              <span>Total</span>
              <span class="js-price"
                id="cTotal"
                data-price-inr="<?= $grandTotal; ?>">
                ₹<?= number_format($grandTotal, 2); ?>
              </span>
            </div>



            <input type="hidden" name="subtotal" id="subtotal_input">
            <input type="hidden" name="discount" id="discount_input">
            <input type="hidden" name="total" id="total_input">
            <input type="hidden" name="currency" id="currency_input">
            <input type="hidden" name="coupon_code" value="<?= htmlspecialchars($_SESSION['coupon'] ?? ''); ?>">
            <input type="hidden" name="currency_rate" id="currency_rate">


            <button type="submit" class="btn btn-brand w-100">
              Continue to Payment
            </button>

      </form>

    </div>
    </div>
    </div>
    </div>
  </section>
  
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById("checkoutForm").addEventListener("submit", function(e) {

    const selectedAddress = document.querySelector('input[name="saved_address"]:checked');

    if (!selectedAddress) {

        e.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: 'Address Required',
            text: 'Please select a delivery address before continuing.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#00005f'
        });

        return false;
    }

});
</script>





  <script>
    document.addEventListener("DOMContentLoaded", function() {


      const form = document.getElementById("checkoutForm");
      const country = document.getElementById("checkoutCountry");
      const pincode = document.getElementById("pincode");




      if (!form || !country || !pincode) return;

      form.addEventListener("submit", function(e) {

        e.preventDefault();

        fetch("validate-zipcode.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "country=" + encodeURIComponent(country.value) +
              "&zipcode=" + encodeURIComponent(pincode.value)
          })
          .then(response => response.json())
          .then(data => {

            console.log(data);

            if (data.status === true) {

              updateHiddenFields();

              form.submit();

            } else {

              alert(data.message);

              pincode.focus();

            }

          })
          .catch(function(error) {

            console.log(error);

            alert("ZIP validation failed.");

          });

      });

    });
  </script>

  <!-- ============ FOOTER ============ -->
  <?php include 'footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <script>
    $(document).ready(function() {

      // Apply Coupon
      $("#applyCoupon").on("click", function(e) {

        e.preventDefault();

        var code = $("#coupon_code").val().trim();

        if (code == "") {
          $("#couponMessage").html(
            '<div class="alert alert-danger">Please enter coupon code.</div>'
          );
          return;
        }

        $("#applyCoupon").prop("disabled", true);

        $("#couponBtnText").html(
          '<span class="spinner-border spinner-border-sm me-1"></span> Applying...'
        );

        // Redirect to apply-coupon.php
        window.location.href = "apply-coupon.php?code=" + encodeURIComponent(code);

      });

      // Copy Coupon
      $(document).on("click", ".copyCoupon", function() {

        var code = $(this).data("code");

        navigator.clipboard.writeText(code);

        $("#coupon_code").val(code);

        $("#couponMessage").html(
          '<div class="alert alert-info">Coupon copied successfully. Click Apply.</div>'
        );

      });

    });
  </script>

  <script>
    // function updateHiddenFields() {

    //   const loc = NEH.getLocale();

    //   const rate = LIVE_RATES[loc.currency] || 1;

    //   document.getElementById("currency_rate").value = rate;


    //   const subtotalINR = <?= (float)$subtotal ?>;
    //   const discountINR = <?= (float)$discount ?>;
    //   const totalINR = <?= (float)$grandTotal ?>;

    //   // ===== DEBUG =====
    //   console.log("---------------");
    //   console.log("Country :", loc.code);
    //   console.log("Currency:", loc.currency);
    //   console.log("Rate    :", rate);

    //   console.log("Subtotal INR :", subtotalINR);
    //   console.log("Discount INR :", discountINR);
    //   console.log("Total INR    :", totalINR);

    //   console.log("Subtotal Save :", (subtotalINR * rate).toFixed(2));
    //   console.log("Discount Save :", (discountINR * rate).toFixed(2));
    //   console.log("Total Save    :", (totalINR * rate).toFixed(2));
    //   console.log("---------------");

    //   document.getElementById("currency_input").value = loc.currency;

    //   document.getElementById("subtotal_input").value =
    //     (subtotalINR * rate).toFixed(2);

    //   document.getElementById("discount_input").value =
    //     (discountINR * rate).toFixed(2);

    //   document.getElementById("total_input").value =
    //     (totalINR * rate).toFixed(2);
    // }
    
    function updateHiddenFields() {

    if (typeof NEH === "undefined") {
        console.error("NEH is not loaded.");
        return;
    }

    const loc = NEH.getLocale();

    let rate = 1;

    if (
        loc.currency !== "INR" &&
        window.LIVE_RATES &&
        typeof window.LIVE_RATES[loc.currency] !== "undefined"
    ) {
        rate = parseFloat(window.LIVE_RATES[loc.currency]);
    }

    if (!isFinite(rate) || rate <= 0) {
        rate = 1;
    }

    const subtotalINR = <?= (float)$subtotal ?>;
    const discountINR = <?= (float)$discount ?>;

    /*
     * FOREIGN COUNTRY:
     * India price + 70%
     *
     * Example:
     * ₹100 -> ₹170 -> USD conversion
     */
    let convertedSubtotalBase = subtotalINR;

    if (loc.code !== "IN") {
        convertedSubtotalBase = subtotalINR * 1.70;
    }

    /*
     * Discount remains the actual coupon discount.
     */
    let convertedTotalBase = convertedSubtotalBase - discountINR;

    if (convertedTotalBase < 0) {
        convertedTotalBase = 0;
    }

    const finalSubtotal =
        convertedSubtotalBase * rate;

    const finalDiscount =
        discountINR * rate;

    const finalTotal =
        convertedTotalBase * rate;

    document.getElementById("currency_input").value =
        loc.currency;

    document.getElementById("currency_rate").value =
        rate;

    document.getElementById("subtotal_input").value =
        finalSubtotal.toFixed(2);

    document.getElementById("discount_input").value =
        finalDiscount.toFixed(2);

    document.getElementById("total_input").value =
        finalTotal.toFixed(2);


    console.log("========== FINAL ORDER PRICE ==========");

    console.log("Country:", loc.code);
    console.log("Currency:", loc.currency);

    console.log("Original INR:", subtotalINR);

    console.log(
        "Foreign Base INR:",
        convertedSubtotalBase
    );

    console.log(
        "Exchange Rate:",
        rate
    );

    console.log(
        "Final Subtotal:",
        finalSubtotal.toFixed(2)
    );

    console.log(
        "Discount:",
        finalDiscount.toFixed(2)
    );

    console.log(
        "Final Total:",
        finalTotal.toFixed(2)
    );

    console.log("=======================================");
}



    // function fillCountrySelect() {

    //   const sel = document.getElementById("checkoutCountry");

    //   sel.innerHTML = COUNTRIES.map(function(c) {

    //     return `<option value="${c.code}">
    //                 ${c.name} (${c.currency})
    //             </option>`;

    //   }).join("");

    //   sel.value = NEH.getLocale().code;

    //   updateHiddenFields();

    //   sel.addEventListener("change", function() {

    //     NEH.setLocale(sel.value);

    //     renderSummary();

    //     setTimeout(function() {

    //       updateHiddenFields();

    //     }, 200);

    //   });

    // }

    // document.addEventListener("DOMContentLoaded", function() {

    //   fillCountrySelect();

    //   renderSummary();

    //   setTimeout(function() {

    //     updateHiddenFields();

    //   }, 300);

    // });
    
    document.addEventListener("DOMContentLoaded", function() {

    updateHiddenFields();

});

    // document.addEventListener("neh:localechange", function() {

    //   renderSummary();

    //   setTimeout(function() {

    //     updateHiddenFields();

    //   }, 200);

    // });
    
    document.addEventListener("neh:localechange", function() {

    if (typeof renderSummary === "function") {
        renderSummary();
    }

    updateHiddenFields();

});

    // Belt-and-braces: make sure the hidden fields reflect the latest
    // computed values right before the form is actually submitted too,
    // using the SAME source of truth (data-price-inr + LIVE_RATES),
    // never a stray "data-price" attribute.
    // document.querySelector("form").addEventListener("submit", function() {
    //   updateHiddenFields();
    // });



document.getElementById("checkoutForm").addEventListener("submit", function() {
    updateHiddenFields();
});


    // document.querySelectorAll(".savedAddress").forEach(function(r) {

    //   r.addEventListener("change", function() {

    //     document.getElementById("full_name").value = this.dataset.name;

    //     // document.getElementById("email").value = this.dataset.email;

    //     document.getElementById("phone").value = this.dataset.phone;

    //     document.getElementById("address").value = this.dataset.address;

    //     document.getElementById("city").value = this.dataset.city;

    //     document.getElementById("state").value = this.dataset.state;

    //     document.getElementById("countrynew").value = this.dataset.countrynew;

    //     document.getElementById("pincode").value = this.dataset.pincode;

    //   });

    // });

    // document.querySelector(".savedAddress:checked")?.dispatchEvent(new Event("change"));


    document.querySelectorAll(".savedAddress").forEach(function(radio) {

      radio.addEventListener("change", fillAddress);

    });

    function fillAddress() {

      document.getElementById("address_id").value = this.dataset.id;

      document.getElementById("full_name").value = this.dataset.name;
      document.getElementById("phone").value = this.dataset.phone;
      document.getElementById("address").value = this.dataset.address;
      document.getElementById("city").value = this.dataset.city;
      document.getElementById("state").value = this.dataset.state;
      document.getElementById("country").value = this.dataset.country;
      document.getElementById("pincode").value = this.dataset.pincode;

      document.getElementById("billing_full_name").value = this.dataset.billing_full_name;
      document.getElementById("billing_phone").value = this.dataset.billing_phone;
      document.getElementById("billing_state").value = this.dataset.billing_state;
      document.getElementById("billing_city").value = this.dataset.billing_city;
      document.getElementById("billing_address").value = this.dataset.billing_address;
      document.getElementById("billing_pincode").value = this.dataset.billing_pincode;
      document.getElementById("billing_address_type").value = this.dataset.billing_address_type;
      document.getElementById("billing_region").value = this.dataset.billing_region;
      document.getElementById("same_as_shipping").value = this.dataset.same_as_shipping;

    }

    // Page load par default selected address bhi form me aa jayega
    const checked = document.querySelector(".savedAddress:checked");

    if (checked) {
      checked.dispatchEvent(new Event("change"));
    }
  </script>
</body>

</html>