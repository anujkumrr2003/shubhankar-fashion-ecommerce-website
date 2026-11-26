<?php
session_start();
include 'config.php';



if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = (int)$_SESSION['user_id'];

$sql = "
SELECT
    c.id AS cart_id,
    c.qty,
    c.variant_id,
    c.size,
    c.color,

    p.id AS product_id,
    p.name,
    p.price AS product_price,
    p.image AS product_image,

    s.subcategory_name

FROM cart c

INNER JOIN products p
ON p.id=c.product_id

LEFT JOIN subcategories s
ON s.id=p.subcategory_id

WHERE c.user_id=?

ORDER BY c.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
  die(mysqli_error($conn));
}

// Fetch every row's variant separately (if it has one). This is simpler and
// more reliable than a single big JOIN, and matches how product.php already
// looks up variants.
$variantStmt = $conn->prepare("
    SELECT
        variant_name,
        variant_price,
        images
    FROM product_variants
    WHERE id=? AND product_id=?
    LIMIT 1
");

$rows = [];

while ($row = $result->fetch_assoc()) {

  $row['isVariant'] = false;
  $row['variant_name'] = "";
  $row['variant_price'] = 0;
  $row['variant_images'] = "";
  $row['size'] = $row['size']; // cart table se size
  $row['color'] = $row['color'];

  if ((int)$row['variant_id'] > 0) {

    $vId = (int)$row['variant_id'];
    $pId = (int)$row['product_id'];

    $variantStmt->bind_param("ii", $vId, $pId);
    $variantStmt->execute();

    $vResult = $variantStmt->get_result();

    if ($vResult->num_rows > 0) {

      $vRow = $vResult->fetch_assoc();

      $row['isVariant']      = true;
      $row['variant_name']   = $vRow['variant_name'];
      $row['variant_price']  = $vRow['variant_price'];
      $row['variant_images'] = $vRow['images'];
    }
  }

  $rows[] = $row;
}

$subtotal = 0;

?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Shopping Cart | Shubhankar</title>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta name="title" content="Shopping Cart | Shubhankar">
<meta name="description" content="Review the products in your shopping cart, update quantities and proceed to secure checkout at Shubhankar.">

<meta name="author" content="Shubhankar">
<meta name="publisher" content="Shubhankar">

<!-- Don't Index Cart -->
<meta name="robots" content="noindex,follow">
<meta name="googlebot" content="noindex,follow">
<meta name="bingbot" content="noindex,follow">

<link rel="canonical" href="https://shubhankarfashion.com/cart">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="Shubhankar">
<meta property="og:title" content="Shopping Cart | Shubhankar">
<meta property="og:description" content="View the products in your shopping cart and continue to checkout.">
<meta property="og:url" content="https://shubhankarfashion.com/cart">
<meta property="og:image" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:secure_url" content="https://shubhankarfashion.com/img/logo-removebg-preview.png">
<meta property="og:image:type" content="image/png">
<meta property="og:image:alt" content="Shubhankar Logo">
<meta property="og:locale" content="en_IN">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Shopping Cart | Shubhankar">
<meta name="twitter:description" content="Review your selected products before checkout.">
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
  "name":"Shopping Cart",
  "url":"https://shubhankarfashion.com/cart",
  "description":"Shopping cart page for reviewing selected products before checkout.",
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
      "name":"Shopping Cart",
      "item":"https://shubhankarfashion.com/cart"
    }
  ]
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

</head>

<body>

  <?php include 'header.php'; ?>

  <section class="section-sm bg-surface-2 border-bottom">
    <div class="container">
      <nav class="small text-muted-soft mb-2"><a href="index.php">Home</a> / <span>Cart</span></nav>
      <h1 class="h3 mb-0">Your Cart</h1>
    </div>
  </section>
  <section class="section">
    <div class="container">
      <div id="emptyCart" class="text-center py-5 d-none">
        <i class="bi bi-bag-x" style="font-size:3rem;color:var(--muted);"></i>
        <p class="text-muted-soft mt-2 mb-3">Your cart is empty.</p>
        <a href="shop.php" class="btn btn-brand text-white">Continue Shopping</a>
      </div>
<style>
    /* ===========================
   CART RESPONSIVE DESIGN
===========================*/

@media (max-width: 767.98px){

.card-panel{
   /* padding:0 !important;*/
   /*background:#fff;*/
   
}

.card-panel>.d-flex{
    display:block !important;
    
}

 
}
</style>
      <div class="row g-4" id="cartWrap">
        <div class="col-lg-8">
          <div class="card-panel">

            <?php if (count($rows) > 0) { ?>

              <?php foreach ($rows as $row) { ?>
                <?php

                $isVariant = $row['isVariant'];

                $unitPrice = $isVariant ? $row['variant_price'] : $row['product_price'];

                $total = $unitPrice * $row['qty'];

                $subtotal += $total;

                // Pick the correct image + folder for base product vs. variant
                if ($isVariant && !empty($row['variant_images'])) {
                  $variantImgs = explode(',', $row['variant_images']);
                  $firstVariantImg = trim($variantImgs[0]);
                  $imgSrc = ($firstVariantImg !== '')
                    ? "admin/img/" . $firstVariantImg
                    : "admin/uploads/" . $row['product_image'];
                } else {
                  $imgSrc = "admin/uploads/" . $row['product_image'];
                }

                ?>

                <div class="d-flex align-items-center gap-3 py-3 border-bottom">

                  <img src="<?php echo htmlspecialchars($imgSrc); ?>"
                    style="width:80px;height:80px;object-fit:cover;">

                  <div class="flex-grow-1">

                    <h6 class="mb-1">
                      <a href="product/<?= urlencode($row['name']) ?>">
                        <?php echo $row['name']; ?>
                      </a>
                    </h6>

                    <div class="text-muted small">
                      <?php echo $row['subcategory_name']; ?>
                      <?php if ($isVariant) { ?>
                        &middot; <?php echo htmlspecialchars($row['variant_name']); ?>
                      <?php } ?>

                     <?php if (!empty($row['size'])) { ?>
    <div>
        Size:
        <strong><?= htmlspecialchars($row['size']); ?></strong>
    </div>
<?php } ?>

<?php if (!empty($row['color'])) {

    $colorName = strtolower(trim($row['color']));

    $colorMap = [
        "black"      => "#000000",
        "white"      => "#ffffff",
        "red"        => "#ff0000",
        "blue"       => "#0066ff",
        "green"      => "#28a745",
        "yellow"     => "#ffd700",
        "orange"     => "#ff7f00",
        "pink"       => "#ff69b4",
        "purple"     => "#800080",
        "brown"      => "#8b4513",
        "grey"       => "#808080",
        "gray"       => "#808080",
        "navy"       => "#001f54",
        "maroon"     => "#800000",
        "olive"      => "#808000",
        "sky blue"   => "#87ceeb",
        "beige"      => "#f5f5dc",
        "cream"      => "#fffdd0",
        "gold"       => "#d4af37",
        "silver"     => "#c0c0c0",
    ];

    $bg = $colorMap[$colorName] ?? "#cccccc";
?>

<div class="d-flex align-items-center gap-2 mt-1">
    <span>Color:</span>

    <span style="
        width:16px;
        height:16px;
        border-radius:50%;
        border:1px solid #999;
        background:<?= $bg ?>;
        display:inline-block;
    "></span>

    <strong><?= htmlspecialchars($row['color']); ?></strong>
</div>

<?php } ?>

                    </div>

                    <div class="fw-bold js-price"
                      data-price-inr="<?= $unitPrice; ?>">
                      ₹<?php echo number_format($total, 2); ?>
                    </div>

                  </div>

                  <div class="qty-box" style="padding: 0px 10px;">

                    <a href="cart-update.php?id=<?= $row['cart_id'] ?>&action=minus"
                      class="qty-btn">-</a>

                    <input type="text"
                      value="<?php echo $row['qty']; ?>"
                      readonly>

                    <a href="cart-update.php?id=<?= $row['cart_id'] ?>&action=plus"
                      class="qty-btn">+</a>

                  </div>

                  <div class="text-end">

                    <div class="fw-bold" data-price-inr="<?= $total; ?>">
                      ₹<?php echo number_format($total); ?>
                    </div>

                    <a href="cart-delete.php?id=<?= $row['cart_id'] ?>"
                      class="text-danger">

                      <i class="bi bi-trash"></i>

                    </a>


                  </div>

                </div>

              <?php } ?>

            <?php } else { ?>

              <script>
                document.getElementById("emptyCart").classList.remove("d-none");

                document.getElementById("cartWrap").classList.add("d-none");
              </script>

            <?php } ?>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card-panel">
            <h2 class="h6 mb-3">Order Summary</h2>
            <div class="d-flex justify-content-between small mb-2">
              <span>Subtotal</span>

              <span class="js-price" data-price-inr="<?= $subtotal; ?>">
                ₹<?php echo number_format($subtotal, 2); ?>
              </span>
            </div>
            <div class="d-flex justify-content-between small mb-2 b2c-only d-none">
              <span>Estimated Shipping</span>
              <span>Free</span>
            </div>
            <?php
            $gst = $subtotal * 18 / 100;
            ?>

            <hr>
            <?php
            $total = $subtotal;
            ?>
            <div class="d-flex justify-content-between fw-bold mb-3">
              <span>Total</span>
              <span class="js-price" data-price-inr="<?= $total; ?>">
                ₹<?php echo number_format($total, 2); ?>
              </span>
            </div>

            <?php if (isset($_SESSION['user_id'])) { ?>

              <a href="checkout" class="btn btn-brand w-100 text-white">
                Proceed to Checkout
              </a>

            <?php } else { ?>

              <a href="login.php?redirect=checkout" class="btn btn-brand w-100 text-white">
                Proceed to Checkout
              </a>

            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ FOOTER ============ -->
  <?php include 'footer.php'; ?>



</body>


</html>